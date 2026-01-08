<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Crear Persona</title>

    <!-- CSS plantilla -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

    <link rel="shortcut icon" href="{{ asset('img/icons/icon-48x48.png') }}" />
</head>

<body>
<!-- Menú superior -->

<main class="content">
    <div class="container-fluid p-0">
        
        <div class="row">
            <div class="mb-3 text-center">
                <h1 class="h3 d-inline align-middle">CONSULTAR PERSONA/EMPRESA</h1>
                
            </div>

            <div class="col-12 col-lg-8 mx-auto">
                <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
                    <div class="container-fluid">

                        <a class="navbar-brand fw-bold" href="#">
                            CONSULTAS
                        </a>

                        <!-- Botón hamburguesa -->
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                            aria-controls="menuPrincipal" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                        <!-- Menú colapsable -->
                        <div class="collapse navbar-collapse" id="menuPrincipal">
                            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                                <li class="nav-item">
                                    <a href="{{ route('personas.crear') }}" class="nav-link">
                                        Nueva consulta
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('personas.index') }}" class="nav-link">
                                        Ver reportes
                                    </a>
                                </li>
                            </ul>
                        </div>

                    </div>
                </nav>

                <!-- Mensajes -->
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Datos de la Persona</h5>
                    </div>

                    <form method="POST" action="{{ route('personas.store') }}">
                        @csrf

                        <div class="card-body">

                            <!-- Tipo de Documento -->
                            <div class="card-header">
								<h5 class="card-title mb-0">Tipo de Identificación</h5>
							</div>
                            <div class="card-body">
                                <label class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="ppersonatipodoc" value="CC"
                                           {{ old('ppersonatipodoc') == 'CC' ? 'checked' : '' }} required>
                                    <span class="form-check-label">CC</span>
                                </label>

                                <label class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="ppersonatipodoc" value="NIT"
                                           {{ old('ppersonatipodoc') == 'NIT' ? 'checked' : '' }} required>
                                    <span class="form-check-label">NIT</span>
                                </label>

                                <label class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="ppersonatipodoc" value="PPT"
                                           {{ old('ppersonatipodoc') == 'PPT' ? 'checked' : '' }} required>
                                    <span class="form-check-label">PPT</span>
                                </label>
                            </div>

                            @error('ppersonatipodoc')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror                          

                            <!-- Documento -->
                            <div class="card-header">
                                <h5 class="card-title mb-0">Número de Documento</h5>
							</div>
							<div class="card-body">
                                <input type="text" class="form-control" id="ppersonadoc" name="ppersonadoc"
                                    value="{{ old('ppersonadoc') }}" required maxlength="20"
                                    pattern="^\d{3,20}$" placeholder="1234567890">

                                @error('ppersonadoc')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>                            

                            <!-- Fecha Expedición -->
                            <div class="card-header">
                                <h5 class="card-title mb-0">Fecha de Expedición (Opcional)</h5>
							</div>
							<div class="card-body">
                                <div class="border rounded p-3 mb-3"
                                     style="max-height: 150px; background-color: #f8f9fa; font-size: 0.9rem;">
                                   SOLO INGRESE LA FECHA DE EXPEDICION SI VA A CONSULTAR:
                                    <ul class="mt-2 mb-0 ps-3" style="line-height: 1.4;">
                                        <li>Inhabilidades por Delitos Sexuales</li>
                                        <li>Registro Nacional de Medidas Correctivas (RNMC)</li>
                                    </ul>
                                <br>
                                <input type="date" class="form-control" id="fechaexpedicion" name="fechaexpedicion"
                                    value="{{ old('fechaexpedicion') }}">

                                @error('fechaexpedicion')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg">
                                Cosultar
                            </button>

                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>
</main>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
