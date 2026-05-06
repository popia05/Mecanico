<?php
$servidor = "localhost";
$usuario  = "root";
$password = ""; 
$base_datos = "mecanico"; // <--- Asegúrate de que esté igual que en phpMyAdmin

try {
    $conexion = new PDO("mysql:host=$servidor;dbname=$base_datos;charset=utf8", $usuario, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Esta línea es útil ahora que empiezas para confirmar que conectó
    // echo "Conexión exitosa a la base de datos MECANICO"; 
    
} catch (PDOException $error) {
    echo "Error de conexión: " . $error->getMessage();
}
?>