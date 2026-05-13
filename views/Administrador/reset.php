<?php
session_start();
require_once __DIR__ . '/config/database.php';

$token   = $_GET['token'] ?? $_POST['token'] ?? '';
$mensaje = '';
$tipo    = '';
$valido  = false;
$empleadoId = null;

$db = obtenerConexion();

// Validar token
if ($db && $token) {
    $stmt = $db->prepare("SELECT empleado_id FROM password_resets
                          WHERE token = :t AND expira > NOW() AND usado = 0
                          LIMIT 1");
    $stmt->execute([':t' => $token]);
    $row = $stmt->fetch();
    if ($row) {
        $valido = true;
        $empleadoId = $row['empleado_id'];
    }
} elseif (!$db && $token) {
    // Modo demo: aceptar cualquier token
    $valido = true;
}

// Procesar nueva contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valido) {
    $pass1 = $_POST['password']  ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if (strlen($pass1) < 6) {
        $mensaje = 'La contraseña debe tener al menos 6 caracteres.';
        $tipo = 'error';
    } elseif ($pass1 !== $pass2) {
        $mensaje = 'Las contraseñas no coinciden.';
        $tipo = 'error';
    } else {
        if ($db) {
            $hash = password_hash($pass1, PASSWORD_DEFAULT);
            $db->prepare("UPDATE empleados SET password = :p WHERE id = :id")
               ->execute([':p' => $hash, ':id' => $empleadoId]);
            $db->prepare("UPDATE password_resets SET usado = 1 WHERE token = :t")
               ->execute([':t' => $token]);
        }
        $mensaje = 'Contraseña actualizada. Ya puedes iniciar sesion.';
        $tipo = 'exito';
        $valido = false;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Contraseña - Auto Master</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { min-height: 100vh; background: #f1f5f9; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { background: #fff; border-radius: 16px; padding: 45px; width: 100%; max-width: 480px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); text-align: center; }
        h1 { font-size: 26px; margin-bottom: 25px; color: #111827; }
        h1 span { color: #dc2626; }
        .campo { text-align: left; margin-bottom: 18px; }
        .campo label { display: block; font-size: 14px; font-weight: 600; color: #1f2937; margin-bottom: 8px; }
        .campo input { width: 100%; padding: 13px 18px; border: 1px solid #e5e7eb; border-radius: 30px; font-size: 14px; outline: none; }
        .campo input:focus { border-color: #dc2626; }
        .btn { width: 100%; padding: 14px; background: linear-gradient(135deg, #dc2626, #991b1b); color: #fff; border: none; border-radius: 30px; font-size: 15px; font-weight: 600; cursor: pointer; }
        .btn:hover { box-shadow: 0 6px 18px rgba(220,38,38,0.3); }
        .alerta { padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 20px; text-align: left; }
        .alerta-exito { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .alerta-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .volver { display: inline-block; margin-top: 18px; color: #475569; text-decoration: none; font-size: 14px; }
        .volver:hover { color: #dc2626; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Nueva <span>Contraseña</span></h1>

        <?php if ($mensaje): ?>
            <div class="alerta alerta-<?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <?php if ($valido): ?>
            <form method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="campo">
                    <label>Nueva contraseña</label>
                    <input type="password" name="password" required minlength="6" placeholder="Minimo 6 caracteres">
                </div>
                <div class="campo">
                    <label>Confirmar contraseña</label>
                    <input type="password" name="password2" required minlength="6" placeholder="Repite la contraseña">
                </div>
                <button type="submit" class="btn">Guardar nueva contraseña</button>
            </form>
        <?php elseif (!$mensaje): ?>
            <div class="alerta alerta-error">El enlace es invalido o expiro.</div>
        <?php endif; ?>

        <a href="login.php" class="volver"><i class="fas fa-chevron-left"></i> Ir a inicio de sesion</a>
    </div>
</body>
</html>