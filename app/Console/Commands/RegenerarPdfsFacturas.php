<?php

namespace App\Console\Commands;

use CfdiUtils\Nodes\XmlNodeUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpCfdi\CfdiCleaner\Cleaner;
use PhpCfdi\CfdiToPdf\Builders\Html2PdfBuilder;
use PhpCfdi\CfdiToPdf\CfdiDataBuilder;
use PhpCfdi\CfdiToPdf\Converter;

class RegenerarPdfsFacturas extends Command
{
    protected $signature = 'facturas:regenerar-pdfs {--limit=0 : Máximo de PDFs a generar (0 = sin límite)}';

    protected $description = 'Regenera los PDF faltantes (representación impresa genérica) a partir del XML timbrado';

    public function handle(): int
    {
        $converter = new Converter(new Html2PdfBuilder());
        $limit     = (int) $this->option('limit');

        $staffPdfs    = storage_path('app/public/pdfs/');
        $staffXmls    = storage_path('app/public/xmls/');
        $staffInv     = storage_path('app/public/invoices/');
        $aquaPdfs     = base_path('../facturacion_aqua/storage/app/public/pdfs/');
        $aquaXmls     = base_path('../facturacion_aqua/storage/app/public/xmls/');

        $generados = 0;
        $fallidos  = 0;
        $sinXml    = 0;

        // ── Individuales ──
        $txs = DB::table('local_transaction')
            ->whereNotNull('fiscal_invoice')
            ->whereNull('deleted_at')
            ->get(['local_transaction_id', 'CadenaFacturacion']);

        foreach ($txs as $tx) {
            if ($limit > 0 && $generados >= $limit) {
                break;
            }

            $name = $tx->CadenaFacturacion ?: 'IND_' . $tx->local_transaction_id;

            $tienePdf = file_exists($staffPdfs . $name . '.pdf')
                || file_exists($staffInv . $name . '.pdf')
                || file_exists($aquaPdfs . $name . '.pdf');
            if ($tienePdf) {
                continue;
            }

            $xmlPath = file_exists($staffXmls . $name . '.xml') ? $staffXmls . $name . '.xml'
                : (file_exists($aquaXmls . $name . '.xml') ? $aquaXmls . $name . '.xml' : null);
            if (!$xmlPath) {
                $sinXml++;
                continue;
            }

            try {
                $tmp = sys_get_temp_dir() . '/' . $name . '.pdf';
                $this->convertir($converter, $xmlPath, $tmp);
                foreach ([$staffPdfs, $aquaPdfs] as $dst) {
                    copy($tmp, $dst . $name . '.pdf');
                    @chown($dst . $name . '.pdf', 'www-data');
                    @chgrp($dst . $name . '.pdf', 'www-data');
                    @chmod($dst . $name . '.pdf', 0644);
                }
                unlink($tmp);
                $generados++;
                $this->line("Individual: {$name}.pdf");
            } catch (\Throwable $e) {
                $fallidos++;
                $this->warn("Falló {$name}: " . $e->getMessage());
            }
        }

        // ── Globales ──
        $gis = DB::table('global_invoice')->whereNotNull('file_name')->get(['id', 'file_name']);

        foreach ($gis as $gi) {
            if ($limit > 0 && $generados >= $limit) {
                break;
            }

            $pdfPath = $staffInv . $gi->file_name . '.pdf';
            $xmlPath = $staffInv . $gi->file_name . '.xml';

            if (file_exists($pdfPath) || !file_exists($xmlPath)) {
                if (!file_exists($xmlPath) && !file_exists($pdfPath)) {
                    $sinXml++;
                }
                continue;
            }

            try {
                $this->convertir($converter, $xmlPath, $pdfPath);
                @chown($pdfPath, 'www-data');
                @chgrp($pdfPath, 'www-data');
                @chmod($pdfPath, 0644);
                $generados++;
                $this->line("Global: {$gi->file_name}.pdf");
            } catch (\Throwable $e) {
                $fallidos++;
                $this->warn("Falló global {$gi->file_name}: " . $e->getMessage());
            }
        }

        $this->info("PDFs generados: {$generados} | fallidos: {$fallidos} | sin XML disponible: {$sinXml}");
        return $fallidos > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function convertir(Converter $converter, string $xmlPath, string $pdfPath): void
    {
        $xml = Cleaner::staticClean(file_get_contents($xmlPath));
        $comprobante = XmlNodeUtils::nodeFromXmlString($xml);
        $cfdiData = (new CfdiDataBuilder())->build($comprobante);
        $converter->createPdfAs($cfdiData, $pdfPath);
    }
}
