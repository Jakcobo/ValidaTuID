<?php
// usuario.php
session_start();
date_default_timezone_set('America/Bogota');

// Habilitar la visualización de errores para depuración (solo en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION["cc"])) {
    header("Location: index.php");
    exit();
}

$cc = $_SESSION["cc"];

// Obtener las peticiones del cliente desde el microservicio
$servurlPeticiones = "http://balanceadors1:3003/peticiones/cliente/" . urlencode($cc);
$curl = curl_init($servurlPeticiones);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$responsePeticiones = curl_exec($curl);
$httpCodePeticiones = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($httpCodePeticiones === 200) {
    $peticiones = json_decode($responsePeticiones);
} else {
    $peticiones = [];
}

// Manejo de mensajes de éxito o error
$mensaje = '';
$error = '';
if (isset($_GET['mensaje'])) {
    if ($_GET['mensaje'] === 'peticion_creada') {
        $mensaje = "Petición creada exitosamente.";
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'campos_vacios':
            $error = "Por favor, completa todos los campos requeridos.";
            break;
        case 'error_crear_peticion':
            $error = "Error al crear la petición. Inténtalo nuevamente.";
            break;
        case 'tipo_archivo_no_permitido':
            $error = "Tipo de archivo no permitido. Solo se aceptan imágenes y PDFs.";
            break;
        case 'archivo_demasiado_grande':
            $error = "El archivo es demasiado grande. Máximo permitido: 5MB.";
            break;
        case 'error_subida_archivo':
            $error = "Error al subir el archivo. Inténtalo nuevamente.";
            break;
        case 'subida_incompleta':
            $error = "La subida del archivo se interrumpió.";
            break;
        case 'no_se_subio_archivo':
            $error = "No se subió ningún archivo.";
            break;
        case 'datos_invalidos':
            $error = "Datos proporcionados inválidos.";
            break;
        case 'conexion_error':
            $error = "Error de conexión al procesar la petición.";
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
    <title>Panel de Cliente</title>
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
            max-width: 900px;
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

        .btn-outline-light {
            color: var(--button-primary);
            border-color: var(--button-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .btn-outline-light:hover {
            background-color: var(--button-primary);
            color: var(--secondary-color);
        }

        input, select, textarea {
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

        @media (max-width: 576px) {
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
            <a class="navbar-brand" href="#">Panel de Cliente</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="navbar-text text-white">
                        Bienvenido, <?php echo htmlspecialchars($cc); ?>
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
            <h2 class="text-warning mb-5">Panel de Cliente</h2>
            
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

            <!-- Formulario para crear una nueva petición -->
            <div class="mb-5" data-aos="fade-up" data-aos-delay="100">
                <h4 class="text-warning mb-4"><i class="fas fa-plus-circle me-2"></i> Crear Nueva Petición</h4>
                <form action="crearPeticion.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3 text-start">
                        <label for="ccarchivo" class="form-label"><i class="fas fa-file-upload me-2"></i>Cédula del Archivo</label>
                        <input type="file" class="form-control" id="ccarchivo" name="ccarchivo" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label for="tiposervicio" class="form-label"><i class="fas fa-concierge-bell me-2"></i> Tipo de Servicio</label>
                        <select name="tiposervicio" id="tiposervicio" class="form-select" required>
                            <option value="">Selecciona tu servicio</option>
                            <option value="CDT">CDT</option>
                            <option value="Cuenta Ahorros">Cuenta Ahorros</option>
                            <option value="Credito">Crédito</option>
                        </select>
                    </div>
                    <div class="mb-3 text-start">
                        <label for="bancocliente" class="form-label"><i class="fas fa-university me-2"></i> Banco del Cliente</label>
                        <select name="bancocliente" id="bancocliente" class="form-select" required>
                            <option value="">Selecciona tu banco</option>
                            <option value="Banco de Bogota">Banco de Bogotá</option>
                            <option value="Banco Agrario web Cuentas de Ahorros">Banco Agrario web Cuentas de Ahorros</option>
                            <option value="Banco Pichincha">Banco Pichincha</option>
                            <option value="Banco Agrario Movil">Banco Agrario Móvil</option>
                            <option value="ScotiaBank_Colpatria">ScotiaBank Colpatria</option>
                            <option value="VIVA 1A IPS S.A.">VIVA 1A IPS S.A.</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100 btn-custom"><i class="fas fa-paper-plane me-2"></i> Crear Petición</button>
                </form>
            </div>

            <!-- Lista de peticiones del cliente -->
            <h4 class="text-warning mb-4"><i class="fas fa-list-alt me-2"></i> Mis Peticiones</h4>
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tipo de Servicio</th>
                        <th>Estado</th>
                        <th>Fecha de Solicitud</th>
                        <th>Hora de Solicitud</th>
                        <th>Archivo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($peticiones)): ?>
                        <?php foreach ($peticiones as $peticion): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($peticion->id); ?></td>
                                <td><?php echo htmlspecialchars($peticion->tiposervicio); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($peticion->estado)); ?></td>
                                <td><?php echo htmlspecialchars($peticion->fechasolicitud); ?><td>
                                <td><?php echo htmlspecialchars($peticion->horasolicitud); ?></td>
                                <td>
                                    <?php if (!empty($peticion->ccarchivo)): ?>
                                        <a href="uploads/ccarchivos/<?php echo htmlspecialchars($peticion->ccarchivo); ?>" target="_blank" class="text-primary"><i class="fas fa-eye me-2"></i> Ver Archivo</a>
                                    <?php else: ?>
                                        No hay archivo.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No tienes peticiones registradas.</td>
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
