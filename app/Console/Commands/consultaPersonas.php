<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\personas;
use App\Services\AntecedentesApiService;
use Illuminate\Support\Facades\Log;

class consultaPersonas extends Command
{
    protected $signature = 'app:consulta-personas';
    protected $description = 'Consulta personas pendientes por procesar';

    protected $apiService;

    public function __construct(AntecedentesApiService $apiService)
    {
        parent::__construct();
        $this->apiService = $apiService;
    }

    public function handle()
    {
        $this->info("Proceso iniciado");

        try 
        {
            $personas = Personas::where('estado', 'NO')
                ->get();

            if ($personas->isEmpty()) {
                $this->info("No hay personas pendientes por procesar.");
                Log::info("consulta-personas: No hay registros con estado NO.");
                return Command::SUCCESS;
            }

            $resultados = [];

            foreach ($personas as $persona) {
                $this->info("Procesando persona con documento {$persona->ppersonadoc}");
                $fechaExpedicion = $persona->fechaexpedicion ?: null;

                $response = $this->apiService->enviarPersona(
                    $persona->ppersonadoc,
                    $persona->ppersonatipodoc,
                    $fechaExpedicion ?? null
                );

                if (isset($response['error']) && $response['error'] === true) {
                    $resultados[] = [
                        'id' => $persona->ppersonadoc,
                        'nombre' => $persona->nombre,
                        'error' => $response['message'] ?? $response
                    ];
                    continue;
                } else {
                    $persona->personanombre = $response['nombre'] ?? $persona->nombre;
                    $persona->correo = $response['email'] ?? $persona->correo;            
                    $persona->jobid = $response['jobid'] ?? null;
                    $persona->validado = $response['validado'] ?? null;    
                    $persona->estado = 'PENDIENTE';
                    $persona->save();

                    $this->info("Persona {$persona->ppersonadoc} actualizada con jobid {$persona->jobid}");
    
                    $resultados[] = [
                        'documento' => $persona->ppersonadoc,
                        'nombre' => $persona->personanombre,
                        'jobid' => $persona->jobid,
                        'estado' => 'PROCESADO',
                    ];
                }
            }

            $this->info("Proceso realizado con éxito.");
            $this->info(json_encode($resultados, JSON_PRETTY_PRINT));
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error al procesar: " . $e->getMessage());
            Log::error("consulta-personas error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
