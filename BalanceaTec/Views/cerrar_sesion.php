<?php
session_start();

//Destruir la sesión
session_unset();
session_destroy();

//Redirigir al usuario a la página de inicio de sesión
header("Location: ../../inicio_sesion.php");
exit;
?>
