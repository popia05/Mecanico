<?php
require_once '../../php/db_conexion.php';

$mensaje = '';
$tipo_mensaje = '';

// ── Guardar nuevo item ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_item'])) {
    $material    = trim($_POST['material'] ?? '');
    $cantidad    = intval($_POST['cantidad'] ?? 1);
    $estado      = $_POST['estado'] ?? 'Disponible';
    $ubicacion   = trim($_POST['ubicacion'] ?? '');
    $id_mecanico = intval($_POST['mecanico_id'] ?? 0) ?: null;

    try {
        $stmt = $conexion->prepare("INSERT INTO inventario (nombre_producto, cantidad_stock, estado, ubicacion, id_mecanico) VALUES (:m,:c,:e,:u,:mec)");
        $stmt->execute([':m'=>$material,':c'=>$cantidad,':e'=>$estado,':u'=>$ubicacion,':mec'=>$id_mecanico]);
        $mensaje = '¡Material agregado al inventario!';
        $tipo_mensaje = 'exito';

        // Auditoría
        if (function_exists('registrarAuditoria')) {
            registrarAuditoria($conexion, 'Creacion', 'Nuevo producto', "Se agregó al inventario: $material (cant: $cantidad)", 'inventario', (int)$conexion->lastInsertId());
        }
    } catch (PDOException $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}

// ── Actualizar item ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_item'])) {
    $id_item     = intval($_POST['id_item'] ?? 0);
    $material    = trim($_POST['material_editar'] ?? '');
    $cantidad    = intval($_POST['cantidad_editar'] ?? 1);
    $estado      = $_POST['estado_editar'] ?? 'Disponible';
    $ubicacion   = trim($_POST['ubicacion_editar'] ?? '');
    $id_mecanico = intval($_POST['mecanico_id_editar'] ?? 0) ?: null;

    try {
        $stmt = $conexion->prepare("UPDATE inventario SET nombre_producto=:m, cantidad_stock=:c, estado=:e, ubicacion=:u, id_mecanico=:mec WHERE id_producto=:id");
        $stmt->execute([':m'=>$material,':c'=>$cantidad,':e'=>$estado,':u'=>$ubicacion,':mec'=>$id_mecanico,':id'=>$id_item]);
        $mensaje = '¡Item actualizado!';
        $tipo_mensaje = 'exito';

        if (function_exists('registrarAuditoria')) {
            registrarAuditoria($conexion, 'Modificacion', 'Producto editado', "Se editó el producto ID $id_item: $material", 'inventario', $id_item);
        }
    } catch (PDOException $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}

// ── NUEVO: Registrar uso (descontar stock) ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_uso'])) {
    $id_producto  = intval($_POST['id_producto_uso'] ?? 0);
    $piezas_usar  = intval($_POST['piezas_usar'] ?? 1);
    $id_mecanico  = intval($_POST['mecanico_uso'] ?? 0) ?: null;
    $nota_uso     = trim($_POST['nota_uso'] ?? '');

    try {
        // Verificar stock actual
        $check = $conexion->prepare("SELECT nombre_producto, cantidad_stock FROM inventario WHERE id_producto = :id");
        $check->execute([':id' => $id_producto]);
        $prod = $check->fetch(PDO::FETCH_ASSOC);

        if (!$prod) {
            $mensaje = 'Producto no encontrado.';
            $tipo_mensaje = 'error';
        } elseif ($prod['cantidad_stock'] < $piezas_usar) {
            $mensaje = "Stock insuficiente. Solo quedan {$prod['cantidad_stock']} piezas de \"{$prod['nombre_producto']}\".";
            $tipo_mensaje = 'error';
        } else {
            $nuevo_stock = $prod['cantidad_stock'] - $piezas_usar;
            $nuevo_estado = $nuevo_stock === 0 ? 'En Uso' : 'Disponible';

            $upd = $conexion->prepare("UPDATE inventario SET cantidad_stock=:c, estado=:e, id_mecanico=:mec WHERE id_producto=:id");
            $upd->execute([':c'=>$nuevo_stock, ':e'=>$nuevo_estado, ':mec'=>$id_mecanico, ':id'=>$id_producto]);

            $nombre_emp = 'empleado';
            if ($id_mecanico) {
                $em = $conexion->prepare("SELECT CONCAT(nombre,' ',apellido) AS n FROM empleados WHERE id_empleado=:id");
                $em->execute([':id'=>$id_mecanico]);
                $nombre_emp = $em->fetchColumn() ?: 'empleado';
            }

            $desc = "$nombre_emp tomó $piezas_usar pieza(s) de \"{$prod['nombre_producto']}\". Stock restante: $nuevo_stock.";
            if ($nota_uso) $desc .= " Nota: $nota_uso";

            if (function_exists('registrarAuditoria')) {
                registrarAuditoria($conexion, 'Modificacion', 'Uso de inventario', $desc, 'inventario', $id_producto);
            }

            $mensaje = "✔ Se descontaron $piezas_usar pieza(s). Stock restante: $nuevo_stock.";
            $tipo_mensaje = 'exito';
        }
    } catch (PDOException $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}

