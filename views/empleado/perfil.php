<?php
// ============================================================
//  perfil.php — Perfil del Mecánico + Cambio de Contraseña
//  Ruta: views/empleado/perfil.php
// ============================================================

$id = sesion_empleado_id();

// --- Procesar cambio de contraseña ---
$msg_ok  = '';
$msg_err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_password'])) {
    $actual   = trim($_POST['password_actual']   ?? '');
    $nueva    = trim($_POST['password_nueva']     ?? '');
    $confirma = trim($_POST['password_confirma']  ?? '');

    if (!$actual || !$nueva || !$confirma) {
        $msg_err = 'Completa todos los campos.';
    } elseif (strlen($nueva) < 6) {
        $msg_err = 'La nueva contraseña debe tener al menos 6 caracteres.';
    } elseif ($nueva !== $confirma) {
        $msg_err = 'Las contraseñas nuevas no coinciden.';
    } else {
        // Obtener hash actual de la BD
        $stmt = $conexion->prepare("SELECT password FROM empleados WHERE id_empleado = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        $hash_actual = $fila['password'] ?? '';

        // Si el hash está vacío (primer uso), aceptar cualquier contraseña actual
        $hash_valido = empty($hash_actual)
            ? true
            : password_verify($actual, $hash_actual);

        if (!$hash_valido) {
            $msg_err = 'La contraseña actual es incorrecta.';
        } else {
            $nuevo_hash = password_hash($nueva, PASSWORD_DEFAULT);
            $upd = $conexion->prepare("UPDATE empleados SET password = :hash WHERE id_empleado = :id");
            $upd->execute([':hash' => $nuevo_hash, ':id' => $id]);
            $msg_ok = 'Contraseña actualizada correctamente.';
        }
    }
}

// --- Obtener datos del empleado ---
$stmt = $conexion->prepare(
    "SELECT nombre, apellido, correo, telefono, puesto, cargo,
            fecha_ingreso, foto, especialidad
     FROM empleados
     WHERE id_empleado = :id LIMIT 1"
);
$stmt->execute([':id' => $id]);
$emp = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="perfil-wrapper">

    <!-- ====== TARJETA DE INFORMACIÓN ====== -->
    <div class="perfil-card">
        <div class="perfil-avatar">
            <?php if (!empty($emp['foto'])): ?>
                <img src="../../uploads/<?= htmlspecialchars($emp['foto']) ?>"
                     alt="Foto de perfil">
            <?php else: ?>
                <div class="avatar-iniciales">
                    <?= htmlspecialchars(sesion_iniciales()) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="perfil-info">
            <h2><?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellido']) ?></h2>
            <span class="perfil-puesto"><?= htmlspecialchars($emp['puesto'] ?? 'Mecánico') ?></span>

            <div class="perfil-datos">
                <div class="dato-item">
                    <i class="fas fa-envelope"></i>
                    <span><?= htmlspecialchars($emp['correo']) ?></span>
                </div>
                <div class="dato-item">
                    <i class="fas fa-phone"></i>
                    <span><?= htmlspecialchars($emp['telefono'] ?? '—') ?></span>
                </div>
                <div class="dato-item">
                    <i class="fas fa-briefcase"></i>
                    <span><?= htmlspecialchars($emp['cargo'] ?? '—') ?></span>
                </div>
                <div class="dato-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Ingreso: <?= htmlspecialchars($emp['fecha_ingreso'] ?? '—') ?></span>
                </div>
                <?php if (!empty($emp['especialidad'])): ?>
                <div class="dato-item">
                    <i class="fas fa-star"></i>
                    <span><?= htmlspecialchars($emp['especialidad']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ====== TARJETA CAMBIO DE CONTRASEÑA ====== -->
    <div class="perfil-card">
        <h3><i class="fas fa-lock"></i> Cambiar Contraseña</h3>

        <?php if ($msg_ok): ?>
            <div class="alerta alerta-ok">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg_ok) ?>
            </div>
        <?php endif; ?>
        <?php if ($msg_err): ?>
            <div class="alerta alerta-err">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($msg_err) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="form-password">
            <input type="hidden" name="cambiar_password" value="1">

            <div class="campo">
                <label>Contraseña actual</label>
                <div class="input-ojo">
                    <input type="password" name="password_actual"
                           placeholder="Tu contraseña actual" required>
                    <button type="button" onclick="toggleOjo(this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="campo">
                <label>Nueva contraseña</label>
                <div class="input-ojo">
                    <input type="password" name="password_nueva"
                           placeholder="Mínimo 6 caracteres" required>
                    <button type="button" onclick="toggleOjo(this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="campo">
                <label>Confirmar nueva contraseña</label>
                <div class="input-ojo">
                    <input type="password" name="password_confirma"
                           placeholder="Repite la nueva contraseña" required>
                    <button type="button" onclick="toggleOjo(this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-guardar">
                <i class="fas fa-save"></i> Guardar Contraseña
            </button>
        </form>
    </div>
