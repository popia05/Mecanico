<?php
require_once '../../php/db_conexion.php';

$mensaje = '';
$tipo_mensaje = '';

// Actualizar orden existente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_orden'])) {
    $id_ord   = intval($_POST['id_orden_editar'] ?? 0);
    $vehiculo = trim($_POST['vehiculo_editar'] ?? '');
    $cliente  = trim($_POST['cliente_editar'] ?? '');
    $servicio = trim($_POST['servicio_editar'] ?? '');
    $id_mec   = intval($_POST['mecanico_id_editar'] ?? 0) ?: null;
    $estado   = $_POST['estado_editar'] ?? 'Pendiente';
    try {
        $sql = "UPDATE ordenes SET vehiculo=:v, cliente=:c, servicio=:s, id_mecanico=:m, estado=:e WHERE id_orden=:id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':v'=>$vehiculo,':c'=>$cliente,':s'=>$servicio,':m'=>$id_mec,':e'=>$estado,':id'=>$id_ord]);
        $mensaje = '¡Orden actualizada exitosamente!';
        $tipo_mensaje = 'exito';
    } catch (PDOException $e) {
        $mensaje = 'Error al actualizar: ' . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}

// Guardar nueva orden
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_orden'])) {
    $vehiculo    = trim($_POST['vehiculo'] ?? '');
    $cliente     = trim($_POST['cliente'] ?? '');
    $servicio    = trim($_POST['servicio'] ?? '');
    $id_mecanico = intval($_POST['mecanico_id'] ?? 0) ?: null;
    $estado      = $_POST['estado'] ?? 'Pendiente';

    try {
        $sql = "INSERT INTO ordenes (vehiculo, cliente, servicio, id_mecanico, estado)
                VALUES (:vehiculo, :cliente, :servicio, :id_mecanico, :estado)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':vehiculo'    => $vehiculo,
            ':cliente'     => $cliente,
            ':servicio'    => $servicio,
            ':id_mecanico' => $id_mecanico,
            ':estado'      => $estado,
        ]);
        $mensaje = '¡Orden guardada exitosamente!';
        $tipo_mensaje = 'exito';
    } catch (PDOException $e) {
        $mensaje = 'Error al guardar: ' . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}

// Obtener empleados para el select
$empleados = [];
try {
    $stmt = $conexion->query("SELECT id_empleado, nombre, apellido, puesto FROM empleados ORDER BY nombre ASC");
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $empleados = [];
}

