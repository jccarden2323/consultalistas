<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Reportes</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <a href="{{ route('personas.crear') }}" class="btn btn-secondary mb-3">← Volver</a>
    <h2 class="mb-4">Reportes Generados</h2>

    <form method="GET" action="{{ route('personas.index') }}" class="row g-2 mb-3">
    <div class="col-md-4">
        <input type="text"
               name="documento"
               class="form-control"
               placeholder="Buscar por documento"
               value="{{ request('documento') }}">
    </div>

    <div class="col-auto">
        <button type="submit" class="btn btn-primary">
            Buscar
        </button>

        @if(request()->filled('documento'))
            <a href="{{ route('personas.index') }}" class="btn btn-outline-secondary">
                Limpiar
            </a>
        @endif
    </div>
</form>

    {{-- @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif --}}
    @if(session('info'))
        <div class="alert alert-info">
            {{ session('info') }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Documento</th>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Fecha solicitud</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($personas as $p)
                    <tr>
                        <td>{{ $p->ppersonadoc }}</td>
                        <td>
                            @if($p->validado == 0)
                                <span class="text-muted">Validando Nombre</span>
                            @else
                                {{ $p->personanombre }}
                            @endif
                        </td>
                        <td>{{ $p->estado }}</td>
                        <td>{{ $p->fechasolicitud }}</td>
                        <td>
                            <a href="{{ route('personas.reporte', $p->ppersonadoc) }}" class="btn btn-sm btn-primary">
                                Ver Reporte
                            </a>                       

                            <form action="{{ route('personas.retry', $p->ppersonadoc) }}" method="POST"
                                onsubmit="return confirm('¿Desea reintentar la consulta para cargar las fuentes con error?');">
                                @csrf
                                <button type="submit"
                                        class="btn btn-sm btn-warning"
                                        {{ !$p->idreporte ? 'disabled' : '' }}>
                                    Reintentar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">
                            No se encontraron resultados para la búsqueda
                            @if(request('documento'))
                                con el documento "{{ request('documento') }}"
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center mt-4">
        {{ $personas->links('pagination::bootstrap-5') }}
    </div>

</div>

</body>
</html>
