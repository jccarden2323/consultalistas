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

    $personas = Personas::whereNotNull('jobid')
        ->where('estado', 'PENDIENTE')
        ->get();

    if ($personas->isEmpty()) {
        $this->info("No se encontraron registros pendientes para procesar.");
        Log::info("No hay registros pendientes");
        return Command::SUCCESS;
    }

    foreach ($personas as $persona) {

        try {

            $this->info("Procesando persona con jobid {$persona->jobid}");

            // 1️⃣ Consultar estado del JOB
            $response = $this->consultarApiJobStatus->consultarJobId($persona->jobid);
            
            $estado  = strtolower($response['estado'] ?? $response['task_estado'] ?? '');
            $idReporte = $response['id'] ?? null;

            // 2️⃣ Guardar SIEMPRE el JSON del job
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
                    'jobid' => $persona->jobid,
                    'response' => $response,
                ]);
                continue;
            }

            $persona->idreporte = $idReporte;
            $persona->save();
 
            if ($estado !== 'finalizado') {
                $this->info("Job {$persona->jobid} aún no finaliza ({$estado})");
                continue;
            }

            Log::info("Consultando obtenerReporte()", [
                'jobid' => $persona->jobid,
                'idReporte' => $idReporte
            ]);


            // 4️⃣ Consultar reporte FINAL usando el ID
            $reporte = $this->consultarApiJobStatus->obtenerReporte($idReporte);
            $jsonCrudo = json_encode($reporte, JSON_UNESCAPED_UNICODE);

            //Log::info("Reporte recibido", ['id' => $reportId]);

            reportapi::updateOrCreate(
                ['idreportdoc' => $persona->ppersonadoc],
                [
                    'estadojob' => 'finalizado',
                    'reportjson' => $jsonCrudo,
                    'fechareport' => Carbon::now(),
                ]
            );               

            // 6️⃣ Procesar reporte
            $reporteProcesado = $this->procesarReporteService->procesarReporte($jsonCrudo);

            reportapi::updateOrCreate(
                ['idreportdoc' => $persona->ppersonadoc],
                [
                    'reportjsonprocesado' => json_encode($reporteProcesado, JSON_UNESCAPED_UNICODE),                    
                ]
            );

            if (($reporte['error'] ?? false) === true) {
                Log::info("Reporte con errores parciales", [
                    'jobid' => $persona->jobid,
                    'errores' => $reporte['errores'] ?? [],
                ]);
            }


            // 7️⃣ Marcar persona como procesada
            $persona->estado = 'PROCESADO';
            $persona->save();

            Log::info("Reporte final guardado correctamente", [
                'jobid' => $persona->jobid,
                'id_reporte' => $idReporte
            ]);

        } catch (\Exception $e) {
            Log::error("Error procesando jobid {$persona->jobid}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    return Command::SUCCESS;
}

}
