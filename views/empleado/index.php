<?php
$pagina = $_GET['p'] ?? 'panel';

$paginas_validas = ['panel','perfil','ordenes','gestion','inventario','auditoria','nota-remision','factura','configuracion'];

if (!in_array($pagina, $paginas_validas)) {
    $pagina = 'panel';
}

$titulos = [
    'panel' => 'Panel Principal',
    'perfil' => 'Informacion del Empleado',
    'ordenes' => 'Tareas Asignadas',
    'gestion' => 'Gestion de Ordenes',
    'inventario' => 'Inventario',
    'auditoria' => 'Auditoria - Panel Admin',
    'nota-remision' => 'Notas De Remision',
    'factura' => 'Factura',
    'configuracion' => 'Configuracion'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulos[$pagina] ?></title>
    <link rel="stylesheet" href="../css/mecanico.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="contenedor">
        <?php include '../includes/menu_mecanico.php'; ?>

        <main class="contenido">
            <header class="cabecera">
                <h1><?= $titulos[$pagina] ?></h1>
                <div class="cabecera-acciones">
                    <button><i class="fas fa-search"></i></button>
                    <button><i class="fas fa-bell"></i></button>
                    <button><i class="fas fa-question-circle"></i></button>
                </div>
            </header>

            <div class="pagina">
                <?php include $pagina . '.php'; ?>
            </div>
        </main>
    </div>

    <script src="../js/menu_mecanico.js"></script>
</body>
</html>