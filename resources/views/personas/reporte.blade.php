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
            FUNCIÓN ÚNICA DE RENDER (ROBUSTA Y SEGURA)
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

            {{-- ============================================================
            RESUMEN EJECUTIVO
            ============================================================ --}}
            <div class="card mb-4">
                <div class="card-header bg-light"><strong>Resumen de Riesgo</strong></div>
                <div class="card-body text-center">
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

            {{-- ============================================================
            DATOS GENERALES
            ============================================================ --}}
            <div class="card mb-3">
                <div class="card-header">Datos Generales</div>
                <div class="card-body">

                    @if(!is_array($json) || empty($json))
                        <div class="alert alert-warning">
                            Este reporte aún no ha sido procesado o no contiene datos.
                        </div>
                    @else

                        {{-- PERSONA --}}
                        @if(isset($json['tipo_sujeto']) && $json['tipo_sujeto'] === 'persona')
                            {!! renderSource('Datos personales', $json['datos_persona'] ?? []) !!}
                        @endif

                        {{-- EMPRESA --}}
                        @if(isset($json['tipo_sujeto']) && $json['tipo_sujeto'] === 'empresa')
                            {!! renderSource('Datos Generales de la Empresa', $json['datos_empresa'] ?? []) !!}
                            
                            {{-- ACTIVIDAD ECONÓMICA --}}
                            {!! renderSource('Actividad Económica', $json['actividad_economica'] ?? []) !!}

                            {{-- REGISTRO MERCANTIL --}}
                            {!! renderSource('Registro Mercantil', $json['registro_mercantil'] ?? []) !!}

                            {{-- REPRESENTACIÓN LEGAL Y VÍNCULOS --}}
                            {!! renderSource('Representación Legal y Vínculos', $json['representacion_legal'] ?? []) !!}
                            
                            {{-- OTROS DATOS DE LA EMPRESA --}}
                            {!! renderSource('Otros Datos de la Empresa', $json['otros_datos_empresa'] ?? []) !!}
                        @endif
                    @endif
                </div>
            </div>

            {{-- ============================================================
            BLOQUES SOLO PERSONA
            ============================================================ --}}
            @if(is_array($json) && ($json['tipo_sujeto'] ?? null) === 'persona')

                {{-- HALLAZGOS --}}
                <div class="card mb-4">
                    <div class="card-header bg-warning">Hallazgos</div>
                    <div class="card-body">
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

                {{-- LISTAS RESTRICTIVAS --}}
                <div class="card mb-3">
                    <div class="card-header bg-danger text-white">Listas Restrictivas</div>
                    <div class="card-body">
                        @foreach($json['listas_restrictivas'] ?? [] as $k => $v)
                            {!! renderSource(strtoupper(str_replace('_',' ', $k)), $v) !!}
                        @endforeach
                    </div>
                </div>

                {{-- ANTECEDENTES --}}
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">Antecedentes Legales</div>
                    <div class="card-body">
                        @foreach($json['antecedentes_legales'] ?? [] as $k => $v)
                            {!! renderSource(strtoupper(str_replace('_',' ', $k)), $v) !!}
                        @endforeach
                    </div>
                </div>

                {{-- RUNT --}}
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">Tránsito y RUNT</div>
                    <div class="card-body">
                        @foreach($json['transito_y_runt'] ?? [] as $k => $v)
                            {!! renderSource(strtoupper(str_replace('_',' ', $k)), $v) !!}
                        @endforeach
                    </div>
                </div>

                {{-- PROFESIONALES --}}
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">Profesionales y Económicos</div>
                    <div class="card-body">
                        @foreach($json['profesionales_y_economicos'] ?? [] as $k => $v)
                            {!! renderSource(strtoupper(str_replace('_',' ', $k)), $v) !!}
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </body>
</html>
