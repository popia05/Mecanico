<?php
$p = $_GET['p'] ?? 'panel';
$paginas = ['panel','perfil','ordenes','gestion','inventario','nota-remision','nota-detalle','configuracion'];
if (!in_array($p, $paginas)) $p = 'panel';

$titulos = [
    'panel' => 'Panel',
    'perfil' => 'Detalles de Empleado',
    'ordenes' => 'Ordenes Asignadas',
    'gestion' => 'Gestion de Ordenes',
    'inventario' => 'Ver Inventario',
    'nota-remision' => 'Notas De Remision',
    'nota-detalle' => 'Nota de Remision',
    'configuracion' => 'Configuracion'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $titulos[$p] ?></title>
<link rel="stylesheet" href="../../css/mecanico.css?v=2">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="contenedor">
<?php include 'header.php'; ?>
<main class="contenido">
    <header class="cabecera">
        <h1><?= $titulos[$p] ?></h1>
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