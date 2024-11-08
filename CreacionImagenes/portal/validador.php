<?php
// validador.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
date_default_timezone_set('America/Bogota');

// Verificar si el usuario ha iniciado sesión y es validador
if (!isset($_SESSION["usuario"]) || $_SESSION["rol"] !== 'validador') {
    header("Location: index.php");
    exit();
}

// Definir la variable $usuario
$usuario = htmlspecialchars($_SESSION["usuario"]);

// Obtener filtros de la URL y sanitizarlos
$filtros = [];
$allowed_filters = ['cliente', 'tiposervicio', 'fechaInicio', 'fechaFin']; // Eliminado 'estado'

// Siempre añadir 'estado=pendiente' al filtro
$filtros['estado'] = 'pendiente';

// Recopilar otros filtros si están presentes
foreach ($allowed_filters as $filter) {
    if (isset($_GET[$filter]) && !empty(trim($_GET[$filter]))) {
        $filtros[$filter] = htmlspecialchars(trim($_GET[$filter]));
    }
}

// Construir la URL para obtener las peticiones
if (!empty($filtros)) {
    $query = http_build_query($filtros);
    $servurlPeticiones = "http://balanceadors1:3003/peticiones_filtradas?$query";
} else {
    $servurlPeticiones = "http://balanceadors1:3003/peticiones_pendiente";
}

// Obtener las peticiones desde el microservicio
$curl = curl_init($servurlPeticiones);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$responsePeticiones = curl_exec($curl);
$httpCodePeticiones = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

// Inicializar variables
$peticiones = [];
$mensaje = '';
$error = '';

// Verificar la respuesta del microservicio
if ($httpCodePeticiones === 200) {
    $peticiones = json_decode($responsePeticiones);

    // Verificar si la decodificación fue exitosa
    if (json_last_error() !== JSON_ERROR_NONE) {
        $error = "Error al decodificar la respuesta del servidor: " . json_last_error_msg();
        $peticiones = [];
    }
} else {
    // Manejo de errores basado en la respuesta del microservicio
    $errorData = json_decode($responsePeticiones, true);
    if (isset($errorData['message'])) {
        $error = htmlspecialchars($errorData['message']);
    } else {
        $error = "Error al obtener peticiones pendientes. Código de respuesta: $httpCodePeticiones";
    }
}

