<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\personas;
use App\Models\reportapi;
use App\Services\ConsultarApiJobStatus;
use App\Services\ProcesarReporteService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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

        // $horaActual = Carbon::now();
        // $horaHaceDosMinutos = Carbon::now()->subMinutes(10);

        $personas = Personas::whereNotNull('jobid')
            //->whereBetween('fechasolicitud',[$horaHaceDosMinutos, $horaActual])
            ->where('estado', 'PENDIENTE')
            ->get();

        // Validación cuando NO hay registros
        if ($personas->isEmpty()) {
            $this->info("No se encontraron registros pendientes para procesar.");
            Log::info("No hay registros pendientes");
            return Command::SUCCESS;
        }

        $this->info("Se encontraron registros");

        foreach($personas as $persona) {
            
            try {
                $this->info("Procesando persona con jobid {$persona->jobid}");

                $response = $this->consultarApiJobStatus->consultarJobId("6460fc34-4154-43db-9438-8c5a059304c0");

                $isError = ($response['error'] ?? false) === true;
                $estado = strtolower($response['estado'] ?? $response['task_estado'] ?? '');

                reportapi::updateOrCreate(
                    ['idreportdoc' => $persona->ppersonadoc],
                    [
                        'estadojob'    => $isError ? 'error' : $estado,
                        'reportjson' => is_string($response) ? $response : json_encode($response, JSON_UNESCAPED_UNICODE),
                        'fechareport'   => Carbon::now(),
                    ]
                );

                if ($isError) {
                    Log::warning("VerificarJobStatus: error en consulta jobid {$persona->jobid}", [
                        'idreportdoc' => $persona->ppersonadoc,
                        'jobid' => $persona->jobid,
                        'response' => $response,
                    ]);
                    continue;
                }

                if ($estado !== 'finalizado') {
                    $this->info("Jobid {$persona->jobid} aún no finaliza (estado: {$estado}). Estado guardado en reportapi.");
                    continue;
                }

                $reporte = $this->consultarApiJobStatus->obtenerReporte($persona->jobid);
                $jsonCrudo = json_encode($reporte, JSON_UNESCAPED_UNICODE);
                Log::info("JSON recibido para procesar", ['json' => $jsonCrudo]);

                if (($reporte['error'] ?? false) === true) {
                    Log::warning("VerificarJobStatus: error al obtener reporte para jobid {$persona->jobid}", [
                        'idreportdoc' => $persona->ppersonadoc,
                        'jobid' => $persona->jobid,
                        'response' => $reporte,
                    ]);

                    reportapi::updateOrCreate(
                        ['idreportdoc' => $persona->ppersonadoc],
                        [
                            'estadojob'    => 'finalizado_error_report',
                            'reportjson' => is_string($reporte) ? $reporte : json_encode($reporte, JSON_UNESCAPED_UNICODE),
                            'fechareport'   => Carbon::now(),
                        ]
                    );
                    continue;
                }

                $reporteProcesado = $this->procesarReporteService->procesarReporte($jsonCrudo);
                Log::info("Procesado OK", ['procesado' => $reporteProcesado]);
                
                reportapi::updateOrCreate(
                    ['idreportdoc' => $persona->ppersonadoc],
                    [
                        'estadojob' => 'finalizado',
                        'reportjson' => json_encode($reporte, JSON_UNESCAPED_UNICODE),
                        'reportjsonprocesado' => json_encode($reporteProcesado, JSON_UNESCAPED_UNICODE),
                        'fechareport' => Carbon::now(),
                    ]
                );

                $persona->estado = 'PROCESADO';
                $persona->save();

                Log::info("VerificarJobStatus: reporte final guardado y persona marcada PROCESADO (jobid {$persona->jobid})");

            } catch (\Exception $e) {
                Log::error("Error procesando jobid {$persona->jobid}: " . $e->getMessage());
                continue;                
            }
        }
        return Command::SUCCESS;
    }
}
