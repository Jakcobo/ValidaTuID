<?php
// verTodasPeticiones.php
session_start();
date_default_timezone_set('America/Bogota');  // Establecer zona horaria

// Verificar si el usuario ha iniciado sesión y tiene el rol adecuado (admin o validador)
if (!isset($_SESSION["usuario"]) || !in_array($_SESSION["rol"], ['admin', 'validador'])) {
    header("Location: index.php");
    exit();
}

// Definir la variable $usuario para mostrar en la barra de navegación
$usuario = htmlspecialchars($_SESSION["usuario"]);

// URL del microservicio para obtener todas las peticiones
// Asegúrate de que este endpoint devuelve todas las peticiones con los detalles necesarios
$servurlPeticiones = "http://balanceadors1:3003/peticiones/";

// Inicializar cURL
$curl = curl_init($servurlPeticiones);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

// Si la API requiere autenticación, agrega los encabezados necesarios
// Por ejemplo, si usas tokens, agrega algo como:
// curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $_SESSION['token']]);

$responsePeticiones = curl_exec($curl);
$httpCodePeticiones = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

// Verificar la respuesta del microservicio
if ($httpCodePeticiones === 200) {
    $peticiones = json_decode($responsePeticiones, true);
} else {
    $peticiones = [];
    $error = "Error al obtener las peticiones. Código de respuesta: $httpCodePeticiones";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Todas las Peticiones</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation Library -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <style>
        /* Estilos personalizados */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            padding-top: 70px; /* Para la barra de navegación fija */
        }
        .container-custom {
            max-width: 1200px;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .table-responsive {
            margin-top: 20px;
        }
        .navbar-brand {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Panel de <?php echo ucfirst($_SESSION["rol"]); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="navbar-text text-white">
                            Bienvenido, <?php echo $usuario; ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-outline-light ms-3">
                            <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="container container-custom" data-aos="fade-up">
        <h2 class="text-center text-warning mb-4"><i class="fas fa-eye me-2"></i> Todas las Peticiones</h2>

        <!-- Mostrar mensaje de error si existe -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Tabla de peticiones -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID de Solicitud</th>
                        <th>Cédula del Cliente</th>
                        <th>Validador</th>
                        <th>Tipo de Solicitud</th>
                        <th>Estado</th>
                        <th>Fecha y Hora</th>
                        <th>Archivo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($peticiones)): ?>
                        <?php foreach ($peticiones as $peticion): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($peticion['id']); ?></td>
                                <td><?php echo htmlspecialchars($peticion['cccliente']); ?></td>
                                <td><?php echo htmlspecialchars($peticion['usuariovalidador']); ?></td>
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
                                <td><?php echo htmlspecialchars($peticion['fechasolicitud']); ?> <?php echo htmlspecialchars($peticion['horasolicitud']); ?></td>
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
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay peticiones para mostrar.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

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
