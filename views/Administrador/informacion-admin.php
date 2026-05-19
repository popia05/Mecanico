<?php
session_start();

$adminProfile = $_SESSION['admin_profile'] ?? [
    'puesto'    => 'Jefe de Taller',
    'status'    => 'Activo',
    'compania'  => 'Auto Master',
    'email'     => 'daniel@automaster.com',
    'celular'   => '(430) 065-7387',
    'direccion' => 'Agua Prieta, Sonora, Mx.',
];

$mensajeExito = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_admin'])) {
    $adminProfile = [
        'puesto'    => trim($_POST['puesto'] ?? $adminProfile['puesto']),
        'status'    => trim($_POST['status'] ?? $adminProfile['status']),
        'compania'  => trim($_POST['compania'] ?? $adminProfile['compania']),
        'email'     => trim($_POST['email'] ?? $adminProfile['email']),
        'celular'   => trim($_POST['celular'] ?? $adminProfile['celular']),
        'direccion' => trim($_POST['direccion'] ?? $adminProfile['direccion']),
    ];

    $_SESSION['admin_profile'] = $adminProfile;
    $mensajeExito = 'Cambios guardados exitosamente';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informacion de Administrador - Auto Master</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/estilos-generales.css">
</head>
<body>

    <div class="contenedor">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <div class="sidebar-logo-img">
                    <i class="fas fa-wrench"></i>
                </div>
                <div class="sidebar-logo-texto">
                    <h2>Menu</h2>
                    <span>Categorias</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="panel.php" class="nav-item">
                    <i class="fas fa-th-large"></i>
                    <span>Panel</span>
                </a>

                <div class="nav-item submenu-toggle" onclick="toggleSubmenu('perfil')">
                    <i class="fas fa-user"></i>
                    <span>Usuario</span>
                    <i class="fas fa-chevron-down flecha" id="flecha-perfil"></i>
                </div>
                <div class="submenu" id="submenu-perfil">
                    <a href="informacion-admin.php" class="nav-item activo">
                        <i class="fas fa-info-circle"></i>
                        <span>Administrador</span>
                    </a>
                    <a href="informacion-empleados.php" class="nav-item">
                        <i class="fas fa-info-circle"></i>
                        <span>Empleados</span>
                    </a>
                    <a href="agregar-empleado.php" class="nav-item">
                        <i class="fas fa-user-plus"></i>
                        <span>Agregar Empleados</span>
                    </a>
                </div>

                <div class="nav-item submenu-toggle" onclick="toggleSubmenu('tareas')">
                    <i class="fas fa-tasks"></i>
                    <span>Gestión de Tareas</span>
                    <i class="fas fa-chevron-down flecha" id="flecha-tareas"></i>
                </div>

                <div class="submenu" id="submenu-tareas">
                    <a href="gestion-ordenes.php" class="nav-item">
                        <i class="fas fa-info-circle"></i>
                        <span>Gestión de Ordenes</span>
                    </a>
                    <a href="inventario.php" class="nav-item">
                        <i class="fas fa-wrench"></i>
                        <span>Ver Inventario</span>
                    </a>
                    <a href="auditoria.php" class="nav-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Auditoria</span>
                    </a>
                </div>

                <div class="nav-item submenu-toggle" onclick="toggleSubmenu('clientes')">
                <i class="fas fa-users"></i><span>Clientes</span>
                <i class="fas fa-chevron-down flecha" id="flecha-clientes"></i>
            </div>
            <div class="submenu" id="submenu-clientes">
                <a href="informacion-clientes.php" class="nav-item"><i class="fas fa-address-card"></i><span>Ver Clientes</span></a>
            </div>

                <a href="nota-remision.php" class="nav-item">
                    <i class="fas fa-file-invoice"></i>
                    <span>Notas de Remisión</span>
                </a>
                <a href="respaldo.php" class="nav-item">
                    <i class="fas fa-database"></i>
                    <span>Respaldo</span>
                </a>
                <a href="login.php" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Cerrar Sesión</span>
                </a>
            </nav>

            <div class="sidebar-usuario">
                <div class="sidebar-usuario-avatar">DG</div>
                <div class="sidebar-usuario-info">
                    <h4>Daniel G.</h4>
                    <span>Administrador</span>
                </div>
            </div>
        </aside>

        <!-- CONTENIDO -->
        <main class="contenido">
            <header class="cabecera">
            </header>

            <div class="pagina">
                <div class="pagina-titulo">
                    <h2>Detalles de Administrador</h2>
                </div>

                <div class="tarjeta">
                    <div class="tarjeta-header">
                        <h3>Perfil de Administrador</h3>
                        <button type="button" id="btn-editar" class="btn btn-primario" onclick="activarEdicion()">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                    </div>

                    <?php if ($mensajeExito): ?>
                        <div id="mensaje-exito" style="margin: 0 20px 20px; padding: 14px 18px; border-radius: 10px; background:#e6f8ef; color:#1f6f47; font-weight:600; box-shadow:0 1px 4px rgba(0,0,0,.08);">
                            <?= htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <div class="tarjeta-body">
                        <form method="POST" action="informacion-admin.php">
                            <div style="display: flex; gap: 30px; align-items: flex-start;">
                                <div style="text-align: center;">
                                    <div style="width: 120px; height: 120px; background: var(--rosa); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 40px; font-weight: bold; margin-bottom: 10px;">
                                        DG
                                    </div>
                                    <h3 style="font-size: 18px;">Daniel Garcia Olivas</h3>
                                </div>
                                <div style="flex: 1; display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                                    <div class="form-grupo">
                                        <label>Puesto</label>
                                        <input id="input-puesto" name="puesto" type="text" value="<?= htmlspecialchars($adminProfile['puesto'], ENT_QUOTES, 'UTF-8') ?>" readonly style="background: var(--fondo);">
                                    </div>
                                    <div class="form-grupo">
                                        <label>Status</label>
                                        <input id="input-status" name="status" type="text" value="<?= htmlspecialchars($adminProfile['status'], ENT_QUOTES, 'UTF-8') ?>" readonly style="background: var(--fondo);">
                                    </div>
                                    <div class="form-grupo">
                                        <label>Compañía</label>
                                        <input id="input-compania" name="compania" type="text" value="<?= htmlspecialchars($adminProfile['compania'], ENT_QUOTES, 'UTF-8') ?>" readonly style="background: var(--fondo);">
                                    </div>
                                    <div class="form-grupo">
                                        <label>Email</label>
                                        <input id="input-email" name="email" type="email" value="<?= htmlspecialchars($adminProfile['email'], ENT_QUOTES, 'UTF-8') ?>" readonly style="background: var(--fondo);">
                                    </div>
                                    <div class="form-grupo">
                                        <label>Numero de Celular</label>
                                        <input id="input-celular" name="celular" type="text" value="<?= htmlspecialchars($adminProfile['celular'], ENT_QUOTES, 'UTF-8') ?>" readonly style="background: var(--fondo);">
                                    </div>
                                    <div class="form-grupo">
                                        <label>Dirección</label>
                                        <input id="input-direccion" name="direccion" type="text" value="<?= htmlspecialchars($adminProfile['direccion'], ENT_QUOTES, 'UTF-8') ?>" readonly style="background: var(--fondo);">
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top: 24px; display: none; justify-content: flex-end;" id="guardar-container">
                                <button type="submit" name="guardar_admin" class="btn btn-primario" style="display: inline-flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-save"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </main>

    </div>

    <script>
        function activarEdicion() {
            const inputs = document.querySelectorAll('.tarjeta-body input');
            inputs.forEach(input => {
                input.removeAttribute('readonly');
                input.style.background = '#ffffff';
            });
            document.getElementById('guardar-container').style.display = 'flex';
            document.getElementById('btn-editar').style.display = 'none';
        }
    </script>
    <script src="../../js/menu.js"></script>
</body>
</html>