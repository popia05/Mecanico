<?php
session_start();
require_once '../../php/db_conexion.php';

$page_title  = 'Inventario de Herramientas - Auto Master';
$id_empleado = $_SESSION['id_empleado'];

// ── Tomar herramienta ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'], $_POST['id_producto'])) {
    $id_producto = (int) $_POST['id_producto'];
    $accion      = $_POST['accion'];

    if ($accion === 'tomar') {
        // Verificar que esté disponible y tenga stock
        $check = $conexion->prepare("SELECT id_producto, cantidad_stock FROM inventario WHERE id_producto = ? AND estado = 'Disponible' AND cantidad_stock > 0");
        $check->execute([$id_producto]);
        $h = $check->fetch(PDO::FETCH_ASSOC);
        if ($h) {
            $nuevo_stock  = (int)$h['cantidad_stock'] - 1;
            $nuevo_estado = $nuevo_stock <= 0 ? 'En Uso' : 'Disponible';
            $stmt = $conexion->prepare("UPDATE inventario SET cantidad_stock = ?, estado = ?, tomada_por = ? WHERE id_producto = ?");
            $stmt->execute([$nuevo_stock, $nuevo_estado, $id_empleado, $id_producto]);
            header('Location: Inventario.php?msg=tomada');
            exit();
        }
    }

    if ($accion === 'regresar') {
        // Solo puede regresar el empleado que la tomó
        $check = $conexion->prepare("SELECT id_producto, cantidad_stock FROM inventario WHERE id_producto = ? AND tomada_por = ?");
        $check->execute([$id_producto, $id_empleado]);
        $h = $check->fetch(PDO::FETCH_ASSOC);
        if ($h) {
            $nuevo_stock = (int)$h['cantidad_stock'] + 1;
            $stmt = $conexion->prepare("UPDATE inventario SET cantidad_stock = ?, estado = 'Disponible', tomada_por = NULL WHERE id_producto = ?");
            $stmt->execute([$nuevo_stock, $id_producto]);
            header('Location: Inventario.php?msg=regresada');
            exit();
        }
    }

    header('Location: Inventario.php');
    exit();
}

// ── Filtros ──
$filtro_estado = $_GET['estado'] ?? 'todos';
$busqueda      = trim($_GET['buscar'] ?? '');
$estados_validos = ['Disponible', 'En Uso', 'Mantenimiento'];

$where  = [];
$params = [];

if (in_array($filtro_estado, $estados_validos)) {
    $where[]  = "i.estado = ?";
    $params[] = $filtro_estado;
}
if (!empty($busqueda)) {
    $where[]  = "(i.nombre_producto LIKE ? OR i.ubicacion LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

$sql = "SELECT i.*, e.nombre as empleado_nombre, e.apellido as empleado_apellido
        FROM inventario i
        LEFT JOIN empleados e ON e.id_empleado = i.tomada_por";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY i.nombre_producto ASC";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$herramientas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Contadores ──
$stmt = $conexion->prepare("SELECT estado, COUNT(*) as total FROM inventario GROUP BY estado");
$stmt->execute();
$conteos = ['todos' => 0, 'Disponible' => 0, 'En Uso' => 0, 'Mantenimiento' => 0];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $conteos[$row['estado']] = (int)$row['total'];
    $conteos['todos'] += (int)$row['total'];
}

// ── Mis herramientas en uso ──
$stmt = $conexion->prepare("SELECT * FROM inventario WHERE tomada_por = ?");
$stmt->execute([$id_empleado]);
$mis_herramientas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Ubicaciones para el mapa ──
$stmt = $conexion->prepare("SELECT DISTINCT ubicacion FROM inventario WHERE ubicacion IS NOT NULL AND ubicacion != '' ORDER BY ubicacion");
$stmt->execute();
$ubicaciones = $stmt->fetchAll(PDO::FETCH_COLUMN);

include 'header.php';
?>

