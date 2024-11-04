<?php
// verPeticionesCliente.php
session_start();
date_default_timezone_set('America/Bogota');  // Establecer zona horaria

// Verificar si el usuario ha iniciado sesión y es validador
if (!isset($_SESSION["usuario"]) || $_SESSION["rol"] !== 'validador') {
    header("Location: index.php");
    exit();
}

$cc = $_GET['cc']; // Asegúrate de validar y sanitizar esta entrada

$servurl = "http://balanceadors1:3003/peticiones/cliente/" . urlencode($cc);

$curl = curl_init($servurl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($httpCode === 200) {
    $peticiones = json_decode($response, true);
} else {
    $error = "Error al obtener las peticiones del usuario";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Peticiones del Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Peticiones del Cliente: <?php echo htmlspecialchars($cc); ?></h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php else: ?>
            <?php if (!empty($peticiones)): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Archivo</th>
                            <th>Tipo de Servicio</th>
                            <th>Banco</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($peticiones as $peticion): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($peticion['id']); ?></td>
                                <td><?php echo htmlspecialchars($peticion['ccarchivo']); ?></td>
                                <td><?php echo htmlspecialchars($peticion['tiposervicio']); ?></td>
                                <td><?php echo htmlspecialchars($peticion['bancocliente']); ?></td>
                                <td><?php echo htmlspecialchars($peticion['estado']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">No hay peticiones para este cliente.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
