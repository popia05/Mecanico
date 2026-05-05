<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Empleado - Auto Master</title>
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
                    <a href="informacion-admin.php" class="nav-item">
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
                    <span>Notas de Remisión</span>
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
                <div class="cabecera-acciones">
                    <button><i class="fas fa-search"></i></button>
                    <button><i class="fas fa-bell"></i></button>
                    <button><i class="fas fa-question-circle"></i></button>
                </div>
            </header>

            <div class="pagina">
                <div class="pagina-titulo">
                    <h2>Agregar Nuevo Empleado</h2>
                    <p>Complete la información necesaria para dar de alta a un nuevo integrante en el sistema.</p>
                </div>

                <div class="tarjeta">
                    <div class="tarjeta-header">
                        <h3>Ficha de Ingreso</h3>
                    </div>
                    <div class="tarjeta-body">
                        <div style="display: grid; gap: 20px;">
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                                <div class="form-grupo">
                                    <label>Nombre *</label>
                                    <input type="text" placeholder="Nombre">
                                </div>
                                <div class="form-grupo">
                                    <label>Apellido *</label>
                                    <input type="text" placeholder="Apellido">
                                </div>
                                <div class="form-grupo">
                                    <label>Correo Electrónico *</label>
                                    <input type="email" placeholder="correo@empresa.com">
                                </div>
                                <div class="form-grupo">
                                    <label>Teléfono *</label>
                                    <input type="tel" placeholder="(000) 000-0000">
                                </div>
                            </div>

                            <div style="margin-top: 24px;">
                                <h3 style="font-size: 16px; margin-bottom: 16px;">Detalles del Cargo</h3>
                                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                                    <div class="form-grupo">
                                        <label>Cargo / Puesto *</label>
                                        <input type="text" placeholder="Mecánico, Administrador, Recepción">
                                    </div>
                                    <div class="form-grupo">
                                        <label>Fecha de Ingreso *</label>
                                        <input type="date">
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top: 24px;">
                                <h3 style="font-size: 16px; margin-bottom: 16px;">Documentación y Multimedia</h3>
                                <div style="display: grid; grid-template-columns: 1fr 270px; gap: 20px; align-items: start;">
                                    <div class="form-grupo">
                                        <label>Fotografía del Empleado</label>
                                        <div style="border: 2px dashed var(--borde); border-radius: 14px; padding: 24px; background: var(--fondo); text-align: center;">
                                            <i class="fas fa-cloud-upload-alt" style="font-size: 32px; color: var(--color-gris);"></i>
                                            <p style="margin: 18px 0 6px; color: var(--color-gris);">Haga clic o arrastre su foto aquí</p>
                                            <p style="font-size: 13px; color: var(--color-gris);">PNG, JPG o GIF. Tamaño máximo de 5MB. Recomendado: 400x400px.</p>
                                            <button class="btn btn-secundario" style="margin-top: 16px;">Seleccionar Archivo</button>
                                        </div>
                                    </div>
                                    <div class="form-grupo">
                                        <label>Documento de Identidad</label>
                                        <input type="file">
                                    </div>
                                </div>
                            </div>

                            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                                <button class="btn btn-secundario">Cancelar</button>
                                <button class="btn btn-primario">Guardar Empleado</button>
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