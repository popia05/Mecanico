<?php
session_start();
require_once '../../php/db_conexion.php';

$page_title = 'Panel - Auto Master';

// ── Datos del empleado ──
$id_empleado = $_SESSION['id_empleado'];

$stmt = $conexion->prepare("SELECT * FROM empleados WHERE id_empleado = ?");
$stmt->execute([$id_empleado]);
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

$nombre_completo = htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido']);
$iniciales       = strtoupper(substr($empleado['nombre'], 0, 1) . substr($empleado['apellido'], 0, 1));
$puesto          = htmlspecialchars($empleado['puesto'] ?? 'Mecánico');
$correo          = htmlspecialchars($empleado['correo']);
$telefono        = htmlspecialchars($empleado['telefono'] ?? '—');
$foto            = $empleado['foto'] ?? null;

// ── Contadores ──
$stmt = $conexion->prepare("SELECT COUNT(*) FROM ordenes WHERE id_mecanico = ? AND estado = 'En Progreso'");
$stmt->execute([$id_empleado]);
$activas = $stmt->fetchColumn();

$stmt = $conexion->prepare("SELECT COUNT(*) FROM ordenes WHERE id_mecanico = ? AND estado = 'Terminado'");
$stmt->execute([$id_empleado]);
$completadas = $stmt->fetchColumn();

$stmt = $conexion->prepare("SELECT COUNT(*) FROM ordenes WHERE id_mecanico = ? AND estado = 'Pendiente'");
$stmt->execute([$id_empleado]);
$pendientes = $stmt->fetchColumn();

$stmt = $conexion->prepare("SELECT COUNT(*) FROM inventario WHERE id_mecanico = ?");
$stmt->execute([$id_empleado]);
$herramientas = $stmt->fetchColumn();

