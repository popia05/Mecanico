<?php
// menu_admin.php — sidebar para el panel de Administrador
$pagina_actual = $_GET['p'] ?? 'panel';
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="../imagenes/logo.png" alt="Logo" onerror="this.style.display='none'">
        <h2>Menu</h2>
        <span>Categorias</span>
    </div>

    <nav class="sidebar-nav">
        <a href="index.php?p=panel" class="nav-item <?= $pagina_actual=='panel'?'activo':'' ?>">
            <i class="fas fa-th-large"></i>
            <span>Panel</span>
        </a>

        <div class="nav-item <?= in_array($pagina_actual,['perfil','ordenes'])?'abierto':'' ?>" onclick="toggleSubmenu(this)">
            <i class="fas fa-user"></i>
            <span>Perfil</span>
            <i class="fas fa-chevron-down flecha"></i>
        </div>

        <div class="submenu <?= in_array($pagina_actual,['perfil','ordenes'])?'abierto':'' ?>">
            <a href="index.php?p=perfil" class="nav-item <?= $pagina_actual=='perfil'?'activo':'' ?>">
                <i class="fas fa-info-circle"></i>
                <span>Administrador</span>
            </a>
            <a href="index.php?p=ordenes" class="nav-item <?= $pagina_actual=='ordenes'?'activo':'' ?>">
                <i class="fas fa-clipboard-list"></i>
                <span>Ver Tareas Asignadas</span>
            </a>
        </div>

        <a href="index.php?p=gestion" class="nav-item <?= $pagina_actual=='gestion'?'activo':'' ?>">
            <i class="fas fa-tasks"></i>
            <span>Gestion de Ordenes</span>
        </a>

        <a href="index.php?p=inventario" class="nav-item <?= $pagina_actual=='inventario'?'activo':'' ?>">
            <i class="fas fa-wrench"></i>
            <span>Ver Inventario</span>
        </a>

        <a href="index.php?p=auditoria" class="nav-item <?= $pagina_actual=='auditoria'?'activo':'' ?>">
            <i class="fas fa-shield-alt"></i>
            <span>Auditoria (Admin)</span>
        </a>

        <a href="index.php?p=nota-remision" class="nav-item <?= $pagina_actual=='nota-remision'?'activo':'' ?>">
            <i class="fas fa-file-invoice"></i>
            <span>Nota De Remision</span>
        </a>

        <a href="index.php?p=factura" class="nav-item <?= $pagina_actual=='factura'?'activo':'' ?>">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Factura</span>
        </a>

        <a href="index.php?p=configuracion" class="nav-item <?= $pagina_actual=='configuracion'?'activo':'' ?>">
            <i class="fas fa-cog"></i>
            <span>Configuracion</span>
        </a>
    </nav>

    <div class="sidebar-usuario">
        <div class="avatar-usuario">DG</div>
        <div>
            <h4>Daniel G.</h4>
            <span>Administrador</span>
        </div>
    </div>
</aside>