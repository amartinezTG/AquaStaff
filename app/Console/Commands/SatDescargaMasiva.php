<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\CfdiPackageReader;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\Fiel;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\FielRequestBuilder;
use PhpCfdi\SatWsDescargaMasiva\Service;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\WebClient\GuzzleWebClient;

class SatDescargaMasiva extends Command
{
    protected $signature = 'sat:descarga
        {accion : solicitar | verificar | acomodar}
        {--inicio= : Fecha inicio (Y-m-d) para solicitar}
        {--fin= : Fecha fin (Y-m-d) para solicitar}';

    protected $description = 'Descarga masiva de CFDIs emitidos desde el SAT (FIEL AquaCar) para recuperar XMLs faltantes';

    private string $zipsDir;
    private string $cfdisDir;

    public function __construct()
    {
        parent::__construct();
        $this->zipsDir  = storage_path('app/sat_descarga/zips');
        $this->cfdisDir = storage_path('app/sat_descarga/cfdis');
    }

    public function handle(): int
    {
        $accion = $this->argument('accion');

        return match ($accion) {
            'solicitar' => $this->solicitar(),
            'verificar' => $this->verificar(),
            'acomodar'  => $this->acomodar(),
            default     => $this->errorReturn("Acción no válida: {$accion}. Usa solicitar, verificar o acomodar."),
        };
    }

    private function errorReturn(string $msg): int
    {
        $this->error($msg);
        return self::FAILURE;
    }

    private function crearServicio(): Service
    {
        $fielDir = storage_path('app/fiel');
        $cer = glob($fielDir . '/*.cer')[0] ?? null;
        $key = glob($fielDir . '/*.key')[0] ?? null;
        $password = env('FIEL_PASSWORD');

        if (!$cer || !$key) {
            throw new \RuntimeException("No se encontró el .cer o .key en {$fielDir}");
        }
        if (!$password) {
            throw new \RuntimeException('Falta FIEL_PASSWORD en el .env');
        }

        $fiel = Fiel::create(
            file_get_contents($cer),
            file_get_contents($key),
            $password
        );

        if (!$fiel->isValid()) {
            throw new \RuntimeException('FIEL inválida (revisa contraseña, vigencia, o que no sea CSD)');
        }

        return new Service(new FielRequestBuilder($fiel), new GuzzleWebClient());
    }

