<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncActualizarClientes extends Command
{
    protected $signature   = 'sync:actualizar-clientes';
    protected $description = 'Sincronización completa: actualiza clientes y membresías, soft-delete los que ya no están en API (2:30pm y 11pm)';

    private string $baseUrl      = 'http://10.100.1.5';
    private string $clientId     = '236168dd-fb41-46c9-9ac3-68a44d1cc68c';
    private string $clientSecret = '1ff2d909-ab5e-4f07-97aa-c7333d62ce9f';

    private int $clientesActualizados = 0;
    private int $clientesInsertados   = 0;
    private int $clientesSoftDeleted  = 0;
    private int $memActualizadas      = 0;
    private int $memInsertadas        = 0;
    private int $memSoftDeleted       = 0;

    public function handle()
    {
        $start = now();
        $this->info('[sync:actualizar-clientes] Iniciando...');

        $token = $this->getToken();
        if (!$token) {
            Log::error('[sync:actualizar-clientes] No se pudo obtener token');
            return 1;
        }

        // ── Paso 1: Comparar IDs ──────────────────────────────────────
        $apiIds = Http::timeout(15)->withToken($token)
            ->get("{$this->baseUrl}/api/Data/ClientIds")
            ->json();

        if (!is_array($apiIds)) {
            Log::error('[sync:actualizar-clientes] Respuesta inválida de ClientIds');
            return 1;
        }

        $apiIdSet = array_flip($apiIds);

        $bdClients = DB::table('clients')
            ->whereNull('deleted_at')
            ->whereNotNull('_id')
            ->pluck('_id')
            ->all();
        $bdIdSet = array_flip($bdClients);

        $soloEnApi = array_diff_key($apiIdSet, $bdIdSet);
        $soloEnBd  = array_diff_key($bdIdSet,  $apiIdSet);

        $this->info('API: ' . count($apiIds) . ' | BD: ' . count($bdClients) .
                    ' | Nuevos: ' . count($soloEnApi) . ' | Solo BD: ' . count($soloEnBd));

        // ── Paso 2: Soft delete clientes que ya no están en API ───────
        if (!empty($soloEnBd)) {
            DB::table('clients')
                ->whereIn('_id', array_keys($soloEnBd))
                ->whereNull('deleted_at')
                ->update(['deleted_at' => now()]);
            $this->clientesSoftDeleted = count($soloEnBd);
        }

        // ── Paso 3: Fetch paralelo de todos los clientes ──────────────
        $clientsDetail   = [];
        $membershipsDetail = [];

        $chunks = array_chunk($apiIds, 50);
        foreach ($chunks as $chunk) {
            $responses = [];
            foreach ($chunk as $cid) {
                $responses[$cid] = Http::timeout(15)->withToken($token)
                    ->async()
                    ->get("{$this->baseUrl}/api/Data/Client/{$cid}");
            }
            foreach ($responses as $cid => $promise) {
                try {
                    $data = $promise->wait()->json();
                    if ($data && isset($data['_id'])) {
                        $clientsDetail[$cid] = $data;
                    }
                } catch (\Exception $e) {
                    // continuar
                }
            }
        }

        $this->info('Clientes obtenidos de API: ' . count($clientsDetail));

        // ── Paso 4: Actualizar / insertar clientes ────────────────────
        $bdData = DB::table('clients')
            ->whereNull('deleted_at')
            ->whereNotNull('_id')
            ->get(['_id','first_name','last_name','email','phone','plate',
                   'brand','model','color','is_recurrent','prosepago_id','banco','titular',
                   'tag','tag_code'])
            ->keyBy('_id');

        foreach ($clientsDetail as $cid => $c) {
            if (isset($bdIdSet[$cid])) {
                // UPDATE si hay diferencias
                $row     = $bdData[$cid] ?? null;
                $changes = $this->diffCliente($c, $row);
                if (!empty($changes)) {
                    $changes['updated_at'] = now();
                    DB::table('clients')->where('_id', $cid)->whereNull('deleted_at')->update($changes);
                    $this->clientesActualizados++;
                }
            } else {
                // INSERT nuevo
                $this->insertarCliente($c);
                $this->clientesInsertados++;
            }
        }

        // ── Paso 5: Recopilar membresías únicas ───────────────────────
        $memIdsNeeded = [];
        foreach ($clientsDetail as $c) {
            foreach ($c['active_membership'] ?? [] as $m) {
                if (isset($m['_id'])) $memIdsNeeded[$m['_id']] = true;
            }
        }

        $this->info('Membresías a sincronizar: ' . count($memIdsNeeded));

        // Fetch membresías en chunks
        $chunks = array_chunk(array_keys($memIdsNeeded), 50);
        foreach ($chunks as $chunk) {
            $responses = [];
            foreach ($chunk as $mid) {
                $responses[$mid] = Http::timeout(15)->withToken($token)
                    ->async()
                    ->get("{$this->baseUrl}/api/Data/ClientMembership/{$mid}");
            }
            foreach ($responses as $mid => $promise) {
                try {
                    $data = $promise->wait()->json();
                    if ($data && isset($data['_id'])) {
                        $membershipsDetail[$mid] = $data;
                    }
                } catch (\Exception $e) {
                    // continuar
                }
            }
        }

        // ── Paso 6: Actualizar / insertar membresías ──────────────────
        $bdMems = DB::table('client_membership')
            ->whereNull('deleted_at')
            ->pluck('_id')
            ->flip()
            ->all();

        foreach ($membershipsDetail as $mid => $m) {
            if (isset($bdMems[$mid])) {
                $changes = $this->diffMembresia($m);
                if (!empty($changes)) {
                    $changes['updated_at'] = now();
                    DB::table('client_membership')->where('_id', $mid)->whereNull('deleted_at')->update($changes);
                    $this->memActualizadas++;
                }
            } else {
                $this->insertarMembresia($m);
                $this->memInsertadas++;
            }
        }

        // ── Paso 7: Soft delete membresías que ya no están en API ─────
        $apiMemIds = array_keys($membershipsDetail);
        $softDelCount = DB::table('client_membership')
            ->whereNull('deleted_at')
            ->whereNotIn('_id', $apiMemIds)
            ->update(['deleted_at' => now()]);
        $this->memSoftDeleted = $softDelCount;

        // ── Resumen ───────────────────────────────────────────────────
        $elapsed = now()->diffInSeconds($start);
        $msg = "[sync:actualizar-clientes] Completado en {$elapsed}s | " .
               "Clientes → insertados: {$this->clientesInsertados}, " .
               "actualizados: {$this->clientesActualizados}, " .
               "soft-deleted: {$this->clientesSoftDeleted} | " .
               "Membresías → insertadas: {$this->memInsertadas}, " .
               "actualizadas: {$this->memActualizadas}, " .
               "soft-deleted: {$this->memSoftDeleted}";

        $this->info($msg);
        Log::info($msg);

        return 0;
    }

    private function diffCliente(array $api, $bd): array
    {
        if (!$bd) return [];
        $changes = [];
        $map = [
            'first_name'   => 'first_name',
            'last_name'    => 'last_name',
            'email'        => 'email',
            'phone'        => 'phone',
            'plate'        => 'plate',
            'brand'        => 'brand',
            'model'        => 'model',
            'color'        => 'color',
            'prosepago_id' => 'prosepago_id',
            'banco'        => 'banco',
            'titular'      => 'titular',
            'tag'          => 'tag',
            'tagCode'      => 'tag_code',
        ];
        $maxLengths = ['tag_code' => 20, 'tag' => 200];
        foreach ($map as $ak => $bk) {
            $av = trim($api[$ak] ?? '');
            $bv = trim($bd->$bk ?? '');
            if (isset($maxLengths[$bk]) && strlen($av) > $maxLengths[$bk]) continue;
            if ($av && strtolower($av) !== strtolower($bv)) {
                $changes[$bk] = $av;
                if ($bk === 'first_name' || $bk === 'last_name') {
                    $changes['full_name'] = trim(($api['first_name'] ?? '') . ' ' . ($api['last_name'] ?? ''));
                }
            }
        }
        $apiRec = $api['is_recurrent'] ? 'true' : 'false';
        if ($apiRec !== strtolower(trim($bd->is_recurrent ?? ''))) {
            $changes['is_recurrent'] = $apiRec;
        }
        return $changes;
    }

    private function diffMembresia(array $m): array
    {
        $changes = [];
        $bd = DB::table('client_membership')->where('_id', $m['_id'])->whereNull('deleted_at')->first();
        if (!$bd) return [];
        if (($m['prosepago_id'] ?? 0) != ($bd->prosepago_id ?? 0))
            $changes['prosepago_id'] = $m['prosepago_id'] ?? 0;
        if (($m['uses'] ?? 0) != ($bd->uses ?? 0))
            $changes['uses'] = $m['uses'] ?? 0;
        if (($m['extension_days'] ?? 0) != ($bd->extension_days ?? 0))
            $changes['extension_days'] = $m['extension_days'] ?? 0;
        $startDate = $this->parseDate($m['start_date'] ?? null);
        if ($startDate && $startDate !== $bd->start_date)
            $changes['start_date'] = $startDate;
        $endDate = $this->parseDate($m['end_date'] ?? null);
        if ($endDate && $endDate !== $bd->end_date)
            $changes['end_date'] = $endDate;
        $blocked = $m['isBlocked'] ? 1 : 0;
        if ($blocked != ($bd->isBlocked ?? 0)) {
            $changes['isBlocked']  = $blocked;
            $changes['is_blocked'] = $blocked;
        }
        $isSync = $m['isSync'] ? 'true' : 'false';
        if ($isSync !== strtolower(trim($bd->IsSync ?? '')))
            $changes['IsSync'] = $isSync;
        $lastSync = $this->parseDate($m['lastSync'] ?? null);
        if ($lastSync && $lastSync !== $bd->lastSync) $changes['lastSync'] = $lastSync;
        return $changes;
    }

    private function insertarCliente(array $c): void
    {
        $activeMem = $c['active_membership'] ?? [];
        $firstMemId = (count($activeMem) > 0 && isset($activeMem[0]['_id'])) ? $activeMem[0]['_id'] : null;
        DB::table('clients')->insert([
            '_id'              => $c['_id'],
            'tag'              => $c['tag']      ?? '',
            'tag_code'         => $c['tagCode']  ?? '',
            'full_name'        => trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? '')),
            'first_name'       => $c['first_name']   ?? '',
            'last_name'        => $c['last_name']    ?? '',
            'active_membership'=> $firstMemId,
            'email'            => $c['email']    ?? '',
            'phone'            => $c['phone']    ?? '',
            'plate'            => $c['plate']    ?? '',
            'brand'            => $c['brand']    ?? '',
            'model'            => $c['model']    ?? '',
            'color'            => $c['color']    ?? '',
            'is_recurrent'     => $c['is_recurrent'] ? 'true' : 'false',
            'renewal_count'    => $c['renewal_count'] ?? 0,
            'facility'         => 'AQUA.JRZ.PROD.LOCAL',
            'prosepago_id'     => $c['prosepago_id'] ?? null,
            'banco'            => $c['banco']    ?? null,
            'tipoTarjeta'      => $c['tipoTarjeta'] ?? null,
            'titular'          => $c['titular']  ?? '',
            'IsSync'           => 'false',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    private function insertarMembresia(array $m): void
    {
        DB::table('client_membership')->insert([
            '_id'            => $m['_id'],
            'membership_id'  => $m['membership_id'] ?? null,
            'client_id'      => $m['client_id']     ?? null,
            'start_date'     => $this->parseDate($m['start_date'] ?? null),
            'end_date'       => $this->parseDate($m['end_date']   ?? null),
            'extension_days' => $m['extension_days'] ?? 0,
            'prosepago_id'   => $m['prosepago_id']   ?? 0,
            'months'         => $m['months']         ?? 1,
            'uses'           => $m['uses']           ?? 0,
            'facility'       => $m['facility']       ?? 'AQUA.JRZ.PROD.LOCAL',
            'isBlocked'      => $m['isBlocked'] ? 1 : 0,
            'is_blocked'     => $m['isBlocked'] ? 1 : 0,
            '__v'            => $m['__v']            ?? 0,
            'IsSync'         => $m['isSync'] ? 'true' : 'false',
            'lastSync'       => $this->parseDate($m['lastSync'] ?? null),
            'created_at'     => $this->parseDate($m['created_at'] ?? null) ?? now(),
            'updated_at'     => now(),
        ]);
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
        return substr(str_replace('T', ' ', $d), 0, 19);
    }
}
