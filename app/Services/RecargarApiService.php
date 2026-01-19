<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\personas;
use Carbon\Carbon;

class RecargarApiService
{
    private $baseUrl = "https://dash-board.tusdatos.co/api";

    public function recargar(Personas $persona)
    {
        try {
            if (!$persona->idreporte) {
                throw new \Exception('La persona no tiene idreporte');
            }

            $url = "{$this->baseUrl}/retry/{$persona->idreporte}";

            Log::info('🔄 Reintentando consulta API', [
                'persona_id' => $persona->id,
                'idreporte'  => $persona->idreporte,
                'typedoc'    => $persona->ppersonatipodoc
            ]);

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Basic bHVpc2FsYmVydG9fdmxAY29vdHJhbnNub3JjYWxkYXMuY29tOktyb25vczUwMDc1IQ==',
                    'Content-Type' => 'application/json',
                ])
                ->get($url, [
                    'typedoc' => $persona->ppersonatipodoc
                ]);

        if (!$response->successful()) {
            Log::error('Error en retry API', [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);

            throw new \Exception('Error al reintentar la consulta');
        }

        $data = $response->json();

        if (isset($data['estado']) && !isset($data['jobid'])) {
            // Estado funcional (no hay fuentes para recargar)
            return [
                'info' => true,
                'mensaje' => $data['estado']
            ];
        }

        // Actualizamos el JOB ACTIVO
        $persona->update([
            'jobidretry' => $data['jobid'],
            'estado'     => 'PENDIENTE',
            'fecharetry' => Carbon::now(),
        ]);

        return $data;

        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => 'Error al consultar estado: ' . $e->getMessage(),
            ];
        }
    }    
}