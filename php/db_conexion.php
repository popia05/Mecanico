<?php
// Datos de la base de datos
$servidor = "localhost";
$usuario  = "root";
$password = ""; // En XAMPP por defecto está vacío
$base_datos = "mecanico"; // Asegúrate de que este nombre sea igual al de phpMyAdmin

try {
    // Crear la conexión
    $conexion = new PDO("mysql:host=$servidor;dbname=$base_datos;charset=utf8", $usuario, $password);
    
    
    
    

} catch (PDOException $error) {
    echo "Error de conexión: " . $error->getMessage();
}
?>