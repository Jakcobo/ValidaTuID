<?php
// index.php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio - Sistema de Login</title>
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
            --button-success: #28a745;
            --button-success-hover: #218838;
            --button-danger: #dc3545;
            --button-danger-hover: #c82333;
            --text-color: #ffffff;
            --footer-background: #1a1a1a;
            --team-member-background: #2c2c2c;
            --team-member-text: #ffffff;
            --team-member-accent: #FFD700;
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

        .container {
            max-width: 500px;
            background-color: var(--secondary-color);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .container:hover {
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

        .btn-success {
            background-color: var(--button-success);
            border-color: var(--button-success);
        }

        .btn-success:hover {
            background-color: var(--button-success-hover);
            border-color: var(--button-success-hover);
        }

        .btn-danger {
            background-color: var(--button-danger);
            border-color: var(--button-danger);
        }

        .btn-danger:hover {
            background-color: var(--button-danger-hover);
            border-color: var(--button-danger-hover);
        }

        .btn-outline-secondary {
            color: var(--button-primary);
            border-color: var(--button-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .btn-outline-secondary:hover {
            background-color: var(--button-primary);
            color: var(--secondary-color);
        }

        footer {
            background-color: var(--footer-background);
            padding: 60px 0;
            margin-top: auto;
            animation: fadeInUp 1s ease-out;
        }

        .team-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
        }

        .team-member {
            max-width: 200px;
            text-align: center;
            background-color: var(--team-member-background);
            padding: 20px;
            border-radius: 10px;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .team-member[data-aos="fade-up"] {
            opacity: 1;
            transform: translateY(0);
        }

        .team-member img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }

        .team-member img:hover {
            transform: scale(1.1);
        }

        .team-member h5 {
            color: var(--team-member-text);
            margin-bottom: 5px;
        }

        .team-member p {
            color: var(--team-member-accent);
            margin-bottom: 15px;
        }

        .fab {
            font-size: 1.2em;
            margin: 0 8px;
            color: var(--button-primary);
            transition: color 0.3s ease;
        }

        .fab:hover {
            color: #ffffff;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .container {
                padding: 30px 20px;
            }

            .team-member {
                max-width: 150px;
            }

            .team-member img {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>

    <!-- Inicio de la sección principal -->
    <div class="d-flex justify-content-center align-items-center flex-grow-1">
        <div class="container text-center" data-aos="zoom-in">
            <h2 class="mb-5 text-warning">Bienvenido al Sistema de Login</h2>
            
            <div class="login-option mb-4" data-aos="fade-up" data-aos-delay="100">
                <a href="loginCliente.php" class="btn btn-primary btn-lg w-100 btn-custom">
                    <i class="fas fa-user mr-2"></i> Iniciar Sesión como Cliente
                </a>
            </div>
            <div class="login-option mb-4" data-aos="fade-up" data-aos-delay="200">
                <a href="loginValidador.php" class="btn btn-success btn-lg w-100 btn-custom">
                    <i class="fas fa-check-circle mr-2"></i> Iniciar Sesión como Validador
                </a>
            </div>
            <div class="login-option mb-5" data-aos="fade-up" data-aos-delay="300">
                <a href="loginAdmin.php" class="btn btn-danger btn-lg w-100 btn-custom">
                    <i class="fas fa-user-shield mr-2"></i> Iniciar Sesión como Administrador
                </a>
            </div>
            
            <div data-aos="fade-up" data-aos-delay="400">
                <a href="registro.php" class="btn btn-outline-secondary">
                    <i class="fas fa-user-plus mr-2"></i> ¿No tienes una cuenta? Regístrate aquí
                </a>
            </div>
        </div>
    </div>
    <!-- Fin de la sección principal -->

    <!-- Sección de nuestro equipo -->
    <footer>
        <div class="container">
            <h3 class="text-center mb-5 text-warning">Nuestro Equipo</h3>
            <div class="team-container">
                <div class="team-member" data-aos="fade-up">
                    <img src="https://cdn-icons-png.flaticon.com/512/4113/4113045.png" alt="Yáxul S. Cárdenas">
                    <h5>Yáxul S. Cárdenas</h5>
                    <p class="text-muted">Humildad</p>
                    <div>
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="team-member" data-aos="fade-up" data-aos-delay="100">
                    <img src="https://cdn-icons-png.flaticon.com/512/4113/4113045.png" alt="Gabriel Martínez">
                    <h5>Gabriel Martínez</h5>
                    <p class="text-muted">Manager</p>
                    <div>
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="team-member" data-aos="fade-up" data-aos-delay="200">
                    <img src="https://cdn-icons-png.flaticon.com/512/4113/4113045.png" alt="Jacobo Delgado">
                    <h5>Jacobo Delgado</h5>
                    <p class="text-muted">Líder</p>
                    <div>
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="team-member" data-aos="fade-up" data-aos-delay="300">
                    <img src="https://png.pngtree.com/png-vector/20230509/ourmid/pngtree-avatar-woman-glasses-vector-png-image_7090474.png" alt="Yesenia Díaz">
                    <h5>Yesenia Díaz</h5>
                    <p class="text-muted">Coordinadora</p>
                    <div>
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- Fin de la sección de nuestro equipo -->

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
