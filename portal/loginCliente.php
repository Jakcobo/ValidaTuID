<?php
// loginCliente.php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Cliente</title>
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

        .container-custom {
            max-width: 500px;
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

        .btn-outline-secondary {
            color: var(--button-primary);
            border-color: var(--button-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .btn-outline-secondary:hover {
            background-color: var(--button-primary);
            color: var(--secondary-color);
        }

        input {
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

        .alert {
            margin-top: 10px;
            background-color: rgba(220, 53, 69, 0.9);
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
    <!-- Sección Principal -->
    <div class="d-flex justify-content-center align-items-center flex-grow-1">
        <div class="container-custom text-center" data-aos="zoom-in">
            <h2 class="mb-5 text-warning">Iniciar Sesión como Cliente</h2>
            
            <!-- Mensaje de error -->
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php
                        switch ($_GET['error']) {
                            case 'campos_vacios':
                                echo "Por favor, completa todos los campos.";
                                break;
                            case 'usuario_incorrecto':
                                echo "Cédula o contraseña incorrectos.";
                                break;
                            default:
                                echo "Ha ocurrido un error.";
                        }
                    ?>
                </div>
            <?php endif; ?>
            
            <form action="iniciarSesion.php" method="post">
                <div class="mb-3 text-start">
                    <label for="cc" class="form-label">Cédula</label>
                    <input type="text" class="form-control" id="cc" name="cc" required>
                </div>
                <div class="mb-4 text-start">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100 btn-custom">
                    <i class="fas fa-sign-in-alt me-2"></i> Iniciar Sesión
                </button>
            </form>
            <div class="mt-3 text-center">
                <a href="index.php" class="text-warning"><i class="fas fa-home me-2"></i> Volver al Inicio</a>
            </div>
        </div>
    </div>
    <!-- Fin de la Sección Principal -->

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
