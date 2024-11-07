<?php
session_start();

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION["usuario"]) || $_SESSION["rol"] !== 'admin') {
    header("Location: index.php");
    exit();
}

$usuario = htmlspecialchars($_SESSION["usuario"]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Panel de Administrador</title>
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

        .card {
            background-color: var(--secondary-color);
            border: none;
            border-radius: 12px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.7);
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid #444;
        }

        .card-title {
            color: var(--primary-color);
            margin-bottom: 0;
        }

        img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            cursor: pointer;
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
                    <!-- Botón de Administrar -->
                    <li class="nav-item me-2">
                        <a href="admin.php" class="btn btn-outline-light">
                            <i class="fas fa-tasks me-2"></i> Administrar
                        </a>
                    </li>
                    <!-- Enlace al Dashboard -->
                    <li class="nav-item me-2">
                        <a href="dashboard.php" class="btn btn-outline-light active">
                            <i class="fas fa-chart-line me-2"></i> Dashboard
                        </a>
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
        <h1 class="text-warning mb-4" data-aos="fade-down"><i class="fas fa-chart-line me-2"></i> Dashboard</h1>
        <div class="row">
            <!-- Gráfico 1 -->
            <div class="col-md-6 mb-4" data-aos="zoom-in" data-aos-delay="100">
                <div class="card bg-dark text-light">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-chart-pie me-2"></i> Por estado</h5>
                    </div>
                    <div class="card-body">
                        <!-- Imagen ampliable -->
                        <img src="graficos/Por_Estado_Pastel.png" alt="Por Estado" class="img-fluid clickable-image">
                    </div>
                </div>
            </div>
            <!-- Gráfico 2 -->
            <div class="col-md-6 mb-4" data-aos="flip-left" data-aos-delay="200">
                <div class="card bg-dark text-light">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-chart-bar me-2"></i> Por hora</h5>
                    </div>
                    <div class="card-body">
                        <!-- Imagen ampliable -->
                        <img src="graficos/Por_Hora.png" alt="Por hora" class="img-fluid clickable-image">
                    </div>
                </div>
            </div>

            <!-- Gráfico 3 -->
            <div class="col-md-6 mb-4" data-aos="slide-up" data-aos-delay="300">
                <div class="card bg-dark text-light">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-chart-bar me-2"></i> Por tipo de servicio</h5>
                    </div>
                    <div class="card-body">
                        <!-- Imagen ampliable -->
                        <img src="graficos/Por_TipoDeServicio_Pastel.png" alt="Tipo de Servicio" class="img-fluid clickable-image">
                    </div>
                </div>
            </div>
            <!-- Gráfico 4 -->
            <div class="col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="card bg-dark text-light">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-chart-bar me-2"></i> Por validador</h5>
                    </div>
                    <div class="card-body">
                        <!-- Imagen ampliable -->
                        <img src="graficos/Por_validador.png" alt="Por validador" class="img-fluid clickable-image">
                    </div>
                </div>
            </div>
            <!-- Gráfico 5 -->
            <div class="col-md-6 mb-4" data-aos="zoom-in-up" data-aos-delay="500">
                <div class="card bg-dark text-light">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-chart-bar me-2"></i> Top 10 clientes más peticiones</h5>
                    </div>
                    <div class="card-body">
                        <!-- Imagen ampliable -->
                        <img src="graficos/Top10_Clientes_Peticiones.png" alt="Top 10 clientes más peticiones" class="img-fluid clickable-image">
                    </div>
                </div>
            </div>
             <!-- Gráfico 6 -->
             <div class="col-md-6 mb-4" data-aos="zoom-in-up" data-aos-delay="500">
                <div class="card bg-dark text-light">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-chart-bar me-2"></i> Tiempo de respuesta validadores</h5>
                    </div>
                    <div class="card-body">
                        <!-- Imagen ampliable -->
                        <img src="graficos/tiempo_respuesta_por_validador.png" alt="Tiempo de respuesta de validores" class="img-fluid clickable-image">
                    </div>
                </div>
            </div>
            <!-- Puedes añadir más gráficos y animaciones según sea necesario -->
        </div>
    </div>

    <!-- Modal para mostrar imágenes ampliadas -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark text-light">
          <div class="modal-header">
            <h5 class="modal-title" id="imageModalLabel">Vista ampliada</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <img src="" id="modalImage" class="img-fluid" alt="Imagen ampliada">
          </div>
        </div>
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

        // Funcionalidad de Lightbox para imágenes
        const images = document.querySelectorAll('.clickable-image');
        const modalImage = document.getElementById('modalImage');
        const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));

        images.forEach((img) => {
            img.addEventListener('click', () => {
                modalImage.src = img.src;
                imageModal.show();
            });
        });
    </script>
</body>
</html>
