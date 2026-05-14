<?php
session_start();

// Datos del Administrador Fijo
define('ADMIN_CORREO',   'admin@automaster.com');
define('ADMIN_PASSWORD', 'admin123');

function procesar_login(PDO $conexion): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

    $correo   = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$correo || !$password) {
        $_SESSION['login_error'] = 'Por favor, completa todos los campos.';
        return;
    }

    // 1. Verificar si es Administrador
    if ($correo === ADMIN_CORREO && $password === ADMIN_PASSWORD) {
        $_SESSION['usuario_id']        = 0;
        $_SESSION['usuario_nombre']    = 'Administrador';
        $_SESSION['usuario_rol']       = 'admin';
        $_SESSION['usuario_iniciales'] = 'AD';
        header('Location: /Mecanico/views/Administrador/Index.php');
        exit;
    }

    // 2. Verificar si es Empleado en la BD
    try {
        $stmt = $conexion->prepare("SELECT id_empleado, nombre, apellido, correo, password, puesto FROM empleados WHERE correo = :correo LIMIT 1");
        $stmt->execute([':correo' => $correo]);
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($empleado) {
            if (password_verify($password, $empleado['password']) || $password === $empleado['password']) {
                
                $_SESSION['usuario_id']        = $empleado['id_empleado'];
                $_SESSION['usuario_nombre']    = $empleado['nombre'] . ' ' . $empleado['apellido'];
                $_SESSION['usuario_rol']       = 'mecanico';
                $_SESSION['usuario_puesto']    = $empleado['puesto'] ?? 'Mecánico';
                $_SESSION['usuario_iniciales'] = strtoupper(substr($empleado['nombre'], 0, 1) . substr($empleado['apellido'], 0, 1));
                
                header('Location: /Mecanico/views/empleado/index.php?p=panel');
                exit;
            }
        }
        $_SESSION['login_error'] = 'Correo o contraseña incorrectos.';
    } catch (PDOException $e) {
        $_SESSION['login_error'] = 'Error de conexión.';
    }
}

// Funciones de ayuda para tus vistas
function requerir_mecanico(): void {
    if (empty($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'mecanico') {
        header('Location: /Mecanico/index.php');
        exit;
    }
}

function sesion_nombre(): string    { return $_SESSION['usuario_nombre']    ?? 'Usuario'; }
function sesion_iniciales(): string { return $_SESSION['usuario_iniciales'] ?? 'U'; }
function sesion_id_empleado(): int  { return (int)($_SESSION['usuario_id']  ?? 0); }
function cerrar_sesion(): void {
    session_destroy();
    header('Location: /Mecanico/index.php');
    exit;
}