<?php
// logout.php
session_start();

// Destruir todas las sesiones activas
session_destroy();

// Redirigir a la página de inicio de sesión
header("Location: index.php");
exit();
?>
