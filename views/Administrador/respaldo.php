<?php
// ══════════════════════════════════════════════════════
//  respaldo.php  —  Respaldo del Sistema  (Auto Master)
//  Lógica: crear respaldo, toggles automáticos, restaurar
// ══════════════════════════════════════════════════════

session_start();

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

    // 1. CREAR RESPALDO
    if (isset($_POST['accion']) && $_POST['accion'] === 'crear_respaldo') {
        $tipo = in_array($_POST['tipo_respaldo'] ?? '', ['Completo','Parcial','Configuración'])
            ? $_POST['tipo_respaldo'] : 'Completo';

        // Simulación: falla si el tipo es "Parcial" para demostrar el error
        $exito = ($tipo !== 'Parcial'); // ← en producción reemplaza con tu lógica real

        if ($exito) {
            $_SESSION['notif'] = ['tipo'=>'exito','msg'=>'Respaldo completado'];
        } else {
            $_SESSION['notif'] = ['tipo'=>'error','msg'=>'Respaldo fallido, intenta nuevamente'];
        }
        header('Location: respaldo.php');
        exit;
    }

    // 2. GUARDAR TOGGLES
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

    // 3. RESTAURAR SISTEMA
    if (isset($_POST['accion']) && $_POST['accion'] === 'restaurar') {
        $id = intval($_POST['respaldo_id'] ?? 0);
        // Simulación: siempre exitoso (conectar con lógica real)
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
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:         #f0f2f5;
            --sidebar:    #1a1d23;
            --sidebar-h:  #2a2f3a;
            --accent:     #e5282a;
            --accent2:    #ff5c5e;
            --teal:       #0ab4b4;
            --card:       #ffffff;
            --border:     #e2e6ea;
            --text:       #1e2330;
            --muted:      #7a8394;
            --green:      #18c78e;
            --red:        #e5282a;
            --warn-bg:    #fff8e1;
            --warn-bd:    #f5c518;
            --radius:     10px;
            --shadow:     0 2px 12px rgba(0,0,0,.07);
        }

        body { font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--text); display:flex; min-height:100vh; }

        /* ── Sidebar ── */
        .sidebar { width:185px; min-height:100vh; background:var(--sidebar); display:flex; flex-direction:column; position:fixed; top:0; left:0; bottom:0; z-index:100; }
        .sidebar-brand { display:flex; align-items:center; gap:10px; padding:22px 18px 18px; border-bottom:1px solid #2a2f3a; }
        .brand-icon { width:36px; height:36px; background:var(--accent); border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:18px; }
        .brand-text { font-family:'Syne',sans-serif; font-size:13px; font-weight:700; color:var(--accent); line-height:1.2; }
        .brand-sub  { font-size:10px; color:#5a6070; letter-spacing:.5px; }
        nav { flex:1; padding:12px 0; }
        .nav-item { display:flex; align-items:center; gap:10px; padding:11px 18px; color:#8a93a6; font-size:13px; cursor:pointer; transition:background .15s,color .15s; border-left:3px solid transparent; text-decoration:none; }
        .nav-item:hover  { background:var(--sidebar-h); color:#fff; }
        .nav-item.active { background:var(--sidebar-h); color:#fff; border-left-color:var(--accent); }
        .nav-item .icon  { font-size:15px; width:18px; text-align:center; }
        .nav-chevron { margin-left:auto; font-size:10px; }

        /* ── Main ── */
        .main { margin-left:185px; flex:1; display:flex; flex-direction:column; min-height:100vh; }

        /* Topbar */
        .topbar { height:56px; background:var(--card); border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end; padding:0 28px; gap:18px; position:sticky; top:0; z-index:50; }
        .topbar-icon { width:34px; height:34px; border-radius:50%; background:#f0f2f5; display:flex; align-items:center; justify-content:center; font-size:15px; color:var(--muted); cursor:pointer; }
        .topbar-icon:hover { background:var(--border); }
        .avatar { width:34px; height:34px; border-radius:50%; background:var(--accent); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; }

        /* ── Notificación toast ── */
        .toast {
            position:absolute; top:10px; left:50%; transform:translateX(-50%);
            display:flex; align-items:center; gap:10px;
            padding:11px 20px; border-radius:30px;
            font-size:13.5px; font-weight:600; white-space:nowrap;
            box-shadow:0 4px 18px rgba(0,0,0,.15);
            animation: toastIn .35s ease, toastOut .4s ease 4.5s forwards;
            z-index:200;
        }
        .toast-exito { background:var(--green); color:#fff; }
        .toast-error { background:var(--red);   color:#fff; }
        .toast-close { background:none; border:none; color:inherit; font-size:16px; cursor:pointer; margin-left:6px; opacity:.8; }
        .toast-close:hover { opacity:1; }
        @keyframes toastIn  { from{opacity:0;transform:translateX(-50%) translateY(-12px)} to{opacity:1;transform:translateX(-50%) translateY(0)} }
        @keyframes toastOut { from{opacity:1} to{opacity:0;pointer-events:none} }

        /* Content */
        .content { padding:30px 32px; flex:1; }
        .page-title { font-family:'Syne',sans-serif; font-size:26px; font-weight:700; margin-bottom:6px; }
        .page-subtitle { display:flex; align-items:center; gap:7px; font-size:13px; color:var(--muted); margin-bottom:24px; }

        /* Cards */
        .card { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:22px 24px; margin-bottom:18px; box-shadow:var(--shadow); }
        .card-title { font-family:'Syne',sans-serif; font-size:15px; font-weight:700; display:flex; align-items:center; gap:8px; margin-bottom:4px; }
        .card-desc  { font-size:12.5px; color:var(--muted); margin-bottom:18px; }

        /* Último respaldo */
        .last-backup { border:1.5px solid var(--teal); border-radius:var(--radius); padding:14px 20px; display:flex; align-items:center; gap:10px; font-size:14px; font-weight:500; color:var(--teal); background:rgba(10,180,180,.05); margin-bottom:18px; }

        /* Tipo de respaldo */
        .tipo-label { font-size:13px; font-weight:500; margin-bottom:10px; }
        .tipo-btns  { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:4px; }
        .btn-tipo {
            display:inline-flex; align-items:center; gap:7px;
            padding:9px 18px; border-radius:7px; font-size:13px; font-weight:600;
            cursor:pointer; border:1.5px solid var(--border); background:transparent; color:var(--muted);
            transition:all .15s; font-family:'DM Sans',sans-serif;
        }
        .btn-tipo:hover   { border-color:var(--accent); color:var(--accent); }
        .btn-tipo.activo  { background:var(--accent); color:#fff; border-color:var(--accent); }

        /* Crear respaldo btn */
        .btn-crear {
            display:inline-flex; align-items:center; gap:8px;
            padding:10px 20px; margin-top:14px;
            background:var(--accent); color:#fff;
            border:none; border-radius:8px; font-size:13px; font-weight:600;
            cursor:pointer; transition:background .15s,transform .1s;
            font-family:'DM Sans',sans-serif;
        }
        .btn-crear:hover  { background:var(--accent2); }
        .btn-crear:active { transform:scale(.97); }
        .btn-crear.loading { opacity:.75; pointer-events:none; }
        .spin { display:inline-block; animation:spin .8s linear infinite; }
        @keyframes spin { to { transform:rotate(360deg); } }

        /* Toggles */
        .toggle-row { display:flex; align-items:center; justify-content:space-between; padding:9px 0; border-bottom:1px solid #f0f2f5; font-size:13.5px; }
        .toggle-row:last-of-type { border-bottom:none; }
        .toggle { position:relative; width:40px; height:22px; }
        .toggle input { opacity:0; width:0; height:0; }
        .toggle-slider { position:absolute; inset:0; background:#d1d5db; border-radius:22px; cursor:pointer; transition:background .2s; }
        .toggle-slider::before { content:''; position:absolute; width:16px; height:16px; background:#fff; border-radius:50%; top:3px; left:3px; transition:transform .2s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
        .toggle input:checked + .toggle-slider { background:var(--accent); }
        .toggle input:checked + .toggle-slider::before { transform:translateX(18px); }
        .btn-guardar-toggles {
            margin-top:14px; padding:8px 18px; font-size:13px; font-weight:600;
            background:var(--accent); color:#fff; border:none; border-radius:8px;
            cursor:pointer; font-family:'DM Sans',sans-serif;
        }
        .btn-guardar-toggles:hover { background:var(--accent2); }

        /* Warning / restaurar */
        .warning-box { background:var(--warn-bg); border:1.5px solid var(--warn-bd); border-radius:8px; padding:12px 16px; font-size:13px; color:#7a5c00; display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .restaurar-panel { display:none; margin-top:16px; padding-top:16px; border-top:1px solid var(--border); }
        .restaurar-panel.visible { display:block; }
        .restaurar-select { padding:9px 14px; border-radius:7px; border:1.5px solid var(--border); font-size:13px; font-family:'DM Sans',sans-serif; color:var(--text); background:var(--card); margin-bottom:12px; width:100%; max-width:380px; display:block; }
        .btn-restaurar { display:inline-flex; align-items:center; gap:8px; padding:10px 20px; background:#d97706; color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; font-family:'DM Sans',sans-serif; }
        .btn-restaurar:hover { background:#b45309; }

        /* Historial */
        table { width:100%; border-collapse:collapse; font-size:13px; }
        thead th { text-align:left; font-weight:600; padding:10px 14px; border-bottom:1.5px solid var(--border); color:var(--muted); font-size:12px; text-transform:uppercase; letter-spacing:.5px; }
        tbody tr:hover { background:#fafbfc; }
        tbody td { padding:12px 14px; border-bottom:1px solid #f0f2f5; }
        tbody tr:last-child td { border-bottom:none; }
        .tipo-auto     { color:var(--teal);  font-weight:500; }
        .tipo-completo { color:var(--accent); font-weight:500; }
        .tipo-parcial  { color:#8b5cf6; font-weight:500; }
        .estado-ok     { color:var(--teal);  font-weight:600; }
        .estado-fail   { color:var(--red);   font-weight:600; }
    </style>
</head>
<body>

<!-- ══ SIDEBAR ══ -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">🔧</div>
        <div>
            <div class="brand-text">Menú</div>
            <div class="brand-sub">Categorías</div>
        </div>
    </div>
    <nav>
        <a class="nav-item" href="panel.php"><span class="icon">🗂️</span> Panel De Administrador</a>
        <a class="nav-item" href="informacion.php">
            <span class="icon">👤</span> Usuario <span class="nav-chevron">▾</span>
        </a>
        <a class="nav-item" href="tareas-asignadas.php">
            <span class="icon">📋</span> Gestión de Tareas <span class="nav-chevron">▾</span>
        </a>
        <a class="nav-item" href="gestion-ordenes.php">
            <span class="icon">👥</span> Clientes <span class="nav-chevron">▾</span>
        </a>
        <a class="nav-item active" href="respaldo.php">
            <span class="icon">💾</span> Respaldo
        </a>
        <a class="nav-item" href="../login.php"><span class="icon">🚪</span> Cerrar Sesión</a>
    </nav>
</aside>

<!-- ══ MAIN ══ -->
<div class="main">

    <!-- Topbar con toast -->
    <header class="topbar" style="position:relative;">
        <?php if ($notificacion): ?>
        <div class="toast toast-<?= $notificacion['tipo'] ?>" id="toast">
            <?= $notificacion['tipo'] === 'exito' ? '✅' : '❌' ?>
            <?= htmlspecialchars($notificacion['msg']) ?>
            <button class="toast-close" onclick="document.getElementById('toast').remove()">✕</button>
        </div>
        <?php endif; ?>

        <div class="topbar-icon">🔍</div>
        <div class="topbar-icon">🔔</div>
        <div class="topbar-icon">❓</div>
        <div class="avatar">A</div>
    </header>

    <div class="content">
        <h1 class="page-title">Respaldo del Sistema</h1>
        <p class="page-subtitle"><span>💾</span> Gestiona los respaldos y restauraciones de datos del taller</p>

        <!-- Último respaldo -->
        <div class="last-backup">
            <span>🕐</span> <?= htmlspecialchars($ultimo_respaldo) ?>
        </div>

        <!-- ── CREAR NUEVO RESPALDO ── -->
        <div class="card">
            <div class="card-title">📦 Crear nuevo respaldo</div>
            <p class="card-desc">Genera un respaldo manual de los datos del sistema</p>

            <form method="POST" action="respaldo.php" id="form-respaldo">
                <input type="hidden" name="accion" value="crear_respaldo">
                <input type="hidden" name="tipo_respaldo" id="tipo_hidden" value="Completo">

                <p class="tipo-label">Tipo de respaldo</p>
                <div class="tipo-btns">
                    <button type="button" class="btn-tipo activo" data-tipo="Completo"   onclick="selTipo(this)">📄 Completo</button>
                    <button type="button" class="btn-tipo"        data-tipo="Parcial"     onclick="selTipo(this)">📁 Parcial</button>
                    <button type="button" class="btn-tipo"        data-tipo="Configuración" onclick="selTipo(this)">⚙️ Configuración</button>
                </div>

                <button type="submit" class="btn-crear" id="btn-crear" onclick="iniciarCarga(this)">
                    <span id="btn-icon">⬇️</span>
                    <span id="btn-text">Crear respaldo ahora</span>
                </button>
            </form>
        </div>

        <!-- ── RESPALDO AUTOMÁTICO ── -->
        <div class="card">
            <div class="card-title">🛡️ Respaldo automático</div>
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

                <button type="submit" class="btn-guardar-toggles">💾 Guardar configuración</button>
            </form>
        </div>

        <!-- ── RESTAURAR DATOS ── -->
        <div class="card">
            <div class="card-title">🔄 Restaurar datos</div>
            <p class="card-desc">Restaura el sistema a un punto anterior desde un respaldo</p>

            <div class="warning-box">
                <span>⚠️ Restaurar un respaldo reemplazara los datos actuales del sistema. Esta acción no se puede deshacer.</span>
                <label class="toggle">
                    <input type="checkbox" id="toggle-restaurar" onchange="toggleRestaurar(this)">
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <div class="restaurar-panel" id="panel-restaurar">
                <form method="POST" action="respaldo.php"
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

                    <button type="submit" class="btn-restaurar">🔄 Restaurar sistema</button>
                </form>
            </div>
        </div>

        <!-- ── HISTORIAL ── -->
        <div class="card">
            <div class="card-title">📋 Historial de respaldos</div>
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
                        <td><?= htmlspecialchars($h['fecha']) ?></td>
                        <td><span class="<?= $h['tipo_clase'] ?>"><?= htmlspecialchars($h['tipo']) ?></span></td>
                        <td><span class="<?= $h['estado_clase'] ?>"><?= htmlspecialchars($h['estado']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div><!-- /content -->
</div><!-- /main -->

<script>
// ── Seleccionar tipo de respaldo ──────────────────────
function selTipo(btn) {
    document.querySelectorAll('.btn-tipo').forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');
    document.getElementById('tipo_hidden').value = btn.dataset.tipo;
}

// ── Animación de carga al crear respaldo ─────────────
function iniciarCarga(btn) {
    setTimeout(() => {
        btn.classList.add('loading');
        document.getElementById('btn-icon').innerHTML = '<span class="spin">🔄</span>';
        document.getElementById('btn-text').textContent = 'Creando respaldo...';
    }, 10);
}

// ── Toggle restaurar con confirmación ────────────────
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

// ── Auto-ocultar toast después de 5 segundos ─────────
const toast = document.getElementById('toast');
if (toast) {
    setTimeout(() => toast.remove(), 5000);
}
</script>

</body>
</html>