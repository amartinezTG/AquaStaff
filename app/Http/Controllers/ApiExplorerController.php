<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiExplorerController extends Controller
{
    private string $baseUrl = 'http://10.100.1.5';

    public function index()
    {
        $activePage = 'api_explorer';
        return view('configuracion.api_explorer', compact('activePage'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'client_id'     => 'required|string',
            'client_secret' => 'required|string',
        ]);

        $response = Http::timeout(10)->post("{$this->baseUrl}/api/LocalUser/LogIn", [
            'clientId'     => $request->client_id,
            'clientSecret' => $request->client_secret,
        ]);

        return response()->json($response->json(), $response->status());
    }

    public function call(Request $request)
    {
        $request->validate([
            'token'    => 'required|string',
            'method'   => 'required|in:GET,POST',
            'endpoint' => 'required|string',
        ]);

        $url  = $this->baseUrl . $request->endpoint;
        $body = $request->input('body');

        $http = Http::timeout(30)->withToken($request->token);

        $response = $request->method === 'POST'
            ? $http->post($url, $body ? json_decode($body, true) : [])
            : $http->get($url);

        return response()->json($response->json(), $response->status());
    }
}
