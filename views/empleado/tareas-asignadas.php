<?php
session_start();
require_once '../../php/db_conexion.php';

$page_title = 'Tareas Asignadas - Auto Master';
$id_empleado = $_SESSION['id_empleado'];

// ── Cambiar estado via POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_orden'], $_POST['nuevo_estado'])) {
    $id_orden     = (int) $_POST['id_orden'];
    $nuevo_estado = $_POST['nuevo_estado'];
    $estados_validos = ['Pendiente', 'En Progreso', 'Terminado'];

    if (in_array($nuevo_estado, $estados_validos)) {
        // Solo puede modificar sus propias órdenes
        $stmt = $conexion->prepare("UPDATE ordenes SET estado = ? WHERE id_orden = ? AND id_mecanico = ?");
        $stmt->execute([$nuevo_estado, $id_orden, $id_empleado]);
    }

    header('Location: tareas-asignadas.php?updated=1');
    exit();
}

// ── Filtro por estado ──
$filtro = $_GET['filtro'] ?? 'todos';
$estados_validos = ['Pendiente', 'En Progreso', 'Terminado'];

if (in_array($filtro, $estados_validos)) {
    $stmt = $conexion->prepare("SELECT * FROM ordenes WHERE id_mecanico = ? AND estado = ? ORDER BY fecha_creacion DESC");
    $stmt->execute([$id_empleado, $filtro]);
} else {
    $stmt = $conexion->prepare("SELECT * FROM ordenes WHERE id_mecanico = ? ORDER BY fecha_creacion DESC");
    $stmt->execute([$id_empleado]);
}
$ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Contadores ──
$stmt = $conexion->prepare("SELECT estado, COUNT(*) as total FROM ordenes WHERE id_mecanico = ? GROUP BY estado");
$stmt->execute([$id_empleado]);
$conteos = ['todos' => 0, 'Pendiente' => 0, 'En Progreso' => 0, 'Terminado' => 0];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $conteos[$row['estado']] = (int)$row['total'];
    $conteos['todos'] += (int)$row['total'];
}

include 'header.php';
?>

