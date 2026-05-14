<?php
session_start();
require_once '../../php/db_conexion.php';

$page_title  = 'Nota de Remisión - Auto Master';
$id_empleado = $_SESSION['id_empleado'];

// ── Guardar reparacion_realizada ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_servicio'], $_POST['reparacion_realizada'])) {
    $id_servicio          = (int) $_POST['id_servicio'];
    $reparacion_realizada = trim($_POST['reparacion_realizada']);

    $check = $conexion->prepare("SELECT id_servicio FROM servicios WHERE id_servicio = ? AND id_empleado = ?");
    $check->execute([$id_servicio, $id_empleado]);

    if ($check->fetch()) {
        $stmt = $conexion->prepare("UPDATE servicios SET reparacion_realizada = ? WHERE id_servicio = ?");
        $stmt->execute([$reparacion_realizada, $id_servicio]);
        header("Location: Nota-remision.php?id=$id_servicio&msg=guardado");
        exit();
    }
}

// ── Cargar servicio seleccionado ──
$servicio = null;

if (isset($_GET['id'])) {
    $id_servicio = (int) $_GET['id'];
    // Traer servicio + datos del empleado + la orden más reciente del empleado
    $stmt = $conexion->prepare("
        SELECT s.*,
               e.nombre as emp_nombre,
               e.apellido as emp_apellido,
               o.vehiculo,
               o.cliente,
               o.estado,
               o.servicio as tipo_servicio
        FROM servicios s
        LEFT JOIN empleados e ON e.id_empleado = s.id_empleado
        LEFT JOIN ordenes o ON o.id_mecanico = s.id_empleado
        WHERE s.id_servicio = ? AND s.id_empleado = ?
        ORDER BY o.fecha_creacion DESC
        LIMIT 1
    ");
    $stmt->execute([$id_servicio, $id_empleado]);
    $servicio = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ── Lista de servicios del empleado (para el select) ──
$stmt = $conexion->prepare("
    SELECT s.id_servicio, s.fecha_ingreso, s.descripcion_falla,
           s.reparacion_realizada, s.costo_total,
           e.nombre as emp_nombre, e.apellido as emp_apellido
    FROM servicios s
    LEFT JOIN empleados e ON e.id_empleado = s.id_empleado
    WHERE s.id_empleado = ?
    ORDER BY s.fecha_ingreso DESC
");
$stmt->execute([$id_empleado]);
$servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nombre del empleado desde sesión como fallback
$nombre_empleado = ($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '');

include 'header.php';
?>

<style>
    .selector-card { background: var(--card-bg); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow); padding: 20px 24px; margin-bottom: 24px; display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
    .selector-label { font-size: 13px; font-weight: 600; color: #1e2238; white-space: nowrap; }
    .selector-select { flex: 1; min-width: 260px; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 10px; font-family: 'Sora', sans-serif; font-size: 13px; color: #1e2238; background: #f8faff; outline: none; cursor: pointer; transition: border-color 0.18s; }
    .selector-select:focus { border-color: var(--accent); }
    .btn-cargar { padding: 10px 22px; background: var(--accent); color: #fff; border: none; border-radius: 10px; font-family: 'Sora', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: filter 0.18s, transform 0.15s; white-space: nowrap; }
    .btn-cargar:hover { filter: brightness(0.9); transform: translateY(-1px); }

    .nota-grid { display: grid; grid-template-columns: 1fr 280px; gap: 24px; align-items: start; }

    .nota-card { background: var(--card-bg); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow); overflow: hidden; }

    .nota-empresa { padding: 20px 28px; border-bottom: 2px solid var(--accent); display: flex; align-items: center; justify-content: space-between; gap: 16px; background: linear-gradient(135deg, #1e2238, #2d3158); }
    .empresa-info h3 { color: #fff; font-size: 18px; font-weight: 700; margin-bottom: 3px; }
    .empresa-info p  { color: #8b92a9; font-size: 12px; line-height: 1.5; }
    .empresa-logo { width: 48px; height: 48px; background: var(--accent); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 22px; flex-shrink: 0; }

    .nota-titulo { text-align: center; padding: 14px; font-size: 12px; font-weight: 700; color: #94a3b8; letter-spacing: 2px; text-transform: uppercase; border-bottom: 1px solid var(--border); background: #f8faff; }

    .nota-campos { padding: 20px 28px; display: flex; flex-direction: column; gap: 0; }

    .campo-grupo { display: flex; align-items: flex-start; gap: 14px; padding: 14px 0; border-bottom: 1px solid var(--border); }
    .campo-grupo:last-child { border-bottom: none; }

    .campo-icono { width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; margin-top: 2px; }
    .ci-azul    { background: #eff6ff; color: #3b82f6; }
    .ci-rosa    { background: #fdf2f8; color: var(--accent); }
    .ci-verde   { background: #f0fdf4; color: #22c55e; }
    .ci-naranja { background: #fffbeb; color: #f59e0b; }
    .ci-morado  { background: #f5f3ff; color: #7c3aed; }
    .ci-gris    { background: #f1f5f9; color: #64748b; }

    .campo-content { flex: 1; }
    .campo-label { font-size: 10.5px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 4px; }
    .campo-valor { font-size: 13.5px; font-weight: 600; color: #1e2238; }
    .campo-valor.muted { font-weight: 400; color: #64748b; }

    .campo-editable-wrapper { flex: 1; }
    .campo-editable-label { font-size: 10.5px; font-weight: 700; color: var(--accent); text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px; display: flex; align-items: center; gap: 6px; }
    .editable-badge { background: #fdf2f8; color: var(--accent); font-size: 10px; padding: 2px 8px; border-radius: 10px; font-weight: 600; }

    .campo-textarea { width: 100%; padding: 12px 14px; border: 1.5px solid var(--accent); border-radius: 10px; font-family: 'Sora', sans-serif; font-size: 13px; color: #1e2238; background: #fff9fb; resize: vertical; min-height: 100px; outline: none; transition: border-color 0.18s, box-shadow 0.18s; line-height: 1.6; }
    .campo-textarea:focus { box-shadow: 0 0 0 3px rgba(229,57,53,0.10); }
    .campo-textarea::placeholder { color: #94a3b8; font-style: italic; }

    .campos-fila { display: grid; grid-template-columns: 1fr 1fr; gap: 0; border-bottom: 1px solid var(--border); }
    .campos-fila .campo-grupo { border-bottom: none; border-right: 1px solid var(--border); }
    .campos-fila .campo-grupo:last-child { border-right: none; }

    .nota-footer { padding: 16px 28px; border-top: 1px solid var(--border); display: flex; align-items: center; justify-content: flex-end; gap: 12px; background: #f8faff; }
    .btn-cancelar { padding: 10px 20px; background: transparent; color: #64748b; border: 1.5px solid var(--border); border-radius: 10px; font-family: 'Sora', sans-serif; font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: none; transition: all 0.18s; }
    .btn-cancelar:hover { border-color: #94a3b8; color: #1e2238; }
    .btn-guardar { padding: 10px 24px; background: var(--accent); color: #fff; border: none; border-radius: 10px; font-family: 'Sora', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: filter 0.18s, transform 0.15s; box-shadow: 0 4px 14px rgba(229,57,53,0.25); }
    .btn-guardar:hover { filter: brightness(0.9); transform: translateY(-1px); }

    .estado-card { background: var(--card-bg); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow); overflow: hidden; }
    .estado-card-header { padding: 16px 20px; border-bottom: 1px solid var(--border); font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; }
    .estado-lista { padding: 16px 20px; display: flex; flex-direction: column; gap: 14px; }
    .estado-item { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .estado-item-label { font-size: 13px; color: #64748b; }
    .estado-check  { color: #22c55e; font-size: 15px; }
    .estado-pencil { color: var(--accent); font-size: 14px; }
    .estado-pending{ color: #94a3b8; font-size: 14px; }

    .total-card { background: var(--accent); border-radius: var(--radius); padding: 24px 20px; text-align: center; margin-top: 16px; }
    .total-label { font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.70); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
    .total-monto { font-size: 36px; font-weight: 700; color: #fff; margin-bottom: 4px; }
    .total-fecha  { font-size: 11.5px; color: rgba(255,255,255,0.60); margin-bottom: 0; }

    .nota-vacia { background: var(--card-bg); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow); padding: 80px 40px; text-align: center; color: #94a3b8; grid-column: 1 / -1; }
    .nota-vacia i  { font-size: 52px; color: #e2e8f0; margin-bottom: 16px; display: block; }
    .nota-vacia h3 { font-size: 16px; color: #64748b; margin-bottom: 6px; }

    .toast { position: fixed; bottom: 28px; right: 28px; background: #22c55e; color: #fff; padding: 14px 20px; border-radius: 12px; font-size: 13.5px; font-weight: 500; display: flex; align-items: center; gap: 10px; box-shadow: 0 8px 28px rgba(30,34,60,0.20); z-index: 999; transform: translateY(80px); opacity: 0; transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); }
    .toast.show { transform: translateY(0); opacity: 1; }

    @media (max-width: 900px) {
        .nota-grid    { grid-template-columns: 1fr; }
        .campos-fila  { grid-template-columns: 1fr; }
        .campos-fila .campo-grupo { border-right: none; border-bottom: 1px solid var(--border); }
    }
</style>

<div class="pagina-titulo">
    <h2><i class="fas fa-file-invoice"></i> Nota de Remisión</h2>
    <p>Selecciona un servicio para agregar los comentarios de reparación.</p>
</div>

<!-- Selector -->
<form method="GET" action="Nota-remision.php" class="selector-card">
    <span class="selector-label">Seleccionar Servicio</span>
    <select name="id" class="selector-select">
        <option value="">— Elige un servicio —</option>
        <?php foreach ($servicios as $s): ?>
        <option value="<?= $s['id_servicio'] ?>"
            <?= (isset($_GET['id']) && (int)$_GET['id'] === (int)$s['id_servicio']) ? 'selected' : '' ?>>
            #<?= $s['id_servicio'] ?> —
            <?= htmlspecialchars($s['descripcion_falla'] ?? 'Sin descripción') ?> |
            <?= htmlspecialchars($s['fecha_ingreso']) ?>
        </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn-cargar">
        <i class="fas fa-arrow-right"></i> Cargar
    </button>
</form>

<!-- Contenido -->
<div class="nota-grid">

<?php if (!$servicio): ?>
    <div class="nota-vacia">
        <i class="fas fa-file-circle-question"></i>
        <h3>Ningún servicio seleccionado</h3>
        <p>Elige un servicio del selector de arriba y haz clic en "Cargar".</p>
    </div>

<?php else:
    // Datos con fallback seguros
    $cliente     = $servicio['cliente']       ?? '—';
    $vehiculo    = $servicio['vehiculo']       ?? '—';
    $estado      = $servicio['estado']         ?? '—';
    $mecanico    = !empty($servicio['emp_nombre'])
                    ? $servicio['emp_nombre'] . ' ' . $servicio['emp_apellido']
                    : $nombre_empleado;
?>

    <!-- Nota principal -->
    <form method="POST">
        <input type="hidden" name="id_servicio" value="<?= $servicio['id_servicio'] ?>">

        <div class="nota-card">

            <div class="nota-empresa">
                <div class="empresa-info">
                    <h3>Auto Master</h3>
                    <p>Fuel Injection · Agua Prieta, Sonora, MX<br>
                       Folio #<?= str_pad($servicio['id_servicio'], 4, '0', STR_PAD_LEFT) ?></p>
                </div>
                <div class="empresa-logo"><i class="fas fa-wrench"></i></div>
            </div>

            <div class="nota-titulo">Nota de Remisión de Servicio</div>

            <div class="nota-campos">

                <!-- Cliente -->
                <div class="campo-grupo">
                    <div class="campo-icono ci-azul"><i class="fas fa-user"></i></div>
                    <div class="campo-content">
                        <div class="campo-label">Cliente</div>
                        <div class="campo-valor"><?= htmlspecialchars($cliente) ?></div>
                    </div>
                </div>

                <!-- Vehículo -->
                <div class="campo-grupo">
                    <div class="campo-icono ci-naranja"><i class="fas fa-car"></i></div>
                    <div class="campo-content">
                        <div class="campo-label">Vehículo · Placas</div>
                        <div class="campo-valor"><?= htmlspecialchars($vehiculo) ?></div>
                    </div>
                </div>

                <!-- Mecánico -->
                <div class="campo-grupo">
                    <div class="campo-icono ci-rosa"><i class="fas fa-user-gear"></i></div>
                    <div class="campo-content">
                        <div class="campo-label">Asignación de Personal</div>
                        <div class="campo-valor"><?= htmlspecialchars($mecanico) ?></div>
                    </div>
                </div>

                <!-- Descripción falla (solo lectura) -->
                <div class="campo-grupo">
                    <div class="campo-icono ci-gris"><i class="fas fa-triangle-exclamation"></i></div>
                    <div class="campo-content">
                        <div class="campo-label">Descripción de la Falla</div>
                        <div class="campo-valor muted"><?= htmlspecialchars($servicio['descripcion_falla'] ?? '—') ?></div>
                    </div>
                </div>

                <!-- Reparación realizada — EDITABLE -->
                <div class="campo-grupo">
                    <div class="campo-icono ci-rosa"><i class="fas fa-pen-to-square"></i></div>
                    <div class="campo-editable-wrapper">
                        <div class="campo-editable-label">
                            <i class="fas fa-pen"></i>
                            Reparación Realizada
                            <span class="editable-badge">Editable</span>
                        </div>
                        <textarea
                            name="reparacion_realizada"
                            class="campo-textarea"
                            placeholder="Describe el trabajo realizado, piezas cambiadas, ajustes hechos..."
                            required
                        ><?= htmlspecialchars($servicio['reparacion_realizada'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Fechas -->
                <div class="campos-fila">
                    <div class="campo-grupo">
                        <div class="campo-icono ci-verde"><i class="fas fa-calendar-check"></i></div>
                        <div class="campo-content">
                            <div class="campo-label">Fecha de Ingreso</div>
                            <div class="campo-valor"><?= date('d/m/Y', strtotime($servicio['fecha_ingreso'])) ?></div>
                        </div>
                    </div>
                    <div class="campo-grupo">
                        <div class="campo-icono ci-morado"><i class="fas fa-calendar-xmark"></i></div>
                        <div class="campo-content">
                            <div class="campo-label">Fecha de Salida</div>
                            <div class="campo-valor muted">—</div>
                        </div>
                    </div>
                </div>

                <!-- Costo y garantía -->
                <div class="campos-fila">
                    <div class="campo-grupo">
                        <div class="campo-icono ci-verde"><i class="fas fa-dollar-sign"></i></div>
                        <div class="campo-content">
                            <div class="campo-label">Costo Total</div>
                            <div class="campo-valor">$<?= number_format($servicio['costo_total'] ?? 0, 2) ?></div>
                        </div>
                    </div>
                    <div class="campo-grupo">
                        <div class="campo-icono ci-azul"><i class="fas fa-shield-halved"></i></div>
                        <div class="campo-content">
                            <div class="campo-label">Garantía</div>
                            <div class="campo-valor muted">30 días en mano de obra</div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="nota-footer">
                <a href="Nota-remision.php" class="btn-cancelar">Cancelar</a>
                <button type="submit" class="btn-guardar">
                    <i class="fas fa-floppy-disk"></i> Guardar nota
                </button>
            </div>

        </div>
    </form>

    <!-- Panel lateral -->
    <div>
        <div class="estado-card">
            <div class="estado-card-header">Estado de la Orden</div>
            <div class="estado-lista">
                <div class="estado-item">
                    <span class="estado-item-label">Vehículo registrado</span>
                    <i class="fas <?= $vehiculo !== '—' ? 'fa-check estado-check' : 'fa-clock estado-pending' ?>"></i>
                </div>
                <div class="estado-item">
                    <span class="estado-item-label">Mecánico asignado</span>
                    <i class="fas fa-check estado-check"></i>
                </div>
                <div class="estado-item">
                    <span class="estado-item-label">Descripción del servicio</span>
                    <i class="fas <?= !empty($servicio['reparacion_realizada']) ? 'fa-check estado-check' : 'fa-pen estado-pencil' ?>"></i>
                </div>
                <div class="estado-item">
                    <span class="estado-item-label">Estado: <strong><?= htmlspecialchars($estado) ?></strong></span>
                    <i class="fas <?= $estado === 'Terminado' ? 'fa-check estado-check' : 'fa-clock estado-pending' ?>"></i>
                </div>
            </div>
        </div>

        <div class="total-card">
            <div class="total-label">Total</div>
            <div class="total-monto">$<?= number_format($servicio['costo_total'] ?? 0, 2) ?></div>
            <div class="total-fecha"><?= date('d/m/Y', strtotime($servicio['fecha_ingreso'])) ?></div>
        </div>
    </div>

<?php endif; ?>

</div>

<!-- Toast -->
<div class="toast" id="toast">
    <i class="fas fa-check-circle"></i> Reparación guardada correctamente
</div>

    </div>
</main>

<script>
function toggleSubmenu(id) {
    const submenu = document.getElementById('submenu-' + id);
    const toggle  = submenu.previousElementSibling;
    submenu.classList.toggle('open');
    toggle.classList.toggle('open');
}
<?php if (isset($_GET['msg']) && $_GET['msg'] === 'guardado'): ?>
window.addEventListener('DOMContentLoaded', () => {
    const toast = document.getElementById('toast');
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3500);
});
<?php endif; ?>
</script>
</body>
</html>