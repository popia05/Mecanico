<?php
require_once '../../php/db_conexion.php';

$mensaje = '';
$tipo_mensaje = '';

// Guardar nuevo item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_item'])) {
    $material    = trim($_POST['material'] ?? '');
    $cantidad    = intval($_POST['cantidad'] ?? 1);
    $estado      = $_POST['estado'] ?? 'Disponible';
    $ubicacion   = trim($_POST['ubicacion'] ?? '');
    $id_mecanico = intval($_POST['mecanico_id'] ?? 0) ?: null;

    try {
        $sql = "INSERT INTO inventario (nombre_producto, cantidad_stock, estado, ubicacion, id_mecanico)
                VALUES (:material, :cantidad, :estado, :ubicacion, :id_mecanico)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':material'=>$material,':cantidad'=>$cantidad,':estado'=>$estado,':ubicacion'=>$ubicacion,':id_mecanico'=>$id_mecanico]);
        $mensaje = '¡Material agregado al inventario!';
        $tipo_mensaje = 'exito';
    } catch (PDOException $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}

// Actualizar item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_item'])) {
    $id_item     = intval($_POST['id_item'] ?? 0);
    $material    = trim($_POST['material_editar'] ?? '');
    $cantidad    = intval($_POST['cantidad_editar'] ?? 1);
    $estado      = $_POST['estado_editar'] ?? 'Disponible';
    $ubicacion   = trim($_POST['ubicacion_editar'] ?? '');
    $id_mecanico = intval($_POST['mecanico_id_editar'] ?? 0) ?: null;

    try {
        $sql = "UPDATE inventario SET nombre_producto=:m, cantidad_stock=:c, estado=:e, ubicacion=:u, id_mecanico=:mec WHERE id_producto=:id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':m'=>$material,':c'=>$cantidad,':e'=>$estado,':u'=>$ubicacion,':mec'=>$id_mecanico,':id'=>$id_item]);
        $mensaje = '¡Item actualizado!';
        $tipo_mensaje = 'exito';
    } catch (PDOException $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}

// Obtener empleados
$empleados = [];
try {
    $stmt = $conexion->query("SELECT id_empleado, nombre, apellido FROM empleados ORDER BY nombre ASC");
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $empleados = []; }

