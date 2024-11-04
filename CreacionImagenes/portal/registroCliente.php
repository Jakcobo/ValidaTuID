<?php
// registroCliente.php
session_start();

// Verificar que los campos no estén vacíos
if (empty($_POST['cc']) || empty($_POST['password'])) {
    header("Location: registro.php?error=campos_vacios");
    exit();
}

$cc = $_POST['cc'];
$password = $_POST['password'];

// URL del microservicio para registrar al cliente
$servurl = "http://balanceadors1:3001/clientes";
$data = [
    'cc' => $cc,
    'password' => $password
];

// Enviar la solicitud al microservicio
$curl = curl_init($servurl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

// Manejo de la respuesta del microservicio
if ($httpCode === 201) {
    $_SESSION['cc'] = $cc;
    header("Location: usuario.php"); // Redirigir al panel del cliente
} elseif ($httpCode === 409) { // Código HTTP 409 para conflicto (cédula duplicada)
    header("Location: registro.php?error=duplicado"); // Notificación de duplicado
} else {
    header("Location: registro.php?error=error_general");
}
?>