// Obtener ordenes guardadas en BD
$ordenes_bd = [];
try {
    $stmt = $conexion->query("
        SELECT o.*, CONCAT(e.nombre, ' ', e.apellido) AS mecanico_nombre
        FROM ordenes o
        LEFT JOIN empleados e ON o.id_mecanico = e.id_empleado
        ORDER BY o.fecha_creacion DESC
        LIMIT 10
    ");
    $ordenes_bd = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $ordenes_bd = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ordenes - Auto Master</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/estilos-generales.css">
    <style>
        /* ── Sugerencias de reportes ── */
        .sugerencias {
            display: flex;
            gap: 16px;
            margin-bottom: 28px;
            flex-wrap: wrap;
        }
        .sugerencia-card {
            background: var(--tarjeta, #fff);
            border-radius: 14px;
            padding: 20px 28px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            min-width: 160px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid var(--borde, #f0f0f0);
        }
        .sugerencia-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        .sugerencia-icono {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
        }
        .sugerencia-icono.rosa   { background: #fce7eb; color: #e05a6e; }
        .sugerencia-icono.verde  { background: #dcfce7; color: #16a34a; }
        .sugerencia-icono.azul   { background: #dbeafe; color: #2563eb; }
        .sugerencia-nombre { font-size: 14px; font-weight: 600; color: var(--texto, #222); }
        .sugerencia-sub    { font-size: 12px; color: var(--color-gris, #888); }

        /* ── Barra de filtros ── */
        .ordenes-barra {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .ordenes-barra h4 { font-size: 15px; font-weight: 700; color: var(--texto, #222); margin: 0; }
        .ordenes-barra-acciones { display: flex; align-items: center; gap: 10px; }
        .btn-icon {
            background: none;
            border: 1px solid var(--borde, #e5e7eb);
            border-radius: 8px;
            padding: 7px 12px;
            font-size: 13px;
            color: var(--color-gris, #666);
            cursor: pointer;
            display: flex; align-items: center; gap: 6px;
            transition: background 0.15s;
        }
        .btn-icon:hover { background: var(--fondo, #f5f5f5); }

        /* ── Lista de órdenes ── */
        .ordenes-lista { background: var(--tarjeta, #fff); border-radius: 16px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
        .ordenes-header {
            display: grid;
            grid-template-columns: 2fr 1.2fr 1.2fr 80px;
            padding: 12px 20px;
            border-bottom: 1px solid var(--borde, #f0f0f0);
            font-size: 12px;
            font-weight: 700;
            color: var(--color-gris, #888);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .orden-fila {
            display: grid;
            grid-template-columns: 2fr 1.2fr 1.2fr 80px;
            padding: 14px 20px;
            border-bottom: 1px solid var(--borde, #f5f5f5);
            align-items: center;
            transition: background 0.15s;
            cursor: pointer;
        }
        .orden-fila:last-child { border-bottom: none; }
        .orden-fila:hover { background: var(--fondo, #fafafa); }

        .orden-nombre {
            display: flex; align-items: center; gap: 10px;
            font-size: 14px; font-weight: 500; color: var(--texto, #222);
        }
        .orden-tipo-icono {
            width: 32px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; flex-shrink: 0;
        }
        .orden-tipo-icono.azul   { background: #dbeafe; color: #2563eb; }
        .orden-tipo-icono.verde  { background: #dcfce7; color: #16a34a; }
        .orden-tipo-icono.naranja{ background: #ffedd5; color: #ea580c; }
        .orden-tipo-icono.rosa   { background: #fce7eb; color: #e05a6e; }

        .orden-fecha { font-size: 13px; color: var(--color-gris, #888); }
        .orden-creador {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: var(--texto, #444);
        }
        .mini-avatar {
            width: 28px; height: 28px; border-radius: 50%;
            background: #e05a6e; color: #fff;
            font-size: 10px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .mini-avatar.azul   { background: #2563eb; }
        .mini-avatar.verde  { background: #16a34a; }
        .mini-avatar.naranja{ background: #ea580c; }

        .orden-acciones { display: flex; gap: 6px; justify-content: flex-end; }
        .orden-acciones button {
            background: none; border: none; cursor: pointer;
            color: var(--color-gris, #aaa); font-size: 15px;
            padding: 4px 6px; border-radius: 6px;
            transition: color 0.15s, background 0.15s;
        }
        .orden-acciones button:hover { color: var(--texto, #333); background: var(--fondo, #f0f0f0); }

        /* ── Paginación ── */
        .paginacion {
            display: flex; align-items: center; justify-content: flex-end;
            gap: 6px; padding: 16px 20px;
            border-top: 1px solid var(--borde, #f0f0f0);
        }
        .paginacion button {
            width: 32px; height: 32px; border-radius: 8px;
            border: 1px solid var(--borde, #e5e7eb);
            background: #fff; font-size: 13px; font-weight: 600;
            cursor: pointer; color: var(--texto, #555);
            transition: background 0.15s;
        }
        .paginacion button.activo { background: #e05a6e; color: #fff; border-color: #e05a6e; }
        .paginacion button:hover:not(.activo) { background: var(--fondo, #f5f5f5); }

        /* ── Modal nueva orden ── */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.45); z-index: 9998;
            align-items: center; justify-content: center;
        }
        .modal-overlay.abierto { display: flex; }
        .modal-box {
            background: #fff; border-radius: 20px;
            padding: 32px; width: 90%; max-width: 500px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        .modal-box h3 { margin: 0 0 20px; font-size: 18px; }
        .modal-acciones { display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; }
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
                <a href="gestion-ordenes.php" class="nav-item activo"><i class="fas fa-info-circle"></i><span>Gestion de Ordenes</span></a>
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
            <div class="cabecera-acciones">
                <button><i class="fas fa-search"></i></button>
                <button><i class="fas fa-bell"></i></button>
                <button><i class="fas fa-question-circle"></i></button>
            </div>
        </header>

        <div class="pagina">
            <div class="pagina-titulo">
                <h2>Ordenes</h2>
            </div>

            <!-- Sugerencias de reportes -->
            <p style="font-size:13px; color:var(--color-gris,#888); margin-bottom:12px; font-weight:600;">Sugerencias de reportes</p>
            <div class="sugerencias">
                <div class="sugerencia-card" onclick="abrirModal()">
                    <div class="sugerencia-icono rosa"><i class="fas fa-plus-circle"></i></div>
                    <span class="sugerencia-nombre">Crear orden</span>
                    <span class="sugerencia-sub">1 board · 4 Reports</span>
                </div>
                <div class="sugerencia-card">
                    <div class="sugerencia-icono verde"><i class="fas fa-chart-bar"></i></div>
                    <span class="sugerencia-nombre">Cambiar estado de orden</span>
                    <span class="sugerencia-sub">1 board · 4 Reports</span>
                </div>
                <div class="sugerencia-card">
                    <div class="sugerencia-icono azul"><i class="fas fa-file-export"></i></div>
                    <span class="sugerencia-nombre">Exportar reporte</span>
                    <span class="sugerencia-sub">1 board · 2 Reports</span>
                </div>
            </div>

            <!-- Barra de acciones -->
            <div class="ordenes-barra">
                <h4><?= count($ordenes_bd) ?> ordenes</h4>
                <div class="ordenes-barra-acciones">
                    <button class="btn-icon"><i class="fas fa-filter"></i> Filter</button>
                    <button class="btn-icon"><i class="fas fa-list"></i></button>
                    <button class="btn-icon"><i class="fas fa-grip"></i></button>
                </div>
            </div>

            <!-- Lista de órdenes -->
            <div class="ordenes-lista">
                <div class="ordenes-header">
                    <span>Orden</span>
                    <span>Ultima modificación</span>
                    <span>Creador</span>
                    <span></span>
                </div>

                <?php
                $colores = ['azul','verde','naranja','rosa'];
                $iconos  = ['fa-car','fa-wrench','fa-oil-can','fa-rotate','fa-car-side','fa-pen-ruler'];
                if (!empty($ordenes_bd)):
                    foreach ($ordenes_bd as $i => $o):
                        $color = $colores[$i % count($colores)];
                        $icono = $iconos[$i % count($iconos)];
                        $mecanico = $o['mecanico_nombre'] ?? 'Sin asignar';
                        $iniciales = implode('', array_map(fn($p) => strtoupper($p[0]), array_slice(explode(' ', $mecanico), 0, 2)));
                        $av_color = $colores[($i+1) % count($colores)];
                        $fecha = date('M d, Y', strtotime($o['fecha_creacion']));
                        // Badge estado
                        $badge_map = ['Pendiente'=>'badge-naranja','En Progreso'=>'badge-azul','Terminado'=>'badge-verde'];
                        $badge = $badge_map[$o['estado']] ?? 'badge-naranja';
                ?>
                <div class="orden-fila">
                    <div class="orden-nombre">
                        <div class="orden-tipo-icono <?= $color ?>"><i class="fas <?= $icono ?>"></i></div>
                        <div>
                            <div><?= htmlspecialchars($o['vehiculo']) ?></div>
                            <div style="font-size:11px;color:var(--color-gris,#888);"><?= htmlspecialchars($o['servicio']) ?></div>
                        </div>
                    </div>
                    <div class="orden-fecha">
                        <?= $fecha ?><br>
                        <span class="badge <?= $badge ?>" style="font-size:10px;"><?= $o['estado'] ?></span>
                    </div>
                    <div class="orden-creador">
                        <div class="mini-avatar <?= $av_color ?>"><?= $iniciales ?></div>
                        <?= htmlspecialchars($mecanico) ?>
                    </div>
                    <div class="orden-acciones">
                        <button title="Ver detalle" onclick="verOrden(<?= $o['id_orden'] ?>)" style="color:var(--color-gris,#aaa)"><i class="fas fa-eye"></i></button>
                        <button title="Editar orden" onclick="abrirEditar(<?= $o['id_orden'] ?>, <?= htmlspecialchars(json_encode($o), ENT_QUOTES) ?>)" style="color:var(--color-gris,#aaa)"><i class="fas fa-pen"></i></button>
                    </div>
                </div>
                <?php endforeach;
                else: ?>
                <div style="text-align:center; padding:40px; color:var(--color-gris,#888);">
                    <i class="fas fa-clipboard-list" style="font-size:36px; margin-bottom:12px; display:block;"></i>
                    No hay órdenes registradas aún.
                </div>
                <?php endif; ?>

                <!-- Paginación -->
                <div class="paginacion">
                    <button><i class="fas fa-chevron-left"></i></button>
                    <button class="activo">1</button>
                    <button>2</button>
                    <button>3</button>
                    <button>...</button>
                    <button>10</button>
                    <button><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal nueva orden -->
<div class="modal-overlay" id="modal-orden">
    <div class="modal-box">
        <form method="POST" action="gestion-ordenes.php">
        <h3><i class="fas fa-plus-circle" style="color:#e05a6e; margin-right:8px;"></i>Nueva Orden de Servicio</h3>
        <div style="display:grid; gap:14px;">
            <div class="form-grupo">
                <label>Vehículo *</label>
                <input type="text" name="vehiculo" placeholder="Marca, modelo y año" required>
            </div>
            <div class="form-grupo">
                <label>Cliente *</label>
                <input type="text" name="cliente" placeholder="Nombre del cliente" required>
            </div>
            <div class="form-grupo">
                <label>Servicio *</label>
                <input type="text" name="servicio" placeholder="Descripción del servicio" required>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div class="form-grupo">
                    <label>Mecánico asignado</label>
                    <select name="mecanico_id" style="width:100%; padding:10px 14px; border-radius:10px; border:1.5px solid var(--borde,#e5e7eb); font-size:14px;">
                        <option value="">-- Seleccionar mecánico --</option>
                        <?php foreach ($empleados as $emp): ?>
                        <option value="<?= $emp['id_empleado'] ?>">
                            <?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellido']) ?>
                            <?php if (!empty($emp['puesto'])): ?>
                                — <?= htmlspecialchars($emp['puesto']) ?>
                            <?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                        <?php if (empty($empleados)): ?>
                        <option disabled>No hay empleados registrados</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-grupo">
                    <label>Estado</label>
                    <select name="estado" style="width:100%; padding:10px 14px; border-radius:10px; border:1.5px solid var(--borde,#e5e7eb); font-size:14px;">
                        <option value="Pendiente">Pendiente</option>
                        <option value="En Progreso">En Progreso</option>
                        <option value="Terminado">Terminado</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-acciones">
            <button class="btn btn-secundario" onclick="cerrarModal()">Cancelar</button>
            <button type="submit" name="guardar_orden" class="btn btn-primario">Guardar Orden</button>
        </div>
        </form>
    </div>
</div>

<!-- Modal EDITAR orden -->
<div class="modal-overlay" id="modal-editar">
    <div class="modal-box">
        <form method="POST" action="gestion-ordenes.php" id="form-editar">
        <input type="hidden" name="id_orden_editar" id="edit-id">
        <h3><i class="fas fa-pen" style="color:#e05a6e; margin-right:8px;"></i>Editar Orden</h3>
        <div style="display:grid; gap:14px;">
            <div class="form-grupo">
                <label>Vehículo *</label>
                <input type="text" name="vehiculo_editar" id="edit-vehiculo" placeholder="Marca, modelo y año" required>
            </div>
            <div class="form-grupo">
                <label>Cliente *</label>
                <input type="text" name="cliente_editar" id="edit-cliente" placeholder="Nombre del cliente" required>
            </div>
            <div class="form-grupo">
                <label>Servicio *</label>
                <input type="text" name="servicio_editar" id="edit-servicio" placeholder="Descripción del servicio" required>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div class="form-grupo">
                    <label>Mecánico asignado</label>
                    <select name="mecanico_id_editar" id="edit-mecanico" style="width:100%; padding:10px 14px; border-radius:10px; border:1.5px solid var(--borde,#e5e7eb); font-size:14px;">
                        <option value="">-- Sin asignar --</option>
                        <?php foreach ($empleados as $emp): ?>
                        <option value="<?= $emp['id_empleado'] ?>">
                            <?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellido']) ?>
                            <?php if (!empty($emp['puesto'])): ?> — <?= htmlspecialchars($emp['puesto']) ?><?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-grupo">
                    <label>Estado</label>
                    <select name="estado_editar" id="edit-estado" style="width:100%; padding:10px 14px; border-radius:10px; border:1.5px solid var(--borde,#e5e7eb); font-size:14px;">
                        <option value="Pendiente">Pendiente</option>
                        <option value="En Progreso">En Progreso</option>
                        <option value="Terminado">Terminado</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-acciones">
            <button type="button" class="btn btn-secundario" onclick="cerrarEditar()">Cancelar</button>
            <button type="submit" name="actualizar_orden" class="btn btn-primario">Guardar Cambios</button>
        </div>
        </form>
    </div>
</div>

<!-- TOAST -->
<?php if ($mensaje): ?>
<div style="position:fixed; top:24px; right:24px; z-index:9999; display:flex; align-items:center; gap:12px;
            padding:16px 24px; border-radius:12px; font-size:15px; font-weight:600;
            box-shadow:0 8px 32px rgba(0,0,0,0.18);
            background:<?= $tipo_mensaje==='exito' ? '#22c55e' : '#ef4444' ?>; color:#fff;
            animation: slideIn 0.4s ease, fadeOut 0.5s ease 3.5s forwards; pointer-events:none;">
    <i class="fas <?= $tipo_mensaje==='exito' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
    <?= htmlspecialchars($mensaje) ?>
</div>
<style>
@keyframes slideIn { from{opacity:0;transform:translateX(60px)} to{opacity:1;transform:translateX(0)} }
@keyframes fadeOut { to{opacity:0;transform:translateX(60px)} }
</style>
<?php endif; ?>

<script src="../../js/menu.js"></script>
<script>
    function abrirModal()   { document.getElementById('modal-orden').classList.add('abierto'); }
    function cerrarModal()  { document.getElementById('modal-orden').classList.remove('abierto'); }
    function cerrarEditar() { document.getElementById('modal-editar').classList.remove('abierto'); }

    function abrirEditar(id, datos) {
        document.getElementById('edit-id').value       = id;
        document.getElementById('edit-vehiculo').value = datos.vehiculo;
        document.getElementById('edit-cliente').value  = datos.cliente;
        document.getElementById('edit-servicio').value = datos.servicio;
        document.getElementById('edit-estado').value   = datos.estado;
        const selMec = document.getElementById('edit-mecanico');
        selMec.value = datos.id_mecanico || '';
        document.getElementById('modal-editar').classList.add('abierto');
    }

    function verOrden(id) { /* expandir detalle futuro */ }

    document.getElementById('modal-orden').addEventListener('click', function(e) {
        if (e.target === this) cerrarModal();
    });
    document.getElementById('modal-editar').addEventListener('click', function(e) {
        if (e.target === this) cerrarEditar();
    });
    // Abrir submenu tareas por defecto
    document.addEventListener('DOMContentLoaded', () => {
        const sub = document.getElementById('submenu-tareas');
        if (sub) sub.style.display = 'block';
    });
</script>
</body>
</html>