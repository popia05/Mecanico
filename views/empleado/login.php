<?php
session_start();
require_once '../../php/db_conexion.php';

// Si ya hay sesión activa, redirigir al panel
if (isset($_SESSION['id_empleado'])) {
    header('Location: panel.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo   = trim($_POST['correo'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($correo) || empty($password)) {
        $error = 'Por favor completa todos los campos.';
    } else {
        $stmt = $conexion->prepare("SELECT * FROM empleados WHERE correo = ? LIMIT 1");
        $stmt->execute([$correo]);
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($empleado && $empleado['password'] === $password) {
            $_SESSION['id_empleado'] = $empleado['id_empleado'];
            $_SESSION['nombre']      = $empleado['nombre'];
            $_SESSION['apellido']    = $empleado['apellido'];
            $_SESSION['puesto']      = $empleado['puesto'];
            $_SESSION['foto']        = $empleado['foto'];

            header('Location: panel.php');
            exit();
        } else {
            $error = 'Correo o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Auto Master</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --accent: #e84393;
            --accent2: #4f8ef7;
            --bg: #f0f2f8;
            --card: #ffffff;
            --border: #e2e8f0;
            --shadow: 0 8px 40px rgba(30,34,60,0.13);
            --radius: 18px;
            --dark: #1a1d27;
        }

        body {
            font-family: 'Sora', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: -120px; left: -120px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(232,67,147,0.15), transparent 70%);
            pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed;
            bottom: -100px; right: -100px;
            width: 350px; height: 350px;
            background: radial-gradient(circle, rgba(79,142,247,0.13), transparent 70%);
            pointer-events: none;
        }

        .login-wrapper {
            display: flex;
            width: 820px;
            min-height: 480px;
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 1px solid var(--border);
            position: relative;
            z-index: 1;
        }

        /* ── Panel izquierdo ── */
        .login-left {
            width: 340px;
            background: var(--dark);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 36px;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }
        .login-left::before {
            content: '';
            position: absolute;
            top: -80px; left: -80px;
            width: 260px; height: 260px;
            background: radial-gradient(circle, rgba(232,67,147,0.22), transparent 70%);
        }
        .login-left::after {
            content: '';
            position: absolute;
            bottom: -60px; right: -60px;
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(79,142,247,0.18), transparent 70%);
        }

        .brand-icon {
            width: 72px; height: 72px;
            background: var(--accent);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 30px; color: #fff;
            margin-bottom: 24px;
            box-shadow: 0 4px 24px rgba(232,67,147,0.40);
            position: relative; z-index: 1;
        }
        .brand-title {
            color: #fff;
            font-size: 22px;
            font-weight: 700;
            text-align: center;
            position: relative; z-index: 1;
            margin-bottom: 8px;
        }
        .brand-sub {
            color: #8b92a9;
            font-size: 12.5px;
            text-align: center;
            position: relative; z-index: 1;
            line-height: 1.7;
        }

        .dots {
            position: absolute;
            bottom: 36px; left: 36px;
            display: grid;
            grid-template-columns: repeat(6, 8px);
            gap: 6px;
            z-index: 1;
        }
        .dots span {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
        }
        .dots span:nth-child(3n+1) { background: rgba(232,67,147,0.40); }

        /* ── Panel derecho ── */
        .login-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 48px 44px;
        }

        .login-title {
            font-size: 23px;
            font-weight: 700;
            color: #1e2238;
            margin-bottom: 6px;
        }
        .login-subtitle {
            color: #64748b;
            font-size: 13px;
            margin-bottom: 32px;
        }

        .alerta-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            border-radius: 10px;
            padding: 11px 16px;
            font-size: 13px;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group { margin-bottom: 20px; }

        .form-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .input-wrapper { position: relative; }

        .input-icon {
            position: absolute;
            left: 14px; top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-family: 'Sora', sans-serif;
            font-size: 13.5px;
            color: #1e2238;
            background: #f8faff;
            outline: none;
            transition: border-color 0.18s, box-shadow 0.18s;
        }
        .form-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(232,67,147,0.10);
            background: #fff;
        }

        .toggle-password {
            position: absolute;
            right: 14px; top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
            cursor: pointer;
            transition: color 0.15s;
        }
        .toggle-password:hover { color: var(--accent); }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'Sora', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.18s, transform 0.15s, box-shadow 0.18s;
            box-shadow: 0 4px 16px rgba(232,67,147,0.30);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-login:hover {
            background: #d4337f;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(232,67,147,0.38);
        }
        .btn-login:active { transform: translateY(0); }

        .login-footer {
            margin-top: 28px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }

        @media (max-width: 700px) {
            .login-left { display: none; }
            .login-wrapper { width: 95%; }
            .login-right { padding: 36px 28px; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">

    <!-- Izquierda -->
    <div class="login-left">
        <div class="brand-icon"><i class="fas fa-wrench"></i></div>
        <div class="brand-title">Auto Master</div>
        <div class="brand-sub">
            Sistema de gestión<br>
            Fuel Injection Auto Master
        </div>
        <div class="dots">
            <span></span><span></span><span></span>
            <span></span><span></span><span></span>
            <span></span><span></span><span></span>
            <span></span><span></span><span></span>
        </div>
    </div>

    <!-- Derecha -->
    <div class="login-right">
        <div class="login-title">Bienvenido 👋</div>
        <div class="login-subtitle">Ingresa tus credenciales para continuar.</div>

        <?php if ($error): ?>
        <div class="alerta-error">
            <i class="fas fa-circle-exclamation"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="form-group">
                <label class="form-label" for="correo">Correo electrónico</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope input-icon"></i>
                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        class="form-input"
                        placeholder="ejemplo@correo.com"
                        value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                        required
                        autocomplete="email"
                    >
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                    <i class="fas fa-eye toggle-password" onclick="togglePass()"></i>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-arrow-right-to-bracket"></i>
                Iniciar Sesión
            </button>

        </form>

        <div class="login-footer">
            &copy; <?= date('Y') ?> Fuel Injection Auto Master
        </div>
    </div>

</div>

<script>
function togglePass() {
    const input = document.getElementById('password');
    const icon  = document.querySelector('.toggle-password');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
</body>
</html>