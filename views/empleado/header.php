<?php $p = $_GET['p'] ?? 'panel'; ?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-circulo">
            <img src="../../logo.png" alt="Auto Master" onerror="this.style.display='none'">
        </div>
        <h2>Menu</h2>
        <span>Categorias</span>
    </div>

    <nav class="sidebar-nav">
        <a href="index.php?p=panel" class="nav-item <?= $p=='panel'?'activo':'' ?>">
            <i class="fas fa-th-large"></i><span>Panel</span>
        </a>

        <div class="nav-item nav-toggle <?= in_array($p,['perfil','ordenes'])?'abierto activo':'' ?>" onclick="toggleSubmenu(this)">
            <i class="fas fa-user"></i><span>Perfil</span>
            <i class="fas fa-chevron-down flecha"></i>
        </div>
        <div class="submenu <?= in_array($p,['perfil','ordenes'])?'abierto':'' ?>">
            <a href="index.php?p=perfil" class="nav-item <?= $p=='perfil'?'activo':'' ?>">
                <i class="fas fa-info-circle"></i><span>Informacion</span>
            </a>
            <a href="index.php?p=ordenes" class="nav-item <?= $p=='ordenes'?'activo':'' ?>">
                <i class="fas fa-clipboard-list"></i><span>Ver Tareas Asignadas</span>
            </a>
        </div>

        <a href="index.php?p=gestion" class="nav-item <?= $p=='gestion'?'activo':'' ?>">
            <i class="fas fa-clipboard"></i><span>Gestion de Ordenes</span>
        </a>
        <a href="index.php?p=inventario" class="nav-item <?= $p=='inventario'?'activo':'' ?>">
            <i class="fas fa-wrench"></i><span>Ver Inventario</span>
        </a>
        <a href="index.php?p=nota-remision" class="nav-item <?= in_array($p,['nota-remision','nota-detalle'])?'activo':'' ?>">
            <i class="fas fa-file-invoice"></i><span>Nota De Remision</span>
        </a>
        <a href="index.php?p=configuracion" class="nav-item <?= $p=='configuracion'?'activo':'' ?>">
            <i class="fas fa-cog"></i><span>Configuracion</span>
        </a>
    </nav>

    <div class="sidebar-usuario">
        <div class="avatar-usuario">DG</div>
        <div><h4>Daniel G.</h4><span>Mecanico</span></div>
    </div>
</aside>