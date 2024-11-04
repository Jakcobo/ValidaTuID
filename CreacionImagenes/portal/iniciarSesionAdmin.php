<?php
// iniciarSesionAdmin.php
session_start();

// Verificar que los campos no estén vacíos
if (empty($_POST['usuario']) || empty($_POST['password'])) {
    header("Location: loginAdmin.php?error=campos_vacios");
    exit();
}

$usuario = $_POST['usuario'];
$password = $_POST['password'];

// URL del microservicio para validar al administrador
$servurl = "http://balanceadors2:3002/usuarios/$usuario";

// Enviar la solicitud al microservicio
$curl = curl_init($servurl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($httpCode === 200) {
    $usuarioData = json_decode($response);
    if ($usuarioData->password === $password && $usuarioData->rol === 'admin') {
        $_SESSION['usuario'] = $usuarioData->usuario;
        $_SESSION['rol'] = $usuarioData->rol;
        header("Location: admin.php"); // Redirigir al panel de administrador
        exit();
    } else {
        header("Location: loginAdmin.php?error=no_admin");
        exit();
    }
} else {
    header("Location: loginAdmin.php?error=usuario_incorrecto");
    exit();
}
?>
