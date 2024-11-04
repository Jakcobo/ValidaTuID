<?php
// admin.php
session_start();
date_default_timezone_set('America/Bogota');  // Establecer zona horaria

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION["usuario"]) || $_SESSION["rol"] !== 'admin') {
    header("Location: index.php");
    exit();
}

$usuario = htmlspecialchars($_SESSION["usuario"]);

// Definir filtros permitidos
$allowed_filters = ['cliente', 'estado', 'fechaInicio', 'fechaFin', 'tiposervicio'];
$filtros = [];

// Recoger y sanitizar los filtros de la URL
foreach ($allowed_filters as $filter) {
    if (isset($_GET[$filter]) && !empty(trim($_GET[$filter]))) {
        $filtros[$filter] = htmlspecialchars(trim($_GET[$filter]));
    }
}

// Determinar la URL del microservicio según si hay filtros o no
if (!empty($filtros)) {
    // Usar la ruta de peticiones filtradas
    $query = http_build_query($filtros);
    $servurlPeticiones = "http://balanceadors1:3003/peticiones_filtradas?" . $query;
    $esperaRespuesta = 'array'; // Esperar un arreglo
} else {
    // Usar la ruta de todas las peticiones
    $servurlPeticiones = "http://balanceadors1:3003/peticiones/";
    $esperaRespuesta = 'object'; // Esperar un objeto con status y data
}

// Obtener las peticiones desde el microservicio
$curlPeticiones = curl_init($servurlPeticiones);
curl_setopt($curlPeticiones, CURLOPT_RETURNTRANSFER, true);
$responsePeticiones = curl_exec($curlPeticiones);
$httpCodePeticiones = curl_getinfo($curlPeticiones, CURLINFO_HTTP_CODE);
curl_close($curlPeticiones);

if ($httpCodePeticiones === 200) {
    $decodedPeticiones = json_decode($responsePeticiones, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $peticiones = [];
        $errorPeticiones = "Respuesta de API no válida.";
    } else {
        if ($esperaRespuesta === 'array') {
            // Ruta /peticiones_filtradas devuelve un arreglo
            if (is_array($decodedPeticiones)) {
                $peticiones = $decodedPeticiones;
            } else {
                $peticiones = [];
                $errorPeticiones = "Respuesta de API no válida.";
            }
        } else {
            // Ruta /peticiones devuelve un objeto con status y data
            if (isset($decodedPeticiones['status']) && $decodedPeticiones['status'] === 'success' && isset($decodedPeticiones['data'])) {
                $peticiones = $decodedPeticiones['data'];
            } else {
                $peticiones = [];
                $errorPeticiones = "Respuesta de API no válida.";
            }
        }
    }
} else {
    $peticiones = [];
    $errorPeticiones = "Error al obtener las peticiones. Código de respuesta: $httpCodePeticiones";
}

// Obtener todos los usuarios desde el microservicio
$servurlUsuarios = "http://balanceadors2:3002/usuarios";
$curlUsuarios = curl_init($servurlUsuarios);
curl_setopt($curlUsuarios, CURLOPT_RETURNTRANSFER, true);
$responseUsuarios = curl_exec($curlUsuarios);
$httpCodeUsuarios = curl_getinfo($curlUsuarios, CURLINFO_HTTP_CODE);
curl_close($curlUsuarios);

if ($httpCodeUsuarios === 200) {
    $usuarios = json_decode($responseUsuarios);
} else {
    $usuarios = [];
    $errorUsuarios = "Error al obtener los usuarios. Código de respuesta: $httpCodeUsuarios";
}

