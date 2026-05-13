<?php
// ============================================================
//  login.php — Punto de entrada único para admin y mecánicos
//  Ruta: views/Administrador/login.php
// ============================================================
require_once '../../php/db_conexion.php';
require_once '../../php/auth.php';

// Si ya tiene sesión activa, redirigir directo
if (!empty($_SESSION['usuario_rol'])) {
    if ($_SESSION['usuario_rol'] === 'admin') {
        header('Location: Index.php');
    } else {
        header('Location: ../empleado/index.php');
    }
    exit;
}

// Procesar el formulario POST
procesar_login($conexion);

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Auto Master</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/estilos-generales.css">
    <link rel="stylesheet" href="../../css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-left">
                <div class="brand-header">
                    <div class="brand-logo">
                        <img src="../../logo.png.png" alt="Fuel Injection Auto Master logo">
                    </div>
                    <div>
                        <p class="brand-caption"><b>Fuel Injection</b></p>
                        <h1>Auto Master</h1>
                    </div>
                </div>

                <h2>Iniciar Sesión</h2>

                <?php if ($error): ?>
                <div class="alerta-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <form id="loginForm" method="POST" action="">
                    <div class="input-group">
                        <label for="username">Email</label>
                        <input type="email" id="username" name="username"
                               placeholder="ejemplo@correo.com"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               required>
                    </div>
                    <div class="input-group">
                        <label for="phone">Número de celular</label>
                        <input type="tel" id="phone" name="phone"
                               placeholder="4300000000"
                               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                               required>
                    </div>
                    <div class="input-group password-group">
                        <label for="password">Contraseña</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password"
                                   placeholder="Ingresa tu contraseña" required>
                            <button type="button" class="password-toggle" aria-label="Mostrar contraseña">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-footer">
                       <a href="recuperar.php" class="link-olvido">¿Olvidaste tu contraseña?</a>
                    </div>
                    <button type="submit" class="btn-login">Iniciar Sesión</button>
                </form>

                <!-- Credenciales de prueba visibles durante desarrollo -->
                <div class="credenciales-demo">
                    <p><strong>Admin:</strong> admin@automaster.com / 4300000000 / automaster123</p>
                    <p><strong>Mecánico:</strong> usa correo + teléfono de la BD, contraseña = correo</p>
                </div>
            </div>
            <div class="login-right">
                <div class="hero-card">
                    <div class="hero-image">
                        <img src="../../logo.png.png" alt="Fuel Injection Auto Master logo">
                    </div>
                    <p><b>¡Comienza a optimizar el rendimiento de tu auto hoy!</b></p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .alerta-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 16px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .credenciales-demo {
            margin-top: 20px;
            padding: 12px;
            background: #f0f9ff;
            border-radius: 8px;
            border: 1px solid #bae6fd;
            font-size: 12px;
            color: #0c4a6e;
            line-height: 1.8;
        }
        .credenciales-demo p { margin: 0; }
    </style>

    <script>
        // Toggle mostrar/ocultar contraseña
        const toggle = document.querySelector('.password-toggle');
        const passInput = document.getElementById('password');
        if (toggle && passInput) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const isPass = passInput.type === 'password';
                passInput.type = isPass ? 'text' : 'password';
                toggle.querySelector('i').className = isPass ? 'fas fa-eye-slash' : 'fas fa-eye';
                toggle.setAttribute('aria-label', isPass ? 'Ocultar contraseña' : 'Mostrar contraseña');
            });
        }
    </script>
</body>
</html>