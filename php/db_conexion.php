<?php
$servidor = "localhost";
$usuario  = "root";
$password = ""; 
$base_datos = "mecanico"; 

try {
    $conexion = new PDO("mysql:host=$servidor;dbname=$base_datos;charset=utf8", $usuario, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    
    
} catch (PDOException $error) {
    echo "Error de conexión: " . $error->getMessage();
}
?>