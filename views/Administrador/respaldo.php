<?php
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/estilos-generales.css">
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
            <a href="panel.php" class="nav-item activo"><i class="fas fa-th-large"></i><span>Panel</span></a>

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
            <a href="login.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Cerrar Sesión</span></a>
        </nav>
        <div class="sidebar-usuario">
            <div class="sidebar-usuario-avatar">DG</div>
            <div class="sidebar-usuario-info"><h4>Daniel G.</h4><span>Administrador</span></div>
        </div>
    </aside>

    <!-- CONTENIDO -->
    <main class="contenido">

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
