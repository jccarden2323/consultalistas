<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\personas;
use App\Models\reportapi;
use App\Services\ConsultarApiJobStatus;
use App\Services\ProcesarReporteService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;

class VerificarJobStatus extends Command
{
    
    protected $signature = 'app:verificar-job-status';
    protected $description = 'Verifica el estado de los jobid y obtiene el reporte';

    protected $consultarApiJobStatus;
    protected $procesarReporteService;

    public function __construct(
        ConsultarApiJobStatus $consultarApiJobStatus,
        ProcesarReporteService $procesarReporteService)
    {
        parent::__construct();
        $this->consultarApiJobStatus = $consultarApiJobStatus;
        $this->procesarReporteService = $procesarReporteService;
    }

public function handle()
{
    $this->info("Proceso iniciado");

    $personas = Personas::where(function ($q) {
            $q->whereNotNull('jobidretry')
            ->orWhereNotNull('jobid');
        })
        ->where('estado', 'PENDIENTE')
        ->get();

    if ($personas->isEmpty()) {
        $this->info("No se encontraron registros pendientes para procesar.");
        Log::info("No hay registros pendientes");
        return Command::SUCCESS;
    }

    foreach ($personas as $persona) {

        try {
            $jobActivo = $persona->jobidretry ?? $persona->jobid;

            $this->info("Procesando persona con jobid {$jobActivo}");

            // Consultar estado del JOB
            $response = $this->consultarApiJobStatus->consultarJobId($jobActivo);
            
            $estado  = strtolower($response['estado'] ?? $response['task_estado'] ?? '');
            $idReporte = $response['id'] ?? null;

            // Guardar SIEMPRE el JSON del job
            reportapi::updateOrCreate(
                ['idreportdoc' => $persona->ppersonadoc],
                [
                    'estadojob'  => $estado,
                    'reportjson' => json_encode($response, JSON_UNESCAPED_UNICODE),
                    'fechareport'=> Carbon::now(),
                ]
            );

            if (!$idReporte) {
                Log::warning("Job finalizado pero sin ID de reporte", [
                    'jobid' => $jobActivo,
                    'response' => $response,
                ]);
                continue;
            }

            $persona->idreporte = $idReporte;
            $persona->save();
 
            if ($estado !== 'finalizado') {
                $this->info("Job {$jobActivo} aún no finaliza ({$estado})");
                continue;
            }

            Log::info("Consultando obtenerReporte()", [
                'jobid' => $jobActivo,
                'idReporte' => $idReporte
            ]);


            // Consultar reporte FINAL usando el ID
            $reporte = $this->consultarApiJobStatus->obtenerReporte($idReporte);
            $jsonCrudo = json_encode($reporte, JSON_UNESCAPED_UNICODE);

            reportapi::updateOrCreate(
                ['idreportdoc' => $persona->ppersonadoc],
                [
                    'estadojob' => 'finalizado',
                    'reportjson' => $jsonCrudo,
                    'fechareport' => Carbon::now(),
                ]
            );               

            // Procesar reporte
            $reporteProcesado = $this->procesarReporteService->procesarReporte($jsonCrudo);

            reportapi::updateOrCreate(
                ['idreportdoc' => $persona->ppersonadoc],
                [
                    'reportjsonprocesado' => json_encode($reporteProcesado, JSON_UNESCAPED_UNICODE),                    
                ]
            );

            if (($reporte['error'] ?? false) === true) {
                Log::info("Reporte con errores parciales", [
                    'jobid' => $jobActivo,
                    'errores' => $reporte['errores'] ?? [],
                ]);
            }

            $nombrePersona = $reporteProcesado['datos_persona']['nombre'] ?? null;
            
            if (!empty($nombrePersona) && empty($persona->personanombre)) {
                $persona->personanombre = $nombrePersona;
                $persona->validado = 1;
            }

            // Marcar persona como procesada 
            $persona->estado = 'PROCESADO';
            $persona->save();

            Log::info("Reporte final guardado correctamente", [
                'jobid' => $jobActivo,
                'id_reporte' => $idReporte
            ]);

        } catch (\Exception $e) {
            Log::error("Error procesando jobid {$jobActivo}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    return Command::SUCCESS;
}

}
