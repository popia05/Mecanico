<?php
session_start();
require_once '../../php/db_conexion.php';

// ── Notificación persistente entre redirecciones ──────
$notificacion = null;
if (isset($_SESSION['notif'])) {
    $notificacion = $_SESSION['notif'];
    unset($_SESSION['notif']);
}

// ── Historial (en producción vendría de la BD) ────────
$historial = [
    ["id"=>1,"nombre"=>"Respaldo completo del sistema","fecha"=>"Mar 05, 2026 - 2:00","tipo"=>"Automatico","tipo_clase"=>"tipo-auto",   "estado"=>"Completado","estado_clase"=>"estado-ok"],
    ["id"=>2,"nombre"=>"Respaldo de inventario",       "fecha"=>"Mar 02, 2026 - 14:00","tipo"=>"Completo", "tipo_clase"=>"tipo-completo","estado"=>"Completado","estado_clase"=>"estado-ok"],
    ["id"=>3,"nombre"=>"Respaldo de ordenes de trabajo","fecha"=>"Mar 01, 2026 - 2:00","tipo"=>"Parcial",  "tipo_clase"=>"tipo-parcial", "estado"=>"Fallido",   "estado_clase"=>"estado-fail"],
];

$ultimo_respaldo = "Mar 05, 2026 Ultimo respaldo";
$toggles = $_SESSION['toggles'] ?? ['diario'=>false,'semanal'=>true,'notificar'=>true];

