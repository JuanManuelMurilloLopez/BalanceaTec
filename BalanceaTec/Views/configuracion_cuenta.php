<?php
session_start();

//Verificar si el usuario está logueado
if (!isset($_SESSION['user_ID'])) {
    header("Location: ../../inicio_sesion.php"); //Redirigir a login si no está logueado
    exit;
}

//Recuperar el user_ID de la sesión
$user_ID = $_SESSION['user_ID'];
$username = $_SESSION['user_name'];

//Credenciales para conectarse a la base
$host = 'localhost';
$user = 'reto';
$password = '';
$database = 'iot_reto';

//Conectar a la base de datos
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//Consulta para obtener los datos del usuario
$query = "SELECT * FROM user_account WHERE user_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_ID);
$stmt->execute();
$result = $stmt->get_result();

//Verificar si el usuario existe en la base de datos
if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc(); //Obtener los datos del usuario
    $username = $user_data['user_name']; 
    $password = $user_data['password'];
} else {
    echo "Error: No se encontraron los datos del usuario.";
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BalanceaTec | Cuenta</title>
</head>
<body>
    <div id="Titulo">
        <h1>BalanceaTec</h1>
    </div>
    <div id="Info_Cuenta">
    <h2>Cuenta: <?php echo htmlspecialchars($username) ?></h2>
    </div>
    <div id="Opciones">
        <button>Añadir Dispositivo</button>
        <button onclick="window.location.href = 'cambiar_contrasena.php'">Cambiar Contraseña</button>
    </div>
    
    
</body>
</html>
