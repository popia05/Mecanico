<?php
session_start();
require_once '../../php/db_conexion.php';

$page_title  = 'Gestión de Órdenes - Auto Master';
$id_empleado = $_SESSION['id_empleado'];

// ── Agregar nota via POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_orden'], $_POST['nota'])) {
    $id_orden = (int) $_POST['id_orden'];
    $nota     = trim($_POST['nota']);

    if (!empty($nota)) {
        // Verificar que la orden pertenece al empleado
        $check = $conexion->prepare("SELECT id_orden FROM ordenes WHERE id_orden = ? AND id_mecanico = ?");
        $check->execute([$id_orden, $id_empleado]);

        if ($check->fetch()) {
            $stmt = $conexion->prepare("INSERT INTO notas_ordenes (id_orden, id_mecanico, nota) VALUES (?, ?, ?)");
            $stmt->execute([$id_orden, $id_empleado, $nota]);
        }
    }

    header("Location: Gestion.php?id=$id_orden&updated=1");
    exit();
}

// ── Ver detalle de una orden ──
$orden_detalle = null;
$notas         = [];

if (isset($_GET['id'])) {
    $id_orden = (int) $_GET['id'];

    $stmt = $conexion->prepare("SELECT * FROM ordenes WHERE id_orden = ? AND id_mecanico = ?");
    $stmt->execute([$id_orden, $id_empleado]);
    $orden_detalle = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($orden_detalle) {
        $stmt = $conexion->prepare("
            SELECT n.*, e.nombre, e.apellido
            FROM notas_ordenes n
            LEFT JOIN empleados e ON e.id_empleado = n.id_mecanico
            WHERE n.id_orden = ?
            ORDER BY n.fecha_nota DESC
        ");
        $stmt->execute([$id_orden]);
        $notas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// ── Lista de órdenes del empleado ──
$stmt = $conexion->prepare("
    SELECT o.*,
        (SELECT COUNT(*) FROM notas_ordenes n WHERE n.id_orden = o.id_orden) as total_notas
    FROM ordenes o
    WHERE o.id_mecanico = ?
    ORDER BY o.fecha_creacion DESC
");
$stmt->execute([$id_empleado]);
$ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<style>
    .gestion-grid {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 24px;
        align-items: start;
    }

    /* ── Lista de órdenes ── */
    .ordenes-lista { display: flex; flex-direction: column; gap: 0; max-height: 75vh; overflow-y: auto; }
    .ordenes-lista::-webkit-scrollbar { width: 5px; }
    .ordenes-lista::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

    .orden-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        cursor: pointer;
        transition: background 0.15s;
        text-decoration: none;
        color: inherit;
    }
    .orden-item:last-child { border-bottom: none; }
    .orden-item:hover { background: #f8faff; }
    .orden-item.activa { background: #fdf2f8; border-left: 3px solid var(--accent); }

    .orden-item-icon {
        width: 40px; height: 40px;
        border-radius: 10px;
        background: #eff6ff;
        display: flex; align-items: center; justify-content: center;
        color: var(--accent2);
        font-size: 16px;
        flex-shrink: 0;
    }
    .orden-item.activa .orden-item-icon { background: #fdf2f8; color: var(--accent); }

    .orden-item-body { flex: 1; min-width: 0; }
    .orden-item-vehiculo { font-size: 13.5px; font-weight: 600; color: #1e2238; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .orden-item-sub { font-size: 12px; color: #94a3b8; margin-top: 3px; display: flex; align-items: center; gap: 8px; }

    .orden-item-right { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; flex-shrink: 0; }

    .badge { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 20px; white-space: nowrap; }
    .badge-pendiente  { background: #fef3c7; color: #d97706; }
    .badge-proceso    { background: #eff6ff; color: #3b82f6; }
    .badge-completado { background: #dcfce7; color: #16a34a; }

    .nota-count { font-size: 11px; color: #94a3b8; display: flex; align-items: center; gap: 4px; }

    /* ── Panel detalle ── */
    .detalle-vacio {
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        min-height: 400px;
        color: #94a3b8; text-align: center;
        padding: 40px;
    }
    .detalle-vacio i { font-size: 52px; color: #e2e8f0; margin-bottom: 16px; }
    .detalle-vacio h3 { font-size: 16px; color: #64748b; margin-bottom: 6px; }

    /* Header del detalle */
    .detalle-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }
    .detalle-vehiculo { font-size: 17px; font-weight: 700; color: #1e2238; margin-bottom: 4px; }
    .detalle-meta { font-size: 12.5px; color: #94a3b8; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .detalle-meta span { display: flex; align-items: center; gap: 5px; }

    /* Info rápida */
    .detalle-info {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0;
        border-bottom: 1px solid var(--border);
    }
    .detalle-info-item {
        padding: 16px 22px;
        border-right: 1px solid var(--border);
    }
    .detalle-info-item:last-child { border-right: none; }
    .detalle-info-label { font-size: 11.5px; color: #94a3b8; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.4px; }
    .detalle-info-valor { font-size: 13.5px; font-weight: 600; color: #1e2238; }

    /* Notas */
    .notas-section { padding: 20px 24px; }
    .notas-titulo { font-size: 13.5px; font-weight: 600; color: #1e2238; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    .notas-titulo i { color: var(--accent2); }

    .notas-lista { display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px; max-height: 260px; overflow-y: auto; }
    .notas-lista::-webkit-scrollbar { width: 5px; }
    .notas-lista::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

    .nota-item {
        background: #f8faff;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 14px 16px;
    }
    .nota-item-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
    .nota-autor { font-size: 12.5px; font-weight: 600; color: #1e2238; display: flex; align-items: center; gap: 6px; }
    .nota-autor i { color: var(--accent); font-size: 11px; }
    .nota-fecha { font-size: 11.5px; color: #94a3b8; }
    .nota-texto { font-size: 13px; color: #374151; line-height: 1.6; }

    .sin-notas { text-align: center; padding: 24px; color: #94a3b8; font-size: 13px; }
    .sin-notas i { display: block; font-size: 28px; color: #e2e8f0; margin-bottom: 8px; }

    /* Formulario nueva nota */
    .nota-form { border-top: 1px solid var(--border); padding-top: 16px; }
    .nota-textarea {
        width: 100%;
        padding: 12px 14px;
        border: 1.5px solid var(--border);
        border-radius: 10px;
        font-family: 'Sora', sans-serif;
        font-size: 13px;
        color: #1e2238;
        background: #f8faff;
        resize: vertical;
        min-height: 90px;
        outline: none;
        transition: border-color 0.18s;
        margin-bottom: 12px;
    }
    .nota-textarea:focus { border-color: var(--accent); background: #fff; }
    .nota-textarea::placeholder { color: #94a3b8; }

    .btn-nota {
        padding: 10px 22px;
        background: var(--accent);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-family: 'Sora', sans-serif;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: filter 0.18s, transform 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-nota:hover { filter: brightness(0.9); transform: translateY(-1px); }

    /* Toast */
    .toast { position: fixed; bottom: 28px; right: 28px; background: #1e2238; color: #fff; padding: 14px 20px; border-radius: 12px; font-size: 13.5px; font-weight: 500; display: flex; align-items: center; gap: 10px; box-shadow: 0 8px 28px rgba(30,34,60,0.20); z-index: 999; transform: translateY(80px); opacity: 0; transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); }
    .toast.show { transform: translateY(0); opacity: 1; }
    .toast i { color: #22c55e; }

    @media (max-width: 900px) {
        .gestion-grid { grid-template-columns: 1fr; }
        .detalle-info { grid-template-columns: 1fr 1fr; }
    }
</style>

<div class="pagina-titulo">
    <h2><i class="fas fa-clipboard-list"></i> Gestión de Órdenes</h2>
    <p>Selecciona una orden para ver su detalle y agregar notas.</p>
</div>

<div class="gestion-grid">

    <!-- Lista de órdenes -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i> Mis Órdenes
            <span style="margin-left:auto; font-size:12px; color:#94a3b8;"><?= count($ordenes) ?> total</span>
        </div>
        <div class="ordenes-lista">
            <?php if (empty($ordenes)): ?>
                <div style="padding:40px; text-align:center; color:#94a3b8; font-size:13px;">
                    <i class="fas fa-inbox" style="font-size:32px; color:#e2e8f0; display:block; margin-bottom:10px;"></i>
                    Sin órdenes asignadas.
                </div>
            <?php else: ?>
                <?php foreach ($ordenes as $o):
                    $activa = isset($orden_detalle) && $orden_detalle['id_orden'] == $o['id_orden'];
                    $clase_badge = match($o['estado']) {
                        'Pendiente'   => 'badge-pendiente',
                        'En Progreso' => 'badge-proceso',
                        'Terminado'   => 'badge-completado',
                        default       => 'badge-pendiente'
                    };
                ?>
                <a href="Gestion.php?id=<?= $o['id_orden'] ?>" class="orden-item <?= $activa ? 'activa' : '' ?>">
                    <div class="orden-item-icon"><i class="fas fa-car"></i></div>
                    <div class="orden-item-body">
                        <div class="orden-item-vehiculo"><?= htmlspecialchars($o['vehiculo']) ?></div>
                        <div class="orden-item-sub">
                            <span><?= htmlspecialchars($o['cliente']) ?></span>
                            &bull;
                            <span><?= date('d/m/Y', strtotime($o['fecha_creacion'])) ?></span>
                        </div>
                    </div>
                    <div class="orden-item-right">
                        <span class="badge <?= $clase_badge ?>"><?= htmlspecialchars($o['estado']) ?></span>
                        <?php if ($o['total_notas'] > 0): ?>
                        <span class="nota-count"><i class="fas fa-comment"></i> <?= $o['total_notas'] ?></span>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Detalle de la orden seleccionada -->
    <div class="card">
        <?php if (!$orden_detalle): ?>
            <div class="detalle-vacio">
                <i class="fas fa-hand-pointer"></i>
                <h3>Selecciona una orden</h3>
                <p>Haz clic en cualquier orden de la lista para ver su detalle y agregar notas.</p>
            </div>
        <?php else:
            $clase_badge = match($orden_detalle['estado']) {
                'Pendiente'   => 'badge-pendiente',
                'En Progreso' => 'badge-proceso',
                'Terminado'   => 'badge-completado',
                default       => 'badge-pendiente'
            };
        ?>
            <!-- Header -->
            <div class="detalle-header">
                <div>
                    <div class="detalle-vehiculo"><?= htmlspecialchars($orden_detalle['vehiculo']) ?></div>
                    <div class="detalle-meta">
                        <span><i class="fas fa-user"></i> <?= htmlspecialchars($orden_detalle['cliente']) ?></span>
                        <span><i class="fas fa-calendar"></i> <?= date('d/m/Y H:i', strtotime($orden_detalle['fecha_creacion'])) ?></span>
                        <span><i class="fas fa-hashtag"></i> Orden #<?= $orden_detalle['id_orden'] ?></span>
                    </div>
                </div>
                <span class="badge <?= $clase_badge ?>" style="font-size:12px; padding:5px 14px;">
                    <?= htmlspecialchars($orden_detalle['estado']) ?>
                </span>
            </div>

            <!-- Info rápida -->
            <div class="detalle-info">
                <div class="detalle-info-item">
                    <div class="detalle-info-label">Cliente</div>
                    <div class="detalle-info-valor"><?= htmlspecialchars($orden_detalle['cliente']) ?></div>
                </div>
                <div class="detalle-info-item">
                    <div class="detalle-info-label">Servicio</div>
                    <div class="detalle-info-valor"><?= htmlspecialchars($orden_detalle['servicio']) ?></div>
                </div>
                <div class="detalle-info-item">
                    <div class="detalle-info-label">Notas agregadas</div>
                    <div class="detalle-info-valor"><?= count($notas) ?></div>
                </div>
            </div>

            <!-- Notas -->
            <div class="notas-section">
                <div class="notas-titulo"><i class="fas fa-comments"></i> Notas de esta orden</div>

                <div class="notas-lista">
                    <?php if (empty($notas)): ?>
                        <div class="sin-notas">
                            <i class="fas fa-comment-slash"></i>
                            Sin notas aún. Agrega la primera.
                        </div>
                    <?php else: ?>
                        <?php foreach ($notas as $nota): ?>
                        <div class="nota-item">
                            <div class="nota-item-header">
                                <div class="nota-autor">
                                    <i class="fas fa-circle-user"></i>
                                    <?= htmlspecialchars($nota['nombre'] . ' ' . $nota['apellido']) ?>
                                </div>
                                <div class="nota-fecha"><?= date('d/m/Y H:i', strtotime($nota['fecha_nota'])) ?></div>
                            </div>
                            <div class="nota-texto"><?= nl2br(htmlspecialchars($nota['nota'])) ?></div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Formulario nueva nota -->
                <div class="nota-form">
                    <form method="POST">
                        <input type="hidden" name="id_orden" value="<?= $orden_detalle['id_orden'] ?>">
                        <textarea
                            name="nota"
                            class="nota-textarea"
                            placeholder="Escribe una nota sobre esta orden... (ej: se encontró fuga de aceite adicional)"
                            required
                        ></textarea>
                        <button type="submit" class="btn-nota">
                            <i class="fas fa-paper-plane"></i> Agregar nota
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Toast -->
<div class="toast" id="toast">
    <i class="fas fa-check-circle"></i> Nota agregada correctamente
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