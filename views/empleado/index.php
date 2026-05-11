<?php
// ============================================================
//  index.php — Panel del Mecánico
//  Ruta: views/empleado/index.php
// ============================================================
require_once '../../php/db_conexion.php';
require_once '../../php/auth.php';

requerir_mecanico(); // Redirige al login si no es mecánico

// Cerrar sesión
if (isset($_GET['logout'])) {
    cerrar_sesion();
}

$p = $_GET['p'] ?? 'panel';
$paginas = ['panel','perfil','ordenes','gestion','inventario','nota-remision','nota-detalle','cerrar-sesion'];
if (!in_array($p, $paginas)) $p = 'panel';

$titulos = [
    'panel'         => 'Panel',
    'perfil'        => 'Mi Perfil',
    'ordenes'       => 'Órdenes Asignadas',
    'gestion'       => 'Gestión de Órdenes',
    'inventario'    => 'Ver Inventario',
    'nota-remision' => 'Notas de Remisión',
    'nota-detalle'  => 'Detalle de Nota',
    'cerrar-sesion' => 'Cerrar Sesión',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulos[$p]) ?> - Auto Master</title>
    <link rel="stylesheet" href="../../css/mecanico.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="contenedor">
    <?php include 'header.php'; ?>
    <main class="contenido">
        <header class="cabecera">
            <h1><?= htmlspecialchars($titulos[$p]) ?></h1>
           <div class="cabecera-acciones">
                <button><i class="fas fa-search"></i></button>
                <button class="boton-notif"><i class="fas fa-bell"></i></button>
                <button><i class="fas fa-question-circle"></i></button>
            </div>
        </header>
        <div class="pagina">
            <?php include $p . '.php'; ?>
        </div>
    </main>
</div>
<script src="../../js/menu_mecanico.js?v=2"></script>
</body>
</html>