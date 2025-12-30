<?php

namespace App\Services;

class ProcesarReporteService
{
    public function procesarReporte(string $jsonCrudo): array
    {
        $data = json_decode($jsonCrudo, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error' => true,
                'mensaje' => 'JSON inválido: ' . json_last_error_msg(),
            ];
        }

        // Detectar sujeto y mapear datos
        if (!empty($data['razon_social']) || !empty($data['registro_mercantil'])) {
            // Empresa
            $empresa = $this->mapEmpresa($data);
            $tipo_sujeto = 'empresa';
            $datos_empresa = $empresa['datos_empresa'];
            $otros_datos_empresa = $empresa['otros_datos'];
            $actividad_economica = $empresa['actividad_economica'];
            $registro_mercantil = $empresa['registro_mercantil'];
            $representacion_legal = $empresa['representacion_legal_y_vinculos'];
            $datos_persona = [];
        } else {
            // Persona
            $persona = $this->mapPersona($data);

            $tipo_sujeto = 'persona';
            $datos_persona = $persona['datos_persona'];

            $datos_empresa = [];
            $otros_datos_empresa = [];
            $actividad_economica = [];
            $registro_mercantil = [];
            $representacion_legal = [];
        }

        return [
            'tipo_sujeto' => $tipo_sujeto,
            'datos_persona' => $datos_persona,
            'datos_empresa' => $datos_empresa,
            'actividad_economica' => $actividad_economica,
            'registro_mercantil' => $registro_mercantil,
            'representacion_legal' => $representacion_legal,
            'otros_datos_empresa' => $otros_datos_empresa,
            'hallazgos' => $this->procesarHallazgosConDetalle($data),
            'listas_restrictivas' => $this->bloqueListasRestrictivas($data), 
            'antecedentes_legales' => $this->bloqueAntecedentes($data), 
            'antecedentes_legales_completos' => $this->bloqueAntecedentesCompletos($data), 
            'transito_y_runt' => $this->bloqueRuntTransito($data), 
            'profesionales_y_economicos' => $this->bloqueProfesionalFinanciero($data),
        ];
    }

    /* =====================================================
     |  DETECCIÓN DE TIPO DE SUJETO
     ===================================================== */
    // private function esEmpresa(array $data): bool
    // {
    //     return !empty($data['razon_social']) || !empty($data['registro_mercantil']);
    // }

    /* =====================================================
     |  MAPEO PERSONA
     ===================================================== */
    private function mapPersona(array $data): array
    {
        return [
            'tipo_sujeto' => 'persona',
            'datos_persona' => [
                'nombre' => $data['nombre'] ?? null,
                'documento' => $data['rut'] ?? $data['id'] ?? null,
                'genero' => $data['genero'] ?? null,
            ],
            'datos_empresa' => [],
        ];
    }

    /* =====================================================
     |  MAPEO EMPRESA (AQUÍ AGREGAMOS TODO)
     ===================================================== */
    private function mapEmpresa(array $data): array
    {
        return [
             'datos_empresa' => [
                'razon_social' => $data['razon_social'] ?? null,
                'nit' => $data['nit'] ?? null,
                'matricula' => $data['matricula'] ?? null,
                'identificacion' => $data['identificacion'] ?? null,
                'tipo' => $data['tipo'] ?? null,
                'organizacion_juridica' => $data['organizacion_juridica'] ?? null,
                'estado' => $data['estado'] ?? null,
            ],
            'actividad_economica' => $data['actividades_economicas'] ?? [],
            'registro_mercantil' => $data['registro_mercantil'] ?? [],
            'representacion_legal_y_vinculos' => $data['representacion_legal_y_vinculos'] ?? [],
            'otros_datos' => [
                'categoria_matricula' => $data['categoria_matricula'] ?? null,
                'clase_identificacion' => $data['clase_identificacion'] ?? null,
                'codigo_camara' => $data['codigo_camara'] ?? null,
                'codigo_estado' => $data['codigo_estado'] ?? null,
                'concordato' => $data['concordato'] ?? null,
                'contaduria' => $data['contaduria'] ?? null,
                'contraloria' => $data['contraloria'] ?? null,
                'dest' => $data['dest'] ?? null,
                'empresa_prestadora' => $data['empresa_prestadora'] ?? null,
                'fecha' => $data['fecha'] ?? null,
                'google' => $data['google'] ?? [],
                'grupo_empresarial' => $data['grupo_empresarial'] ?? [],
                'informacion_del_establecimiento' => $data['informacion_del_establecimiento'] ?? null,
                'informacion_financiera' => $data['informacion_financiera'] ?? [],
                'juzgados_tyba' => $data['juzgados_tyba'] ?? null,
                'lista_onu' => $data['lista_onu'] ?? null,
                'nombre_camara' => $data['nombre_camara'] ?? null,
                'peps' => $data['peps'] ?? null,
                'peps_demon' => $data['peps_demon'] ?? null,
                'peps_denom' => $data['peps_denom'] ?? null,
                'procuraduria' => $data['procuraduria'] ?? null,
                'propietario_o_establecimientos_agencias_sucursales' => $data['propietario_o_establecimientos_agencias_sucursales'] ?? [],
                'proveedores_ficticios' => $data['proveedores_ficticios'] ?? null,
                'registro_proponentes' => $data['registro_proponentes'] ?? null,
                'renovaciones_anteriores' => $data['renovaciones_anteriores'] ?? [],
                'rut' => $data['rut'] ?? null,
                'rut_estado' => $data['rut_estado'] ?? null,
                'secop2' => $data['secop2'] ?? null,
                'secop_s' => $data['secop_s'] ?? null,
                'simit' => $data['simit'] ?? null,
                'simur' => $data['simur'] ?? [],
                'transitobog' => $data['transitobog'] ?? null,
            ],
        ];
    }

    /* =====================================================
     |  HALLAZGOS
     ===================================================== */
    private function procesarHallazgosConDetalle(array $data): array
    {
        $dict = $data['dict_hallazgos'] ?? [];

        if (!is_array($dict)) {
            return [
                'altos' => [],
                'medios' => [],
                'bajos' => [],
                'infos' => [],
            ];
        }

        $resultado = [
            'altos' => [],
            'medios' => [],
            'bajos' => [],
            'infos' => [],
        ];

        foreach ($resultado as $nivel => $_) {

            if (!isset($dict[$nivel]) || !is_array($dict[$nivel])) {
                continue;
            }

            foreach ($dict[$nivel] as $item) {

                if (!is_array($item)) {
                    continue;
                }

                $codigo = $item['codigo'] ?? null;

                $resultado[$nivel][] = [
                    'codigo' => $codigo,
                    'descripcion' => $item['descripcion'] ?? '',
                    'fuente' => $item['fuente'] ?? '',
                    'hallazgo' => $item['hallazgo'] ?? '',
                    'nivel' => $nivel,
                    'detalle' => ($codigo && array_key_exists($codigo, $data))
                        ? $data[$codigo]
                        : null,
                ];
            }
        }

        return $resultado;
    }

    /* =====================================================
     |  BLOQUES AUXILIARES
     ===================================================== */
    private function bloqueListasRestrictivas(array $data): array
    {
        return $this->mapSources($data, [
            'lista_onu', 
            'ofac', 
            'europol', 
            'iadb', 
            'peps', 
            'peps2'
        ]);
    }

    private function bloqueAntecedentes(array $data): array
    {
        return $this->mapSources($data, [
            'contraloria', 
            'contaduria', 
            'procuraduria',
            'antecedentes_disciplinarios',
            'policia', 
            'delitos_sexuales', 
            'rut', 
            'rnmc', 
            'sirna'
        ]);
    }

    private function bloqueAntecedentesCompletos(array $data): array
    {
        return $this->mapSources($data, [
            'rama_unificada'

        ]);
    }
    
    private function bloqueRuntTransito(array $data): array
    {
        return $this->mapSources($data, [
            'runt_app', 'runt_licencia_automoviles', 'runt_paz_y_salvo',
            'simit_above10', 'simit_below10'
        ]);
    }

    private function bloqueProfesionalFinanciero(array $data): array
    {
        return $this->mapSources($data, [
            'contadores_s', 'proveedores_ficticios', 'rues', 'rues_estado', 'jcc'
        ]);
    }

    private function mapSources(array $data, array $fuentes): array
    {
        $out = [];

        foreach ($fuentes as $f) {

            if (!array_key_exists($f, $data)) {
                continue;
            }

            // Ignorar errores técnicos
            if ($data[$f] === 'Error') {
                continue;
            }

            // Normalizamos todo a array
            if (is_array($data[$f])) {
                $out[$f] = $data[$f];
            } elseif (is_bool($data[$f])) {
                $out[$f] = $data[$f];
            } elseif (is_null($data[$f]) || $data[$f] === '') {
                $out[$f] = [];
            } else {
                // Convertir cualquier string, número u otro a array con un solo elemento
                $out[$f] = [$data[$f]];
            }
        }

        return $out;
    }

}
