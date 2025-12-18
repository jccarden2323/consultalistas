<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\personas;
use App\Models\reportapi;
use App\Services\AntecedentesApiService;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PersonasController extends Controller
{   
    protected $apiService;

    public function __construct(AntecedentesApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index()
    {
        $personas = Personas::orderBy('fechasolicitud', 'desc')
            ->limit(50)
            ->get();

        return view('personas.index', compact('personas'));
    }

    public function reporte($doc)
    {
        $reporte = Reportapi::where('idreportdoc', $doc)->first();

        if (!$reporte) {
            return redirect()->route('personas.index')
                ->with('error', 'No existe reporte para este documento');
        }
        
        $json = json_decode($reporte->reportjsonprocesado, true);
        //dd($json ?? 'No hay datos');

        return view('personas.reporte', compact('reporte', 'json'));
    }

    public function create()
    {
        return view('personas.crear');
    }
        
    public function store(Request $request)
    {
        $request->validate([
            'ppersonadoc' => 'required|numeric|unique:personas,ppersonadoc',
            'ppersonatipodoc' => 'required|string',
            'fechaexpedicion' => 'nullable|date'        
        ]);

        // Creación registro
        $persona = Personas::create([
            'ppersonadoc' => $request->ppersonadoc,
            'ppersonatipodoc' => $request->ppersonatipodoc,
            'fechaexpedicion' => $request->fechaexpedicion,
            'estado' => 'NO'
        ]);

        return redirect()
            ->route('personas.crear')
            ->with('success', 'Registro Exitoso');
    }    

    public function obtenerPersonas()
    {
        try 
        {
            $personas = Personas::where('estado', 'NO')
                ->get();
    
            if ($personas->isEmpty()) {
                return response()->json([
                    'message' => 'No hay personas pendientes por procesar',
                    'data' => []
                ], 200);
            }
            
            $resultados = [];
            
            foreach ($personas as $persona) {
                $fechaExpedicion = $persona->fechaexpedicion ? $persona->fechaexpedicion : null;               
                
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
                    $persona->fechasolicitud = now();
                    $persona->save();
    
                    $resultados[] = [
                        'id' => $persona->ppersonadoc,
                        'nombre' => $persona->nombre,
                        'jobid' => $persona->jobid,
                        'estado' => 'PROCESADO',
                    ];
                }
            }
            return response()->json([
                'message' => 'Proceso realizado con exito',
                'response' => $resultados
            ], 200);
    

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al procesar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