// Obtener inventario
$items = [];
try {
    $stmt = $conexion->query("
        SELECT i.id_producto AS id_item, i.nombre_producto AS material, i.cantidad_stock AS cantidad,
               i.precio_unitario, i.estado, i.ubicacion, i.id_mecanico, i.fecha_registro,
               CONCAT(e.nombre, ' ', e.apellido) AS mecanico_nombre
        FROM inventario i
        LEFT JOIN empleados e ON i.id_mecanico = e.id_empleado
        ORDER BY i.fecha_registro DESC
    ");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $items = []; }

// Conteos
$disponibles   = count(array_filter($items, fn($i) => ($i['estado'] ?? '') === 'Disponible'));
$en_uso        = count(array_filter($items, fn($i) => ($i['estado'] ?? '') === 'En Uso'));
$mantenimiento = count(array_filter($items, fn($i) => ($i['estado'] ?? '') === 'Mantenimiento'));
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
        .sugerencia-card {
            background: var(--tarjeta,#fff);
            border: 2px dashed var(--borde,#e5e7eb);
            border-radius:14px; padding:24px 32px;
            display:flex; flex-direction:column; align-items:center; gap:10px;
            min-width:160px; cursor:pointer;
            transition:border-color 0.2s, transform 0.2s;
        }
        .sugerencia-card:hover { border-color:#e05a6e; transform:translateY(-2px); }
        .sugerencia-icono { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px; }
        .sugerencia-icono.rosa { background:#fce7eb; color:#e05a6e; }
        .sugerencia-nombre { font-size:14px; font-weight:600; color:var(--texto,#222); text-align:center; }

        /* ── Lista inventario ── */
        .inv-lista { background:var(--tarjeta,#fff); border-radius:16px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.06); }
        .inv-barra {
            display:flex; align-items:center; justify-content:space-between;
            margin-bottom:16px; flex-wrap:wrap; gap:12px;
        }
        .inv-barra h4 { font-size:15px; font-weight:700; color:var(--texto,#222); margin:0; }
        .btn-icon { background:none; border:1px solid var(--borde,#e5e7eb); border-radius:8px; padding:7px 12px; font-size:13px; color:var(--color-gris,#666); cursor:pointer; display:flex; align-items:center; gap:6px; transition:background 0.15s; }
        .btn-icon:hover { background:var(--fondo,#f5f5f5); }

        .inv-header {
            display:grid; grid-template-columns:2fr 1.2fr 1.2fr 80px;
            padding:12px 20px; border-bottom:1px solid var(--borde,#f0f0f0);
            font-size:12px; font-weight:700; color:var(--color-gris,#888);
            text-transform:uppercase; letter-spacing:0.04em;
        }
        .inv-fila {
            display:grid; grid-template-columns:2fr 1.2fr 1.2fr 80px;
            padding:14px 20px; border-bottom:1px solid var(--borde,#f5f5f5);
            align-items:center; transition:background 0.15s; cursor:default;
        }
        .inv-fila:last-child { border-bottom:none; }
        .inv-fila:hover { background:var(--fondo,#fafafa); }

        .inv-nombre { display:flex; align-items:center; gap:10px; font-size:14px; font-weight:500; color:var(--texto,#222); }
        .inv-tipo-icono { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0; }
        .inv-tipo-icono.azul   { background:#dbeafe; color:#2563eb; }
        .inv-tipo-icono.verde  { background:#dcfce7; color:#16a34a; }
        .inv-tipo-icono.naranja{ background:#ffedd5; color:#ea580c; }
        .inv-tipo-icono.rosa   { background:#fce7eb; color:#e05a6e; }

        .inv-fecha { font-size:13px; color:var(--color-gris,#888); }
        .inv-creador { display:flex; align-items:center; gap:8px; font-size:13px; color:var(--texto,#444); }
        .mini-avatar { width:28px; height:28px; border-radius:50%; background:#e05a6e; color:#fff; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .mini-avatar.azul   { background:#2563eb; }
        .mini-avatar.verde  { background:#16a34a; }
        .mini-avatar.naranja{ background:#ea580c; }

        .inv-acciones { display:flex; gap:6px; justify-content:flex-end; }
        .inv-acciones button { background:none; border:none; cursor:pointer; color:var(--color-gris,#aaa); font-size:15px; padding:4px 6px; border-radius:6px; transition:color 0.15s, background 0.15s; }
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
                <a href="informacion-admin.php" class="nav-item"><i class="fas fa-info-circle"></i><span>Administrador</span></a>
                <a href="informacion-empleados.php" class="nav-item"><i class="fas fa-info-circle"></i><span>Empleados</span></a>
                <a href="agregar-empleado.php" class="nav-item"><i class="fas fa-user-plus"></i><span>Agregar Empleados</span></a>
            </div>

            <div class="nav-item submenu-toggle activo" onclick="toggleSubmenu('tareas')">
                <i class="fas fa-tasks"></i><span>Gestion de Tareas</span>
                <i class="fas fa-chevron-down flecha" id="flecha-tareas"></i>
            </div>
            <div class="submenu" id="submenu-tareas">
                <a href="gestion-ordenes.php" class="nav-item"><i class="fas fa-info-circle"></i><span>Gestion de Ordenes</span></a>
                <a href="inventario.php" class="nav-item activo"><i class="fas fa-wrench"></i><span>Ver Inventario</span></a>
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
            <div class="cabecera-acciones">
                <button><i class="fas fa-search"></i></button>
                <button><i class="fas fa-bell"></i></button>
                <button><i class="fas fa-question-circle"></i></button>
            </div>
        </header>

        <div class="pagina">
            <div class="pagina-titulo">
                <h2>Inventario</h2>
            </div>

            <!-- Sugerencias -->
            <p style="font-size:13px; color:var(--color-gris,#888); margin-bottom:12px; font-weight:600;">Sugerencias de reportes</p>
            <div class="sugerencias">
                <div class="sugerencia-card" onclick="abrirModal()">
                    <div class="sugerencia-icono rosa"><i class="fas fa-circle-plus"></i></div>
                    <span class="sugerencia-nombre">Agregar al<br>inventario</span>
                </div>
            </div>

            <!-- Barra -->
            <div class="inv-barra">
                <h4>Material del inventario utilizado</h4>
                <div style="display:flex; gap:8px;">
                    <button class="btn-icon"><i class="fas fa-filter"></i> Filter</button>
                    <button class="btn-icon"><i class="fas fa-list"></i></button>
                    <button class="btn-icon"><i class="fas fa-grip"></i></button>
                </div>
            </div>

            <!-- Lista -->
            <div class="inv-lista">
                <div class="inv-header">
                    <span>Materiales</span>
                    <span>Última utilización</span>
                    <span>Responsable del último uso</span>
                    <span></span>
                </div>

                <?php
                $colores = ['azul','verde','naranja','rosa'];
                $iconos  = ['fa-wrench','fa-screwdriver-wrench','fa-toolbox','fa-oil-can','fa-car','fa-gear'];
                if (!empty($items)):
                    foreach ($items as $idx => $item):
                        $color    = $colores[$idx % count($colores)];
                        $icono    = $iconos[$idx % count($iconos)];
                        $mecanico = $item['mecanico_nombre'] ?? 'Sin asignar';
                        $partes   = array_filter(explode(' ', $mecanico));
                        $iniciales = implode('', array_map(fn($p) => strtoupper($p[0]), array_slice($partes, 0, 2)));
                        $av_color = $colores[($idx + 1) % count($colores)];
                        $fecha    = !empty($item['fecha_registro']) ? date('M d, Y', strtotime($item['fecha_registro'])) : '—';
                        $badge_map = ['Disponible'=>'badge-verde','En Uso'=>'badge-azul','Mantenimiento'=>'badge-naranja'];
                        $badge    = $badge_map[$item['estado']] ?? 'badge-verde';
                ?>
                <div class="inv-fila">
                    <div class="inv-nombre">
                        <div class="inv-tipo-icono <?= $color ?>"><i class="fas <?= $icono ?>"></i></div>
                        <div>
                            <div><?= htmlspecialchars($item['material']) ?></div>
                            <div style="font-size:11px; color:var(--color-gris,#888);">
                                Cant: <?= $item['cantidad'] ?> &nbsp;·&nbsp;
                                <?= htmlspecialchars($item['ubicacion'] ?? '') ?> &nbsp;·&nbsp;
                                <span class="badge <?= $badge ?>" style="font-size:10px;"><?= $item['estado'] ?></span>
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
        </div>
    </main>
</div>

<!-- Modal AGREGAR -->
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

<!-- Modal EDITAR -->
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

<!-- Toast -->
<?php if ($mensaje): ?>
<div class="toast <?= $tipo_mensaje ?>">
    <i class="fas <?= $tipo_mensaje==='exito' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
    <?= htmlspecialchars($mensaje) ?>
</div>
<?php endif; ?>

<script src="../../js/menu.js"></script>
<script>
    function abrirModal()   { document.getElementById('modal-agregar').classList.add('abierto'); }
    function cerrarModal()  { document.getElementById('modal-agregar').classList.remove('abierto'); }
    function cerrarEditar() { document.getElementById('modal-editar').classList.remove('abierto'); }

    function abrirEditar(id, datos) {
        document.getElementById('edit-id').value        = id;
        document.getElementById('edit-material').value  = datos.material;
        document.getElementById('edit-cantidad').value  = datos.cantidad;
        document.getElementById('edit-estado').value    = datos.estado;
        document.getElementById('edit-ubicacion').value = datos.ubicacion || '';
        document.getElementById('edit-mecanico').value  = datos.id_mecanico || '';
        document.getElementById('modal-editar').classList.add('abierto');
    }

    document.getElementById('modal-agregar').addEventListener('click', e => { if(e.target===e.currentTarget) cerrarModal(); });
    document.getElementById('modal-editar').addEventListener('click',  e => { if(e.target===e.currentTarget) cerrarEditar(); });

    document.addEventListener('DOMContentLoaded', () => {
        const sub = document.getElementById('submenu-tareas');
        if (sub) sub.style.display = 'block';
    });
</script>
</body>
</html>