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

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
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
                        <td>{{ $p->personanombre }}</td>
                        <td>{{ $p->estado }}</td>
                        <td>{{ $p->fechasolicitud }}</td>
                        <td>
                            <a href="{{ route('personas.reporte', $p->ppersonadoc) }}" class="btn btn-sm btn-primary">
                                Ver Reporte
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Sin registros</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
