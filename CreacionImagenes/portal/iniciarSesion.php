<?php
// iniciarSesion.php
session_start();

// Verificar que los campos no estén vacíos
if (empty($_POST['cc']) || empty($_POST['password'])) {
    header("Location: loginCliente.php?error=campos_vacios");
    exit();
}

$cc = $_POST['cc'];
$password = $_POST['password'];

// URL del microservicio para validar al cliente
$servurl = "http://balanceadors1:3001/clientes/$cc/$password";

// Enviar la solicitud al microservicio
$curl = curl_init($servurl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

// Manejo de la respuesta del microservicio
if ($httpCode === 200) {
    $clienteData = json_decode($response);
    if ($clienteData->password === $password) { // Verificar la contraseña
        $_SESSION['cc'] = $clienteData->cc;
        header("Location: usuario.php"); // Redirigir al panel del cliente
        exit();
    } else {
        header("Location: loginCliente.php?error=usuario_incorrecto");
        exit();
    }
} else {
    header("Location: loginCliente.php?error=usuario_incorrecto");
    exit();
}
?>
