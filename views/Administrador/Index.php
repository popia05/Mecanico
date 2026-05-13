<?php
require_once '../../php/db_conexion.php';
require_once '../../php/auth.php';

requerir_admin();

$pagina = $_GET['p'] ?? 'panel';

$paginas_validas = [
    'panel', 'perfil', 'ordenes', 'gestion-ordenes',
    'inventario', 'auditoria', 'nota-remision',
    'respaldo', 'informacion-admin', 'informacion-empleados',
    'informacion-clientes', 'agregar-empleado', 'tareas-asignadas',
    'informacion', 'editar-empleado'
];

if (!in_array($pagina, $paginas_validas)) {
    $pagina = 'panel';
}

$titulos = [
    'panel'                 => 'Panel Principal',
    'perfil'                => 'Información del Administrador',
    'ordenes'               => 'Tareas Asignadas',
    'gestion-ordenes'       => 'Gestión de Órdenes',
    'inventario'            => 'Inventario',
    'auditoria'             => 'Auditoría',
    'nota-remision'         => 'Notas de Remisión',
    'respaldo'              => 'Respaldo',
    'informacion-admin'     => 'Información Admin',
    'informacion-empleados' => 'Información Empleados',
    'informacion-clientes'  => 'Información Clientes',
    'agregar-empleado'      => 'Agregar Empleado',
    'tareas-asignadas'      => 'Tareas Asignadas',
    'informacion'           => 'Información',
    'editar-empleado'       => 'Editar Empleado',
];

// Cerrar sesión
if (isset($_GET['logout'])) {
    cerrar_sesion();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulos[$pagina]) ?> - Auto Master</title>
    <link rel="stylesheet" href="../../css/estilos-generales.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="contenedor">
        <?php include '../../includes/menu_admin.php'; ?>

        <main class="contenido">
            <header class="cabecera">
                <div class="cabecera-titulo">
                    <h1><?= htmlspecialchars($titulos[$pagina]) ?></h1>
                </div>
                <div class="cabecera-acciones">
                    <button><i class="fas fa-search"></i></button>
                    <button><i class="fas fa-bell"></i></button>
                    <button><i class="fas fa-question-circle"></i></button>
                </div>
            </header>

            <div class="pagina">
                <?php include __DIR__ . '/' . $pagina . '.php'; ?>
            </div>
        </main>
    </div>

    <script src="../../js/menu.js"></script>
</body>
</html>