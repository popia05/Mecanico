<?php
// ============================================================
//  auth.php — Sistema de autenticación central
// ============================================================

session_start();

// ------------------------------------------------------------
// CREDENCIALES DEL ADMINISTRADOR (fijas en código)
// ------------------------------------------------------------
define('ADMIN_CORREO',   'admin@automaster.com');
define('ADMIN_TELEFONO', '6331157599');
define('ADMIN_PASSWORD', 'admin123');
define('ADMIN_NOMBRE',   'Administrador');

// ------------------------------------------------------------
// Función principal: procesar login
// ------------------------------------------------------------
function procesar_login(PDO $conexion): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

    $correo   = trim($_POST['username'] ?? '');
    $telefono = trim($_POST['phone']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$correo || !$telefono || !$password) {
        $_SESSION['login_error'] = 'Por favor completa todos los campos.';
        return;
    }

    // --- ¿Es el administrador fijo? ---
    if (
        $correo   === ADMIN_CORREO   &&
        $telefono === ADMIN_TELEFONO &&
        password_verify($password, password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT))
    ) {
        $_SESSION['usuario_id']        = 0;
        $_SESSION['usuario_nombre']    = ADMIN_NOMBRE;
        $_SESSION['usuario_correo']    = ADMIN_CORREO;
        $_SESSION['usuario_rol']       = 'admin';
        $_SESSION['usuario_iniciales'] = 'AD';
        header('Location: /Mecanico/views/Administrador/Index.php');
        exit;
    }

    // --- ¿Es un mecánico en la BD? ---
    try {
        $stmt = $conexion->prepare(
            "SELECT id_empleado, nombre, apellido, correo, telefono, password, puesto
             FROM empleados
             WHERE correo = :correo AND activo = 1
             LIMIT 1"
        );
        $stmt->execute([':correo' => $correo]);
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$empleado) {
            $_SESSION['login_error'] = 'Credenciales incorrectas.';
            return;
        }

        // Verificar teléfono
        $tel_bd    = preg_replace('/\D/', '', $empleado['telefono'] ?? '');
        $tel_input = preg_replace('/\D/', '', $telefono);

        if ($tel_bd !== $tel_input) {
            $_SESSION['login_error'] = 'Credenciales incorrectas.';
            return;
        }

        // Verificar contraseña con hash
        if (!password_verify($password, $empleado['password'])) {
            $_SESSION['login_error'] = 'Contraseña incorrecta.';
            return;
        }

        $nombre_completo = trim($empleado['nombre'] . ' ' . ($empleado['apellido'] ?? ''));
        $iniciales = strtoupper(
            substr($empleado['nombre'], 0, 1) .
            substr($empleado['apellido'] ?? 'X', 0, 1)
        );

        $_SESSION['usuario_id']        = $empleado['id_empleado'];
        $_SESSION['usuario_nombre']    = $nombre_completo;
        $_SESSION['usuario_correo']    = $empleado['correo'];
        $_SESSION['usuario_rol']       = 'mecanico';
        $_SESSION['usuario_puesto']    = $empleado['puesto'] ?? 'Mecánico';
        $_SESSION['usuario_iniciales'] = $iniciales;
        $_SESSION['empleado_id']       = $empleado['id_empleado'];

        header('Location: /Mecanico/views/empleado/index.php');
        exit;

    } catch (PDOException $e) {
        $_SESSION['login_error'] = 'Error de conexión. Intenta de nuevo.';
    }
}

// ------------------------------------------------------------
// Proteger páginas
// ------------------------------------------------------------
function requerir_sesion(): void {
    if (empty($_SESSION['usuario_rol'])) {
        header('Location: /Mecanico/views/Administrador/login.php');
        exit;
    }
}

function requerir_admin(): void {
    requerir_sesion();
    if ($_SESSION['usuario_rol'] !== 'admin') {
        // Si es mecánico lo manda a su panel, si no tiene sesión ya fue al login
        header('Location: /Mecanico/views/empleado/index.php');
        exit;
    }
}

function requerir_mecanico(): void {
    requerir_sesion();
    if ($_SESSION['usuario_rol'] !== 'mecanico') {
        // ✅ Corregido: ya no manda al admin, manda al login
        header('Location: /Mecanico/views/Administrador/login.php');
        exit;
    }
}

// ------------------------------------------------------------
// Cerrar sesión
// ------------------------------------------------------------
function cerrar_sesion(): void {
    session_destroy();
    header('Location: /Mecanico/views/Administrador/login.php');
    exit;
}

// ------------------------------------------------------------
// Helpers de sesión
// ------------------------------------------------------------
function sesion_nombre(): string    { return $_SESSION['usuario_nombre']    ?? 'Usuario'; }
function sesion_iniciales(): string { return $_SESSION['usuario_iniciales'] ?? 'U'; }
function sesion_rol(): string       { return $_SESSION['usuario_rol']       ?? ''; }
function sesion_id(): int           { return (int)($_SESSION['usuario_id']  ?? 0); }
function sesion_empleado_id(): int  { return (int)($_SESSION['empleado_id'] ?? 0); }
function es_admin(): bool           { return sesion_rol() === 'admin'; }