</div>

<!-- ====== ESTILOS ====== -->
<style>
.perfil-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    max-width: 600px;
}

.perfil-card {
    background: #fff;
    border-radius: 12px;
    padding: 1.8rem;
    box-shadow: 0 2px 12px rgba(0,0,0,.08);
}

.perfil-card h3 {
    margin: 0 0 1.2rem;
    font-size: 1rem;
    color: #333;
    display: flex;
    align-items: center;
    gap: .5rem;
}

/* Avatar */
.perfil-avatar {
    display: flex;
    justify-content: center;
    margin-bottom: 1rem;
}
.perfil-avatar img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e53935;
}
.avatar-iniciales {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: #e53935;
    color: #fff;
    font-size: 2rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Info */
.perfil-info { text-align: center; }
.perfil-info h2 { margin: 0 0 .3rem; font-size: 1.3rem; color: #222; }
.perfil-puesto {
    display: inline-block;
    background: #fce4e4;
    color: #e53935;
    padding: .2rem .8rem;
    border-radius: 20px;
    font-size: .85rem;
    font-weight: 600;
    margin-bottom: 1rem;
}
.perfil-datos {
    display: flex;
    flex-direction: column;
    gap: .6rem;
    text-align: left;
    margin-top: .5rem;
}
.dato-item {
    display: flex;
    align-items: center;
    gap: .7rem;
    font-size: .9rem;
    color: #555;
}
.dato-item i { color: #e53935; width: 16px; }

/* Alertas */
.alerta {
    padding: .7rem 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    font-size: .9rem;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.alerta-ok  { background: #e8f5e9; color: #2e7d32; }
.alerta-err { background: #ffebee; color: #c62828; }

/* Formulario */
.form-password { display: flex; flex-direction: column; gap: 1rem; }
.campo { display: flex; flex-direction: column; gap: .3rem; }
.campo label { font-size: .85rem; font-weight: 600; color: #444; }
.input-ojo {
    display: flex;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}
.input-ojo input {
    flex: 1;
    border: none;
    padding: .6rem .8rem;
    font-size: .95rem;
    outline: none;
}
.input-ojo button {
    background: #f5f5f5;
    border: none;
    padding: 0 .8rem;
    cursor: pointer;
    color: #888;
}
.input-ojo button:hover { color: #e53935; }

.btn-guardar {
    background: #e53935;
    color: #fff;
    border: none;
    padding: .7rem 1.5rem;
    border-radius: 8px;
    font-size: .95rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: .5rem;
    align-self: flex-start;
    transition: background .2s;
}
.btn-guardar:hover { background: #c62828; }
</style>

<!-- ====== SCRIPT OJO ====== -->
<script>
function toggleOjo(btn) {
    const input = btn.closest('.input-ojo').querySelector('input');
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>