<style>
    /* Toolbar */
    .toolbar { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
    .search-wrapper { position: relative; flex: 1; min-width: 220px; max-width: 340px; }
    .search-icon { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px; pointer-events: none; }
    .search-input { width: 100%; padding: 10px 14px 10px 38px; border: 1.5px solid var(--border); border-radius: 10px; font-family: 'Sora', sans-serif; font-size: 13px; color: #1e2238; background: var(--card-bg); outline: none; transition: border-color 0.18s; box-shadow: var(--shadow); }
    .search-input:focus { border-color: var(--accent); }

    .filtros { display: flex; gap: 8px; flex-wrap: wrap; }
    .filtro-btn { display: flex; align-items: center; gap: 7px; padding: 9px 16px; border-radius: 10px; border: 1.5px solid var(--border); background: var(--card-bg); font-family: 'Sora', sans-serif; font-size: 13px; font-weight: 500; color: #64748b; cursor: pointer; text-decoration: none; transition: all 0.18s; box-shadow: var(--shadow); white-space: nowrap; }
    .filtro-btn:hover { border-color: var(--accent); color: var(--accent); }
    .filtro-btn.activo { background: var(--accent); border-color: var(--accent); color: #fff; }
    .filtro-btn.disponible.activo  { background: #22c55e; border-color: #22c55e; }
    .filtro-btn.en-uso.activo      { background: #3b82f6; border-color: #3b82f6; }
    .filtro-btn.mantenimiento.activo { background: #f59e0b; border-color: #f59e0b; }
    .filtro-btn .count { font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 20px; background: rgba(0,0,0,0.10); min-width: 20px; text-align: center; }
    .filtro-btn:not(.activo) .count { background: #f1f5f9; color: #64748b; }

    /* Mis herramientas banner */
    .mis-herramientas-banner {
        background: linear-gradient(135deg, #1e2238, #2d3158);
        border-radius: var(--radius);
        padding: 18px 24px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        border: 1px solid #3d4270;
    }
    .mis-herramientas-banner-icon { font-size: 22px; color: var(--accent); flex-shrink: 0; }
    .mis-herramientas-banner-text h4 { color: #fff; font-size: 14px; font-weight: 600; margin-bottom: 2px; }
    .mis-herramientas-banner-text p  { color: #8b92a9; font-size: 12.5px; }
    .mis-herramientas-chips { display: flex; gap: 8px; flex-wrap: wrap; margin-left: auto; }
    .chip {
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 20px;
        padding: 5px 14px;
        font-size: 12.5px;
        color: #fff;
        font-weight: 500;
        display: flex; align-items: center; gap: 6px;
    }
    .chip i { color: var(--accent); }

    /* Grid herramientas */
    .herramientas-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap: 18px; margin-bottom: 32px; }

    .herramienta-card { background: var(--card-bg); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow); overflow: hidden; transition: transform 0.18s, box-shadow 0.18s; }
    .herramienta-card:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(30,34,60,0.13); }
    .herramienta-card.es-mia { border-color: var(--accent); box-shadow: 0 4px 20px rgba(229,57,53,0.15); }

    .herramienta-top { padding: 18px 18px 14px; display: flex; align-items: flex-start; gap: 13px; }
    .herramienta-icono { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 19px; flex-shrink: 0; }
    .icono-disponible    { background: #f0fdf4; color: #22c55e; }
    .icono-en-uso        { background: #eff6ff; color: #3b82f6; }
    .icono-mantenimiento { background: #fffbeb; color: #f59e0b; }
    .icono-mia           { background: #fdf2f8; color: var(--accent); }

    .herramienta-nombre { font-size: 14px; font-weight: 700; color: #1e2238; margin-bottom: 4px; }
    .herramienta-stock  { font-size: 12px; color: #64748b; display: flex; align-items: center; gap: 5px; }

    .herramienta-middle { padding: 0 18px 12px; display: flex; flex-direction: column; gap: 6px; }
    .ubicacion-row { display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: #64748b; }
    .ubicacion-row i { color: #94a3b8; width: 14px; text-align: center; }
    .ubicacion-valor { font-weight: 600; color: #1e2238; }

    .en-uso-por { font-size: 12px; color: #3b82f6; display: flex; align-items: center; gap: 5px; background: #eff6ff; padding: 5px 10px; border-radius: 8px; margin-top: 4px; }

    .herramienta-bottom { padding: 12px 18px 16px; border-top: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 10px; }

    .badge-estado { display: inline-flex; align-items: center; gap: 5px; font-size: 11.5px; font-weight: 600; padding: 4px 12px; border-radius: 20px; }
    .badge-disponible    { background: #f0fdf4; color: #16a34a; }
    .badge-en-uso        { background: #eff6ff; color: #3b82f6; }
    .badge-mantenimiento { background: #fffbeb; color: #d97706; }

    /* Botones */
    .btn-tomar {
        padding: 8px 16px; background: var(--accent); color: #fff;
        border: none; border-radius: 8px; font-family: 'Sora', sans-serif;
        font-size: 12px; font-weight: 600; cursor: pointer;
        display: flex; align-items: center; gap: 6px;
        transition: filter 0.18s, transform 0.15s;
        white-space: nowrap;
    }
    .btn-tomar:hover { filter: brightness(0.9); transform: translateY(-1px); }

    .btn-regresar {
        padding: 8px 16px; background: #22c55e; color: #fff;
        border: none; border-radius: 8px; font-family: 'Sora', sans-serif;
        font-size: 12px; font-weight: 600; cursor: pointer;
        display: flex; align-items: center; gap: 6px;
        transition: filter 0.18s, transform 0.15s;
        white-space: nowrap;
    }
    .btn-regresar:hover { filter: brightness(0.9); transform: translateY(-1px); }

    .btn-deshabilitado {
        padding: 8px 16px; background: #f1f5f9; color: #94a3b8;
        border: 1px solid var(--border); border-radius: 8px;
        font-family: 'Sora', sans-serif; font-size: 12px; font-weight: 600;
        cursor: not-allowed; display: flex; align-items: center; gap: 6px;
        white-space: nowrap;
    }

    /* Mapa ubicaciones */
    .ubicaciones-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 14px; }
    .ubicacion-card { background: var(--card-bg); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow); padding: 18px 20px; transition: transform 0.18s; }
    .ubicacion-card:hover { transform: translateY(-2px); }
    .ubicacion-card-header { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
    .ubicacion-card-icon { width: 36px; height: 36px; background: #eff6ff; color: var(--accent2); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
    .ubicacion-card-nombre { font-size: 13px; font-weight: 700; color: #1e2238; }
    .ubicacion-herramientas { display: flex; flex-direction: column; gap: 6px; }
    .ubicacion-herramienta-item { display: flex; align-items: center; justify-content: space-between; font-size: 12.5px; padding: 6px 10px; background: #f8faff; border-radius: 8px; border: 1px solid var(--border); }
    .ubicacion-herramienta-nombre { color: #1e2238; font-weight: 500; }
    .dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .dot-disponible    { background: #22c55e; }
    .dot-en-uso        { background: #3b82f6; }
    .dot-mantenimiento { background: #f59e0b; }

    /* Sin resultados */
    .sin-resultados { grid-column: 1/-1; padding: 60px 20px; text-align: center; color: #94a3b8; }
    .sin-resultados i { font-size: 48px; color: #e2e8f0; margin-bottom: 14px; display: block; }

    /* Toast */
    .toast { position: fixed; bottom: 28px; right: 28px; color: #fff; padding: 14px 20px; border-radius: 12px; font-size: 13.5px; font-weight: 500; display: flex; align-items: center; gap: 10px; box-shadow: 0 8px 28px rgba(30,34,60,0.20); z-index: 999; transform: translateY(80px); opacity: 0; transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); }
    .toast.show { transform: translateY(0); opacity: 1; }
    .toast.tomada   { background: #3b82f6; }
    .toast.regresada { background: #22c55e; }
</style>

<div class="pagina-titulo">
    <h2><i class="fas fa-toolbox"></i> Inventario de Herramientas</h2>
    <p>Consulta disponibilidad, toma y regresa herramientas del taller.</p>
</div>

<!-- Banner mis herramientas en uso -->
<?php if (!empty($mis_herramientas)): ?>
<div class="mis-herramientas-banner">
    <div class="mis-herramientas-banner-icon"><i class="fas fa-hand-holding-hand"></i></div>
    <div class="mis-herramientas-banner-text">
        <h4>Tienes <?= count($mis_herramientas) ?> herramienta<?= count($mis_herramientas) > 1 ? 's' : '' ?> en uso</h4>
        <p>Recuerda regresar las herramientas cuando termines.</p>
    </div>
    <div class="mis-herramientas-chips">
        <?php foreach ($mis_herramientas as $mh): ?>
        <div class="chip"><i class="fas fa-wrench"></i><?= htmlspecialchars($mh['nombre_producto']) ?></div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Búsqueda + filtros -->
<form method="GET" action="Inventario.php" id="form-filtro">
    <div class="toolbar">
        <div class="search-wrapper">
            <i class="fas fa-search search-icon"></i>
            <input type="text" name="buscar" class="search-input"
                placeholder="Buscar herramienta o ubicación..."
                value="<?= htmlspecialchars($busqueda) ?>"
                onchange="document.getElementById('form-filtro').submit()">
        </div>
        <input type="hidden" name="estado" value="<?= htmlspecialchars($filtro_estado) ?>">
        <div class="filtros">
            <a href="Inventario.php?buscar=<?= urlencode($busqueda) ?>&estado=todos"
               class="filtro-btn <?= $filtro_estado === 'todos' ? 'activo' : '' ?>">
                <i class="fas fa-list"></i> Todas <span class="count"><?= $conteos['todos'] ?></span>
            </a>
            <a href="Inventario.php?buscar=<?= urlencode($busqueda) ?>&estado=Disponible"
               class="filtro-btn disponible <?= $filtro_estado === 'Disponible' ? 'activo' : '' ?>">
                <i class="fas fa-circle-check"></i> Disponibles <span class="count"><?= $conteos['Disponible'] ?></span>
            </a>
            <a href="Inventario.php?buscar=<?= urlencode($busqueda) ?>&estado=En Uso"
               class="filtro-btn en-uso <?= $filtro_estado === 'En Uso' ? 'activo' : '' ?>">
                <i class="fas fa-screwdriver-wrench"></i> En Uso <span class="count"><?= $conteos['En Uso'] ?></span>
            </a>
            <a href="Inventario.php?buscar=<?= urlencode($busqueda) ?>&estado=Mantenimiento"
               class="filtro-btn mantenimiento <?= $filtro_estado === 'Mantenimiento' ? 'activo' : '' ?>">
                <i class="fas fa-triangle-exclamation"></i> Mantenimiento <span class="count"><?= $conteos['Mantenimiento'] ?></span>
            </a>
        </div>
    </div>
</form>

<!-- Grid herramientas -->
<div class="seccion-titulo">
    <i class="fas fa-wrench" style="color:var(--accent2);"></i> Herramientas
    <span style="font-size:12.5px; color:#94a3b8; font-weight:400;"><?= count($herramientas) ?> resultado(s)</span>
</div>

<div class="herramientas-grid">
    <?php if (empty($herramientas)): ?>
        <div class="sin-resultados">
            <i class="fas fa-magnifying-glass"></i>
            <h3>Sin resultados</h3>
            <p>No se encontraron herramientas con ese filtro.</p>
        </div>
    <?php else: ?>
        <?php foreach ($herramientas as $h):
            $es_mia      = (int)$h['tomada_por'] === (int)$id_empleado;
            $en_uso_otro = $h['estado'] === 'En Uso' && !$es_mia;

            $clase_icono = $es_mia ? 'icono-mia' : match($h['estado']) {
                'Disponible'    => 'icono-disponible',
                'En Uso'        => 'icono-en-uso',
                'Mantenimiento' => 'icono-mantenimiento',
                default         => 'icono-disponible'
            };
            $clase_badge = match($h['estado']) {
                'Disponible'    => 'badge-disponible',
                'En Uso'        => 'badge-en-uso',
                'Mantenimiento' => 'badge-mantenimiento',
                default         => 'badge-disponible'
            };
            $icono_estado = match($h['estado']) {
                'Disponible'    => 'fa-circle-check',
                'En Uso'        => 'fa-screwdriver-wrench',
                'Mantenimiento' => 'fa-triangle-exclamation',
                default         => 'fa-wrench'
            };

            // Parsear estante y caja
            $ubi = $h['ubicacion'] ?? '—';
            preg_match('/estante\s*([^\s,]+)/i', $ubi, $me);
            preg_match('/caj[oaó]+\s*([^\s,]+)/i', $ubi, $mc);
            $estante = !empty($me[1]) ? strtoupper($me[1]) : null;
            $caja    = !empty($mc[1]) ? strtoupper($mc[1]) : null;
        ?>
        <div class="herramienta-card <?= $es_mia ? 'es-mia' : '' ?>">
            <div class="herramienta-top">
                <div class="herramienta-icono <?= $clase_icono ?>">
                    <i class="fas fa-wrench"></i>
                </div>
                <div>
                    <div class="herramienta-nombre"><?= htmlspecialchars($h['nombre_producto']) ?></div>
                    <div class="herramienta-stock">
                        <i class="fas fa-layer-group"></i>
                        <?= (int)$h['cantidad_stock'] ?> unidad<?= $h['cantidad_stock'] != 1 ? 'es' : '' ?>
                    </div>
                </div>
            </div>

            <div class="herramienta-middle">
                <?php if ($estante): ?>
                <div class="ubicacion-row">
                    <i class="fas fa-cabinet-filing"></i> Estante
                    <span class="ubicacion-valor"><?= htmlspecialchars($estante) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($caja): ?>
                <div class="ubicacion-row">
                    <i class="fas fa-box"></i> Caja
                    <span class="ubicacion-valor"><?= htmlspecialchars($caja) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!$estante && !$caja && $ubi !== '—'): ?>
                <div class="ubicacion-row">
                    <i class="fas fa-location-dot"></i> Ubicación
                    <span class="ubicacion-valor"><?= htmlspecialchars($ubi) ?></span>
                </div>
                <?php endif; ?>

                <?php if ($en_uso_otro && $h['empleado_nombre']): ?>
                <div class="en-uso-por">
                    <i class="fas fa-user"></i>
                    En uso por: <strong><?= htmlspecialchars($h['empleado_nombre'] . ' ' . $h['empleado_apellido']) ?></strong>
                </div>
                <?php endif; ?>

                <?php if ($es_mia): ?>
                <div class="en-uso-por" style="background:#fdf2f8; color:var(--accent);">
                    <i class="fas fa-hand"></i> Tú tienes esta herramienta
                </div>
                <?php endif; ?>
            </div>

            <div class="herramienta-bottom">
                <span class="badge-estado <?= $clase_badge ?>">
                    <i class="fas <?= $icono_estado ?>"></i>
                    <?= htmlspecialchars($h['estado']) ?>
                </span>

                <?php if ($es_mia): ?>
                    <!-- Botón regresar -->
                    <form method="POST">
                        <input type="hidden" name="id_producto" value="<?= $h['id_producto'] ?>">
                        <input type="hidden" name="accion" value="regresar">
                        <button type="submit" class="btn-regresar">
                            <i class="fas fa-rotate-left"></i> Regresar
                        </button>
                    </form>

                <?php elseif ($h['estado'] === 'Disponible'): ?>
                    <!-- Botón tomar -->
                    <form method="POST">
                        <input type="hidden" name="id_producto" value="<?= $h['id_producto'] ?>">
                        <input type="hidden" name="accion" value="tomar">
                        <button type="submit" class="btn-tomar">
                            <i class="fas fa-hand"></i> Tomar
                        </button>
                    </form>

                <?php else: ?>
                    <!-- No disponible -->
                    <span class="btn-deshabilitado">
                        <i class="fas fa-lock"></i> No disponible
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Mapa de ubicaciones -->
<?php if (!empty($ubicaciones)): ?>
<div class="seccion-titulo" style="margin-top:8px;">
    <i class="fas fa-map-location-dot" style="color:var(--accent2);"></i> Mapa de Ubicaciones
</div>
<div class="ubicaciones-grid">
    <?php foreach ($ubicaciones as $ubi):
        $stmt = $conexion->prepare("SELECT nombre_producto, cantidad_stock, estado FROM inventario WHERE ubicacion = ? ORDER BY nombre_producto");
        $stmt->execute([$ubi]);
        $items_ubi = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="ubicacion-card">
        <div class="ubicacion-card-header">
            <div class="ubicacion-card-icon"><i class="fas fa-location-dot"></i></div>
            <div class="ubicacion-card-nombre"><?= htmlspecialchars($ubi) ?></div>
        </div>
        <div class="ubicacion-herramientas">
            <?php foreach ($items_ubi as $item):
                $dot = match($item['estado']) {
                    'Disponible'    => 'dot-disponible',
                    'En Uso'        => 'dot-en-uso',
                    'Mantenimiento' => 'dot-mantenimiento',
                    default         => 'dot-disponible'
                };
            ?>
            <div class="ubicacion-herramienta-item">
                <span class="ubicacion-herramienta-nombre"><?= htmlspecialchars($item['nombre_producto']) ?></span>
                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-size:11.5px; color:#94a3b8;"><?= $item['cantidad_stock'] ?> uds.</span>
                    <span class="dot <?= $dot ?>"></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Toast -->
<div class="toast" id="toast"></div>

    </div><!-- /pagina -->
</main><!-- /contenido -->

<script>
function toggleSubmenu(id) {
    const submenu = document.getElementById('submenu-' + id);
    const toggle  = submenu.previousElementSibling;
    submenu.classList.toggle('open');
    toggle.classList.toggle('open');
}

<?php if (isset($_GET['msg'])): ?>
window.addEventListener('DOMContentLoaded', () => {
    const toast = document.getElementById('toast');
    <?php if ($_GET['msg'] === 'tomada'): ?>
        toast.innerHTML = '<i class="fas fa-hand"></i> Herramienta tomada correctamente';
        toast.classList.add('tomada');
    <?php elseif ($_GET['msg'] === 'regresada'): ?>
        toast.innerHTML = '<i class="fas fa-rotate-left"></i> Herramienta regresada correctamente';
        toast.classList.add('regresada');
    <?php endif; ?>
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3500);
});
<?php endif; ?>
</script>
</body>
</html>