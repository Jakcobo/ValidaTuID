<?php
// crearUsuario.php
session_start();

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION["usuario"]) || $_SESSION["rol"] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Verificar que los campos no estén vacíos
if (empty($_POST['usuario']) || empty($_POST['nombre']) || empty($_POST['rol']) || empty($_POST['password'])) {
    header("Location: admin.php?error=error_crear_usuario");
    exit();
}

$usuario = $_POST['usuario'];
$nombre = $_POST['nombre'];
$rol = $_POST['rol'];
$password = $_POST['password'];

// URL del microservicio para crear un usuario
$servurl = "http://balanceadors2:3002/usuarios";
$data = [
    'usuario' => $usuario,
    'nombre' => $nombre,
    'rol' => $rol,
    'password' => $password
];

// Inicializar cURL
$curl = curl_init($servurl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

// Ejecutar la solicitud
$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

// Manejo de la respuesta del microservicio
if ($httpCode === 201) {
    header("Location: admin.php?mensaje=usuario_creado");
} elseif ($httpCode === 409) { // Código HTTP 409 para conflicto (usuario duplicado)
    header("Location: admin.php?error=duplicado");
} else {
    header("Location: admin.php?error=error_crear_usuario");
}
?>