// ── Actividad reciente ──
$stmt = $conexion->prepare("
    SELECT vehiculo, cliente, servicio, estado, fecha_creacion
    FROM ordenes
    WHERE id_mecanico = ?
    ORDER BY fecha_creacion DESC
    LIMIT 8
");
$stmt->execute([$id_empleado]);
$actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

function badgeInfo($estado) {
    return match($estado) {
        'Pendiente'   => ['clase' => 'badge-pendiente',  'icono' => 'fas fa-car'],
        'En Progreso' => ['clase' => 'badge-proceso',    'icono' => 'fas fa-car-side'],
        'Terminado'   => ['clase' => 'badge-completado', 'icono' => 'fas fa-check-circle'],
        default       => ['clase' => 'badge-pendiente',  'icono' => 'fas fa-car'],
    };
}

include 'header.php';
?>

<style>
    .tarjetas-resumen { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 32px; }
    .tarjeta-resumen { background: var(--card-bg); border-radius: var(--radius); padding: 22px 20px; display: flex; align-items: center; gap: 16px; box-shadow: var(--shadow); border: 1px solid var(--border); transition: transform 0.18s, box-shadow 0.18s; }
    .tarjeta-resumen:hover { transform: translateY(-3px); box-shadow: 0 8px 32px rgba(30,34,60,0.13); }
    .tarjeta-icono { width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
    .tarjeta-icono.azul    { background: #eff6ff; color: #3b82f6; }
    .tarjeta-icono.verde   { background: #f0fdf4; color: #22c55e; }
    .tarjeta-icono.naranja { background: #fffbeb; color: #f59e0b; }
    .tarjeta-icono.rosa    { background: #fdf2f8; color: #e84393; }
    .tarjeta-info h3 { font-size: 26px; font-weight: 700; }
    .tarjeta-info span { font-size: 12.5px; color: #64748b; margin-top: 2px; display: block; }

    .grid-principal { display: grid; grid-template-columns: 1fr 1.8fr; gap: 24px; margin-bottom: 28px; }

    /* Perfil */
    .card-perfil { display: flex; flex-direction: column; align-items: center; padding: 32px 24px 24px; text-align: center; }
    .perfil-avatar { width: 90px; height: 90px; background: var(--accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 36px; font-weight: 700; margin-bottom: 16px; box-shadow: 0 4px 20px rgba(232,67,147,0.30); overflow: hidden; }
    .perfil-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .perfil-nombre { font-size: 17px; font-weight: 700; color: var(--accent); }
    .perfil-rol { font-size: 12.5px; color: #64748b; margin-top: 4px; }
    .perfil-info { width: 100%; margin-top: 24px; display: flex; flex-direction: column; gap: 0; }
    .info-fila { display: flex; align-items: center; justify-content: space-between; font-size: 13px; padding: 12px 0; border-bottom: 1px solid var(--border); }
    .info-fila:last-child { border-bottom: none; }
    .info-label { display: flex; align-items: center; gap: 8px; color: #64748b; font-size: 12.5px; }
    .info-label i { color: #94a3b8; width: 16px; text-align: center; }
    .info-valor { font-weight: 500; font-size: 12.5px; color: #1e2238; text-align: right; }
    .info-valor.link { color: var(--accent2); }

    /* Actividad */
    .actividad-lista { display: flex; flex-direction: column; max-height: 430px; overflow-y: auto; }
    .actividad-lista::-webkit-scrollbar { width: 5px; }
    .actividad-lista::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .actividad-item { display: flex; gap: 14px; padding: 16px 22px; border-bottom: 1px solid var(--border); align-items: flex-start; transition: background 0.15s; }
    .actividad-item:last-child { border-bottom: none; }
    .actividad-item:hover { background: #f8faff; }
    .actividad-icono { width: 36px; height: 36px; border-radius: 10px; background: #eff6ff; display: flex; align-items: center; justify-content: center; color: var(--accent2); font-size: 14px; flex-shrink: 0; margin-top: 2px; }
    .actividad-body { flex: 1; min-width: 0; }
    .actividad-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px; gap: 8px; }
    .actividad-titulo { font-size: 13.5px; font-weight: 600; }
    .actividad-fecha { font-size: 11.5px; color: #94a3b8; margin-top: 2px; }
    .actividad-desc { font-size: 12.5px; color: #64748b; line-height: 1.5; }
    .sin-actividad { padding: 40px; text-align: center; color: #94a3b8; font-size: 13px; }
    .sin-actividad i { font-size: 32px; margin-bottom: 10px; display: block; color: #cbd5e1; }

    /* Accesos rápidos */
    .accesos-rapidos { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .acceso-rapido { background: var(--card-bg); border-radius: var(--radius); padding: 22px 16px; display: flex; flex-direction: column; align-items: center; gap: 10px; text-decoration: none; color: #1e2238; border: 1px solid var(--border); box-shadow: var(--shadow); transition: all 0.18s; font-size: 13px; font-weight: 500; text-align: center; }
    .acceso-rapido i { font-size: 22px; color: var(--accent2); }
    .acceso-rapido:hover { border-color: var(--accent); transform: translateY(-3px); box-shadow: 0 8px 28px rgba(232,67,147,0.13); color: var(--accent); }
    .acceso-rapido:hover i { color: var(--accent); }

    @media (max-width: 1100px) {
        .tarjetas-resumen { grid-template-columns: repeat(2, 1fr); }
        .grid-principal { grid-template-columns: 1fr; }
        .accesos-rapidos { grid-template-columns: repeat(2, 1fr); }
    }
</style>

<!-- Título -->
<div class="pagina-titulo">
    <h2>Bienvenido al Panel</h2>
    <p>Sistema de gestión - Fuel Injection Auto Master</p>
</div>

<!-- Tarjetas resumen -->
<div class="tarjetas-resumen">
    <div class="tarjeta-resumen">
        <div class="tarjeta-icono azul"><i class="fas fa-car"></i></div>
        <div class="tarjeta-info"><h3><?= $activas ?></h3><span>Órdenes Activas</span></div>
    </div>
    <div class="tarjeta-resumen">
        <div class="tarjeta-icono verde"><i class="fas fa-check-circle"></i></div>
        <div class="tarjeta-info"><h3><?= $completadas ?></h3><span>Completadas</span></div>
    </div>
    <div class="tarjeta-resumen">
        <div class="tarjeta-icono naranja"><i class="fas fa-clock"></i></div>
        <div class="tarjeta-info"><h3><?= $pendientes ?></h3><span>Pendientes</span></div>
    </div>
    <div class="tarjeta-resumen">
        <div class="tarjeta-icono rosa"><i class="fas fa-tools"></i></div>
        <div class="tarjeta-info"><h3><?= $herramientas ?></h3><span>Mis Herramientas</span></div>
    </div>
</div>

<!-- Grid perfil + actividad -->
<div class="grid-principal">

    <!-- Perfil -->
    <div class="card">
        <div class="card-perfil">
            <div class="perfil-avatar">
                <?php if ($foto): ?>
                    <img src="../../uploads/<?= htmlspecialchars($foto) ?>" alt="foto">
                <?php else: ?>
                    <?= $iniciales ?>
                <?php endif; ?>
            </div>
            <div class="perfil-nombre"><?= $nombre_completo ?></div>
            <div class="perfil-rol"><?= $puesto ?></div>

            <div class="perfil-info">
                <div class="info-fila">
                    <span class="info-label"><i class="fas fa-circle-dot"></i> Estatus</span>
                    <span class="badge badge-activo">Activo</span>
                </div>
                <div class="info-fila">
                    <span class="info-label"><i class="fas fa-building"></i> Compañía</span>
                    <span class="info-valor link">Auto Master</span>
                </div>
                <div class="info-fila">
                    <span class="info-label"><i class="fas fa-phone"></i> Teléfono</span>
                    <span class="info-valor"><?= $telefono ?></span>
                </div>
                <div class="info-fila">
                    <span class="info-label"><i class="fas fa-envelope"></i> Email</span>
                    <span class="info-valor link"><?= $correo ?></span>
                </div>
                <?php if (!empty($empleado['especialidad'])): ?>
                <div class="info-fila">
                    <span class="info-label"><i class="fas fa-star"></i> Especialidad</span>
                    <span class="info-valor"><?= htmlspecialchars($empleado['especialidad']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($empleado['fecha_ingreso'])): ?>
                <div class="info-fila">
                    <span class="info-label"><i class="fas fa-calendar"></i> Ingreso</span>
                    <span class="info-valor"><?= date('d/m/Y', strtotime($empleado['fecha_ingreso'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Actividad -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-bolt"></i> Actividad
        </div>
        <div class="actividad-lista">
            <?php if (empty($actividades)): ?>
                <div class="sin-actividad">
                    <i class="fas fa-inbox"></i>
                    Sin órdenes asignadas aún.
                </div>
            <?php else: ?>
                <?php foreach ($actividades as $item):
                    $info  = badgeInfo($item['estado']);
                    $fecha = date('H:i - M d, Y', strtotime($item['fecha_creacion']));
                ?>
                <div class="actividad-item">
                    <div class="actividad-icono"><i class="<?= $info['icono'] ?>"></i></div>
                    <div class="actividad-body">
                        <div class="actividad-top">
                            <div>
                                <div class="actividad-titulo"><?= htmlspecialchars($item['vehiculo']) ?></div>
                                <div class="actividad-fecha"><?= $fecha ?> &mdash; <?= htmlspecialchars($item['cliente']) ?></div>
                            </div>
                            <span class="badge <?= $info['clase'] ?>"><?= htmlspecialchars($item['estado']) ?></span>
                        </div>
                        <div class="actividad-desc"><?= htmlspecialchars($item['servicio']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Accesos rápidos -->
<div class="seccion-titulo">Accesos Rápidos</div>
<div class="accesos-rapidos">
    <a href="tareas-asignadas.php" class="acceso-rapido">
        <i class="fas fa-list-check"></i><span>Mis Tareas</span>
    </a>
    <a href="Gestion.php" class="acceso-rapido">
        <i class="fas fa-clipboard-list"></i><span>Ver Órdenes</span>
    </a>
    <a href="Inventario.php" class="acceso-rapido">
        <i class="fas fa-boxes"></i><span>Ver Inventario</span>
    </a>
    <a href="Nota-remision.php" class="acceso-rapido">
        <i class="fas fa-file-alt"></i><span>Crear Nota</span>
    </a>
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
</script>
</body>
</html>