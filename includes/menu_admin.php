<?php
// ============================================================
//  menu_admin.php — Sidebar del administrador
//  Requiere que auth.php ya esté incluido (sesión activa)
// ============================================================
$pagina_actual = $_GET['p'] ?? 'panel';
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="../../imagenes/logo.png" alt="Logo" onerror="this.style.display='none'">
        <h2>Menú</h2>
        <span>Categorías</span>
    </div>

    <nav class="sidebar-nav">
        <a href="Index.php?p=panel" class="nav-item <?= $pagina_actual === 'panel' ? 'activo' : '' ?>">
            <i class="fas fa-th-large"></i>
            <span>Panel</span>
        </a>

        <!-- Perfil / Usuario -->
        <div class="nav-item <?= in_array($pagina_actual, ['perfil', 'ordenes']) ? 'abierto' : '' ?>"
             onclick="toggleSubmenu(this)">
            <i class="fas fa-user"></i>
            <span>Perfil</span>
            <i class="fas fa-chevron-down flecha"></i>
        </div>
        <div class="submenu <?= in_array($pagina_actual, ['perfil', 'ordenes']) ? 'abierto' : '' ?>">
            <a href="Index.php?p=perfil" class="nav-item <?= $pagina_actual === 'perfil' ? 'activo' : '' ?>">
                <i class="fas fa-info-circle"></i>
                <span>Administrador</span>
            </a>
            <a href="Index.php?p=ordenes" class="nav-item <?= $pagina_actual === 'ordenes' ? 'activo' : '' ?>">
                <i class="fas fa-clipboard-list"></i>
                <span>Tareas Asignadas</span>
            </a>
        </div>

        <a href="Index.php?p=gestion-ordenes" class="nav-item <?= $pagina_actual === 'gestion-ordenes' ? 'activo' : '' ?>">
            <i class="fas fa-tasks"></i>
            <span>Gestión de Órdenes</span>
        </a>

        <a href="Index.php?p=inventario" class="nav-item <?= $pagina_actual === 'inventario' ? 'activo' : '' ?>">
            <i class="fas fa-wrench"></i>
            <span>Ver Inventario</span>
        </a>

        <a href="Index.php?p=auditoria" class="nav-item <?= $pagina_actual === 'auditoria' ? 'activo' : '' ?>">
            <i class="fas fa-shield-alt"></i>
            <span>Auditoría</span>
        </a>

        <a href="Index.php?p=nota-remision" class="nav-item <?= $pagina_actual === 'nota-remision' ? 'activo' : '' ?>">
            <i class="fas fa-file-invoice"></i>
            <span>Nota de Remisión</span>
        </a>

        <a href="Index.php?p=respaldo" class="nav-item <?= $pagina_actual === 'respaldo' ? 'activo' : '' ?>">
            <i class="fas fa-database"></i>
            <span>Respaldo</span>
        </a>

        <!-- Cerrar sesión -->
        <a href="Index.php?logout=1" class="nav-item"
           onclick="return confirm('¿Cerrar sesión?')">
            <i class="fas fa-sign-out-alt"></i>
            <span>Cerrar Sesión</span>
        </a>
    </nav>

    <!-- Usuario desde sesión -->
    <div class="sidebar-usuario">
        <div class="avatar-usuario">
            <?= htmlspecialchars(sesion_iniciales()) ?>
        </div>
        <div>
            <h4><?= htmlspecialchars(sesion_nombre()) ?></h4>
            <span>Administrador</span>
        </div>
    </div>
</aside>