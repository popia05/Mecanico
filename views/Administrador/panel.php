<<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel - Auto Master</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/estilos-generales.css">
</head>
<body>
<div class="contenedor">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="sidebar-logo-img"><i class="fas fa-wrench"></i></div>
            <div class="sidebar-logo-texto"><h2>Menu</h2><span>Categorias</span></div>
        </div>
        <nav class="sidebar-nav">
            <a href="panel.php" class="nav-item activo"><i class="fas fa-th-large"></i><span>Panel</span></a>

            <div class="nav-item submenu-toggle" onclick="toggleSubmenu('perfil')">
                <i class="fas fa-user"></i><span>Usuario</span>
                <i class="fas fa-chevron-down flecha" id="flecha-perfil"></i>
            </div>
            <div class="submenu" id="submenu-perfil">
                <a href="informacion-admin.php" class="nav-item"><i class="fas fa-info-circle"></i><span>Administrador</span></a>
                <a href="informacion-empleados.php" class="nav-item"><i class="fas fa-info-circle"></i><span>Empleados</span></a>
                <a href="agregar-empleado.php" class="nav-item"><i class="fas fa-user-plus"></i><span>Agregar Empleados</span></a>
            </div>

            <div class="nav-item submenu-toggle" onclick="toggleSubmenu('tareas')">
                <i class="fas fa-tasks"></i><span>Gestion de Tareas</span>
                <i class="fas fa-chevron-down flecha" id="flecha-tareas"></i>
            </div>
            <div class="submenu" id="submenu-tareas">
                <a href="gestion-ordenes.php" class="nav-item"><i class="fas fa-info-circle"></i><span>Gestion de Ordenes</span></a>
                <a href="inventario.php" class="nav-item"><i class="fas fa-wrench"></i><span>Ver Inventario</span></a>
                <a href="auditoria.php" class="nav-item"><i class="fas fa-shield-alt"></i><span>Auditoria</span></a>
            </div>

            <a href="nota-remision.php" class="nav-item"><i class="fas fa-file-invoice"></i><span>Notas de Remisión</span></a>
            <a href="login.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Cerrar Sesión</span></a>
        </nav>
        <div class="sidebar-usuario">
            <div class="sidebar-usuario-avatar">DG</div>
            <div class="sidebar-usuario-info"><h4>Daniel G.</h4><span>Administrador</span></div>
        </div>
    </aside>

    <!-- CONTENIDO -->
    <main class="contenido">
        <header class="cabecera">
            <div class="cabecera-titulo">
                <h1>Panel Principal</h1>
            </div>
            <div class="cabecera-acciones">
                <button><i class="fas fa-search"></i></button>
                <button><i class="fas fa-bell"></i></button>
                <button><i class="fas fa-question-circle"></i></button>
            </div>
        </header>

        <div class="pagina">
            <div class="pagina-titulo">
                <h2>Bienvenido al Panel</h2>
                <p>Sistema de gestion - Fuel Injection Auto Master</p>
            </div>

            <!-- Tarjetas de resumen -->
            <div class="tarjetas-resumen">
                <div class="tarjeta-resumen">
                    <div class="tarjeta-icono azul"><i class="fas fa-car"></i></div>
                    <div class="tarjeta-info"><h3>12</h3><span>Ordenes Activas</span></div>
                </div>
                <div class="tarjeta-resumen">
                    <div class="tarjeta-icono verde"><i class="fas fa-check-circle"></i></div>
                    <div class="tarjeta-info"><h3>45</h3><span>Completadas</span></div>
                </div>
                <div class="tarjeta-resumen">
                    <div class="tarjeta-icono naranja"><i class="fas fa-clock"></i></div>
                    <div class="tarjeta-info"><h3>3</h3><span>Pendientes</span></div>
                </div>
                <div class="tarjeta-resumen">
                    <div class="tarjeta-icono rojo"><i class="fas fa-tools"></i></div>
                    <div class="tarjeta-info"><h3>8</h3><span>Herramientas</span></div>
                </div>
            </div>

            <!-- Accesos rapidos -->
            <div class="seccion-titulo">
                <h3>Accesos Rapidos</h3>
            </div>
            <div class="accesos-rapidos">
                <a href="gestion-ordenes.php" class="acceso-rapido">
                    <i class="fas fa-plus-circle"></i>
                    <span>Nueva Orden</span>
                </a>
                <a href="inventario.php" class="acceso-rapido">
                    <i class="fas fa-boxes"></i>
                    <span>Ver Inventario</span>
                </a>
                <a href="nota-remision.php" class="acceso-rapido">
                    <i class="fas fa-file-alt"></i>
                    <span>Crear Nota</span>
                </a>
                <a href="gestion-ordenes.php" class="acceso-rapido">
                    <i class="fas fa-list-check"></i>
                    <span>Mis Tareas</span>
                </a>
            </div>
        </div>
    </main>

</div>

<script src="../../js/menu.js"></script>
</body>
</html>