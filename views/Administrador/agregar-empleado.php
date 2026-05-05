<?php
require_once '../../php/db_conexion.php';

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre       = trim($_POST['nombre'] ?? '');
    $apellido     = trim($_POST['apellido'] ?? '');
    $correo       = trim($_POST['correo'] ?? '');
    $telefono     = trim($_POST['telefono'] ?? '');
    $cargo        = trim($_POST['cargo'] ?? '');
    $fecha_ingreso = $_POST['fecha_ingreso'] ?? '';

    // --- Subida de foto ---
    $foto_nombre = null;
    if (!empty($_FILES['foto']['name'])) {
        $ext_foto = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg','jpeg','png','gif'];
        if (in_array($ext_foto, $permitidas) && $_FILES['foto']['size'] <= 5 * 1024 * 1024) {
            $foto_nombre = 'foto_' . uniqid() . '.' . $ext_foto;
            $destino = '../../uploads/empleados/' . $foto_nombre;
            if (!is_dir('../../uploads/empleados/')) mkdir('../../uploads/empleados/', 0755, true);
            move_uploaded_file($_FILES['foto']['tmp_name'], $destino);
        }
    }

    // --- Subida de documento ---
    $doc_nombre = null;
    if (!empty($_FILES['documento']['name'])) {
        $ext_doc = strtolower(pathinfo($_FILES['documento']['name'], PATHINFO_EXTENSION));
        $doc_nombre = 'doc_' . uniqid() . '.' . $ext_doc;
        $destino_doc = '../../uploads/empleados/' . $doc_nombre;
        if (!is_dir('../../uploads/empleados/')) mkdir('../../uploads/empleados/', 0755, true);
        move_uploaded_file($_FILES['documento']['tmp_name'], $destino_doc);
    }

    // --- Insertar en BD ---
    try {
        $sql = "INSERT INTO empleados (nombre, apellido, correo, telefono, puesto, fecha_ingreso, foto, documento)
        VALUES (:nombre, :apellido, :correo, :telefono, :cargo, :fecha_ingreso, :foto, :documento)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':nombre'       => $nombre,
            ':apellido'     => $apellido,
            ':correo'       => $correo,
            ':telefono'     => $telefono,
            ':cargo'        => $cargo,
            ':fecha_ingreso'=> $fecha_ingreso,
            ':foto'         => $foto_nombre,
            ':documento'    => $doc_nombre,
        ]);
        $mensaje = '¡Empleado guardado exitosamente!';
        $tipo_mensaje = 'exito';
    } catch (PDOException $e) {
        $mensaje = 'Error al guardar: ' . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Empleado - Auto Master</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/estilos-generales.css">
    <style>
        /* Toast de notificación */
        .toast {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            animation: slideIn 0.4s ease, fadeOut 0.5s ease 3.5s forwards;
            pointer-events: none;
        }
        .toast.exito  { background: #22c55e; color: #fff; }
        .toast.error  { background: #ef4444; color: #fff; }
        .toast i { font-size: 20px; }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(60px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes fadeOut {
            to { opacity: 0; transform: translateX(60px); }
        }

        /* Preview de foto */
        #preview-foto {
            width: 100px; height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin: 12px auto 0;
            display: none;
            border: 3px solid var(--color-primario, #e53935);
        }
    </style>
</head>
<body>
<div class="contenedor">

    <!-- SIDEBAR (sin cambios) -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="sidebar-logo-img"><i class="fas fa-wrench"></i></div>
            <div class="sidebar-logo-texto"><h2>Menu</h2><span>Categorias</span></div>
        </div>
        <nav class="sidebar-nav">
            <a href="panel.php" class="nav-item"><i class="fas fa-th-large"></i><span>Panel</span></a>
            <div class="nav-item submenu-toggle" onclick="toggleSubmenu('perfil')">
                <i class="fas fa-user"></i><span>Usuario</span>
                <i class="fas fa-chevron-down flecha" id="flecha-perfil"></i>
            </div>
            <div class="submenu" id="submenu-perfil">
                <a href="informacion-admin.php" class="nav-item"><i class="fas fa-info-circle"></i><span>Administrador</span></a>
                <a href="informacion-empleados.php" class="nav-item"><i class="fas fa-info-circle"></i><span>Empleados</span></a>
                <a href="agregar-empleado.php" class="nav-item"><i class="fas fa-user-plus"></i><span>Agregar Empleados</span></a>
            </div>
            <div class="nav-item submenu-toggle" onclick="toggleSubmenu('tareas')">
                <i class="fas fa-tasks"></i><span>Gestion de Tareas</span>
                <i class="fas fa-chevron-down flecha" id="flecha-tareas"></i>
            </div>
            <div class="submenu" id="submenu-tareas">
                <a href="gestion-ordenes.php" class="nav-item"><i class="fas fa-info-circle"></i><span>Gestion de Ordenes</span></a>
                <a href="inventario.php" class="nav-item"><i class="fas fa-wrench"></i><span>Ver Inventario</span></a>
                <a href="auditoria.php" class="nav-item"><i class="fas fa-shield-alt"></i><span>Auditoria</span></a>
            </div>
            <a href="nota-remision.php" class="nav-item"><i class="fas fa-file-invoice"></i><span>Notas de Remisión</span></a>
            <a href="login.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Cerrar Sesión</span></a>
        </nav>
        <div class="sidebar-usuario">
            <div class="sidebar-usuario-avatar">DG</div>
            <div class="sidebar-usuario-info"><h4>Daniel G.</h4><span>Administrador</span></div>
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
                <div class="tarjeta-header"><h3>Ficha de Ingreso</h3></div>
                <div class="tarjeta-body">

                    <!-- EL FORM apunta a sí mismo con enctype para archivos -->
                    <form method="POST" action="agregar-empleado.php" enctype="multipart/form-data">
                        <div style="display: grid; gap: 20px;">
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                                <div class="form-grupo">
                                    <label>Nombre *</label>
                                    <input type="text" name="nombre" placeholder="Nombre" required>
                                </div>
                                <div class="form-grupo">
                                    <label>Apellido *</label>
                                    <input type="text" name="apellido" placeholder="Apellido" required>
                                </div>
                                <div class="form-grupo">
                                    <label>Correo Electrónico *</label>
                                    <input type="email" name="correo" placeholder="correo@empresa.com" required>
                                </div>
                                <div class="form-grupo">
                                    <label>Teléfono *</label>
                                    <input type="tel" name="telefono" placeholder="(000) 000-0000">
                                </div>
                            </div>

                            <div style="margin-top: 24px;">
                                <h3 style="font-size: 16px; margin-bottom: 16px;">Detalles del Cargo</h3>
                                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                                    <div class="form-grupo">
                                        <label>Cargo / Puesto *</label>
                                        <input type="text" name="cargo" placeholder="Mecánico, Administrador, Recepción">
                                    </div>
                                    <div class="form-grupo">
                                        <label>Fecha de Ingreso *</label>
                                        <input type="date" name="fecha_ingreso">
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top: 24px;">
                                <h3 style="font-size: 16px; margin-bottom: 16px;">Documentación y Multimedia</h3>
                                <div style="display: grid; grid-template-columns: 1fr 270px; gap: 20px; align-items: start;">
                                    <div class="form-grupo">
                                        <label>Fotografía del Empleado</label>
                                        <div id="drop-zone" style="border: 2px dashed var(--borde); border-radius: 14px; padding: 24px; background: var(--fondo); text-align: center; cursor: pointer;"
                                             onclick="document.getElementById('input-foto').click()">
                                            <i class="fas fa-cloud-upload-alt" style="font-size: 32px; color: var(--color-gris);"></i>
                                            <p style="margin: 18px 0 6px; color: var(--color-gris);">Haga clic o arrastre su foto aquí</p>
                                            <p style="font-size: 13px; color: var(--color-gris);">PNG, JPG o GIF. Tamaño máximo de 5MB. Recomendado: 400x400px.</p>
                                            <!-- Input oculto -->
                                            <input type="file" id="input-foto" name="foto" accept="image/*" style="display:none">
                                            <img id="preview-foto" src="" alt="Preview">
                                            <button type="button" class="btn btn-secundario" style="margin-top: 16px;"
                                                    onclick="event.stopPropagation(); document.getElementById('input-foto').click()">
                                                Seleccionar Archivo
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-grupo">
                                        <label>Documento de Identidad</label>
                                        <input type="file" name="documento">
                                    </div>
                                </div>
                            </div>

                            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                                <a href="informacion-empleados.php" class="btn btn-secundario">Cancelar</a>
                                <button type="submit" class="btn btn-primario">Guardar Empleado</button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </main>
</div>

<!-- TOAST de notificación -->
<?php if ($mensaje): ?>
<div class="toast <?= $tipo_mensaje ?>">
    <i class="fas <?= $tipo_mensaje === 'exito' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
    <?= htmlspecialchars($mensaje) ?>
</div>
<?php endif; ?>

<script src="../../js/menu.js"></script>
<script>
    // Preview de foto al seleccionar
    document.getElementById('input-foto').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById('preview-foto');
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });

    // Drag & drop sobre la zona
    const dropZone = document.getElementById('drop-zone');
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.style.borderColor = '#e53935'; });
    dropZone.addEventListener('dragleave', ()=> { dropZone.style.borderColor = ''; });
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.style.borderColor = '';
        const input = document.getElementById('input-foto');
        input.files = e.dataTransfer.files;
        input.dispatchEvent(new Event('change'));
    });
</script>
</body>
</html>