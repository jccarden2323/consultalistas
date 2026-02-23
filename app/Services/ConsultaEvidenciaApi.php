<?php 

namespace App\Services;

use Illuminate\Support\Facades\Http;


class ConsultaEvidenciaApi 
{
    private $baseUrl = "https://dash-board.tusdatos.co/api";

    public function consultaEvidencia($dest, $fuente)
    {
        try {
            $url = "{$this->baseUrl}/{$dest}/{$fuente}.jpg";

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Basic bHVpc2FsYmVydG9fdmxAY29vdHJhbnNub3JjYWxkYXMuY29tOktyb25vczUwMDc1IQ==',
                    'Content-Type' => 'application/json',
                ])
                ->get($url);

            if ($response->successful()) {
                return $response->json();
            } else {
                return [
                    'error' => true,
                    'status' => $response->status(),
                    'message' => $response->body(),
                ];
            }

        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => 'Error al consultar estado: ' . $e->getMessage(),
            ];
        }
    }
}