<?php
session_start();
session_destroy(); // Destruir la sesión
header('Location: /php/login.php'); // Redirigir al login
exit;
?>
