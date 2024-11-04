<?php
// eliminarUsuario.php
session_start();

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION["usuario"]) || $_SESSION["rol"] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Verificar que se haya recibido el usuario a eliminar
if (empty($_POST['usuario'])) {
    header("Location: admin.php?error=error_eliminar_usuario");
    exit();
}

$usuario = $_POST['usuario'];

// URL del microservicio para eliminar el usuario
$servurl = "http://balanceadors2:3002/usuarios/$usuario";

// Enviar la solicitud al microservicio
$curl = curl_init($servurl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

// Manejo de la respuesta del microservicio
if ($httpCode === 200) {
    header("Location: admin.php?mensaje=usuario_eliminado");
} else {
    header("Location: admin.php?error=error_eliminar_usuario");
}
?>
