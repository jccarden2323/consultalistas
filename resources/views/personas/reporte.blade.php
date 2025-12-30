<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Reporte</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

        <style>
            table {
                table-layout: fixed;
                word-wrap: break-word;
            }
            th {
                width: 35%;
                background: #f8f9fa;
            }
            td, th {
                vertical-align: top;
                white-space: normal !important;
                word-break: break-word;
            }
            .card-body {
                overflow-x: auto;
            }
        </style>
    </head>
    <body>

        <div class="container mt-4">
            <a href="{{ route('personas.index') }}" class="btn btn-secondary mb-3">← Volver</a>

            <h4>Reporte</h4>

                        @php
            /* ============================================================
            FUNCIÓN ÚNICA DE RENDER 
            ============================================================ */
            function renderSource($label, $data) {

                if ($data === null || $data === '' || $data === []) {
                    return '';
                }

                if (is_bool($data)) {
                    return $data
                        ? "<div class='alert alert-danger'><strong>{$label}:</strong> CON HALLAZGOS</div>"
                        : "<div class='alert alert-success'><strong>{$label}:</strong> Sin registros</div>";
                }

                if (is_string($data) || is_numeric($data)) {
                    return "<div class='alert alert-secondary'><strong>{$label}:</strong> {$data}</div>";
                }

                if (is_object($data)) {
                    $data = (array)$data;
                }

                if (!is_array($data)) {
                    return '';
                }

                $html = "<div class='card mb-3'>
                    <div class='card-header bg-light'><strong>{$label}</strong></div>
                    <div class='card-body'>";

                /* LISTA SIMPLE */
                if (isset($data[0]) && is_string($data[0])) {
                    $html .= "<ul class='mb-0'>";
                    foreach ($data as $item) {
                        $html .= "<li>{$item}</li>";
                    }
                    $html .= "</ul>";
                }

                /* ARRAY DE REGISTROS */
                elseif (isset($data[0]) && is_array($data[0])) {
                    foreach ($data as $i => $row) {
                        $html .= "<div class='border rounded p-2 mb-3'>
                                    <strong>Registro " . ($i + 1) . "</strong>
                                    <table class='table table-sm table-bordered mt-2'>";
                        foreach ($row as $k => $v) {
                            if (is_array($v)) {
                                $v = json_encode($v, JSON_UNESCAPED_UNICODE);
                            }
                            $html .= "<tr><th>{$k}</th><td>{$v}</td></tr>";
                        }
                        $html .= "</table></div>";
                    }
                }

                /* ARRAY ASOCIATIVO */
                else {
                    $html .= "<table class='table table-sm table-bordered'>";
                    foreach ($data as $k => $v) {
                        if (is_array($v)) {
                            $v = json_encode($v, JSON_UNESCAPED_UNICODE);
                        }
                        $html .= "<tr><th>{$k}</th><td>{$v}</td></tr>";
                    }
                    $html .= "</table>";
                }

                return $html . "</div></div>";
            }
            @endphp

            <div class="accordion" id="reporteAccordion">

                {{-- ============================================================
                RESUMEN EJECUTIVO
                ============================================================ --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingResumen">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseResumen" aria-expanded="true" aria-controls="collapseResumen">
                            Resumen de Riesgo
                        </button>
                    </h2>
                    <div id="collapseResumen" class="accordion-collapse collapse show" aria-labelledby="headingResumen" data-bs-parent="#reporteAccordion">
                        <div class="accordion-body text-center">
                            @php
                                $altos  = count($json['hallazgos']['altos'] ?? []);
                                $medios = count($json['hallazgos']['medios'] ?? []);
                                $bajos  = count($json['hallazgos']['bajos'] ?? []);

                                if ($altos > 0) { $nivel = 'ALTO'; $color = 'bg-danger'; }
                                elseif ($medios > 0) { $nivel = 'MEDIO'; $color = 'bg-warning'; }
                                elseif ($bajos > 0) { $nivel = 'BAJO'; $color = 'bg-success'; }
                                else { $nivel = 'SIN RIESGO'; $color = 'bg-secondary'; }
                            @endphp

                            <span class="badge {{ $color }} fs-5 p-3">Riesgo {{ $nivel }}</span>
                            <div class="mt-3 text-muted">
                                Altos: {{ $altos }} | Medios: {{ $medios }} | Bajos: {{ $bajos }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============================================================
                DATOS GENERALES
                ============================================================ --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingDatos">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDatos" aria-expanded="true" aria-controls="collapseDatos">
                            Datos Generales
                        </button>
                    </h2>
                    <div id="collapseDatos" class="accordion-collapse collapse show" aria-labelledby="headingDatos" data-bs-parent="#reporteAccordion">
                        <div class="accordion-body">
                            @if(!is_array($json) || empty($json))
                                <div class="alert alert-warning">
                                    Este reporte aún no ha sido procesado o no contiene datos.
                                </div>
                            @else
                                @if(isset($json['tipo_sujeto']) && $json['tipo_sujeto'] === 'persona')
                                    {!! renderSource('Datos personales', $json['datos_persona'] ?? []) !!}
                                @endif
                                
                                @if(isset($json['tipo_sujeto']) && $json['tipo_sujeto'] === 'empresa')
                                    {!! renderSource('Datos Generales de la Empresa', $json['datos_empresa'] ?? []) !!}
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ============================================================
                EMPRESA: SECCIONES INDEPENDIENTES
                ============================================================ --}}
                @if(isset($json['tipo_sujeto']) && $json['tipo_sujeto'] === 'empresa')
                    @foreach([
                        'Actividad Económica' => 'actividad_economica',
                        'Registro Mercantil' => 'registro_mercantil',
                        'Representación Legal y Vínculos' => 'representacion_legal',
                        'Otros Datos de la Empresa' => 'otros_datos_empresa'
                    ] as $label => $key)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $key }}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $key }}" aria-expanded="false" aria-controls="collapse{{ $key }}">
                                    {{ $label }}
                                </button>
                            </h2>
                            <div id="collapse{{ $key }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $key }}" data-bs-parent="#reporteAccordion">
                                <div class="accordion-body">
                                    {!! renderSource($label, $json[$key] ?? []) !!}
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- ============================================================
                BLOQUES SOLO PERSONA (colapsados por defecto)
                ============================================================ --}}
                @php $accordionIndex = 1; @endphp

                @if(is_array($json) && ($json['tipo_sujeto'] ?? null) === 'persona')

                    {{-- HALLAZGOS --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingHallazgos">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHallazgos" aria-expanded="false" aria-controls="collapseHallazgos">
                                Hallazgos
                            </button>
                        </h2>
                        <div id="collapseHallazgos" class="accordion-collapse collapse" aria-labelledby="headingHallazgos" data-bs-parent="#reporteAccordion">
                            <div class="accordion-body">
                                @foreach(['altos'=>'alert-danger','medios'=>'alert-warning','bajos'=>'alert-info','infos'=>'alert-secondary'] as $nivel=>$color)
                                    @php $items = $json['hallazgos'][$nivel] ?? [] @endphp
                                    @if(count($items))
                                        <div class="alert {{ $color }}">
                                            <strong>{{ strtoupper($nivel) }}</strong>
                                            <ul class="mb-0">
                                                @foreach($items as $h)
                                                    <li>
                                                        {{ $h['descripcion'] ?? 'Sin descripción' }}
                                                        @if(!empty($h['codigo']))
                                                            <small class="text-muted">({{ $h['codigo'] }})</small>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- LISTAS RESTRICTIVAS --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingListas">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseListas" aria-expanded="false" aria-controls="collapseListas">
                                Listas Restrictivas
                            </button>
                        </h2>
                        <div id="collapseListas" class="accordion-collapse collapse" aria-labelledby="headingListas" data-bs-parent="#reporteAccordion">
                            <div class="accordion-body">
                                @foreach($json['listas_restrictivas'] ?? [] as $k => $v)
                                    {!! renderSource(strtoupper(str_replace('_',' ', $k)), $v) !!}
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- ANTECEDENTES --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingAntecedentes">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAntecedentes" aria-expanded="false" aria-controls="collapseAntecedentes">
                                Antecedentes Legales
                            </button>
                        </h2>
                        <div id="collapseAntecedentes" class="accordion-collapse collapse" aria-labelledby="headingAntecedentes" data-bs-parent="#reporteAccordion">
                            <div class="accordion-body">
                                @foreach($json['antecedentes_legales'] ?? [] as $k => $v)
                                    {!! renderSource(strtoupper(str_replace('_',' ', $k)), $v) !!}
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- ANTECEDENTES COMPLETOS --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingAntecedentesCompletos">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAntecedentesCompletos" aria-expanded="false" aria-controls="collapseAntecedentesCompletos">
                                Antecedentes Legales Completos
                            </button>
                        </h2>
                        <div id="collapseAntecedentesCompletos" class="accordion-collapse collapse" aria-labelledby="headingAntecedentesCompletos" data-bs-parent="#reporteAccordion">
                            <div class="accordion-body">
                                @foreach($json['antecedentes_legales_completos'] ?? [] as $k => $v)
                                    {!! renderSource(strtoupper(str_replace('_',' ', $k)), $v) !!}
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- RUNT --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingRunt">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRunt" aria-expanded="false" aria-controls="collapseRunt">
                                Tránsito y RUNT
                            </button>
                        </h2>
                        <div id="collapseRunt" class="accordion-collapse collapse" aria-labelledby="headingRunt" data-bs-parent="#reporteAccordion">
                            <div class="accordion-body">
                                @foreach($json['transito_y_runt'] ?? [] as $k => $v)
                                    {!! renderSource(strtoupper(str_replace('_',' ', $k)), $v) !!}
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- PROFESIONALES --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingProfesionales">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProfesionales" aria-expanded="false" aria-controls="collapseProfesionales">
                                Profesionales y Económicos
                            </button>
                        </h2>
                        <div id="collapseProfesionales" class="accordion-collapse collapse" aria-labelledby="headingProfesionales" data-bs-parent="#reporteAccordion">
                            <div class="accordion-body">
                                @foreach($json['profesionales_y_economicos'] ?? [] as $k => $v)
                                    {!! renderSource(strtoupper(str_replace('_',' ', $k)), $v) !!}
                                @endforeach
                            </div>
                        </div>
                    </div>

                @endif
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        </div>
    </body>
</html>
