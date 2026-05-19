<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncNuevosClientes extends Command
{
    protected $signature   = 'sync:nuevos-clientes';
    protected $description = 'Detecta clientes nuevos en la API y los inserta en BD (corre cada 5 min)';

    private string $baseUrl    = 'http://10.100.1.5';
    private string $clientId   = '236168dd-fb41-46c9-9ac3-68a44d1cc68c';
    private string $clientSecret = '1ff2d909-ab5e-4f07-97aa-c7333d62ce9f';

    public function handle()
    {
        $start = now();
        $token = $this->getToken();
        if (!$token) {
            Log::error('[sync:nuevos-clientes] No se pudo obtener token de la API');
            return 1;
        }

        // IDs en la API
        $apiIds = Http::timeout(15)->withToken($token)
            ->get("{$this->baseUrl}/api/Data/ClientIds")
            ->json();

        if (!is_array($apiIds)) {
            Log::error('[sync:nuevos-clientes] Respuesta inválida de ClientIds');
            return 1;
        }

        // IDs activos en BD
        $bdIds = DB::table('clients')
            ->whereNull('deleted_at')
            ->whereNotNull('_id')
            ->pluck('_id')
            ->flip()
            ->all();

        $nuevos = array_filter($apiIds, fn($id) => !isset($bdIds[$id]));

        if (empty($nuevos)) {
            $this->info('[' . now()->format('H:i:s') . '] Sin clientes nuevos.');
            return 0;
        }

        $insertados = 0;
        $memInsertadas = 0;

        foreach ($nuevos as $cid) {
            $client = Http::timeout(15)->withToken($token)
                ->get("{$this->baseUrl}/api/Data/Client/{$cid}")
                ->json();

            if (!$client || !isset($client['_id'])) continue;

            // Insertar cliente
            $activeMem = $client['active_membership'] ?? [];
            $firstMemId = (is_array($activeMem) && count($activeMem) > 0 && isset($activeMem[0]['_id']))
                ? $activeMem[0]['_id'] : null;

            DB::table('clients')->insert([
                '_id'              => $client['_id'],
                'tag'              => $client['tag']      ?? '',
                'tag_code'         => $client['tagCode']  ?? '',
                'full_name'        => trim(($client['first_name'] ?? '') . ' ' . ($client['last_name'] ?? '')),
                'first_name'       => $client['first_name']  ?? '',
                'last_name'        => $client['last_name']   ?? '',
                'active_membership'=> $firstMemId,
                'email'            => $client['email']    ?? '',
                'phone'            => $client['phone']    ?? '',
                'plate'            => $client['plate']    ?? '',
                'brand'            => $client['brand']    ?? '',
                'model'            => $client['model']    ?? '',
                'color'            => $client['color']    ?? '',
                'is_recurrent'     => $client['is_recurrent'] ? 'true' : 'false',
                'renewal_count'    => $client['renewal_count'] ?? 0,
                'facility'         => 'AQUA.JRZ.PROD.LOCAL',
                'prosepago_id'     => $client['prosepago_id'] ?? null,
                'banco'            => $client['banco']    ?? null,
                'tipoTarjeta'      => $client['tipoTarjeta'] ?? null,
                'titular'          => $client['titular']  ?? '',
                'IsSync'           => 'false',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
            $insertados++;

            // Insertar membresías del cliente
            foreach ($activeMem as $mem) {
                if (!isset($mem['_id'])) continue;

                $memDetail = Http::timeout(15)->withToken($token)
                    ->get("{$this->baseUrl}/api/Data/ClientMembership/{$mem['_id']}")
                    ->json();

                if (!$memDetail || !isset($memDetail['_id'])) continue;

                $exists = DB::table('client_membership')
                    ->where('_id', $memDetail['_id'])
                    ->whereNull('deleted_at')
                    ->exists();

                if (!$exists) {
                    DB::table('client_membership')->insert([
                        '_id'            => $memDetail['_id'],
                        'membership_id'  => $memDetail['membership_id'] ?? null,
                        'client_id'      => $memDetail['client_id']     ?? $cid,
                        'start_date'     => $this->parseDate($memDetail['start_date'] ?? null),
                        'end_date'       => $this->parseDate($memDetail['end_date']   ?? null),
                        'extension_days' => $memDetail['extension_days'] ?? 0,
                        'prosepago_id'   => $memDetail['prosepago_id']   ?? 0,
                        'months'         => $memDetail['months']         ?? 1,
                        'uses'           => $memDetail['uses']           ?? 0,
                        'facility'       => $memDetail['facility']       ?? 'AQUA.JRZ.PROD.LOCAL',
                        'isBlocked'      => $memDetail['isBlocked'] ? 1 : 0,
                        'is_blocked'     => $memDetail['isBlocked'] ? 1 : 0,
                        '__v'            => $memDetail['__v']            ?? 0,
                        'IsSync'         => $memDetail['isSync'] ? 'true' : 'false',
                        'lastSync'       => $this->parseDate($memDetail['lastSync'] ?? null),
                        'created_at'     => $this->parseDate($memDetail['created_at'] ?? null) ?? now(),
                        'updated_at'     => now(),
                    ]);
                    $memInsertadas++;
                }
            }
        }

        $elapsed = now()->diffInSeconds($start);
        $msg = "[sync:nuevos-clientes] Clientes insertados: {$insertados}, membresías: {$memInsertadas} ({$elapsed}s)";
        $this->info($msg);
        Log::info($msg);

        return 0;
    }

    private function getToken(): ?string
    {
        $r = Http::timeout(10)->post("{$this->baseUrl}/api/LocalUser/LogIn", [
            'clientId'     => $this->clientId,
            'clientSecret' => $this->clientSecret,
        ]);
        return $r->json('data.authToken');
    }

    private function parseDate(?string $d): ?string
    {
        if (!$d || str_starts_with($d, '0001')) return null;
        return substr($d, 0, 19) === $d ? $d : substr(str_replace('T', ' ', $d), 0, 19);
    }
}
