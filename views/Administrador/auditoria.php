<?php
// auditoria.php — se incluye desde index.php

// ─── CONEXIÓN A BD ─────────────────────────────────────────────────────────
// Ajusta estos datos si tu archivo de conexión ya existe
if (!isset($pdo)) {
    $host   = 'localhost';
    $dbname = 'mecanico';
    $user   = 'root';
    $pass   = '';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

// ─── CREAR TABLA auditoria SI NO EXISTE ────────────────────────────────────
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `auditoria` (
        `id_auditoria`   INT(11)       NOT NULL AUTO_INCREMENT,
        `tipo`           ENUM('Creacion','Modificacion','Eliminacion','Login','Logout','Otro') NOT NULL DEFAULT 'Otro',
        `accion`         VARCHAR(100)  NOT NULL,
        `descripcion`    TEXT          DEFAULT NULL,
        `usuario`        VARCHAR(150)  DEFAULT NULL,
        `fecha`          TIMESTAMP     NOT NULL DEFAULT current_timestamp(),
        `ip`             VARCHAR(45)   DEFAULT NULL,
        `tabla_afectada` VARCHAR(50)   DEFAULT NULL,
        `id_registro`    INT(11)       DEFAULT NULL,
        PRIMARY KEY (`id_auditoria`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
");

// ─── FUNCIÓN HELPER ────────────────────────────────────────────────────────
function registrarAuditoria(PDO $pdo, string $tipo, string $accion, string $descripcion, string $tabla = null, int $id_registro = null): void {
    $usuario = $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'Sistema';
    $ip      = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt    = $pdo->prepare("INSERT INTO auditoria (tipo, accion, descripcion, usuario, ip, tabla_afectada, id_registro) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$tipo, $accion, $descripcion, $usuario, $ip, $tabla, $id_registro]);
}

// ─── BÚSQUEDA ──────────────────────────────────────────────────────────────
$busqueda = trim($_GET['buscar'] ?? '');

// ─── CONSULTAS ─────────────────────────────────────────────────────────────

// TAB: Auditoría
if ($busqueda !== '') {
    $like  = "%$busqueda%";
    $stmt  = $pdo->prepare("SELECT * FROM auditoria WHERE descripcion LIKE ? OR usuario LIKE ? OR accion LIKE ? OR tipo LIKE ? ORDER BY fecha DESC LIMIT 200");
    $stmt->execute([$like, $like, $like, $like]);
} else {
    $stmt = $pdo->query("SELECT * FROM auditoria ORDER BY fecha DESC LIMIT 200");
}
$registros_auditoria = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Totales para las tarjetas
$total_registros = (int)$pdo->query("SELECT COUNT(*) FROM auditoria")->fetchColumn();

$ordenes_hoy = (int)$pdo->query("SELECT COUNT(*) FROM ordenes WHERE DATE(fecha_creacion) = CURDATE()")->fetchColumn();

$usuarios_activos = (int)$pdo->query("SELECT COUNT(*) FROM empleados")->fetchColumn();

$alertas = (int)$pdo->query("SELECT COUNT(*) FROM auditoria WHERE tipo = 'Eliminacion' AND fecha >= NOW() - INTERVAL 7 DAY")->fetchColumn();

// TAB: Empleados
$empleados = $pdo->query("
    SELECT e.*,
           COUNT(o.id_orden) AS total_ordenes,
           MAX(o.fecha_creacion) AS ultima_actividad
    FROM empleados e
    LEFT JOIN ordenes o ON o.id_mecanico = e.id_empleado
    GROUP BY e.id_empleado
    ORDER BY e.nombre ASC
")->fetchAll(PDO::FETCH_ASSOC);

// TAB: Todas las Órdenes
$ordenes = $pdo->query("
    SELECT o.*, e.nombre AS nombre_mecanico, e.apellido AS apellido_mecanico
    FROM ordenes o
    LEFT JOIN empleados e ON e.id_empleado = o.id_mecanico
    ORDER BY o.fecha_creacion DESC
")->fetchAll(PDO::FETCH_ASSOC);

$count_pendiente  = (int)$pdo->query("SELECT COUNT(*) FROM ordenes WHERE estado='Pendiente'")->fetchColumn();
$count_progreso   = (int)$pdo->query("SELECT COUNT(*) FROM ordenes WHERE estado='En Progreso'")->fetchColumn();
$count_terminado  = (int)$pdo->query("SELECT COUNT(*) FROM ordenes WHERE estado='Terminado'")->fetchColumn();

// TAB: Inventario
$inventario = $pdo->query("
    SELECT i.*, e.nombre AS mecanico_nombre
    FROM inventario i
    LEFT JOIN empleados e ON e.id_empleado = i.id_mecanico
    ORDER BY i.nombre_producto ASC
")->fetchAll(PDO::FETCH_ASSOC);

// ─── HELPERS ───────────────────────────────────────────────────────────────
function iniciales(string $nombre, string $apellido = ''): string {
    $i1 = mb_strtoupper(mb_substr(trim($nombre),   0, 1));
    $i2 = mb_strtoupper(mb_substr(trim($apellido), 0, 1));
    return $i1 . $i2;
}
function badgeOrden(string $estado): string {
    return match($estado) {
        'Terminado'   => 'badge-verde',
        'En Progreso' => 'badge-azul',
        default       => 'badge-rojo',
    };
}
function badgeInventario(string $estado): string {
    return match($estado) {
        'Disponible'    => 'badge-verde',
        'En Uso'        => 'badge-azul',
        'Mantenimiento' => 'badge-rojo',
        default         => 'badge-gris',
    };
}
function iconoTipo(string $tipo): string {
    return match($tipo) {
        'Creacion'     => 'fas fa-plus-circle',
        'Modificacion' => 'fas fa-edit',
        'Eliminacion'  => 'fas fa-trash',
        'Login'        => 'fas fa-sign-in-alt',
        'Logout'       => 'fas fa-sign-out-alt',
        default        => 'fas fa-file-alt',
    };
}
function colorIcono(string $tipo): string {
    return match($tipo) {
        'Creacion'     => 'color:#16a34a',
        'Modificacion' => 'color:#2563eb',
        'Eliminacion'  => 'color:#dc2626',
        'Login'        => 'color:#9333ea',
        'Logout'       => 'color:#f59e0b',
        default        => 'color:#6b7280',
    };
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoria - Auto Master</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/estilos-generales.css">
    <style>
        .banner-admin { background: linear-gradient(135deg, var(--rojo), #991b1b); border-radius: 12px; padding: 30px; color: white; display: flex; align-items: center; gap: 20px; margin-bottom: 25px; }
        .banner-icono { width: 50px; height: 50px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .banner-texto h2 { font-size: 24px; font-weight: 700; margin-bottom: 5px; }
        .banner-texto p  { font-size: 14px; opacity: 0.9; }

        .tabs-nav { display: flex; border-bottom: 1px solid var(--borde); margin-bottom: 20px; flex-wrap: wrap; }
        .tab-btn  { display: flex; align-items: center; gap: 8px; padding: 15px 20px; background: none; border: none; cursor: pointer; font-size: 14px; color: var(--texto-claro); border-bottom: 2px solid transparent; margin-bottom: -1px; }
        .tab-btn:hover   { color: var(--texto); }
        .tab-btn.activo  { color: var(--rojo); border-bottom-color: var(--rojo); }
        .tab-contenido   { display: none; }
        .tab-contenido.activo { display: block; }

        .barra-filtros  { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
        .campo-busqueda { display: flex; align-items: center; background: var(--blanco); border: 1px solid var(--borde); border-radius: 8px; padding: 0 12px; min-width: 250px; flex: 1; }
        .campo-busqueda input { flex: 1; border: none; outline: none; padding: 10px 0; font-size: 14px; background: transparent; }

        .icono-tipo { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; background: rgba(37,99,235,0.1); }

        .config-grid    { display: grid; grid-template-columns: repeat(2,1fr); gap: 25px; }
        .config-seccion { background: var(--fondo); border-radius: 12px; padding: 20px; }
        .config-seccion h4 { font-size: 16px; font-weight: 600; margin-bottom: 15px; }
        .config-item    { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--borde); }
        .config-item:last-child { border-bottom: none; }
        .estado-punto   { display: flex; align-items: center; gap: 6px; }
        .punto          { width: 8px; height: 8px; border-radius: 50%; }
        .punto.verde    { background: var(--verde); }
        .punto.gris     { background: #9ca3af; }

        .sin-registros  { text-align: center; padding: 40px; color: var(--texto-claro); font-size: 15px; }
        .sin-registros i { font-size: 36px; display: block; margin-bottom: 10px; opacity: 0.4; }
    </style>
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
            <a href="panel.php" class="nav-item"><i class="fas fa-th-large"></i><span>Panel</span></a>

            <div class="nav-item submenu-toggle" onclick="toggleSubmenu('perfil')">
                <i class="fas fa-user"></i><span>Usuario</span>
                <i class="fas fa-chevron-down flecha" id="flecha-perfil"></i>
            </div>
            <div class="submenu" id="submenu-perfil">
                <a href="informacion-admin.php"      class="nav-item"><i class="fas fa-info-circle"></i><span>Administrador</span></a>
                <a href="informacion-empleados.php"  class="nav-item"><i class="fas fa-info-circle"></i><span>Empleados</span></a>
                <a href="agregar-empleado.php"       class="nav-item"><i class="fas fa-user-plus"></i><span>Agregar Empleados</span></a>
            </div>

            <div class="nav-item submenu-toggle" onclick="toggleSubmenu('tareas')">
                <i class="fas fa-tasks"></i><span>Gestión de Tareas</span>
                <i class="fas fa-chevron-down flecha" id="flecha-tareas"></i>
            </div>
            <div class="submenu" id="submenu-tareas">
                <a href="gestion-ordenes.php" class="nav-item"><i class="fas fa-info-circle"></i><span>Gestión de Ordenes</span></a>
                <a href="inventario.php"      class="nav-item"><i class="fas fa-wrench"></i><span>Ver Inventario</span></a>
                <a href="auditoria.php"       class="nav-item activo"><i class="fas fa-shield-alt"></i><span>Auditoria</span></a>
            </div>

            <div class="nav-item submenu-toggle" onclick="toggleSubmenu('clientes')">
                <i class="fas fa-users"></i><span>Clientes</span>
                <i class="fas fa-chevron-down flecha" id="flecha-clientes"></i>
            </div>
            <div class="submenu" id="submenu-clientes">
                <a href="informacion-clientes.php" class="nav-item"><i class="fas fa-address-card"></i><span>Ver Clientes</span></a>
            </div>

            <a href="nota-remision.php" class="nav-item"><i class="fas fa-file-invoice"></i><span>Notas de Remisión</span></a>
            <a href="respaldo.php"      class="nav-item"><i class="fas fa-database"></i><span>Respaldo</span></a>
            <a href="login.php"         class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Cerrar Sesión</span></a>
        </nav>
        <div class="sidebar-usuario">
            <div class="sidebar-usuario-avatar"><?= iniciales($_SESSION['nombre'] ?? 'Admin', $_SESSION['apellido'] ?? '') ?></div>
            <div class="sidebar-usuario-info">
                <h4><?= htmlspecialchars($_SESSION['nombre'] ?? 'Admin') ?></h4>
                <span><?= htmlspecialchars($_SESSION['puesto'] ?? 'Administrador') ?></span>
            </div>
        </div>
    </aside>

    <!-- CONTENIDO -->
    <main class="contenido">
        <header class="cabecera"></header>

        <div class="pagina">

            <!-- Banner -->
            <div class="banner-admin">
                <div class="banner-icono"><i class="fas fa-shield-alt"></i></div>
                <div class="banner-texto">
                    <h2>Auditoria del Sistema</h2>
                    <p>Registro de todas las acciones realizadas en el sistema</p>
                </div>
            </div>

            <!-- Tarjetas resumen (datos reales) -->
            <div class="tarjetas-resumen">
                <div class="tarjeta-resumen">
                    <div class="tarjeta-icono morado"><i class="fas fa-chart-line"></i></div>
                    <div class="tarjeta-info"><h3><?= $total_registros ?></h3><span>Registros totales</span></div>
                </div>
                <div class="tarjeta-resumen">
                    <div class="tarjeta-icono verde"><i class="fas fa-file-alt"></i></div>
                    <div class="tarjeta-info"><h3><?= $ordenes_hoy ?></h3><span>Ordenes hoy</span></div>
                </div>
                <div class="tarjeta-resumen">
                    <div class="tarjeta-icono azul"><i class="fas fa-users"></i></div>
                    <div class="tarjeta-info"><h3><?= $usuarios_activos ?></h3><span>Empleados activos</span></div>
                </div>
                <div class="tarjeta-resumen">
                    <div class="tarjeta-icono amarillo"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="tarjeta-info"><h3><?= $alertas ?></h3><span>Eliminaciones (7 días)</span></div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="tarjeta">
                <div class="tabs-nav">
                    <button class="tab-btn activo" onclick="cambiarTab('auditoria')"><i class="fas fa-shield-alt"></i> Auditoria</button>
                    <button class="tab-btn" onclick="cambiarTab('empleados')"><i class="fas fa-users"></i> Empleados</button>
                    <button class="tab-btn" onclick="cambiarTab('ordenes')"><i class="fas fa-file-alt"></i> Todas las Ordenes</button>
                    <button class="tab-btn" onclick="cambiarTab('inventario')"><i class="fas fa-wrench"></i> Inventario</button>
                    <button class="tab-btn" onclick="cambiarTab('configuracion')"><i class="fas fa-cog"></i> Configuracion</button>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!-- TAB: AUDITORÍA                                          -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="tab-contenido activo" id="tab-auditoria">
                    <div style="padding: 20px;">

                        <!-- Buscador -->
                        <form method="GET" style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap; align-items:center;">
                            <div class="campo-busqueda">
                                <i class="fas fa-search" style="color:var(--texto-claro);"></i>
                                <input type="text" name="buscar" placeholder="Buscar en registros..." value="<?= htmlspecialchars($busqueda) ?>">
                            </div>
                            <div style="display:flex; gap:10px;">
                                <button type="submit" class="btn btn-secundario"><i class="fas fa-search"></i> Buscar</button>
                                <?php if ($busqueda): ?>
                                    <a href="auditoria.php" class="btn btn-secundario"><i class="fas fa-times"></i> Limpiar</a>
                                <?php endif; ?>
                                <button type="button" onclick="exportarCSV()" class="btn btn-primario"><i class="fas fa-download"></i> Exportar</button>
                            </div>
                        </form>

                        <?php if (empty($registros_auditoria)): ?>
                            <div class="sin-registros">
                                <i class="fas fa-clipboard-list"></i>
                                <?= $busqueda ? "No se encontraron resultados para \"$busqueda\"" : "Aún no hay registros de auditoría. Las acciones del sistema aparecerán aquí automáticamente." ?>
                            </div>
                        <?php else: ?>
                        <table class="tabla" id="tabla-auditoria">
                            <thead>
                                <tr>
                                    <th>TIPO</th>
                                    <th>ACCION</th>
                                    <th>DESCRIPCION</th>
                                    <th>USUARIO</th>
                                    <th>FECHA</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registros_auditoria as $r): ?>
                                <tr>
                                    <td>
                                        <div class="icono-tipo" style="<?= colorIcono($r['tipo']) ?>; background:transparent;">
                                            <i class="<?= iconoTipo($r['tipo']) ?>"></i>
                                        </div>
                                    </td>
                                    <td><strong><?= htmlspecialchars($r['tipo']) ?></strong></td>
                                    <td><?= htmlspecialchars($r['descripcion'] ?? $r['accion']) ?></td>
                                    <td><i class="fas fa-user" style="margin-right:5px;color:var(--texto-claro);"></i><?= htmlspecialchars($r['usuario'] ?? '—') ?></td>
                                    <td><i class="far fa-clock" style="margin-right:5px;color:var(--texto-claro);"></i><?= date('M d, Y - H:i', strtotime($r['fecha'])) ?></td>
                                    <td><i class="fas fa-globe" style="margin-right:5px;color:var(--texto-claro);"></i><?= htmlspecialchars($r['ip'] ?? '—') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p style="margin-top:12px; font-size:13px; color:var(--texto-claro);">
                            Mostrando <?= count($registros_auditoria) ?> registro(s)
                        </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!-- TAB: EMPLEADOS                                          -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="tab-contenido" id="tab-empleados">
                    <div style="padding: 20px;">
                        <h3 style="margin-bottom:20px;">Gestión de Empleados</h3>

                        <?php if (empty($empleados)): ?>
                            <div class="sin-registros"><i class="fas fa-users"></i> No hay empleados registrados.</div>
                        <?php else: ?>
                        <table class="tabla">
                            <thead>
                                <tr>
                                    <th>EMPLEADO</th>
                                    <th>PUESTO</th>
                                    <th>CONTACTO</th>
                                    <th>INGRESO</th>
                                    <th>ÓRDENES</th>
                                    <th>ÚLTIMA ACTIVIDAD</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($empleados as $emp): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <div class="avatar rosa"><?= iniciales($emp['nombre'], $emp['apellido']) ?></div>
                                            <strong><?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellido']) ?></strong>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-gris"><?= htmlspecialchars($emp['puesto'] ?? '—') ?></span></td>
                                    <td>
                                        <?= htmlspecialchars($emp['correo']) ?>
                                        <?php if ($emp['telefono']): ?>
                                            <br><small style="color:var(--texto-claro);"><?= htmlspecialchars($emp['telefono']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $emp['fecha_ingreso'] ? date('d/m/Y', strtotime($emp['fecha_ingreso'])) : '—' ?></td>
                                    <td><strong><?= (int)$emp['total_ordenes'] ?></strong></td>
                                    <td><?= $emp['ultima_actividad'] ? date('M d, Y - H:i', strtotime($emp['ultima_actividad'])) : '—' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!-- TAB: TODAS LAS ÓRDENES                                  -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="tab-contenido" id="tab-ordenes">
                    <div style="padding: 20px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
                            <h3>Todas las Ordenes del Sistema</h3>
                            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                                <span class="badge badge-rojo"  style="padding:8px 14px;"><?= $count_pendiente ?> Pendientes</span>
                                <span class="badge badge-azul"  style="padding:8px 14px;"><?= $count_progreso  ?> En Progreso</span>
                                <span class="badge badge-verde" style="padding:8px 14px;"><?= $count_terminado ?> Terminadas</span>
                            </div>
                        </div>

                        <?php if (empty($ordenes)): ?>
                            <div class="sin-registros"><i class="fas fa-file-alt"></i> No hay órdenes registradas.</div>
                        <?php else: ?>
                        <table class="tabla">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>VEHICULO</th>
                                    <th>CLIENTE</th>
                                    <th>SERVICIO</th>
                                    <th>ESTADO</th>
                                    <th>MECÁNICO</th>
                                    <th>FECHA</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ordenes as $o): ?>
                                <tr>
                                    <td><strong>ORD-<?= str_pad($o['id_orden'], 3, '0', STR_PAD_LEFT) ?></strong></td>
                                    <td><?= htmlspecialchars($o['vehiculo']) ?></td>
                                    <td><?= htmlspecialchars($o['cliente']) ?></td>
                                    <td><?= htmlspecialchars($o['servicio']) ?></td>
                                    <td><span class="badge <?= badgeOrden($o['estado']) ?>"><?= htmlspecialchars($o['estado']) ?></span></td>
                                    <td>
                                        <?php if ($o['nombre_mecanico']): ?>
                                            <div style="display:flex; align-items:center; gap:6px;">
                                                <div class="avatar azul" style="width:28px; height:28px; font-size:10px;">
                                                    <?= iniciales($o['nombre_mecanico'], $o['apellido_mecanico'] ?? '') ?>
                                                </div>
                                                <?= htmlspecialchars($o['nombre_mecanico']) ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="color:var(--texto-claro);">Sin asignar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($o['fecha_creacion'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!-- TAB: INVENTARIO                                          -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="tab-contenido" id="tab-inventario">
                    <div style="padding: 20px;">
                        <h3 style="margin-bottom:20px;">Inventario Completo</h3>

                        <?php if (empty($inventario)): ?>
                            <div class="sin-registros"><i class="fas fa-box-open"></i> No hay productos en inventario.</div>
                        <?php else: ?>
                        <table class="tabla">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>PRODUCTO</th>
                                    <th>CANTIDAD</th>
                                    <th>PRECIO</th>
                                    <th>ESTADO</th>
                                    <th>UBICACIÓN</th>
                                    <th>MECÁNICO</th>
                                    <th>FECHA</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inventario as $item): ?>
                                <tr>
                                    <td><strong>P-<?= str_pad($item['id_producto'], 3, '0', STR_PAD_LEFT) ?></strong></td>
                                    <td><?= htmlspecialchars($item['nombre_producto']) ?></td>
                                    <td style="text-align:center;"><strong><?= (int)$item['cantidad_stock'] ?></strong></td>
                                    <td><?= $item['precio_unitario'] !== null ? '$' . number_format($item['precio_unitario'], 2) : '—' ?></td>
                                    <td><span class="badge <?= badgeInventario($item['estado']) ?>"><?= htmlspecialchars($item['estado']) ?></span></td>
                                    <td><?= htmlspecialchars($item['ubicacion'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($item['mecanico_nombre'] ?? '—') ?></td>
                                    <td><?= date('d/m/Y', strtotime($item['fecha_registro'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!-- TAB: CONFIGURACIÓN                                       -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="tab-contenido" id="tab-configuracion">
                    <div style="padding: 20px;">
                        <h3 style="margin-bottom:20px;">Configuracion del Sistema</h3>
                        <div class="config-grid">
                            <div class="config-seccion">
                                <h4>Notificaciones</h4>
                                <div class="config-item"><label>Notificar nuevas ordenes</label><input type="checkbox" checked></div>
                                <div class="config-item"><label>Alertas de inventario bajo</label><input type="checkbox" checked></div>
                                <div class="config-item"><label>Resumen diario por email</label><input type="checkbox"></div>
                            </div>
                            <div class="config-seccion">
                                <h4>Seguridad</h4>
                                <div class="config-item"><label>Autenticacion en dos pasos</label><input type="checkbox" checked></div>
                                <div class="config-item"><label>Registrar IPs de acceso</label><input type="checkbox" checked></div>
                                <div class="config-item"><label>Sesiones multiples</label><input type="checkbox"></div>
                            </div>
                            <div class="config-seccion">
                                <h4>Respaldos</h4>
                                <?php
                                    $ultimo_respaldo = $pdo->query("SELECT MAX(fecha) FROM auditoria WHERE accion LIKE '%respaldo%'")->fetchColumn();
                                ?>
                                <div class="config-item">
                                    <label>Ultimo respaldo</label>
                                    <span><?= $ultimo_respaldo ? date('M d, Y - H:i', strtotime($ultimo_respaldo)) : 'Sin registros' ?></span>
                                </div>
                                <a href="respaldo.php" class="btn btn-secundario" style="width:100%; margin-top:15px; text-align:center; display:block;">Crear Respaldo Manual</a>
                            </div>
                            <div class="config-seccion">
                                <h4>Sistema</h4>
                                <div class="config-item"><label>Versión</label><span>v2.4.1</span></div>
                                <div class="config-item"><label>Base de datos</label><span>mecanico (MariaDB)</span></div>
                                <div class="config-item">
                                    <label>Total registros BD</label>
                                    <span><?php
                                        $t = 0;
                                        foreach (['clientes','empleados','ordenes','servicios','inventario','vehiculos','auditoria'] as $tabla) {
                                            try { $t += (int)$pdo->query("SELECT COUNT(*) FROM $tabla")->fetchColumn(); } catch(Exception $e) {}
                                        }
                                        echo number_format($t);
                                    ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /.tarjeta -->
        </div><!-- /.pagina -->
    </main>
</div><!-- /.contenedor -->

<script src="../js/menu.js"></script>
<script>
function cambiarTab(tabId) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('activo'));
    document.querySelectorAll('.tab-contenido').forEach(tab => tab.classList.remove('activo'));
    document.querySelector(`[onclick="cambiarTab('${tabId}')"]`).classList.add('activo');
    document.getElementById('tab-' + tabId).classList.add('activo');
}

// Exportar tabla de auditoría a CSV
function exportarCSV() {
    const tabla = document.getElementById('tabla-auditoria');
    if (!tabla) { alert('No hay registros para exportar.'); return; }
    let csv = [];
    tabla.querySelectorAll('tr').forEach(fila => {
        const celdas = [...fila.querySelectorAll('th, td')].map(c => '"' + c.innerText.replace(/"/g, '""').replace(/\n/g,' ') + '"');
        csv.push(celdas.join(','));
    });
    const blob = new Blob(['\uFEFF' + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = 'auditoria_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
    URL.revokeObjectURL(url);
}
</script>
</body>
</html>