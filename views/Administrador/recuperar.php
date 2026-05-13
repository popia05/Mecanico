<?php
session_start();

$mensaje = '';
$tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/php/procesar_recuperar.php';
    $resultado = procesarRecuperar($_POST['email'] ?? '');
    $mensaje = $resultado['mensaje'];
    $tipo    = $resultado['tipo'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Auto Master</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', system-ui, sans-serif; }
        body {
            min-height: 100vh;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .logo-esquina {
            position: absolute;
            top: 25px;
            left: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logo-esquina img { height: 50px; }
        .logo-esquina-texto { font-size: 12px; color: #475569; font-weight: 600; }
        .logo-esquina-texto strong { color: #dc2626; display: block; font-size: 14px; }

        .card-recuperar {
            background: #fff;
            border-radius: 16px;
            padding: 45px 50px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            text-align: center;
        }
        .icono-letrero {
            width: 130px;
            height: 130px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icono-letrero svg { width: 100%; height: 100%; }

        .titulo-recuperar {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 28px;
            color: #111827;
        }
        .titulo-recuperar span { color: #dc2626; }

        .campo-recuperar { text-align: left; margin-bottom: 22px; }
        .campo-recuperar label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }
        .campo-recuperar input {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #e5e7eb;
            border-radius: 30px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }
        .campo-recuperar input:focus {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220,38,38,0.08);
        }

        .btn-recuperar {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: #fff;
            border: none;
            border-radius: 30px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.2s;
            margin-bottom: 18px;
        }
        .btn-recuperar:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(220,38,38,0.3);
        }
        .btn-recuperar:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .volver-login {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #475569;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s;
        }
        .volver-login:hover { color: #dc2626; }

        .alerta {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
        }
        .alerta-exito { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .alerta-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        @media (max-width: 540px) {
            .logo-esquina { position: relative; top: 0; left: 0; margin-bottom: 30px; justify-content: center; }
            .card-recuperar { padding: 35px 25px; }
            .titulo-recuperar { font-size: 24px; }
        }
    </style>
</head>
<body>
    <div class="logo-esquina">
        <img src="../logo.png.png" alt="Logo" onerror="this.style.display='none'">
    </div>

    <div class="card-recuperar">
        <div class="icono-letrero">
            <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <path d="M 30 160 Q 50 155, 70 160 T 110 158 T 170 162" stroke="#1f2937" stroke-width="2" fill="none" stroke-linecap="round"/>
                <rect x="115" y="80" width="60" height="25" fill="#fff" stroke="#1f2937" stroke-width="2.5"/>
                <polygon points="175,80 195,92.5 175,105" fill="#fff" stroke="#1f2937" stroke-width="2.5"/>
                <line x1="120" y1="105" x2="120" y2="155" stroke="#1f2937" stroke-width="3"/>
                <rect x="60" y="115" width="50" height="20" fill="#fff" stroke="#1f2937" stroke-width="2.5"/>
                <polygon points="60,115 45,125 60,135" fill="#fff" stroke="#1f2937" stroke-width="2.5"/>
                <line x1="105" y1="135" x2="105" y2="158" stroke="#1f2937" stroke-width="3"/>
                <path d="M 35 170 Q 45 168, 55 170" stroke="#1f2937" stroke-width="1.5" fill="none"/>
                <path d="M 80 168 Q 90 166, 100 168" stroke="#1f2937" stroke-width="1.5" fill="none"/>
                <path d="M 140 170 Q 155 168, 170 170" stroke="#1f2937" stroke-width="1.5" fill="none"/>
            </svg>
        </div>

        <h1 class="titulo-recuperar">Restablecer <span>Contraseña</span></h1>

        <?php if ($mensaje): ?>
            <div class="alerta alerta-<?= $tipo ?>">
                <i class="fas fa-<?= $tipo === 'exito' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <span><?= htmlspecialchars($mensaje) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" id="form-recuperar">
            <div class="campo-recuperar">
                <label for="email">Correo Electronico</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="e.g. usuario@automaster.com"
                    required
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                >
            </div>

            <button type="submit" class="btn-recuperar" id="btn-enviar">
                Recuperar contraseña
            </button>
        </form>

        <a href="login.php" class="volver-login">
            <i class="fas fa-chevron-left"></i> Volver a inicio de sesion
        </a>
    </div>

    <script>
        document.getElementById('form-recuperar').addEventListener('submit', function() {
            const btn = document.getElementById('btn-enviar');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        });
    </script>
</body>
</html>