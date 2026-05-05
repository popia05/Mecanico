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
                    <a href="informacion.php" class="nav-item activo">
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
                    <span>Gestion de Tareas</span>
                    <i class="fas fa-chevron-down flecha" id="flecha-tareas"></i>
                </div>

                <div class="submenu" id="submenu-tareas">
                    <a href="gestion-ordenes.php" class="nav-item">
                        <i class="fas fa-info-circle"></i>
                        <span>Gestion de Ordenes</span>
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

                <a href="nota-remision.php" class="nav-item">
                    <i class="fas fa-file-invoice"></i>
                    <span>Nota De Remision</span>
                </a>

                <a href="cerrar-sesion.php" class="nav-item">
                    <i class="fas fa-cog"></i>
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
                
                <div class="cabecera-acciones">
                    <button><i class="fas fa-search"></i></button>
                    <button><i class="fas fa-bell"></i></button>
                    <button><i class="fas fa-question-circle"></i></button>
                </div>
            </header>

            <div class="pagina">
                <div class="pagina-titulo">
                    <h2>Detalles de Administrador</h2>
                </div>

                <div class="tarjeta">
                    <div class="tarjeta-header">
                        <h3>Perfil de Administrador</h3>
                        <button class="btn btn-primario">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                    </div>
                    <div class="tarjeta-body">
                        <div style="display: flex; gap: 30px; align-items: flex-start;">
                            <div style="text-align: center;">
                                <div style="width: 120px; height: 120px; background: var(--rosa); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 40px; font-weight: bold; margin-bottom: 10px;">
                                    DG
                                </div>
                                <h3 style="font-size: 18px;">Daniel Garcia Olivas</h3>
                                    <input type="text" value="Jefe de Taller">
                            </div>
                            <div style="flex: 1; display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                                <div class="form-grupo">
                                    <label>Status</label>
                                    <input type="text" value="Activo" readonly style="background: var(--fondo);">
                                </div>
                                <div class="form-grupo">
                                    <label>Compañía</label>
                                    <input type="text" value="Auto Master" readonly style="background: var(--fondo);">
                                </div>
                                <div class="form-grupo">
                                    <label>Email</label>
                                    <input type="email" value="daniel@automaster.com" readonly style="background: var(--fondo);">
                                </div>
                                <div class="form-grupo">
                                    <label>Numero de Celular</label>
                                    <input type="text" value="(430) 065-7387" readonly style="background: var(--fondo);">
                                </div>
                                <div class="form-grupo">
                                    <label>Dirección</label>
                                    <input type="text" value="Agua Prieta, Sonora, Mx." readonly style="background: var(--fondo);">
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>

    </div>

    <script src="../../js/menu.js"></script>
</body>
</html>