<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TokenManager
{
    protected $username;
    protected $password;
    protected $apiBase;
    protected $cacheKey = 'facturoporti_token';

    public function __construct()
    {
        $this->username = 'PruebasTimbrado';
        $this->password = '@Notiene1';
        $this->apiBase  = 'https://testapi.facturoporti.com.mx';
    }

    public function getToken()
    {
        // Reutilizar token si existe en caché
        if (Cache::has($this->cacheKey)) {
            $cached = Cache::get($this->cacheKey);
            Log::info('🔄 Token recuperado desde caché.');
            return $cached;
        }

        // Solicita nuevo token
        $response = Http::get($this->apiBase . '/token/crear', [
            'Usuario'  => $this->username,
            'Password' => $this->password
        ]);

        if ($response->successful()) {
            $data = $response->json();

            if (!empty($data['token'])) {
                Cache::put($this->cacheKey, $data['token'], now()->addMinutes(59));
                Log::info('✅ Nuevo token obtenido. Expira en: ' . now()->addMinutes(59)->toDateTimeString());
                return $data['token'];
            }

            Log::error('❌ Token recibido sin campo válido.', ['response' => $data]);
        } else {
            Log::error('❌ Error HTTP al obtener token:', [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);
        }

        return null;
    }

    public function deleteToken()
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->delete($this->apiBase . '/token/borrar', [
            'Usuario'  => $this->username,
            'Password' => $this->password
        ]);

        Cache::forget($this->cacheKey);

        if ($response->successful()) {
            Log::info('✅ Token eliminado correctamente.');
        } else {
            Log::warning('⚠️ Falló la eliminación del token.', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        return $response->ok();
    }

    public function clearCache()
    {
        Cache::forget($this->cacheKey);
        Log::info('🧹 Cache del token eliminada manualmente.');
    }
}
