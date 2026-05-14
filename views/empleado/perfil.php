<?php
session_start();
require_once '../../php/db_conexion.php';

$page_title = 'Mi Perfil - Auto Master';

$id_empleado = $_SESSION['id_empleado'];

$stmt = $conexion->prepare("SELECT * FROM empleados WHERE id_empleado = ?");
$stmt->execute([$id_empleado]);
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

$nombre_completo = htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido']);
$iniciales       = strtoupper(substr($empleado['nombre'], 0, 1) . substr($empleado['apellido'], 0, 1));
$foto            = $empleado['foto'] ?? null;

// Contar órdenes del empleado
$stmt = $conexion->prepare("SELECT estado, COUNT(*) as total FROM ordenes WHERE id_mecanico = ? GROUP BY estado");
$stmt->execute([$id_empleado]);
$conteos = ['Pendiente' => 0, 'En Progreso' => 0, 'Terminado' => 0];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $conteos[$row['estado']] = $row['total'];
}

include 'header.php';
?>

<style>
    .perfil-grid {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 24px;
        align-items: start;
    }

    /* ── Card avatar ── */
    .card-avatar {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 40px 28px 32px;
        text-align: center;
    }
    .avatar-circle {
        width: 110px; height: 110px;
        background: var(--accent);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 42px; font-weight: 700;
        margin-bottom: 20px;
        box-shadow: 0 6px 28px rgba(229,57,53,0.30);
        overflow: hidden;
        flex-shrink: 0;
    }
    .avatar-circle img { width: 100%; height: 100%; object-fit: cover; }

    .avatar-nombre { font-size: 18px; font-weight: 700; color: #1e2238; margin-bottom: 6px; }
    .avatar-puesto {
        font-size: 12.5px;
        background: #fef2f2;
        color: var(--accent);
        padding: 4px 14px;
        border-radius: 20px;
        font-weight: 600;
        margin-bottom: 24px;
    }

    /* Stats rápidos */
    .avatar-stats {
        width: 100%;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-top: 4px;
    }
    .stat-item {
        background: #f8faff;
        border-radius: 12px;
        padding: 14px 8px;
        text-align: center;
        border: 1px solid var(--border);
    }
    .stat-num { font-size: 22px; font-weight: 700; color: #1e2238; }
    .stat-label { font-size: 11px; color: #64748b; margin-top: 3px; }
    .stat-item.azul .stat-num    { color: #3b82f6; }
    .stat-item.naranja .stat-num { color: #f59e0b; }
    .stat-item.verde .stat-num   { color: #22c55e; }

    /* ── Card info ── */
    .card-info { padding: 0; }
    .card-info .card-header { margin-bottom: 0; }

    .info-lista { padding: 8px 0; }

    .info-row {
        display: flex;
        align-items: center;
        padding: 18px 24px;
        border-bottom: 1px solid var(--border);
        gap: 16px;
        transition: background 0.15s;
    }
    .info-row:last-child { border-bottom: none; }
    .info-row:hover { background: #f8faff; }

    .info-row-icon {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .icon-azul   { background: #eff6ff; color: #3b82f6; }
    .icon-rosa   { background: #fdf2f8; color: var(--accent); }
    .icon-verde  { background: #f0fdf4; color: #22c55e; }
    .icon-naranja{ background: #fffbeb; color: #f59e0b; }
    .icon-morado { background: #f5f3ff; color: #7c3aed; }
    .icon-gris   { background: #f1f5f9; color: #64748b; }

    .info-row-content { flex: 1; }
    .info-row-label { font-size: 11.5px; color: #94a3b8; font-weight: 500; margin-bottom: 3px; }
    .info-row-valor { font-size: 14px; font-weight: 600; color: #1e2238; }
    .info-row-valor.link { color: #4f8ef7; }
    .info-row-valor.activo { color: #22c55e; }

    .badge-activo-lg {
        background: #dcfce7;
        color: #16a34a;
        font-size: 12px;
        font-weight: 600;
        padding: 5px 14px;
        border-radius: 20px;
    }

    @media (max-width: 900px) {
        .perfil-grid { grid-template-columns: 1fr; }
    }
</style>

<!-- Título -->
<div class="pagina-titulo">
    <h2><i class="fas fa-user-circle"></i> Mi Perfil</h2>
    <p>Información personal de tu cuenta</p>
</div>

<div class="perfil-grid">

    <!-- Columna izquierda: avatar + stats -->
    <div class="card">
        <div class="card-avatar">
            <div class="avatar-circle">
                <?php if ($foto): ?>
                    <img src="../../uploads/<?= htmlspecialchars($foto) ?>" alt="foto">
                <?php else: ?>
                    <?= $iniciales ?>
                <?php endif; ?>
            </div>
            <div class="avatar-nombre"><?= $nombre_completo ?></div>
            <div class="avatar-puesto"><?= htmlspecialchars($empleado['puesto'] ?? 'Mecánico') ?></div>

            <div class="avatar-stats">
                <div class="stat-item azul">
                    <div class="stat-num"><?= $conteos['En Progreso'] ?></div>
                    <div class="stat-label">En Progreso</div>
                </div>
                <div class="stat-item naranja">
                    <div class="stat-num"><?= $conteos['Pendiente'] ?></div>
                    <div class="stat-label">Pendientes</div>
                </div>
                <div class="stat-item verde">
                    <div class="stat-num"><?= $conteos['Terminado'] ?></div>
                    <div class="stat-label">Terminadas</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Columna derecha: info detallada -->
    <div class="card card-info">
        <div class="card-header">
            <i class="fas fa-id-card"></i> Información General
        </div>
        <div class="info-lista">

            <div class="info-row">
                <div class="info-row-icon icon-azul"><i class="fas fa-user"></i></div>
                <div class="info-row-content">
                    <div class="info-row-label">Nombre completo</div>
                    <div class="info-row-valor"><?= $nombre_completo ?></div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-row-icon icon-rosa"><i class="fas fa-envelope"></i></div>
                <div class="info-row-content">
                    <div class="info-row-label">Correo electrónico</div>
                    <div class="info-row-valor link"><?= htmlspecialchars($empleado['correo']) ?></div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-row-icon icon-verde"><i class="fas fa-phone"></i></div>
                <div class="info-row-content">
                    <div class="info-row-label">Teléfono</div>
                    <div class="info-row-valor"><?= htmlspecialchars($empleado['telefono'] ?? '—') ?></div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-row-icon icon-naranja"><i class="fas fa-briefcase"></i></div>
                <div class="info-row-content">
                    <div class="info-row-label">Puesto</div>
                    <div class="info-row-valor"><?= htmlspecialchars($empleado['puesto'] ?? '—') ?></div>
                </div>
            </div>

            <?php if (!empty($empleado['especialidad'])): ?>
            <div class="info-row">
                <div class="info-row-icon icon-morado"><i class="fas fa-star"></i></div>
                <div class="info-row-content">
                    <div class="info-row-label">Especialidad</div>
                    <div class="info-row-valor"><?= htmlspecialchars($empleado['especialidad']) ?></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($empleado['fecha_ingreso'])): ?>
            <div class="info-row">
                <div class="info-row-icon icon-gris"><i class="fas fa-calendar-check"></i></div>
                <div class="info-row-content">
                    <div class="info-row-label">Fecha de ingreso</div>
                    <div class="info-row-valor"><?= date('d / m / Y', strtotime($empleado['fecha_ingreso'])) ?></div>
                </div>
            </div>
            <?php endif; ?>

            <div class="info-row">
                <div class="info-row-icon icon-verde"><i class="fas fa-circle-check"></i></div>
                <div class="info-row-content">
                    <div class="info-row-label">Estatus</div>
                    <div class="info-row-valor">
                        <span class="badge-activo-lg">Activo</span>
                    </div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-row-icon icon-azul"><i class="fas fa-building"></i></div>
                <div class="info-row-content">
                    <div class="info-row-label">Compañía</div>
                    <div class="info-row-valor link">Fuel Injection Auto Master</div>
                </div>
            </div>

        </div>
    </div>

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