<style>
    .filtros { display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap; }
    .filtro-btn {
        display: flex; align-items: center; gap: 8px;
        padding: 9px 18px; border-radius: 10px;
        border: 1.5px solid var(--border); background: var(--card-bg);
        font-family: 'Sora', sans-serif; font-size: 13px; font-weight: 500;
        color: #64748b; cursor: pointer; text-decoration: none;
        transition: all 0.18s; box-shadow: var(--shadow);
    }
    .filtro-btn:hover { border-color: var(--accent); color: var(--accent); }
    .filtro-btn.activo { background: var(--accent); border-color: var(--accent); color: #fff; }
    .filtro-btn .count { font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 20px; min-width: 22px; text-align: center; background: rgba(0,0,0,0.10); }
    .filtro-btn:not(.activo) .count { background: #f1f5f9; color: #64748b; }

    .tabla-wrapper { background: var(--card-bg); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow); overflow: hidden; }
    .tabla-header { padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
    .tabla-header-title { font-size: 14px; font-weight: 600; color: #1e2238; display: flex; align-items: center; gap: 8px; }
    .tabla-header-title i { color: var(--accent2); }
    .tabla-total { font-size: 12.5px; color: #94a3b8; }

    table { width: 100%; border-collapse: collapse; }
    thead th { background: #f8faff; padding: 12px 20px; font-size: 11.5px; font-weight: 600; color: #64748b; text-align: left; border-bottom: 1px solid var(--border); text-transform: uppercase; letter-spacing: 0.5px; }
    tbody tr { border-bottom: 1px solid var(--border); transition: background 0.15s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #f8faff; }
    tbody td { padding: 15px 20px; font-size: 13.5px; color: #1e2238; vertical-align: middle; }

    .td-vehiculo { font-weight: 600; }
    .td-sub { font-size: 12.5px; color: #94a3b8; margin-top: 3px; }
    .td-fecha { font-size: 12px; color: #94a3b8; white-space: nowrap; }

    .badge { display: inline-flex; align-items: center; gap: 5px; font-size: 11.5px; font-weight: 600; padding: 4px 12px; border-radius: 20px; white-space: nowrap; }
    .badge-pendiente  { background: #fef3c7; color: #d97706; }
    .badge-proceso    { background: #eff6ff; color: #3b82f6; }
    .badge-completado { background: #dcfce7; color: #16a34a; }

    .estado-form { display: flex; align-items: center; gap: 8px; }
    .estado-select { padding: 7px 10px; border: 1.5px solid var(--border); border-radius: 8px; font-family: 'Sora', sans-serif; font-size: 12.5px; color: #1e2238; background: #f8faff; cursor: pointer; outline: none; transition: border-color 0.18s; }
    .estado-select:focus { border-color: var(--accent); }
    .btn-guardar { padding: 7px 14px; background: var(--accent); color: #fff; border: none; border-radius: 8px; font-family: 'Sora', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; transition: background 0.18s, transform 0.15s; white-space: nowrap; }
    .btn-guardar:hover { filter: brightness(0.9); transform: translateY(-1px); }

    .sin-tareas { padding: 60px 20px; text-align: center; color: #94a3b8; }
    .sin-tareas i { font-size: 48px; color: #e2e8f0; margin-bottom: 14px; display: block; }
    .sin-tareas h3 { font-size: 16px; color: #64748b; margin-bottom: 6px; }

    /* Toast */
    .toast { position: fixed; bottom: 28px; right: 28px; background: #1e2238; color: #fff; padding: 14px 20px; border-radius: 12px; font-size: 13.5px; font-weight: 500; display: flex; align-items: center; gap: 10px; box-shadow: 0 8px 28px rgba(30,34,60,0.20); z-index: 999; transform: translateY(80px); opacity: 0; transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); }
    .toast.show { transform: translateY(0); opacity: 1; }
    .toast i { color: #22c55e; font-size: 16px; }
</style>

<div class="pagina-titulo">
    <h2><i class="fas fa-tasks"></i> Tareas Asignadas</h2>
    <p>Órdenes de trabajo asignadas a ti — solo tú puedes ver y modificar estas.</p>
</div>

<!-- Filtros -->
<div class="filtros">
    <a href="tareas-asignadas.php?filtro=todos" class="filtro-btn <?= $filtro === 'todos' ? 'activo' : '' ?>">
        <i class="fas fa-list"></i> Todas <span class="count"><?= $conteos['todos'] ?></span>
    </a>
    <a href="tareas-asignadas.php?filtro=Pendiente" class="filtro-btn <?= $filtro === 'Pendiente' ? 'activo' : '' ?>">
        <i class="fas fa-clock"></i> Pendientes <span class="count"><?= $conteos['Pendiente'] ?></span>
    </a>
    <a href="tareas-asignadas.php?filtro=En Progreso" class="filtro-btn <?= $filtro === 'En Progreso' ? 'activo' : '' ?>">
        <i class="fas fa-spinner"></i> En Progreso <span class="count"><?= $conteos['En Progreso'] ?></span>
    </a>
    <a href="tareas-asignadas.php?filtro=Terminado" class="filtro-btn <?= $filtro === 'Terminado' ? 'activo' : '' ?>">
        <i class="fas fa-check-circle"></i> Terminadas <span class="count"><?= $conteos['Terminado'] ?></span>
    </a>
</div>

<!-- Tabla -->
<div class="tabla-wrapper">
    <div class="tabla-header">
        <div class="tabla-header-title"><i class="fas fa-clipboard-list"></i> Mis Órdenes</div>
        <div class="tabla-total"><?= count($ordenes) ?> resultado(s)</div>
    </div>

    <?php if (empty($ordenes)): ?>
        <div class="sin-tareas">
            <i class="fas fa-inbox"></i>
            <h3>Sin tareas en este filtro</h3>
            <p>No tienes órdenes con este estado asignadas.</p>
        </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Vehículo</th>
                <th>Cliente</th>
                <th>Servicio</th>
                <th>Fecha</th>
                <th>Estado actual</th>
                <th>Cambiar estado</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($ordenes as $orden):
            $clase_badge = match($orden['estado']) {
                'Pendiente'   => 'badge-pendiente',
                'En Progreso' => 'badge-proceso',
                'Terminado'   => 'badge-completado',
                default       => 'badge-pendiente'
            };
            $icono_badge = match($orden['estado']) {
                'Pendiente'   => 'fa-clock',
                'En Progreso' => 'fa-spinner fa-spin',
                'Terminado'   => 'fa-check-circle',
                default       => 'fa-clock'
            };
        ?>
            <tr>
                <td style="color:#94a3b8; font-size:12px;">#<?= $orden['id_orden'] ?></td>
                <td>
                    <div class="td-vehiculo"><?= htmlspecialchars($orden['vehiculo']) ?></div>
                </td>
                <td>
                    <div><?= htmlspecialchars($orden['cliente']) ?></div>
                </td>
                <td style="max-width:200px; color:#64748b; font-size:13px;">
                    <?= htmlspecialchars($orden['servicio']) ?>
                </td>
                <td class="td-fecha"><?= date('d/m/Y', strtotime($orden['fecha_creacion'])) ?><br>
                    <span style="font-size:11px;"><?= date('H:i', strtotime($orden['fecha_creacion'])) ?></span>
                </td>
                <td>
                    <span class="badge <?= $clase_badge ?>">
                        <i class="fas <?= $icono_badge ?>"></i>
                        <?= htmlspecialchars($orden['estado']) ?>
                    </span>
                </td>
                <td>
                    <form method="POST" class="estado-form">
                        <input type="hidden" name="id_orden" value="<?= $orden['id_orden'] ?>">
                        <select name="nuevo_estado" class="estado-select">
                            <option value="Pendiente"   <?= $orden['estado'] === 'Pendiente'   ? 'selected' : '' ?>>Pendiente</option>
                            <option value="En Progreso" <?= $orden['estado'] === 'En Progreso' ? 'selected' : '' ?>>En Progreso</option>
                            <option value="Terminado"   <?= $orden['estado'] === 'Terminado'   ? 'selected' : '' ?>>Terminado</option>
                        </select>
                        <button type="submit" class="btn-guardar">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Toast -->
<div class="toast" id="toast">
    <i class="fas fa-check-circle"></i> Estado actualizado correctamente
</div>

    </div><!-- /pagina -->
</main><!-- /contenido -->

<script>
function toggleSubmenu(id) {
    const submenu = document.getElementById('submenu-' + id);
    const toggle  = submenu.previousElementSibling;
    submenu.classList.toggle('open');
    toggle.classList.toggle('open');
}

// Mostrar toast si viene de actualización
<?php if (isset($_GET['updated'])): ?>
window.addEventListener('DOMContentLoaded', () => {
    const toast = document.getElementById('toast');
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
});
<?php endif; ?>
</script>
</body>
</html>