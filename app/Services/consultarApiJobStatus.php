<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Persona;

class ConsultarApiJobStatus
{
    //private $baseUrl = "https://docs.tusdatos.co/api";
    private $baseUrl = "https://dash-board.tusdatos.co/api";

    public function consultarJobId($jobid)
    {        
        try {
            $url = "{$this->baseUrl}/results/{$jobid}";

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

    public function obtenerReporte($jobid)
    {
        try {
            $url = "{$this->baseUrl}/report_json/{$jobid}";
    
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Basic bHVpc2FsYmVydG9fdmxAY29vdHJhbnNub3JjYWxkYXMuY29tOktyb25vczUwMDc1IQ==',
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