    private function solicitar(): int
    {
        $inicio = $this->option('inicio');
        $fin    = $this->option('fin');

        if (!$inicio || !$fin) {
            return $this->errorReturn('Indica --inicio=Y-m-d y --fin=Y-m-d');
        }

        $service = $this->crearServicio();

        $query = QueryParameters::create()
            ->withPeriod(DateTimePeriod::createFromValues($inicio . ' 00:00:00', $fin . ' 23:59:59'))
            ->withDownloadType(DownloadType::issued())
            ->withRequestType(RequestType::xml());

        $result = $service->query($query);

        if (!$result->getStatus()->isAccepted()) {
            return $this->errorReturn('SAT rechazó la solicitud: ' . $result->getStatus()->getMessage());
        }

        DB::table('sat_descarga_peticiones')->insert([
            'request_id'   => $result->getRequestId(),
            'fecha_inicio' => $inicio,
            'fecha_fin'    => $fin,
            'estado'       => 'pendiente',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $this->info("Solicitud aceptada. RequestId: {$result->getRequestId()}");
        $this->line('El SAT tarda de minutos a horas en prepararla. Corre después: php artisan sat:descarga verificar');
        return self::SUCCESS;
    }

    private function verificar(): int
    {
        $pendientes = DB::table('sat_descarga_peticiones')->where('estado', 'pendiente')->get();

        if ($pendientes->isEmpty()) {
            $this->info('No hay peticiones pendientes.');
            return self::SUCCESS;
        }

        $service = $this->crearServicio();

        foreach ($pendientes as $p) {
            $verify = $service->verify($p->request_id);

            if (!$verify->getStatus()->isAccepted()) {
                $this->warn("[{$p->request_id}] Falló la verificación: " . $verify->getStatus()->getMessage());
                continue;
            }

            if (!$verify->getCodeRequest()->isAccepted()) {
                $msg = $verify->getCodeRequest()->getMessage();
                $this->warn("[{$p->request_id}] Rechazada por el SAT: " . $msg);
                DB::table('sat_descarga_peticiones')->where('id', $p->id)
                    ->update(['estado' => 'rechazada', 'mensaje_error' => $msg, 'updated_at' => now()]);
                continue;
            }

            $status = $verify->getStatusRequest();

            if ($status->isExpired() || $status->isFailure() || $status->isRejected()) {
                $this->warn("[{$p->request_id}] No se puede completar (expirada/fallida/rechazada).");
                DB::table('sat_descarga_peticiones')->where('id', $p->id)
                    ->update(['estado' => 'fallida', 'updated_at' => now()]);
                continue;
            }

            if ($status->isInProgress() || $status->isAccepted()) {
                $this->line("[{$p->request_id}] El SAT sigue procesándola, intenta más tarde.");
                continue;
            }

            if ($status->isFinished()) {
                $packageIds = $verify->getPackagesIds();
                $this->info("[{$p->request_id}] Lista. Paquetes: " . count($packageIds));

                $totalXmls = 0;
                foreach ($packageIds as $packageId) {
                    $download = $service->download($packageId);
                    if (!$download->getStatus()->isAccepted()) {
                        $this->warn("  No se pudo descargar {$packageId}: " . $download->getStatus()->getMessage());
                        continue;
                    }
                    $zipfile = $this->zipsDir . '/' . $packageId . '.zip';
                    file_put_contents($zipfile, $download->getPackageContent());
                    $this->line("  Paquete guardado: {$zipfile}");

                    $reader = CfdiPackageReader::createFromFile($zipfile);
                    $count = 0;
                    foreach ($reader->cfdis() as $uuid => $content) {
                        file_put_contents($this->cfdisDir . '/' . strtolower($uuid) . '.xml', $content);
                        $count++;
                    }
                    $totalXmls += $count;
                    $this->line("  XMLs extraídos: {$count}");
                }

                DB::table('sat_descarga_peticiones')->where('id', $p->id)->update([
                    'estado'         => 'descargada',
                    'paquetes'       => json_encode($packageIds),
                    'xmls_extraidos' => $totalXmls,
                    'updated_at'     => now(),
                ]);
            }
        }

        $this->line('Para colocar los XMLs recuperados en su lugar: php artisan sat:descarga acomodar');
        return self::SUCCESS;
    }

    private function acomodar(): int
    {
        $files = glob($this->cfdisDir . '/*.xml');
        if (empty($files)) {
            $this->info('No hay XMLs descargados en ' . $this->cfdisDir);
            return self::SUCCESS;
        }

        $xmlDsts = [
            storage_path('app/public/xmls/'),
            base_path('../facturacion_aqua/storage/app/public/xmls/'),
        ];
        $invoicesDir = storage_path('app/public/invoices/');

        $individuales = 0;
        $globales     = 0;
        $sinMatch     = 0;

        foreach ($files as $file) {
            $uuid = basename($file, '.xml');

            $tx = DB::table('local_transaction')
                ->whereRaw('LOWER(fiscal_invoice) = ?', [$uuid])
                ->first(['local_transaction_id', 'CadenaFacturacion']);

            if ($tx) {
                $name = $tx->CadenaFacturacion ?: 'IND_' . $tx->local_transaction_id;
                $placed = false;
                foreach ($xmlDsts as $dst) {
                    $target = $dst . $name . '.xml';
                    if (!file_exists($target) || filesize($target) === 0) {
                        copy($file, $target);
                        @chown($target, 'www-data');
                        @chgrp($target, 'www-data');
                        @chmod($target, 0644);
                        $placed = true;
                    }
                }
                if ($placed) {
                    $individuales++;
                    $this->line("Individual: {$name}.xml ({$uuid})");
                }
                continue;
            }

            $gi = DB::table('global_invoice')
                ->whereRaw('LOWER(uuid) = ?', [$uuid])
                ->first(['id', 'file_name', 'name']);

            if ($gi) {
                $name = $gi->file_name ?: $gi->name;
                $target = $invoicesDir . $name . '.xml';
                if ($name && (!file_exists($target) || filesize($target) === 0)) {
                    copy($file, $target);
                    @chown($target, 'www-data');
                    @chgrp($target, 'www-data');
                    @chmod($target, 0644);
                    $globales++;
                    $this->line("Global: {$name}.xml ({$uuid})");
                }
                continue;
            }

            $sinMatch++;
        }

        $this->info("XMLs colocados — individuales: {$individuales}, globales: {$globales}. Sin match en BD: {$sinMatch} (quedan en sat_descarga/cfdis).");
        return self::SUCCESS;
    }
}
