<?php
// crearPeticion.php
session_start();
date_default_timezone_set('America/Bogota');  // Establecer zona horaria

// Habilitar la visualización de errores para depuración (solo en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION["cc"])) {
    header("Location: index.php");
    exit();
}

// Verificar que los campos no estén vacíos
if (empty($_FILES['ccarchivo']['name']) || empty($_POST['tiposervicio']) || empty($_POST['bancocliente'])) {
    header("Location: usuario.php?error=campos_vacios");
    exit();
}

// Procesar el archivo subido
$archivoTmpPath = $_FILES['ccarchivo']['tmp_name'];
$archivoNombre = $_FILES['ccarchivo']['name'];
$archivoTamaño = $_FILES['ccarchivo']['size'];
$archivoTipo = $_FILES['ccarchivo']['type'];
$archivoError = $_FILES['ccarchivo']['error'];

// Verificar si hubo un error en la subida
if ($archivoError !== UPLOAD_ERR_OK) {
    header("Location: usuario.php?error=error_subida_archivo");
    exit();
}

// Validar el tipo de archivo
$extensionesPermitidas = ['jpg', 'jpeg', 'png', 'pdf'];
$archivoExt = strtolower(pathinfo($archivoNombre, PATHINFO_EXTENSION));

if (!in_array($archivoExt, $extensionesPermitidas)) {
    header("Location: usuario.php?error=tipo_archivo_no_permitido");
    exit();
}

// Validar el tamaño del archivo (máximo 5MB)
$maxSize = 5 * 1024 * 1024;
if ($archivoTamaño > $maxSize) {
    header("Location: usuario.php?error=archivo_demasiado_grande");
    exit();
}

// Generar un nombre único para el archivo
$nuevoNombreArchivo = uniqid('ccarchivo_', true) . '.' . $archivoExt;

// Directorio de destino
$destino = 'uploads/ccarchivos/';
if (!is_dir($destino)) {
    mkdir($destino, 0755, true);
}

// Mover el archivo al directorio de destino
if (!move_uploaded_file($archivoTmpPath, $destino . $nuevoNombreArchivo)) {
    header("Location: usuario.php?error=error_subida_archivo");
    exit();
}

// Preparar los datos para el microservicio
$cccliente = $_SESSION['cc'];
$tiposervicio = $_POST['tiposervicio'];
$bancocliente = $_POST['bancocliente'];

$data = [
    'cccliente' => $cccliente,
    'ccarchivo' => $nuevoNombreArchivo, // Guardar el nombre del archivo
    'tiposervicio' => $tiposervicio,
    'bancocliente' => $bancocliente
];

// URL del microservicio para crear una petición
$servurl = "http://balanceadors1:3003/peticiones";

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
    header("Location: usuario.php?mensaje=peticion_creada");
} else {
    header("Location: usuario.php?error=error_crear_peticion");
}
?>
