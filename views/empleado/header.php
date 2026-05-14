<?php
// header.php — inclúyelo al inicio de cada página del empleado
// Requiere que session_start() y db_conexion.php ya estén cargados antes de incluir este archivo.

if (!isset($_SESSION['id_empleado'])) {
    header('Location: login.php');
    exit();
}

$_nombre  = htmlspecialchars($_SESSION['nombre'] ?? '');
$_puesto  = htmlspecialchars($_SESSION['puesto'] ?? 'Mecánico');
$_foto    = $_SESSION['foto'] ?? null;
$_iniciales = strtoupper(
    substr($_SESSION['nombre'] ?? 'E', 0, 1) .
    substr($_SESSION['apellido'] ?? '', 0, 1)
);

// Detectar página activa
$_pagina_actual = basename($_SERVER['PHP_SELF']);
function esActivo($pagina) {
    global $_pagina_actual;
    return $_pagina_actual === $pagina ? 'activo' : '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Auto Master' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
       :root {
    --sidebar-bg: #212332;
    --sidebar-hover: #2a2d3e;
    --accent: #e53935;
    --accent2: #e53935;
    --text-muted: #8b92a9;
    --body-bg: #f0f2f8;
    --card-bg: #ffffff;
    --border: #e2e8f0;
    --shadow: 0 4px 24px rgba(30,34,60,0.10);
    --radius: 16px;
}
        body { font-family: 'Sora', sans-serif; background: var(--body-bg); color: #1e2238; min-height: 100vh; display: flex; }

        /* ── SIDEBAR ── */
        .sidebar { width: 220px; min-height: 100vh; background: var(--sidebar-bg); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; z-index: 100; box-shadow: 4px 0 24px rgba(30,34,60,0.18); }

        .sidebar-logo { display: flex; align-items: center; gap: 12px; padding: 22px 20px 16px; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .sidebar-logo-icon { width: 36px; height: 36px; background: var(--accent); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 16px; flex-shrink: 0; }
        .sidebar-logo h2 { color: #fff; font-size: 15px; font-weight: 700; line-height: 1.2; }
        .sidebar-logo span { color: var(--text-muted); font-size: 11px; display: block; }

        .sidebar-nav { flex: 1; padding: 14px 10px; display: flex; flex-direction: column; gap: 2px; overflow-y: auto; }

        .nav-item { display: flex; align-items: center; gap: 11px; padding: 10px 14px; border-radius: 10px; color: var(--text-muted); font-size: 13.5px; font-weight: 500; text-decoration: none; cursor: pointer; transition: background 0.18s, color 0.18s; user-select: none; }
        .nav-item i { width: 18px; text-align: center; font-size: 14px; flex-shrink: 0; }
        .nav-item:hover { background: var(--sidebar-hover); color: #fff; }
        .nav-item.activo { background: var(--accent); color: #fff; }

        .submenu-toggle .flecha { margin-left: auto; font-size: 11px; transition: transform 0.25s; }
        .submenu-toggle.open .flecha { transform: rotate(180deg); }
        .submenu { display: none; flex-direction: column; gap: 2px; padding-left: 16px; }
        .submenu.open { display: flex; }
        .submenu .nav-item { font-size: 12.5px; padding: 8px 12px; }

        .nav-separador { height: 1px; background: rgba(255,255,255,0.06); margin: 8px 14px; }

        .sidebar-usuario { display: flex; align-items: center; gap: 10px; padding: 16px 18px; border-top: 1px solid rgba(255,255,255,0.07); flex-shrink: 0; }
        .sidebar-usuario-avatar { width: 38px; height: 38px; background: var(--accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 15px; overflow: hidden; flex-shrink: 0; }
        .sidebar-usuario-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .sidebar-usuario-info h4 { color: #fff; font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px; }
        .sidebar-usuario-info span { color: var(--text-muted); font-size: 11px; }

        /* ── CONTENIDO ── */
        .contenido { margin-left: 220px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

        /* ── CABECERA ── */
        .cabecera { background: var(--card-bg); padding: 0 32px; height: 64px; display: flex; align-items: center; justify-content: flex-end; gap: 18px; border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 50; }
        .cabecera-icon { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 16px; cursor: pointer; transition: background 0.15s; text-decoration: none; }
        .cabecera-icon:hover { background: #f1f5f9; color: var(--accent); }
        .cabecera-avatar { width: 36px; height: 36px; background: var(--accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 13px; cursor: pointer; overflow: hidden; }
        .cabecera-avatar img { width: 100%; height: 100%; object-fit: cover; }

        /* ── PÁGINA ── */
        .pagina { padding: 32px; flex: 1; }

        /* ── ESTILOS COMUNES REUTILIZABLES ── */
        .pagina-titulo { margin-bottom: 28px; }
        .pagina-titulo h2 { font-size: 22px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .pagina-titulo h2 i { color: var(--accent2); font-size: 18px; }
        .pagina-titulo p { color: #64748b; font-size: 13.5px; margin-top: 4px; }

        .card { background: var(--card-bg); border-radius: var(--radius); box-shadow: var(--shadow); border: 1px solid var(--border); overflow: hidden; }
        .card-header { padding: 18px 22px 14px; border-bottom: 1px solid var(--border); font-size: 14px; font-weight: 600; color: #1e2238; display: flex; align-items: center; gap: 8px; }
        .card-header i { color: var(--accent2); }

        .badge { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 20px; white-space: nowrap; }
        .badge-pendiente  { background: #fef3c7; color: #d97706; }
        .badge-completado { background: #dcfce7; color: #16a34a; }
        .badge-proceso    { background: #eff6ff; color: #3b82f6; }
        .badge-activo     { background: #dcfce7; color: #16a34a; }

        .seccion-titulo { font-size: 15px; font-weight: 700; color: #1e2238; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
        .seccion-titulo::after { content: ''; flex: 1; height: 1px; background: var(--border); margin-left: 8px; }

        @media (max-width: 900px) {
            .sidebar { width: 64px; }
            .sidebar-logo h2, .sidebar-logo span, .nav-item span,
            .sidebar-usuario-info, .submenu-toggle .flecha { display: none; }
            .sidebar-logo { justify-content: center; padding: 20px 0; }
            .nav-item { justify-content: center; padding: 12px; }
            .submenu { padding-left: 0; }
            .contenido { margin-left: 64px; }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon"><i class="fas fa-wrench"></i></div>
        <div><h2>Menú</h2><span>Categorías</span></div>
    </div>

    <nav class="sidebar-nav">
        <a href="panel.php" class="nav-item <?= esActivo('panel.php') ?>">
            <i class="fas fa-th-large"></i><span>Panel</span>
        </a>

        <div class="nav-item submenu-toggle <?= (esActivo('Perfil.php')) ? 'open' : '' ?>" onclick="toggleSubmenu('perfil')">
            <i class="fas fa-user"></i><span>Perfil</span>
            <i class="fas fa-chevron-down flecha" id="flecha-perfil"></i>
        </div>
        <div class="submenu <?= (esActivo('Perfil.php')) ? 'open' : '' ?>" id="submenu-perfil">
            <a href="Perfil.php" class="nav-item <?= esActivo('Perfil.php') ?>">
                <i class="fas fa-info-circle"></i><span>Información</span>
            </a>
        </div>

        <a href="tareas-asignadas.php" class="nav-item <?= esActivo('tareas-asignadas.php') ?>">
            <i class="fas fa-tasks"></i><span>Ver Tareas Asignadas</span>
        </a>

        <a href="Gestion.php" class="nav-item <?= esActivo('Gestion.php') ?>">
            <i class="fas fa-clipboard-list"></i><span>Gestión de Órdenes</span>
        </a>

        <a href="Inventario.php" class="nav-item <?= esActivo('Inventario.php') ?>">
            <i class="fas fa-boxes"></i><span>Ver Inventario</span>
        </a>

        <a href="Nota-remision.php" class="nav-item <?= esActivo('Nota-remision.php') ?>">
            <i class="fas fa-file-invoice"></i><span>Nota De Remisión</span>
        </a>

        <div class="nav-separador"></div>

        <a href="login.php?logout=1" class="nav-item">
            <i class="fas fa-sign-out-alt"></i><span>Cerrar Sesión</span>
        </a>
    </nav>

    <div class="sidebar-usuario">
        <div class="sidebar-usuario-avatar">
            <?php if ($_foto): ?>
                <img src="../../uploads/<?= htmlspecialchars($_foto) ?>" alt="foto">
            <?php else: ?>
                <?= $_iniciales ?>
            <?php endif; ?>
        </div>
        <div class="sidebar-usuario-info">
            <h4><?= $_nombre ?></h4>
            <span><?= $_puesto ?></span>
        </div>
    </div>
</div>

<main class="contenido">
    <header class="cabecera">
        <div class="cabecera-icon"><i class="fas fa-search"></i></div>
        <div class="cabecera-icon"><i class="fas fa-bell"></i></div>
        <a href="Perfil.php" class="cabecera-icon"><i class="fas fa-question-circle"></i></a>
        <div class="cabecera-avatar">
            <?php if ($_foto): ?>
                <img src="../../uploads/<?= htmlspecialchars($_foto) ?>" alt="foto">
            <?php else: ?>
                <?= $_iniciales ?>
            <?php endif; ?>
        </div>
    </header>
    <div class="pagina">
<!-- ↑↑ CONTENIDO DE CADA PÁGINA VA AQUÍ ↑↑ -->