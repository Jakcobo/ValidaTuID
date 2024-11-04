<?php
// editarUsuario.php
session_start();

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION["usuario"]) || $_SESSION["rol"] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Verificar que se haya pasado el usuario a editar
if (empty($_GET['usuario'])) {
    header("Location: admin.php?error=error_obtener_usuario");
    exit();
}

$usuario = $_GET['usuario'];

// Obtener los detalles del usuario desde el microservicio
$servurlUsuario = "http://balanceadors2:3002/usuarios/" . urlencode($usuario);
$curl = curl_init($servurlUsuario);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$responseUsuario = curl_exec($curl);
$httpCodeUsuario = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($httpCodeUsuario === 200) {
    $usr = json_decode($responseUsuario);
} else {
    header("Location: admin.php?error=error_obtener_usuario");
    exit();
}

// Manejo de mensajes de éxito o error
$mensaje = '';
$error = '';
if (isset($_GET['mensaje'])) {
    if ($_GET['mensaje'] === 'usuario_actualizado') {
        $mensaje = "Usuario actualizado exitosamente.";
    }
}

if (isset($_GET['error'])) {
    if ($_GET['error'] === 'error_actualizar_usuario') {
        $error = "Error al actualizar el usuario. Inténtalo nuevamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
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
            max-width: 700px;
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

        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
        }

        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #d39e00;
        }

        .btn-outline-light {
            color: var(--primary-color);
            border-color: var(--primary-color);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .btn-outline-light:hover {
            background-color: var(--primary-color);
            color: var(--secondary-color);
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
            <a class="navbar-brand" href="#">Panel de Administrador</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="navbar-text text-white">
                        Bienvenido, <?php echo htmlspecialchars($_SESSION["usuario"]); ?>
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
        <div class="container-custom text-center" data-aos="zoom-in">
            <h2 class="mb-5 text-warning">Editar Usuario</h2>
            
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

            <!-- Formulario para editar un usuario -->
            <div class="mb-4">
                <h4 class="text-warning mb-4">Usuario: <?php echo htmlspecialchars($usr->usuario); ?></h4>
                <form action="actualizarUsuario.php" method="post" data-aos="fade-up" data-aos-delay="100">
                    <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($usr->usuario); ?>">
                    <div class="mb-3 text-start">
                        <label for="nombre" class="form-label"><i class="fas fa-user me-2"></i> Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usr->nombre); ?>" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label for="rol" class="form-label"><i class="fas fa-briefcase me-2"></i> Rol</label>
                        <select class="form-select" id="rol" name="rol" required>
                            <option value="admin" <?php echo ($usr->rol === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                            <option value="validador" <?php echo ($usr->rol === 'validador') ? 'selected' : ''; ?>>Validador</option>
                        </select>
                    </div>
                    <div class="mb-4 text-start">
                        <label for="password" class="form-label"><i class="fas fa-lock me-2"></i> Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" value="<?php echo htmlspecialchars($usr->password); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-warning btn-lg w-100 btn-custom"><i class="fas fa-save me-2"></i> Actualizar Usuario</button>
                </form>
            </div>
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