// ── Empleados ─────────────────────────────────────────────────────────────
$empleados = [];
try {
    $empleados = $conexion->query("SELECT id_empleado, nombre, apellido FROM empleados ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $empleados = []; }

// ── Inventario ────────────────────────────────────────────────────────────
$items = [];
try {
    $items = $conexion->query("
        SELECT i.id_producto AS id_item, i.nombre_producto AS material, i.cantidad_stock AS cantidad,
               i.precio_unitario, i.estado, i.ubicacion, i.id_mecanico, i.fecha_registro,
               CONCAT(e.nombre,' ',e.apellido) AS mecanico_nombre
        FROM inventario i
        LEFT JOIN empleados e ON i.id_mecanico = e.id_empleado
        ORDER BY i.fecha_registro DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $items = []; }

// Conteos para las tarjetas de stock
$total_piezas  = array_sum(array_column($items, 'cantidad'));
$stock_bajo    = array_filter($items, fn($i) => (int)$i['cantidad'] <= 2);
$disponibles   = count(array_filter($items, fn($i) => $i['estado'] === 'Disponible'));
$en_uso        = count(array_filter($items, fn($i) => $i['estado'] === 'En Uso'));
$sin_stock     = array_filter($items, fn($i) => (int)$i['cantidad'] === 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Auto Master</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/estilos-generales.css">
    <style>
        /* ── Sugerencia card ── */
        .sugerencias { display:flex; gap:16px; margin-bottom:28px; flex-wrap:wrap; }
        .sugerencia-card { background:var(--tarjeta,#fff); border:2px dashed var(--borde,#e5e7eb); border-radius:14px; padding:24px 32px; display:flex; flex-direction:column; align-items:center; gap:10px; min-width:160px; cursor:pointer; transition:border-color 0.2s,transform 0.2s; }
        .sugerencia-card:hover { border-color:#e05a6e; transform:translateY(-2px); }
        .sugerencia-icono { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px; }
        .sugerencia-icono.rosa   { background:#fce7eb; color:#e05a6e; }
        .sugerencia-icono.naranja{ background:#ffedd5; color:#ea580c; }
        .sugerencia-nombre { font-size:14px; font-weight:600; color:var(--texto,#222); text-align:center; }

        /* ── Tarjetas stock ── */
        .stock-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:16px; margin-bottom:28px; }
        .stock-card { background:#fff; border-radius:14px; padding:18px 20px; display:flex; align-items:center; gap:14px; box-shadow:0 2px 10px rgba(0,0,0,0.06); border-left:4px solid transparent; }
        .stock-card.verde  { border-left-color:#16a34a; }
        .stock-card.azul   { border-left-color:#2563eb; }
        .stock-card.naranja{ border-left-color:#ea580c; }
        .stock-card.rojo   { border-left-color:#dc2626; }
        .stock-card-icono  { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
        .stock-card-icono.verde  { background:#dcfce7; color:#16a34a; }
        .stock-card-icono.azul   { background:#dbeafe; color:#2563eb; }
        .stock-card-icono.naranja{ background:#ffedd5; color:#ea580c; }
        .stock-card-icono.rojo   { background:#fee2e2; color:#dc2626; }
        .stock-card-info h3 { font-size:22px; font-weight:800; margin:0 0 2px; color:var(--texto,#222); }
        .stock-card-info span { font-size:12px; color:var(--color-gris,#888); }

        /* ── Sección STOCK RESTANTE ── */
        .seccion-stock { background:#fff; border-radius:16px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:28px; overflow:hidden; }
        .seccion-stock-header { display:flex; align-items:center; justify-content:space-between; padding:18px 24px; border-bottom:1px solid var(--borde,#f0f0f0); flex-wrap:wrap; gap:10px; }
        .seccion-stock-header h4 { margin:0; font-size:15px; font-weight:700; display:flex; align-items:center; gap:8px; }
        .stock-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:0; }
        .stock-item { display:flex; align-items:center; gap:14px; padding:16px 24px; border-bottom:1px solid var(--borde,#f5f5f5); border-right:1px solid var(--borde,#f5f5f5); transition:background 0.15s; }
        .stock-item:hover { background:#fafafa; }
        .stock-item-ico { width:36px; height:36px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0; }
        .stock-item-info { flex:1; min-width:0; }
        .stock-item-nombre { font-size:13px; font-weight:600; color:var(--texto,#222); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .stock-item-ubicacion { font-size:11px; color:#9ca3af; margin-top:1px; }
        .stock-barra-wrap { flex:1; min-width:80px; }
        .stock-barra-labels { display:flex; justify-content:space-between; font-size:11px; color:#9ca3af; margin-bottom:4px; }
        .stock-barra-labels .actual { font-weight:700; font-size:13px; color:var(--texto,#222); }
        .barra-bg { height:8px; background:#f3f4f6; border-radius:99px; overflow:hidden; }
        .barra-fill { height:100%; border-radius:99px; transition:width 0.4s ease; }
        .barra-fill.alta   { background:#16a34a; }
        .barra-fill.media  { background:#f59e0b; }
        .barra-fill.baja   { background:#ef4444; }
        .btn-usar { background:none; border:1.5px solid var(--borde,#e5e7eb); border-radius:8px; padding:5px 10px; font-size:12px; font-weight:600; color:#e05a6e; cursor:pointer; white-space:nowrap; transition:background 0.15s,color 0.15s; }
        .btn-usar:hover { background:#fce7eb; border-color:#e05a6e; }
        .badge-agotado { background:#fee2e2; color:#dc2626; border-radius:6px; padding:3px 8px; font-size:11px; font-weight:700; }
        .badge-stock-bajo { background:#fff7ed; color:#ea580c; border-radius:6px; padding:3px 8px; font-size:11px; font-weight:700; }

        /* ── Lista inventario ── */
        .inv-lista { background:var(--tarjeta,#fff); border-radius:16px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.06); }
        .inv-barra { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:12px; }
        .inv-barra h4 { font-size:15px; font-weight:700; color:var(--texto,#222); margin:0; }
        .btn-icon { background:none; border:1px solid var(--borde,#e5e7eb); border-radius:8px; padding:7px 12px; font-size:13px; color:var(--color-gris,#666); cursor:pointer; display:flex; align-items:center; gap:6px; transition:background 0.15s; }
        .btn-icon:hover { background:var(--fondo,#f5f5f5); }
        .inv-header { display:grid; grid-template-columns:2fr 1.2fr 1.2fr 80px; padding:12px 20px; border-bottom:1px solid var(--borde,#f0f0f0); font-size:12px; font-weight:700; color:var(--color-gris,#888); text-transform:uppercase; letter-spacing:0.04em; }
        .inv-fila { display:grid; grid-template-columns:2fr 1.2fr 1.2fr 80px; padding:14px 20px; border-bottom:1px solid var(--borde,#f5f5f5); align-items:center; transition:background 0.15s; }
        .inv-fila:last-child { border-bottom:none; }
        .inv-fila:hover { background:var(--fondo,#fafafa); }
        .inv-nombre { display:flex; align-items:center; gap:10px; font-size:14px; font-weight:500; color:var(--texto,#222); }
        .inv-tipo-icono { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0; }
        .inv-tipo-icono.azul    { background:#dbeafe; color:#2563eb; }
        .inv-tipo-icono.verde   { background:#dcfce7; color:#16a34a; }
        .inv-tipo-icono.naranja { background:#ffedd5; color:#ea580c; }
        .inv-tipo-icono.rosa    { background:#fce7eb; color:#e05a6e; }
        .inv-fecha { font-size:13px; color:var(--color-gris,#888); }
        .inv-creador { display:flex; align-items:center; gap:8px; font-size:13px; color:var(--texto,#444); }
        .mini-avatar { width:28px; height:28px; border-radius:50%; background:#e05a6e; color:#fff; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .mini-avatar.azul    { background:#2563eb; }
        .mini-avatar.verde   { background:#16a34a; }
        .mini-avatar.naranja { background:#ea580c; }
        .inv-acciones { display:flex; gap:6px; justify-content:flex-end; }
        .inv-acciones button { background:none; border:none; cursor:pointer; color:var(--color-gris,#aaa); font-size:15px; padding:4px 6px; border-radius:6px; transition:color 0.15s,background 0.15s; }
        .inv-acciones button:hover { color:var(--texto,#333); background:var(--fondo,#f0f0f0); }

        /* Paginación */
        .paginacion { display:flex; align-items:center; justify-content:flex-end; gap:6px; padding:16px 20px; border-top:1px solid var(--borde,#f0f0f0); }
        .paginacion button { width:32px; height:32px; border-radius:8px; border:1px solid var(--borde,#e5e7eb); background:#fff; font-size:13px; font-weight:600; cursor:pointer; color:var(--texto,#555); transition:background 0.15s; }
        .paginacion button.activo { background:#e05a6e; color:#fff; border-color:#e05a6e; }
        .paginacion button:hover:not(.activo) { background:var(--fondo,#f5f5f5); }

        /* Modal */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9998; align-items:center; justify-content:center; }
        .modal-overlay.abierto { display:flex; }
        .modal-box { background:#fff; border-radius:20px; padding:32px; width:90%; max-width:520px; box-shadow:0 20px 60px rgba(0,0,0,0.2); max-height:90vh; overflow-y:auto; }
        .modal-box h3 { margin:0 0 20px; font-size:18px; }
        .modal-acciones { display:flex; justify-content:flex-end; gap:12px; margin-top:24px; }
        .select-style { width:100%; padding:10px 14px; border-radius:10px; border:1.5px solid var(--borde,#e5e7eb); font-size:14px; background:#fff; }

        /* Modal uso – contador */
        .uso-producto-nombre { background:#f8fafc; border-radius:10px; padding:14px 16px; margin-bottom:16px; display:flex; align-items:center; gap:12px; }
        .uso-producto-nombre strong { font-size:15px; }
        .uso-producto-nombre small  { color:#9ca3af; font-size:12px; display:block; margin-top:2px; }
        .stock-actual-badge { background:#dcfce7; color:#16a34a; border-radius:8px; padding:6px 12px; font-size:13px; font-weight:700; margin-left:auto; white-space:nowrap; }
        .stock-actual-badge.bajo  { background:#fff7ed; color:#ea580c; }
        .stock-actual-badge.agot  { background:#fee2e2; color:#dc2626; }
        .counter-wrap { display:flex; align-items:center; gap:12px; }
        .counter-btn  { width:38px; height:38px; border-radius:10px; border:1.5px solid var(--borde,#e5e7eb); background:#fff; font-size:18px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.15s; color:#e05a6e; }
        .counter-btn:hover { background:#fce7eb; }
        .counter-input { width:80px; text-align:center; font-size:20px; font-weight:700; border:1.5px solid var(--borde,#e5e7eb); border-radius:10px; padding:6px 0; }
        .restante-preview { font-size:13px; color:#6b7280; margin-top:6px; }
        .restante-preview span { font-weight:700; color:#111; }

        /* Toast */
        .toast { position:fixed; top:24px; right:24px; z-index:9999; display:flex; align-items:center; gap:12px; padding:16px 24px; border-radius:12px; font-size:15px; font-weight:600; box-shadow:0 8px 32px rgba(0,0,0,0.18); animation:slideIn 0.4s ease, fadeOut 0.5s ease 3.5s forwards; pointer-events:none; }
        .toast.exito { background:#22c55e; color:#fff; }
        .toast.error { background:#ef4444; color:#fff; }
        @keyframes slideIn { from{opacity:0;transform:translateX(60px)} to{opacity:1;transform:translateX(0)} }
        @keyframes fadeOut { to{opacity:0;transform:translateX(60px)} }
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
                <a href="informacion-admin.php"     class="nav-item"><i class="fas fa-info-circle"></i><span>Administrador</span></a>
                <a href="informacion-empleados.php" class="nav-item"><i class="fas fa-info-circle"></i><span>Empleados</span></a>
                <a href="agregar-empleado.php"      class="nav-item"><i class="fas fa-user-plus"></i><span>Agregar Empleados</span></a>
            </div>
            <div class="nav-item submenu-toggle activo" onclick="toggleSubmenu('tareas')">
                <i class="fas fa-tasks"></i><span>Gestión de Tareas</span>
                <i class="fas fa-chevron-down flecha" id="flecha-tareas"></i>
            </div>
            <div class="submenu" id="submenu-tareas">
                <a href="gestion-ordenes.php" class="nav-item"><i class="fas fa-info-circle"></i><span>Gestión de Ordenes</span></a>
                <a href="inventario.php"      class="nav-item activo"><i class="fas fa-wrench"></i><span>Ver Inventario</span></a>
                <a href="auditoria.php"       class="nav-item"><i class="fas fa-shield-alt"></i><span>Auditoria</span></a>
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
            <div class="sidebar-usuario-avatar">DG</div>
            <div class="sidebar-usuario-info"><h4>Daniel G.</h4><span>Administrador</span></div>
        </div>
    </aside>

    <!-- CONTENIDO -->
    <main class="contenido">
        <header class="cabecera"></header>
        <div class="pagina">

            <div class="pagina-titulo"><h2>Inventario</h2></div>

            <!-- Sugerencias -->
            <p style="font-size:13px; color:var(--color-gris,#888); margin-bottom:12px; font-weight:600;">Sugerencias de reportes</p>
            <div class="sugerencias">
                <div class="sugerencia-card" onclick="abrirModal()">
                    <div class="sugerencia-icono rosa"><i class="fas fa-circle-plus"></i></div>
                    <span class="sugerencia-nombre">Agregar al<br>inventario</span>
                </div>
                <div class="sugerencia-card" onclick="scrollStock()">
                    <div class="sugerencia-icono naranja"><i class="fas fa-boxes-stacked"></i></div>
                    <span class="sugerencia-nombre">Ver stock<br>restante</span>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════ -->
            <!-- TARJETAS DE RESUMEN DE STOCK                               -->
            <!-- ══════════════════════════════════════════════════════════ -->
            <div class="stock-cards">
                <div class="stock-card verde">
                    <div class="stock-card-icono verde"><i class="fas fa-boxes-stacked"></i></div>
                    <div class="stock-card-info">
                        <h3><?= $total_piezas ?></h3>
                        <span>Piezas totales en stock</span>
                    </div>
                </div>
                <div class="stock-card azul">
                    <div class="stock-card-icono azul"><i class="fas fa-check-circle"></i></div>
                    <div class="stock-card-info">
                        <h3><?= $disponibles ?></h3>
                        <span>Productos disponibles</span>
                    </div>
                </div>
                <div class="stock-card naranja">
                    <div class="stock-card-icono naranja"><i class="fas fa-triangle-exclamation"></i></div>
                    <div class="stock-card-info">
                        <h3><?= count($stock_bajo) ?></h3>
                        <span>Stock bajo (≤ 2 piezas)</span>
                    </div>
                </div>
                <div class="stock-card rojo">
                    <div class="stock-card-icono rojo"><i class="fas fa-ban"></i></div>
                    <div class="stock-card-info">
                        <h3><?= count($sin_stock) ?></h3>
                        <span>Productos agotados</span>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════ -->
            <!-- SECCIÓN STOCK RESTANTE                                      -->
            <!-- ══════════════════════════════════════════════════════════ -->
            <div class="seccion-stock" id="seccion-stock">
                <div class="seccion-stock-header">
                    <h4>
                        <i class="fas fa-gauge-high" style="color:#e05a6e;"></i>
                        Piezas en stock — Registro de uso
                    </h4>
                    <span style="font-size:12px; color:#9ca3af;">
                        Haz clic en <strong>"Registrar uso"</strong> para descontar cuando un empleado tome una pieza
                    </span>
                </div>

                <?php if (empty($items)): ?>
                    <div style="text-align:center; padding:40px; color:#9ca3af;">
                        <i class="fas fa-box-open" style="font-size:36px; display:block; margin-bottom:12px;"></i>
                        No hay productos en el inventario.
                    </div>
                <?php else: ?>
                <div class="stock-grid">
                    <?php
                    // Calcular stock máximo para la barra (usamos el mayor como referencia)
                    $max_stock = max(array_map(fn($i) => (int)$i['cantidad'], $items)) ?: 1;
                    foreach ($items as $idx => $item):
                        $cant     = (int)$item['cantidad'];
                        $colores  = ['azul','verde','naranja','rosa'];
                        $iconos   = ['fa-wrench','fa-screwdriver-wrench','fa-toolbox','fa-oil-can','fa-car','fa-gear'];
                        $color    = $colores[$idx % count($colores)];
                        $icono    = $iconos[$idx % count($iconos)];
                        $pct      = $max_stock > 0 ? round(($cant / $max_stock) * 100) : 0;
                        $pct_fill = min($pct, 100);
                        $clase_barra = $pct_fill >= 60 ? 'alta' : ($pct_fill >= 30 ? 'media' : 'baja');
                    ?>
                    <div class="stock-item">
                        <div class="inv-tipo-icono <?= $color ?>">
                            <i class="fas <?= $icono ?>"></i>
                        </div>
                        <div class="stock-item-info">
                            <div class="stock-item-nombre"><?= htmlspecialchars($item['material']) ?></div>
                            <div class="stock-item-ubicacion"><?= htmlspecialchars($item['ubicacion'] ?? '—') ?></div>
                            <div class="stock-barra-wrap" style="margin-top:6px;">
                                <div class="stock-barra-labels">
                                    <span class="actual"><?= $cant ?> piezas</span>
                                    <span><?= $pct_fill ?>%</span>
                                </div>
                                <div class="barra-bg">
                                    <div class="barra-fill <?= $clase_barra ?>" style="width:<?= $pct_fill ?>%;"></div>
                                </div>
                            </div>
                        </div>
                        <div style="display:flex; flex-direction:column; align-items:flex-end; gap:4px; flex-shrink:0; margin-left:10px;">
                            <?php if ($cant === 0): ?>
                                <span class="badge-agotado"><i class="fas fa-ban"></i> Agotado</span>
                            <?php elseif ($cant <= 2): ?>
                                <span class="badge-stock-bajo"><i class="fas fa-triangle-exclamation"></i> Stock bajo</span>
                                <button class="btn-usar" onclick="abrirUso(<?= $item['id_item'] ?>, <?= htmlspecialchars(json_encode($item), ENT_QUOTES) ?>)">
                                    <i class="fas fa-hand"></i> Registrar uso
                                </button>
                            <?php else: ?>
                                <button class="btn-usar" onclick="abrirUso(<?= $item['id_item'] ?>, <?= htmlspecialchars(json_encode($item), ENT_QUOTES) ?>)">
                                    <i class="fas fa-hand"></i> Registrar uso
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- ══════════════════════════════════════════════════════════ -->
            <!-- LISTA COMPLETA DE INVENTARIO                                -->
            <!-- ══════════════════════════════════════════════════════════ -->
            <div class="inv-barra">
                <h4>Material del inventario utilizado</h4>
                <div style="display:flex; gap:8px;">
                    <button class="btn-icon"><i class="fas fa-filter"></i> Filter</button>
                    <button class="btn-icon"><i class="fas fa-list"></i></button>
                    <button class="btn-icon"><i class="fas fa-grip"></i></button>
                </div>
            </div>

            <div class="inv-lista">
                <div class="inv-header">
                    <span>Materiales</span>
                    <span>Última utilización</span>
                    <span>Responsable del último uso</span>
                    <span></span>
                </div>

                <?php
                $colores_list = ['azul','verde','naranja','rosa'];
                $iconos_list  = ['fa-wrench','fa-screwdriver-wrench','fa-toolbox','fa-oil-can','fa-car','fa-gear'];
                if (!empty($items)):
                    foreach ($items as $idx => $item):
                        $color    = $colores_list[$idx % count($colores_list)];
                        $icono    = $iconos_list[$idx % count($iconos_list)];
                        $mecanico = $item['mecanico_nombre'] ?? 'Sin asignar';
                        $partes   = array_filter(explode(' ', $mecanico));
                        $iniciales= implode('', array_map(fn($p) => strtoupper($p[0]), array_slice($partes, 0, 2)));
                        $av_color = $colores_list[($idx + 1) % count($colores_list)];
                        $fecha    = !empty($item['fecha_registro']) ? date('M d, Y', strtotime($item['fecha_registro'])) : '—';
                        $badge_map= ['Disponible'=>'badge-verde','En Uso'=>'badge-azul','Mantenimiento'=>'badge-naranja'];
                        $badge    = $badge_map[$item['estado']] ?? 'badge-verde';
                        $cant     = (int)$item['cantidad'];
                ?>
                <div class="inv-fila">
                    <div class="inv-nombre">
                        <div class="inv-tipo-icono <?= $color ?>"><i class="fas <?= $icono ?>"></i></div>
                        <div>
                            <div><?= htmlspecialchars($item['material']) ?></div>
                            <div style="font-size:11px; color:var(--color-gris,#888);">
                                Cant: <strong><?= $cant ?></strong> &nbsp;·&nbsp;
                                <?= htmlspecialchars($item['ubicacion'] ?? '') ?> &nbsp;·&nbsp;
                                <span class="badge <?= $badge ?>" style="font-size:10px;"><?= $item['estado'] ?></span>
                                <?php if ($cant === 0): ?>
                                    &nbsp;<span style="color:#dc2626; font-weight:700; font-size:10px;">⚠ AGOTADO</span>
                                <?php elseif ($cant <= 2): ?>
                                    &nbsp;<span style="color:#ea580c; font-weight:700; font-size:10px;">⚠ STOCK BAJO</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="inv-fecha"><?= $fecha ?></div>
                    <div class="inv-creador">
                        <div class="mini-avatar <?= $av_color ?>"><?= $iniciales ?></div>
                        <?= htmlspecialchars($mecanico) ?>
                    </div>
                    <div class="inv-acciones">
                        <button title="Ver"><i class="fas fa-eye"></i></button>
                        <button title="Editar" onclick="abrirEditar(<?= $item['id_item'] ?>, <?= htmlspecialchars(json_encode($item), ENT_QUOTES) ?>)">
                            <i class="fas fa-pen"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach;
                else: ?>
                <div style="text-align:center; padding:48px; color:var(--color-gris,#888);">
                    <i class="fas fa-box-open" style="font-size:40px; margin-bottom:14px; display:block;"></i>
                    No hay materiales en el inventario aún.
                </div>
                <?php endif; ?>

                <div class="paginacion">
                    <button><i class="fas fa-chevron-left"></i></button>
                    <button class="activo">1</button>
                    <button>2</button>
                    <button>...</button>
                    <button>10</button>
                    <button><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>

        </div><!-- /.pagina -->
    </main>
</div><!-- /.contenedor -->

<!-- ════════════════════════════════════════════════════════════════════════ -->
<!-- MODAL: AGREGAR                                                           -->
<!-- ════════════════════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="modal-agregar">
    <div class="modal-box">
        <form method="POST" action="inventario.php">
        <h3><i class="fas fa-circle-plus" style="color:#e05a6e; margin-right:8px;"></i>Agregar al Inventario</h3>
        <div style="display:grid; gap:14px;">
            <div class="form-grupo">
                <label>Material / Herramienta *</label>
                <input type="text" name="material" placeholder="Nombre del material o herramienta" required>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div class="form-grupo">
                    <label>Cantidad *</label>
                    <input type="number" name="cantidad" min="1" value="1" required>
                </div>
                <div class="form-grupo">
                    <label>Estado</label>
                    <select name="estado" class="select-style">
                        <option value="Disponible">Disponible</option>
                        <option value="En Uso">En Uso</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                    </select>
                </div>
            </div>
            <div class="form-grupo">
                <label>Ubicación</label>
                <input type="text" name="ubicacion" placeholder="Estante A, Bahía 2, etc.">
            </div>
            <div class="form-grupo">
                <label>Responsable del último uso</label>
                <select name="mecanico_id" class="select-style">
                    <option value="">-- Sin asignar --</option>
                    <?php foreach ($empleados as $emp): ?>
                    <option value="<?= $emp['id_empleado'] ?>"><?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellido']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="modal-acciones">
            <button type="button" class="btn btn-secundario" onclick="cerrarModal()">Cancelar</button>
            <button type="submit" name="guardar_item" class="btn btn-primario">Guardar</button>
        </div>
        </form>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════════════════ -->
<!-- MODAL: EDITAR                                                            -->
<!-- ════════════════════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="modal-editar">
    <div class="modal-box">
        <form method="POST" action="inventario.php">
        <input type="hidden" name="id_item" id="edit-id">
        <h3><i class="fas fa-pen" style="color:#e05a6e; margin-right:8px;"></i>Editar Item</h3>
        <div style="display:grid; gap:14px;">
            <div class="form-grupo">
                <label>Material / Herramienta *</label>
                <input type="text" name="material_editar" id="edit-material" placeholder="Nombre del material" required>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div class="form-grupo">
                    <label>Cantidad *</label>
                    <input type="number" name="cantidad_editar" id="edit-cantidad" min="1" value="1" required>
                </div>
                <div class="form-grupo">
                    <label>Estado</label>
                    <select name="estado_editar" id="edit-estado" class="select-style">
                        <option value="Disponible">Disponible</option>
                        <option value="En Uso">En Uso</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                    </select>
                </div>
            </div>
            <div class="form-grupo">
                <label>Ubicación</label>
                <input type="text" name="ubicacion_editar" id="edit-ubicacion" placeholder="Estante A, Bahía 2, etc.">
            </div>
            <div class="form-grupo">
                <label>Responsable del último uso</label>
                <select name="mecanico_id_editar" id="edit-mecanico" class="select-style">
                    <option value="">-- Sin asignar --</option>
                    <?php foreach ($empleados as $emp): ?>
                    <option value="<?= $emp['id_empleado'] ?>"><?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellido']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="modal-acciones">
            <button type="button" class="btn btn-secundario" onclick="cerrarEditar()">Cancelar</button>
            <button type="submit" name="actualizar_item" class="btn btn-primario">Guardar Cambios</button>
        </div>
        </form>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════════════════ -->
<!-- MODAL: REGISTRAR USO (descontar stock)                                   -->
<!-- ════════════════════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="modal-uso">
    <div class="modal-box" style="max-width:460px;">
        <form method="POST" action="inventario.php">
        <input type="hidden" name="id_producto_uso" id="uso-id">
        <h3><i class="fas fa-hand" style="color:#e05a6e; margin-right:8px;"></i>Registrar uso de pieza</h3>

        <!-- Info producto -->
        <div class="uso-producto-nombre">
            <i class="fas fa-box" style="font-size:22px; color:#e05a6e;"></i>
            <div>
                <strong id="uso-nombre">—</strong>
                <small id="uso-ubicacion">—</small>
            </div>
            <div class="stock-actual-badge" id="uso-badge-stock">— piezas</div>
        </div>

        <div style="display:grid; gap:16px;">
            <!-- Contador de piezas -->
            <div class="form-grupo">
                <label>¿Cuántas piezas toma el empleado? *</label>
                <div class="counter-wrap">
                    <button type="button" class="counter-btn" onclick="cambiarContador(-1)">−</button>
                    <input type="number" name="piezas_usar" id="uso-cantidad" class="counter-input" min="1" value="1" required>
                    <button type="button" class="counter-btn" onclick="cambiarContador(1)">+</button>
                </div>
                <div class="restante-preview">
                    Quedarán en stock: <span id="uso-restante">—</span> piezas
                </div>
            </div>

            <!-- Mecánico -->
            <div class="form-grupo">
                <label>Empleado que toma la pieza *</label>
                <select name="mecanico_uso" id="uso-mecanico" class="select-style" required>
                    <option value="">-- Seleccionar empleado --</option>
                    <?php foreach ($empleados as $emp): ?>
                    <option value="<?= $emp['id_empleado'] ?>"><?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellido']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Nota -->
            <div class="form-grupo">
                <label>Nota (opcional)</label>
                <input type="text" name="nota_uso" placeholder="Ej: Para orden ORD-003, cambio de aceite...">
            </div>
        </div>

        <div class="modal-acciones">
            <button type="button" class="btn btn-secundario" onclick="cerrarUso()">Cancelar</button>
            <button type="submit" name="registrar_uso" class="btn btn-primario" id="btn-confirmar-uso">
                <i class="fas fa-check"></i> Confirmar uso
            </button>
        </div>
        </form>
    </div>
</div>

<!-- Toast -->
<?php if ($mensaje): ?>
<div class="toast <?= $tipo_mensaje ?>">
    <i class="fas <?= $tipo_mensaje==='exito' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
    <?= htmlspecialchars($mensaje) ?>
</div>
<?php endif; ?>

<script src="../../js/menu.js"></script>
<script>
    // ── Modales básicos ────────────────────────────────────────────────────
    function abrirModal()   { document.getElementById('modal-agregar').classList.add('abierto'); }
    function cerrarModal()  { document.getElementById('modal-agregar').classList.remove('abierto'); }
    function cerrarEditar() { document.getElementById('modal-editar').classList.remove('abierto'); }
    function cerrarUso()    { document.getElementById('modal-uso').classList.remove('abierto'); }

    function abrirEditar(id, datos) {
        document.getElementById('edit-id').value        = id;
        document.getElementById('edit-material').value  = datos.material;
        document.getElementById('edit-cantidad').value  = datos.cantidad;
        document.getElementById('edit-estado').value    = datos.estado;
        document.getElementById('edit-ubicacion').value = datos.ubicacion || '';
        document.getElementById('edit-mecanico').value  = datos.id_mecanico || '';
        document.getElementById('modal-editar').classList.add('abierto');
    }

    // ── Modal USO ──────────────────────────────────────────────────────────
    let _stockActual = 0;

    function abrirUso(id, datos) {
        _stockActual = parseInt(datos.cantidad) || 0;

        document.getElementById('uso-id').value       = id;
        document.getElementById('uso-nombre').textContent    = datos.material;
        document.getElementById('uso-ubicacion').textContent = datos.ubicacion || 'Sin ubicación';
        document.getElementById('uso-cantidad').value = 1;
        document.getElementById('uso-cantidad').max   = _stockActual;

        // Badge de stock actual
        const badge = document.getElementById('uso-badge-stock');
        badge.textContent = _stockActual + ' piezas disponibles';
        badge.className = 'stock-actual-badge' + (_stockActual <= 2 ? (_stockActual === 0 ? ' agot' : ' bajo') : '');

        actualizarRestante();
        document.getElementById('modal-uso').classList.add('abierto');
    }

    function cambiarContador(delta) {
        const input = document.getElementById('uso-cantidad');
        let val = parseInt(input.value) + delta;
        val = Math.max(1, Math.min(val, _stockActual));
        input.value = val;
        actualizarRestante();
    }

    function actualizarRestante() {
        const usar     = parseInt(document.getElementById('uso-cantidad').value) || 0;
        const restante = _stockActual - usar;
        const el       = document.getElementById('uso-restante');
        el.textContent = restante >= 0 ? restante : 0;
        el.style.color = restante <= 0 ? '#dc2626' : (restante <= 2 ? '#ea580c' : '#16a34a');
        el.style.fontWeight = '700';

        // Deshabilitar botón si excede stock
        const btn = document.getElementById('btn-confirmar-uso');
        btn.disabled = usar > _stockActual || usar < 1;
    }

    document.getElementById('uso-cantidad').addEventListener('input', actualizarRestante);

    // Cerrar modales al hacer clic fuera
    ['modal-agregar','modal-editar','modal-uso'].forEach(id => {
        document.getElementById(id).addEventListener('click', e => {
            if (e.target === e.currentTarget) document.getElementById(id).classList.remove('abierto');
        });
    });

    // Scroll a sección stock
    function scrollStock() {
        document.getElementById('seccion-stock').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Abrir submenu activo
    document.addEventListener('DOMContentLoaded', () => {
        const sub = document.getElementById('submenu-tareas');
        if (sub) sub.style.display = 'block';
    });
</script>
</body>
</html>