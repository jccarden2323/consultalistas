@props(['titulo', 'color' => 'primary', 'data'])

@php
    // Convertimos cualquier valor no-array en un array vacío
    $seguro = is_array($data) ? $data : [];
@endphp

@if(!empty($seguro))
<div class="card mb-4 border-{{ $color }}">
    <div class="card-header bg-{{ $color }} text-white">
        {{ $titulo }}
    </div>

    <div class="card-body">
        <!-- Datos simples -->
        <ul class="list-group mb-3">
            @foreach($seguro as $k => $v)
                @if(!is_array($v))
                    <li class="list-group-item">
                        <strong>{{ rtrim($k, ':') }}:</strong> {{ $v }}
                    </li>
                @endif
            @endforeach
        </ul>

        <!-- Direcciones -->
        @if(!empty($seguro['addresses']))
            <h6>📍 Direcciones</h6>
            @foreach($seguro['addresses'] as $dir)
                <div class="border rounded p-2 mb-2 bg-light">
                    <div><strong>País:</strong> {{ $dir['Country'] ?? '' }}</div>
                    <div><strong>Ciudad:</strong> {{ $dir['City'] ?? '' }}</div>
                    <div><strong>Dirección:</strong> {{ $dir['Address'] ?? '' }}</div>
                </div>
            @endforeach
        @endif

        <!-- Alias -->
        @if(!empty($seguro['aliases']))
            <h6 class="mt-3">🧩 Alias</h6>
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Categoría</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($seguro['aliases'] as $alias)
                        <tr>
                            <td>{{ $alias['Name'] ?? '' }}</td>
                            <td>{{ $alias['Type'] ?? '' }}</td>
                            <td>{{ $alias['Category'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Documentos -->
        @if(!empty($seguro['docs']))
            <h6 class="mt-3">📄 Documentos</h6>
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Número</th>
                        <th>País</th>
                        <th>Vencimiento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($seguro['docs'] as $doc)
                        <tr>
                            <td>{{ $doc['Type'] ?? '' }}</td>
                            <td>{{ $doc['ID#'] ?? '' }}</td>
                            <td>{{ $doc['Country'] ?? '' }}</td>
                            <td>{{ $doc['Expire Date'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

    </div>
</div>
@endif