// Manejo de mensajes de éxito o error
$mensaje = '';
$error = '';
if (isset($_GET['mensaje'])) {
    switch ($_GET['mensaje']) {
        case 'usuario_creado':
            $mensaje = "Usuario creado exitosamente.";
            break;
        case 'usuario_actualizado':
            $mensaje = "Usuario actualizado exitosamente.";
            break;
        case 'usuario_eliminado':
            $mensaje = "Usuario eliminado exitosamente.";
            break;
        default:
            $mensaje = "";
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'error_crear_usuario':
            $error = "Error al crear el usuario. Inténtalo nuevamente.";
            break;
        case 'duplicado':
            $error = "El usuario ya existe.";
            break;
        case 'error_actualizar_usuario':
            $error = "Error al actualizar el usuario. Inténtalo nuevamente.";
            break;
        case 'error_eliminar_usuario':
            $error = "Error al eliminar el usuario. Inténtalo nuevamente.";
            break;
        case 'error_peticiones':
            $error = isset($errorPeticiones) ? $errorPeticiones : "Error al obtener las peticiones.";
            break;
        case 'error_usuarios':
            $error = isset($errorUsuarios) ? $errorUsuarios : "Error al obtener los usuarios.";
            break;
        case 'error_obtener_usuario':
            $error = "Error al obtener el usuario seleccionado.";
            break;
        default:
            $error = "Ha ocurrido un error.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administrador</title>
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
            --background-color: #f8f9fa; /* Claro */
            --text-color: #000000; /* Texto claro */
            --button-primary: #FFD700;
            --button-primary-hover: #e6c200;
            --button-secondary: #6c757d;
            --button-secondary-hover: #5a6268;
            --alert-success-bg: rgba(40, 167, 69, 0.9);
            --alert-danger-bg: rgba(220, 53, 69, 0.9);
        }

        body.dark-mode {
            --background-color: #1a1a1a; /* Oscuro */
            --text-color: #ffffff; /* Texto oscuro */
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            transition: background-color 0.3s ease, color 0.3s ease;
            padding-top: 80px; /* Para la barra de navegación fija */
        }

        .navbar-dark {
            background-color: var(--secondary-color) !important;
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
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        /* Botones de acciones eliminados */

        /* Sección de peticiones */
        .peticiones-section {
            margin-top: 50px;
        }

        /* Tema Oscuro/Claro Toggle */
        .theme-toggle-btn {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Panel de Administrador</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin" aria-controls="navbarAdmin" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarAdmin">
                <ul class="navbar-nav ms-auto">
                    <!-- Botón de Mostrar Peticiones -->
                    <li class="nav-item me-2">
                        <button onclick="mostrarPeticiones()" class="btn btn-outline-light">
                            <i class="fas fa-tasks mr-2"></i> Mostrar Peticiones
                        </button>
                    </li>
                    <!-- Botón de alternancia de tema -->
                    <li class="nav-item">
                        <button id="toggleTema" class="btn btn-outline-light me-2 theme-toggle-btn">
                            <i class="fas fa-moon"></i> Oscuro
                        </button>
                    </li>
                    <!-- Botón de Cerrar Sesión -->
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-outline-light"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container mt-5 pt-4">
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


        <!-- Formulario para crear un nuevo usuario -->
        <div class="mb-4" data-aos="fade-up" data-aos-delay="100">
            <h3 class="text-warning mb-4"><i class="fas fa-user-plus me-2"></i> Crear Nuevo Usuario</h3>
            <form action="crearUsuario.php" method="post">
                <div class="mb-3 text-start">
                    <label for="usuario" class="form-label"><i class="fas fa-user me-2"></i> Usuario</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" required>
                </div>
                <div class="mb-3 text-start">
                    <label for="nombre" class="form-label"><i class="fas fa-id-badge me-2"></i> Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                <div class="mb-3 text-start">
                    <label for="rol" class="form-label"><i class="fas fa-briefcase me-2"></i> Rol</label>
                    <select class="form-select" id="rol" name="rol" required>
                        <option value="admin">Administrador</option>
                        <option value="validador">Validador</option>
                    </select>
                </div>
                <div class="mb-4 text-start">
                    <label for="password" class="form-label"><i class="fas fa-lock me-2"></i> Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100 btn-custom"><i class="fas fa-plus me-2"></i> Crear Usuario</button>
            </form>
        </div>

        <!-- Lista de usuarios -->
        <h3 class="text-warning mb-4"><i class="fas fa-users me-2"></i> Usuarios Registrados</h3>
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Usuario</th>
                    <th>Nombre</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($usuarios): ?>
                    <?php foreach ($usuarios as $usr): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usr->usuario); ?></td>
                        <td><?php echo htmlspecialchars($usr->nombre); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($usr->rol)); ?></td>
                        <td>
                            <a href="editarUsuario.php?usuario=<?php echo urlencode($usr->usuario); ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit me-2"></i>Editar</a>
                            <form action="eliminarUsuario.php" method="post" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar este usuario?');">
                                <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($usr->usuario); ?>">
                                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt me-2"></i>Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No hay usuarios registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Formulario de Filtrado Avanzado -->
<form id="filterForm" method="GET" action="admin.php" class="mb-4" data-aos="fade-up" data-aos-delay="100">
    <div class="row g-3">
        <div class="col-md-3">
            <input type="text" name="cliente" class="form-control" placeholder="Cédula del Cliente" value="<?php echo isset($_GET['cliente']) ? htmlspecialchars($_GET['cliente']) : ''; ?>">
        </div>
        <div class="col-md-3">
            <select name="estado" class="form-select">
                <option value="">-- Estado --</option>
                <option value="pendiente" <?php if(isset($_GET['estado']) && $_GET['estado'] == 'pendiente') echo 'selected'; ?>>Pendiente</option>
                <option value="Aprobado" <?php if(isset($_GET['estado']) && $_GET['estado'] == 'Aprobado') echo 'selected'; ?>>Aprobado</option>
                <option value="rechazado" <?php if(isset($_GET['estado']) && $_GET['estado'] == 'rechazado') echo 'selected'; ?>>Rechazado</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="date" name="fechaInicio" class="form-control" placeholder="Fecha Inicio" value="<?php echo isset($_GET['fechaInicio']) ? htmlspecialchars($_GET['fechaInicio']) : ''; ?>">
        </div>
        <div class="col-md-3">
            <input type="date" name="fechaFin" class="form-control" placeholder="Fecha Fin" value="<?php echo isset($_GET['fechaFin']) ? htmlspecialchars($_GET['fechaFin']) : ''; ?>">
        </div>
        <div class="col-md-3">
            <select name="tiposervicio" class="form-select">
                <option value="">-- Tipo de Servicio --</option>
                <option value="CDT" <?php if(isset($_GET['tiposervicio']) && $_GET['tiposervicio'] == 'CDT') echo 'selected'; ?>>CDT</option>
                <option value="Cuenta Ahorros" <?php if(isset($_GET['tiposervicio']) && $_GET['tiposervicio'] == 'Cuenta Ahorros') echo 'selected'; ?>>Cuenta Ahorros</option>
                <option value="Credito" <?php if(isset($_GET['tiposervicio']) && $_GET['tiposervicio'] == 'Credito') echo 'selected'; ?>>Crédito</option>
                <!-- Añade más opciones según sea necesario -->
            </select>
        </div>
        <div class="col-md-3 d-grid">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-2"></i> Filtrar</button>
            <a href="admin.php" class="btn btn-secondary mt-2"><i class="fas fa-eraser me-2"></i> Limpiar</a>
        </div>
    </div>
</form>

<!-- Lista de peticiones -->
<div id="seccionPeticiones" class="container mt-5" style="display: none;">
    <h3 class="text-warning mb-4"><i class="fas fa-tasks me-2"></i> Todas las Peticiones</h3>
    <?php if (isset($errorPeticiones)): ?>
        <div class="alert alert-danger-custom alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($errorPeticiones); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>  
    <?php endif; ?>

    <div id="peticionesTable">
        <?php if (!empty($peticiones)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID de Solicitud</th>
                            <th>Cédula del Cliente</th>
                            <th>Validador</th>
                            <th>Tipo de Solicitud</th>
                            <th>Estado</th>
                            <th>Fecha de Solicitud</th>
                            <th>Hora de Solicitud</th>
                            <th>Archivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($peticiones as $peticion): ?>
                            <?php
                                // Verificar que $peticion es un array con las claves necesarias
                                if (is_array($peticion) && isset($peticion['id'], $peticion['cccliente'], $peticion['usuariovalidador'], $peticion['tiposervicio'], $peticion['estado'], $peticion['fechasolicitud'], $peticion['horasolicitud'], $peticion['ccarchivo'])):
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($peticion['id']); ?></td>
                                    <td><?php echo htmlspecialchars($peticion['cccliente']); ?></td>
                                    <td><?php echo htmlspecialchars($peticion['usuariovalidador'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($peticion['tiposervicio']); ?></td>
                                    <td>
                                        <?php 
                                            $estado = ucfirst($peticion['estado']); 
                                            if ($estado === 'Aprobado') {
                                                echo '<span class="badge bg-success">' . $estado . '</span>';
                                            } elseif ($estado === 'Rechazado') {
                                                echo '<span class="badge bg-danger">' . $estado . '</span>';
                                            } else {
                                                echo '<span class="badge bg-warning text-dark">' . $estado . '</span>';
                                            }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($peticion['fechasolicitud']); ?></td>
                                    <td><?php echo htmlspecialchars($peticion['horasolicitud']); ?></td>
                                    <td>
                                        <?php if (!empty($peticion['ccarchivo'])): ?>
                                            <a href="uploads/ccarchivos/<?php echo htmlspecialchars($peticion['ccarchivo']); ?>" 
                                               target="_blank" class="text-primary">
                                                <i class="fas fa-eye me-2"></i>Ver Archivo
                                            </a>
                                        <?php else: ?>
                                            No hay archivo.
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8">Datos de petición inválidos.</td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No hay peticiones para mostrar.</div>
        <?php endif; ?>
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

        // Tema Oscuro/Claro Toggle
        const toggleTemaBtn = document.getElementById('toggleTema');
        const currentTema = localStorage.getItem('tema') || 'claro';

        // Aplicar el tema almacenado al cargar la página
        if (currentTema === 'oscuro') {
            document.body.classList.add('dark-mode');
            toggleTemaBtn.innerHTML = '<i class="fas fa-sun"></i> Claro';
        } else {
            toggleTemaBtn.innerHTML = '<i class="fas fa-moon"></i> Oscuro';
        }

        // Evento para alternar el tema
        toggleTemaBtn.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            let temaActual = 'claro';
            if (document.body.classList.contains('dark-mode')) {
                temaActual = 'oscuro';
                toggleTemaBtn.innerHTML = '<i class="fas fa-sun"></i> Claro';
            } else {
                toggleTemaBtn.innerHTML = '<i class="fas fa-moon"></i> Oscuro';
            }
            localStorage.setItem('tema', temaActual);
        });
    </script>
    <script>
        // Función para mostrar/ocultar la sección de Todas las Peticiones
        function mostrarPeticiones() {
            const seccionPeticiones = document.getElementById('seccionPeticiones');
            if (seccionPeticiones.style.display === 'none') {
                seccionPeticiones.style.display = 'block';
                window.scrollTo({
                    top: seccionPeticiones.offsetTop,
                    behavior: 'smooth'
                });
            } else {
                seccionPeticiones.style.display = 'none';
            }
        }
    </script>
</body>
</html>
