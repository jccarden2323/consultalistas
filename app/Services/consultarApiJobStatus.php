<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Persona;

class ConsultarApiJobStatus
{
    private $baseUrl = "https://docs.tusdatos.co/api";

    public function consultarJobId($jobid)
    {        
        try {
            $url = "{$this->baseUrl}/results/{$jobid}";

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Basic cHJ1ZWJhczpwYXNzd29yZA==',
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

    public function obtenerReporte($jobid)
    {
        try {
            $url = "{$this->baseUrl}/report_json/{$jobid}";
    
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Basic cHJ1ZWJhczpwYXNzd29yZA==',
                    'Content-Type' => 'application/json',
                ])
                ->get($url);
    
            if ($response->successful()) {
                return $response->json();
            }
    
            return [
                'error' => true,
                'status' => $response->status(),
                'message' => $response->body(),
            ];
    
        } catch (\Exception $e) {
                return [
                'error' => true,
                'message' => 'Error al obtener el reporte: ' . $e->getMessage(),
            ];
        }
    }
}