// ══════════════════════════════════════════════════════
//  POST handlers
// ══════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['accion']) && $_POST['accion'] === 'crear_respaldo') {
        $tipo = in_array($_POST['tipo_respaldo'] ?? '', ['Completo','Parcial','Configuración'])
            ? $_POST['tipo_respaldo'] : 'Completo';
        $exito = ($tipo !== 'Parcial');
        if ($exito) {
            $_SESSION['notif'] = ['tipo'=>'exito','msg'=>'Respaldo completado exitosamente'];
        } else {
            $_SESSION['notif'] = ['tipo'=>'error','msg'=>'Respaldo fallido, intenta nuevamente'];
        }
        header('Location: respaldo.php');
        exit;
    }

    if (isset($_POST['accion']) && $_POST['accion'] === 'guardar_toggles') {
        $_SESSION['toggles'] = [
            'diario'    => isset($_POST['diario']),
            'semanal'   => isset($_POST['semanal']),
            'notificar' => isset($_POST['notificar']),
        ];
        $_SESSION['notif'] = ['tipo'=>'exito','msg'=>'Configuración de respaldos actualizada'];
        header('Location: respaldo.php');
        exit;
    }

    if (isset($_POST['accion']) && $_POST['accion'] === 'restaurar') {
        $_SESSION['notif'] = ['tipo'=>'exito','msg'=>'Restauración completada'];
        header('Location: respaldo.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respaldo del Sistema - Auto Master</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/estilos-generales.css">
    <style>
        /* ── Variables ── */
        :root {
            --accent: #e05a6e;
            --accent-hover: #c94d60;
            --radius: 14px;
            --shadow: 0 2px 12px rgba(0,0,0,0.07);
            --border: #e5e7eb;
        }

        /* ── Layout contenido ── */
        .pagina { padding: 28px 32px; max-width: 860px; }
        .pagina-titulo h2 { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
        .pagina-titulo p  { font-size: 13.5px; color: #64748b; display: flex; align-items: center; gap: 6px; }

        /* ── Último respaldo ── */
        .last-backup {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            background: #f0fdfa;
            border: 1.5px solid #99f6e4;
            border-radius: var(--radius);
            font-size: 13.5px;
            font-weight: 500;
            color: #0f766e;
            margin-bottom: 20px;
        }
        .last-backup i { font-size: 15px; }

        /* ── Cards ── */
        .card {
            background: #fff;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            padding: 22px 24px;
            margin-bottom: 18px;
        }
        .card-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e2238;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-title i { color: var(--accent); }
        .card-desc { font-size: 13px; color: #64748b; margin-bottom: 18px; }

        /* ── Tipo de respaldo ── */
        .tipo-label { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 10px; }
        .tipo-btns  { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 18px; }

        .btn-tipo {
            padding: 9px 20px;
            border-radius: 10px;
            border: 1.5px solid var(--border);
            background: #f8faff;
            font-family: inherit;
            font-size: 13px;
            font-weight: 500;
            color: #64748b;
            cursor: pointer;
            transition: all 0.18s;
            display: flex; align-items: center; gap: 6px;
        }
        .btn-tipo:hover { border-color: var(--accent); color: var(--accent); }
        .btn-tipo.activo {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }

        /* ── Botón crear respaldo ── */
        .btn-crear {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 11px 24px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.18s, transform 0.15s;
            box-shadow: 0 4px 14px rgba(224,90,110,0.28);
        }
        .btn-crear:hover { background: var(--accent-hover); transform: translateY(-1px); }
        .btn-crear.loading { opacity: 0.8; pointer-events: none; }
        .spin { display: inline-block; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Toggle switches ── */
        .toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 13px 0;
            border-bottom: 1px solid var(--border);
            font-size: 13.5px;
            color: #374151;
        }
        .toggle-row:last-of-type { border-bottom: none; }

        .toggle { position: relative; display: inline-block; width: 44px; height: 24px; flex-shrink: 0; }
        .toggle input { opacity: 0; width: 0; height: 0; }
        .toggle-slider {
            position: absolute; inset: 0;
            background: #cbd5e1;
            border-radius: 999px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .toggle-slider::before {
            content: '';
            position: absolute;
            width: 18px; height: 18px;
            left: 3px; top: 3px;
            background: #fff;
            border-radius: 50%;
            transition: transform 0.2s;
            box-shadow: 0 1px 4px rgba(0,0,0,0.18);
        }
        .toggle input:checked + .toggle-slider { background: var(--accent); }
        .toggle input:checked + .toggle-slider::before { transform: translateX(20px); }

        .btn-guardar-toggles {
            margin-top: 16px;
            padding: 10px 22px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex; align-items: center; gap: 8px;
            transition: background 0.18s;
        }
        .btn-guardar-toggles:hover { background: var(--accent-hover); }

        /* ── Warning restaurar ── */
        .warning-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 16px;
            background: #fffbeb;
            border: 1.5px solid #fde047;
            border-radius: 10px;
            font-size: 13px;
            color: #854d0e;
            margin-bottom: 14px;
        }
        .warning-box i { flex-shrink: 0; font-size: 15px; color: #d97706; }

        .restaurar-panel { display: none; flex-direction: column; gap: 12px; margin-top: 4px; }
        .restaurar-panel.visible { display: flex; }

        .restaurar-select {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-family: inherit;
            font-size: 13px;
            color: #1e2238;
            background: #f8faff;
            outline: none;
        }
        .restaurar-select:focus { border-color: var(--accent); }

        .btn-restaurar {
            padding: 10px 22px;
            background: #dc2626;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex; align-items: center; gap: 8px;
            transition: background 0.18s;
            width: fit-content;
        }
        .btn-restaurar:hover { background: #b91c1c; }

        /* ── Tabla historial ── */
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        thead th {
            background: #f8faff;
            padding: 11px 16px;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-align: left;
            border-bottom: 1px solid var(--border);
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        tbody tr { border-bottom: 1px solid var(--border); transition: background 0.15s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #f8faff; }
        tbody td { padding: 13px 16px; font-size: 13.5px; color: #1e2238; }

        /* Badges tipo */
        .tipo-auto     { background: #eff6ff; color: #3b82f6; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 20px; }
        .tipo-completo { background: #f0fdf4; color: #16a34a; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 20px; }
        .tipo-parcial  { background: #fffbeb; color: #d97706; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 20px; }

        /* Badges estado */
        .estado-ok   { background: #f0fdf4; color: #16a34a; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 20px; }
        .estado-fail { background: #fef2f2; color: #dc2626; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 20px; }

        /* ── Toast ── */
        .toast {
            position: fixed;
            top: 24px; right: 24px;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 600;
            box-shadow: 0 8px 28px rgba(0,0,0,0.15);
            animation: slideIn 0.4s ease, fadeOut 0.5s ease 4s forwards;
        }
        .toast-exito { background: #22c55e; color: #fff; }
        .toast-error { background: #ef4444; color: #fff; }
        .toast-close { background: transparent; border: none; color: #fff; cursor: pointer; font-size: 14px; margin-left: 8px; opacity: 0.8; }
        .toast-close:hover { opacity: 1; }
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

            <div class="nav-item submenu-toggle" onclick="toggleSubmenu('tareas')">
                <i class="fas fa-tasks"></i><span>Gestión de Tareas</span>
                <i class="fas fa-chevron-down flecha" id="flecha-tareas"></i>
            </div>
            <div class="submenu" id="submenu-tareas">
                <a href="gestion-ordenes.php" class="nav-item"><i class="fas fa-info-circle"></i><span>Gestión de Ordenes</span></a>
                <a href="inventario.php" class="nav-item"><i class="fas fa-wrench"></i><span>Ver Inventario</span></a>
                <a href="auditoria.php" class="nav-item"><i class="fas fa-shield-alt"></i><span>Auditoria</span></a>
            </div>

            <div class="nav-item submenu-toggle" onclick="toggleSubmenu('clientes')">
                <i class="fas fa-users"></i><span>Clientes</span>
                <i class="fas fa-chevron-down flecha" id="flecha-clientes"></i>
            </div>
            <div class="submenu" id="submenu-clientes">
                <a href="informacion-clientes.php" class="nav-item"><i class="fas fa-address-card"></i><span>Ver Clientes</span></a>
            </div>

            <a href="nota-remision.php" class="nav-item"><i class="fas fa-file-invoice"></i><span>Notas de Remisión</span></a>
            <a href="respaldo.php" class="nav-item activo"><i class="fas fa-database"></i><span>Respaldo</span></a>
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
                <h2>Respaldo del Sistema</h2>
                <p><i class="fas fa-database"></i> Gestiona los respaldos y restauraciones de datos del taller</p>
            </div>

            <!-- Último respaldo -->
            <div class="last-backup">
                <i class="fas fa-clock"></i>
                <?= htmlspecialchars($ultimo_respaldo) ?>
            </div>

            <!-- ── CREAR NUEVO RESPALDO ── -->
            <div class="card">
                <div class="card-title">
                    <i class="fas fa-box-archive"></i> Crear nuevo respaldo
                </div>
                <p class="card-desc">Genera un respaldo manual de los datos del sistema</p>

                <form method="POST" action="respaldo.php" id="form-respaldo">
                    <input type="hidden" name="accion" value="crear_respaldo">
                    <input type="hidden" name="tipo_respaldo" id="tipo_hidden" value="Completo">

                    <p class="tipo-label">Tipo de respaldo</p>
                    <div class="tipo-btns">
                        <button type="button" class="btn-tipo activo" data-tipo="Completo" onclick="selTipo(this)">
                            <i class="fas fa-file"></i> Completo
                        </button>
                        
                    </div>

                    <button type="submit" class="btn-crear" id="btn-crear" onclick="iniciarCarga(this)">
                        <i class="fas fa-download" id="btn-icon"></i>
                        <span id="btn-text">Crear respaldo ahora</span>
                    </button>
                </form>
            </div>

            <!-- ── RESPALDO AUTOMÁTICO ── -->
            <div class="card">
                <div class="card-title">
                    <i class="fas fa-shield-halved"></i> Respaldo automático
                </div>
                <p class="card-desc">Programacion de respaldos automáticos</p>

                <form method="POST" action="respaldo.php">
                    <input type="hidden" name="accion" value="guardar_toggles">

                    <div class="toggle-row">
                        <span>Respaldo diario (2:00 AM)</span>
                        <label class="toggle">
                            <input type="checkbox" name="diario" <?= ($toggles['diario'] ? 'checked' : '') ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <span>Respaldo semanal (Domingos)</span>
                        <label class="toggle">
                            <input type="checkbox" name="semanal" <?= ($toggles['semanal'] ? 'checked' : '') ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <span>Notificar si falla un respaldo</span>
                        <label class="toggle">
                            <input type="checkbox" name="notificar" <?= ($toggles['notificar'] ? 'checked' : '') ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <button type="submit" class="btn-guardar-toggles">
                        <i class="fas fa-floppy-disk"></i> Guardar configuración
                    </button>
                </form>
            </div>

            <!-- ── RESTAURAR DATOS ── -->
            <div class="card">
                <div class="card-title">
                    <i class="fas fa-rotate-left"></i> Restaurar datos
                </div>
                <p class="card-desc">Restaura el sistema a un punto anterior desde un respaldo</p>

                <div class="warning-box">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <i class="fas fa-triangle-exclamation"></i>
                        Restaurar un respaldo reemplazara los datos actuales del sistema. Esta acción no se puede deshacer.
                    </div>
                    <label class="toggle" style="flex-shrink:0;">
                        <input type="checkbox" id="toggle-restaurar" onchange="toggleRestaurar(this)">
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <div class="restaurar-panel" id="panel-restaurar">
                    <form method="POST" action="respaldo.php" style="display:flex; gap:12px; flex-wrap:wrap;"
                        onsubmit="return confirm('⚠️ ¿Confirmas la restauración del sistema?\n\nEsta acción reemplazará TODOS los datos actuales y no se puede deshacer.')">
                        <input type="hidden" name="accion" value="restaurar">
                        <select name="respaldo_id" class="restaurar-select">
                            <?php foreach ($historial as $h): ?>
                                <?php if ($h['estado'] === 'Completado'): ?>
                                <option value="<?= $h['id'] ?>">
                                    <?= htmlspecialchars($h['nombre']) ?> — <?= htmlspecialchars($h['fecha']) ?>
                                </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn-restaurar">
                            <i class="fas fa-rotate-left"></i> Restaurar sistema
                        </button>
                    </form>
                </div>
            </div>

            <!-- ── HISTORIAL ── -->
            <div class="card">
                <div class="card-title">
                    <i class="fas fa-clock-rotate-left"></i> Historial de respaldos
                </div>
                <p class="card-desc">Registro de todos los respaldos realizados</p>

                <table>
                    <thead>
                        <tr>
                            <th>Respaldo</th>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historial as $h): ?>
                        <tr>
                            <td><?= htmlspecialchars($h['nombre']) ?></td>
                            <td style="color:#64748b; font-size:13px;"><?= htmlspecialchars($h['fecha']) ?></td>
                            <td><span class="<?= $h['tipo_clase'] ?>"><?= htmlspecialchars($h['tipo']) ?></span></td>
                            <td><span class="<?= $h['estado_clase'] ?>"><?= htmlspecialchars($h['estado']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div><!-- /pagina -->
    </main>
</div><!-- /contenedor -->

<!-- Toast -->
<?php if ($notificacion): ?>
<div class="toast toast-<?= $notificacion['tipo'] ?>" id="toast">
    <i class="fas <?= $notificacion['tipo'] === 'exito' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
    <?= htmlspecialchars($notificacion['msg']) ?>
    <button class="toast-close" onclick="document.getElementById('toast').remove()">✕</button>
</div>
<?php endif; ?>

<script src="../../js/menu.js"></script>
<script>
function selTipo(btn) {
    document.querySelectorAll('.btn-tipo').forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');
    document.getElementById('tipo_hidden').value = btn.dataset.tipo;
}

function iniciarCarga(btn) {
    setTimeout(() => {
        btn.classList.add('loading');
        document.getElementById('btn-icon').className = 'fas fa-rotate spin';
        document.getElementById('btn-text').textContent = 'Creando respaldo...';
    }, 10);
}

function toggleRestaurar(cb) {
    const panel = document.getElementById('panel-restaurar');
    if (cb.checked) {
        if (!confirm('⚠️ ¿Desea habilitar la restauración del sistema?\n\nEsta acción reemplazará todos los datos actuales del sistema.')) {
            cb.checked = false;
            return;
        }
        panel.classList.add('visible');
    } else {
        panel.classList.remove('visible');
    }
}

const toast = document.getElementById('toast');
if (toast) setTimeout(() => toast.remove(), 5000);
</script>
</body>
</html>