// Manejo de mensajes de éxito o error desde la URL
if (isset($_GET['mensaje'])) {
    if ($_GET['mensaje'] === 'peticion_actualizada') {
        $mensaje = "Petición actualizada exitosamente.";
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'error_actualizar_peticion':
            $error = "Error al actualizar la petición. Inténtalo nuevamente.";
            break;
        case 'error_actualizar_peticion_datos_invalidos':
            $error = "Datos de petición inválidos.";
            break;
        default:
            $error = "Ha ocurrido un error.";
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Validador</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation Library -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <style>
        /* Paleta de colores */
        :root {
            --primary-color: #FFD700; /* Amarillo */
            --secondary-color: #1a1a1a; /* Negro oscuro */
            --accent-color: #333333; /* Gris oscuro */
            --background-gradient: linear-gradient(135deg, #1a1a1a, #333333);
            --button-primary: #FFD700;
            --button-primary-hover: #e6c200;
            --button-secondary: #6c757d;
            --button-secondary-hover: #5a6268;
            --text-color: #ffffff;
            --alert-success-bg: rgba(40, 167, 69, 0.9);
            --alert-danger-bg: rgba(220, 53, 69, 0.9);
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: var(--background-gradient);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
            color: var(--text-color);
        }

        .container-custom {
            max-width: 1200px;
            background-color: var(--secondary-color);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .container-custom:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.7);
        }

        .btn-custom {
            transition: background-color 0.3s ease, transform 0.3s ease;
            color: var(--secondary-color);
            font-weight: bold;
        }

        .btn-primary {
            background-color: var(--button-primary);
            border-color: var(--button-primary);
        }

        .btn-primary:hover {
            background-color: var(--button-primary-hover);
            border-color: var(--button-primary-hover);
        }

        .btn-secondary {
            background-color: var(--button-secondary);
            border-color: var(--button-secondary);
        }

        .btn-secondary:hover {
            background-color: var(--button-secondary-hover);
            border-color: var(--button-secondary-hover);
        }

        input, select {
            background-color: #2c2c2c;
            color: var(--text-color);
            border-radius: 5px;
            padding: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border: 1px solid #ced4da;
        }

        label {
            color: var(--text-color);
        }

        .alert-success-custom {
            background-color: var(--alert-success-bg);
            color: #ffffff;
            border: none;
        }

        .alert-danger-custom {
            background-color: var(--alert-danger-bg);
            color: #ffffff;
            border: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
            color: var(--text-color);
        }

        th {
            background-color: #343a40;
            color: var(--primary-color);
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .btn-success-custom {
            background-color: #28a745;
            border: none;
            transition: background-color 0.3s ease;
        }

        .btn-success-custom:hover {
            background-color: #218838;
            box-shadow: 0 4px 6px rgba(40, 167, 69, 0.3);
        }

        .btn-danger-custom {
            background-color: #dc3545;
            border: none;
            transition: background-color 0.3s ease;
        }

        .btn-danger-custom:hover {
            background-color: #c82333;
            box-shadow: 0 4px 6px rgba(220, 53, 69, 0.3);
        }

        @media (max-width: 768px) {
            .container-custom {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Panel de Validador</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="navbar-text text-white">
                        Bienvenido, <?php echo $usuario; ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="btn btn-outline-light ms-3"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="d-flex justify-content-center align-items-center flex-grow-1">
        <div class="container-custom" data-aos="fade-up">
            <h2 class="text-warning mb-5">Panel de Validador</h2>
            
            <!-- Mensajes de éxito o error -->
            <?php if ($mensaje): ?>
                <div class="alert alert-success-custom alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($mensaje); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger-custom alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            <?php endif; ?>

            <!-- Formulario de Filtrado -->
            <form method="GET" action="validador.php" class="mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="cliente" class="form-control" placeholder="Cédula del Cliente" value="<?php echo isset($_GET['cliente']) ? htmlspecialchars($_GET['cliente']) : ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="tiposervicio" class="form-select">
                            <option value="">-- Tipo de Servicio --</option>
                            <option value="CDT" <?php if(isset($_GET['tiposervicio']) && $_GET['tiposervicio'] == 'CDT') echo 'selected'; ?>>CDT</option>
                            <option value="Cuenta Ahorros" <?php if(isset($_GET['tiposervicio']) && $_GET['tiposervicio'] == 'Cuenta Ahorros') echo 'selected'; ?>>Cuenta Ahorros</option>
                            <option value="Credito" <?php if(isset($_GET['tiposervicio']) && $_GET['tiposervicio'] == 'Credito') echo 'selected'; ?>>Crédito</option>
                            <!-- Añadir más opciones según sea necesario -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="fechaInicio" class="form-control" placeholder="Fecha Inicio" value="<?php echo isset($_GET['fechaInicio']) ? htmlspecialchars($_GET['fechaInicio']) : ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="fechaFin" class="form-control" placeholder="Fecha Fin" value="<?php echo isset($_GET['fechaFin']) ? htmlspecialchars($_GET['fechaFin']) : ''; ?>">
                    </div>
                    <div class="col-md-3 d-grid">
                        <button type="submit" class="btn btn-primary btn-custom"><i class="fas fa-filter me-2"></i> Filtrar</button>
                        <a href="validador.php" class="btn btn-secondary btn-custom mt-2"><i class="fas fa-eraser me-2"></i> Limpiar</a>
                    </div>
                </div>
            </form>

            <!-- Lista de peticiones pendientes -->
            <h4 class="text-warning mb-4"><i class="fas fa-tasks me-2"></i> Peticiones Pendientes</h4>
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Cédula del Cliente</th>
                        <th>Tipo de Servicio</th>
                        <th>Banco del Cliente</th>
                        <th>Fecha de Solicitud</th>
                        <th>Hora de Solicitud</th>
                        <th>Archivo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($peticiones) && is_array($peticiones)): ?>
                        <?php foreach ($peticiones as $peticion): ?>
                            <?php
                                // Verificar que $peticion es un objeto con las propiedades necesarias
                                if (is_object($peticion) && isset($peticion->id, $peticion->cccliente, $peticion->tiposervicio, $peticion->bancocliente, $peticion->estado, $peticion->fechasolicitud, $peticion->horasolicitud, $peticion->ccarchivo)):
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($peticion->id); ?></td>
                                    <td><?php echo htmlspecialchars($peticion->cccliente); ?></td>
                                    <td><?php echo htmlspecialchars($peticion->tiposervicio); ?></td>
                                    <td><?php echo htmlspecialchars($peticion->bancocliente); ?></td>
                                    <td><?php echo htmlspecialchars($peticion->fechasolicitud); ?></td>
                                    <td><?php echo htmlspecialchars($peticion->horasolicitud); ?></td>
                                    <td>
                                        <?php if (!empty($peticion->ccarchivo)): ?>
                                            <a href="uploads/ccarchivos/<?php echo htmlspecialchars($peticion->ccarchivo); ?>" target="_blank" class="text-primary"><i class="fas fa-eye me-2"></i> Ver Archivo</a>
                                        <?php else: ?>
                                            No hay archivo.
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form action="actualizarPeticion.php" method="post" class="d-inline">
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($peticion->id); ?>">
                                            <input type="hidden" name="accion" value="aceptar">
                                            <button type="submit" class="btn btn-success-custom btn-sm"><i class="fas fa-check me-2"></i>Aceptar</button>
                                        </form>
                                        <form action="actualizarPeticion.php" method="post" class="d-inline">
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($peticion->id); ?>">
                                            <input type="hidden" name="accion" value="rechazar">
                                            <button type="submit" class="btn btn-danger-custom btn-sm"><i class="fas fa-times me-2"></i>Rechazar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8">Datos de petición inválidos.</td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">No hay peticiones pendientes.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Fin del Contenido Principal -->

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation JS -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    </script>
</body>
</html>
