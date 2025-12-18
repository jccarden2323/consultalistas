<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AntecedentesApiService
{
    public function enviarPersona($doc, $tipoId)
    {
        try{
            
            $url = 'https://docs.tusdatos.co/api/launch';

            // Estructura del cuerpo (la API requiere un array con objetos)
            $payload = [
                'doc' => (string) $doc,
                'typedoc' => (string) $tipoId,
            ];

            if (!empty($fechaExpedicion)) {
                $payload['fechaE'] = $fechaExpedicion;                
            }
   
            $response = Http::withoutVerifying()
             ->withHeaders([
                'Authorization' => 'Basic cHJ1ZWJhczpwYXNzd29yZA==',
                'Content-Type' => 'application/json',
            ])
            ->post($url, $payload);
 
            // Verifica si la petición fue exitosa
            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['jobid'])) {
                    return $data;
                } else {
                    return [
                        'error' => true,
                        'message' => 'Respuesta inesperada de la API',
                        'response' => $data
                    ];
                } 
                
            } else {
                return [
                    'error' => true,
                    'status' => $response->status(),
                    'message' => $response->body()
                ];
            }

        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => 'Error al consumir la API: ' . $e->getMessage()
            ];
        }
    }
}