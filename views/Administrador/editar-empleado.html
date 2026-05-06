<?php
require_once '../../php/db_conexion.php';

$id = intval($_GET['id'] ?? 0);
$mensaje = '';
$tipo_mensaje = '';

// Obtener datos del empleado
$emp = null;
try {
    $stmt = $conexion->prepare("SELECT * FROM empleados WHERE id_empleado = :id");
    $stmt->execute([':id' => $id]);
    $emp = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $emp = null;
}

if (!$emp) {
    die("Empleado no encontrado.");
}

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
    try {
        if (!empty($emp['foto']) && file_exists('../../uploads/empleados/' . $emp['foto'])) {
            unlink('../../uploads/empleados/' . $emp['foto']);
        }
        if (!empty($emp['documento']) && file_exists('../../uploads/empleados/' . $emp['documento'])) {
            unlink('../../uploads/empleados/' . $emp['documento']);
        }
        $stmt = $conexion->prepare("DELETE FROM empleados WHERE id_empleado = :id");
        $stmt->execute([':id' => $id]);
        header("Location: informacion-empleados.php?eliminado=1");
        exit;
    } catch (PDOException $e) {
        $mensaje = 'Error al eliminar: ' . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['eliminar'])) {
    $nombre        = trim($_POST['nombre'] ?? '');
    $apellido      = trim($_POST['apellido'] ?? '');
    $correo        = trim($_POST['correo'] ?? '');
    $telefono      = trim($_POST['telefono'] ?? '');
    $cargo         = trim($_POST['cargo'] ?? '');
    $fecha_ingreso = $_POST['fecha_ingreso'] ?? '';

    // --- Subida de nueva foto ---
    $foto_nombre = $emp['foto']; // mantiene la actual por defecto
    if (!empty($_FILES['foto']['name'])) {
        $ext_foto = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg','jpeg','png','gif'];
        if (in_array($ext_foto, $permitidas) && $_FILES['foto']['size'] <= 5 * 1024 * 1024) {
            $nueva_foto = 'foto_' . uniqid() . '.' . $ext_foto;
            $destino = '../../uploads/empleados/' . $nueva_foto;
            if (!is_dir('../../uploads/empleados/')) mkdir('../../uploads/empleados/', 0755, true);
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                // Borrar foto anterior si existía
                if (!empty($emp['foto']) && file_exists('../../uploads/empleados/' . $emp['foto'])) {
                    unlink('../../uploads/empleados/' . $emp['foto']);
                }
                $foto_nombre = $nueva_foto;
            }
        }
    }

    // --- Subida de nuevo documento ---
    $doc_nombre = $emp['documento']; // mantiene el actual por defecto
    if (!empty($_FILES['documento']['name'])) {
        $ext_doc = strtolower(pathinfo($_FILES['documento']['name'], PATHINFO_EXTENSION));
        $nuevo_doc = 'doc_' . uniqid() . '.' . $ext_doc;
        $destino_doc = '../../uploads/empleados/' . $nuevo_doc;
        if (!is_dir('../../uploads/empleados/')) mkdir('../../uploads/empleados/', 0755, true);
        if (move_uploaded_file($_FILES['documento']['tmp_name'], $destino_doc)) {
            if (!empty($emp['documento']) && file_exists('../../uploads/empleados/' . $emp['documento'])) {
                unlink('../../uploads/empleados/' . $emp['documento']);
            }
            $doc_nombre = $nuevo_doc;
        }
    }

    // --- Actualizar en BD ---
    try {
        $sql = "UPDATE empleados SET
                    nombre        = :nombre,
                    apellido      = :apellido,
                    correo        = :correo,
                    telefono      = :telefono,
                    puesto        = :cargo,
                    fecha_ingreso = :fecha_ingreso,
                    foto          = :foto,
                    documento     = :documento
                WHERE id_empleado = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':nombre'        => $nombre,
            ':apellido'      => $apellido,
            ':correo'        => $correo,
            ':telefono'      => $telefono,
            ':cargo'         => $cargo,
            ':fecha_ingreso' => $fecha_ingreso,
            ':foto'          => $foto_nombre,
            ':documento'     => $doc_nombre,
            ':id'            => $id,
        ]);
        $mensaje = '¡Empleado actualizado exitosamente!';
        $tipo_mensaje = 'exito';
        // Recargar datos actualizados
        $stmt2 = $conexion->prepare("SELECT * FROM empleados WHERE id_empleado = :id");
        $stmt2->execute([':id' => $id]);
        $emp = $stmt2->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $mensaje = 'Error al actualizar: ' . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}

$foto_actual = !empty($emp['foto']) ? '../../uploads/empleados/' . $emp['foto'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empleado - Auto Master</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/estilos-generales.css">
    <style>
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
        .toast.exito { background: #22c55e; color: #fff; }
        .toast.error { background: #ef4444; color: #fff; }
        .toast i { font-size: 20px; }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(60px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes fadeOut {
            to { opacity: 0; transform: translateX(60px); }
        }

        #preview-foto {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin: 12px auto 0;
            display: block;
            border: 3px solid var(--color-primario, #e53935);
        }
    </style>
</head>
<body>
<div class="contenedor">

    <!-- SIDEBAR -->
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
                <h2>Editar Empleado</h2>
                <p>Modifica la información del empleado en el sistema.</p>
            </div>

            <div class="tarjeta">
                <div class="tarjeta-header">
                    <h3>Ficha de Empleado</h3>
                </div>
                <div class="tarjeta-body">
                    <form method="POST" action="editar-empleado.php?id=<?= $id ?>" enctype="multipart/form-data">
                        <div style="display: grid; gap: 20px;">
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                                <div class="form-grupo">
                                    <label>Nombre *</label>
                                    <input type="text" name="nombre" placeholder="Nombre"
                                           value="<?= htmlspecialchars($emp['nombre'] ?? '') ?>" required>
                                </div>
                                <div class="form-grupo">
                                    <label>Apellido *</label>
                                    <input type="text" name="apellido" placeholder="Apellido"
                                           value="<?= htmlspecialchars($emp['apellido'] ?? '') ?>" required>
                                </div>
                                <div class="form-grupo">
                                    <label>Correo Electrónico *</label>
                                    <input type="email" name="correo" placeholder="correo@empresa.com"
                                           value="<?= htmlspecialchars($emp['correo'] ?? '') ?>" required>
                                </div>
                                <div class="form-grupo">
                                    <label>Teléfono *</label>
                                    <input type="tel" name="telefono" placeholder="(000) 000-0000"
                                           value="<?= htmlspecialchars($emp['telefono'] ?? '') ?>">
                                </div>
                            </div>

                            <div style="margin-top: 24px;">
                                <h3 style="font-size: 16px; margin-bottom: 16px;">Detalles del Cargo</h3>
                                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                                    <div class="form-grupo">
                                        <label>Cargo / Puesto *</label>
                                        <input type="text" name="cargo" placeholder="Mecánico, Administrador, Recepción"
                                               value="<?= htmlspecialchars($emp['puesto'] ?? $emp['cargo'] ?? '') ?>">
                                    </div>
                                    <div class="form-grupo">
                                        <label>Fecha de Ingreso *</label>
                                        <input type="date" name="fecha_ingreso"
                                               value="<?= htmlspecialchars($emp['fecha_ingreso'] ?? '') ?>">
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
                                            <input type="file" id="input-foto" name="foto" accept="image/*" style="display:none">
                                            <!-- Preview: muestra foto actual o la nueva seleccionada -->
                                            <img id="preview-foto"
                                                 src="<?= $foto_actual ?? '' ?>"
                                                 alt="Foto actual"
                                                 style="<?= $foto_actual ? 'display:block;' : 'display:none;' ?>">
                                            <button type="button" class="btn btn-secundario" style="margin-top: 16px;"
                                                    onclick="event.stopPropagation(); document.getElementById('input-foto').click()">
                                                <?= $foto_actual ? 'Cambiar Foto' : 'Seleccionar Archivo' ?>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-grupo">
                                        <label>Documento de Identidad</label>
                                        <?php if (!empty($emp['documento'])): ?>
                                            <p style="font-size:13px; color: var(--color-gris); margin-bottom:8px;">
                                                <i class="fas fa-file"></i>
                                                Documento actual: <strong><?= htmlspecialchars($emp['documento']) ?></strong>
                                            </p>
                                        <?php endif; ?>
                                        <input type="file" name="documento">
                                    </div>
                                </div>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                                <button type="button" onclick="document.getElementById('modal-eliminar').style.display='flex'"
                                        style="background:#fee2e2; color:#dc2626; border:none; border-radius:10px; padding:10px 20px; font-size:14px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:8px;">
                                    <i class="fas fa-trash"></i> Eliminar Empleado
                                </button>
                                <div style="display:flex; gap:12px;">
                                    <a href="informacion-empleados.php" class="btn btn-secundario">Cancelar</a>
                                    <button type="submit" class="btn btn-primario">Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Modal confirmación eliminar -->
                    <div id="modal-eliminar" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9998; align-items:center; justify-content:center;">
                        <div style="background:#fff; border-radius:20px; padding:36px 32px; max-width:400px; width:90%; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
                            <div style="width:64px; height:64px; background:#fee2e2; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                                <i class="fas fa-trash" style="font-size:26px; color:#dc2626;"></i>
                            </div>
                            <h3 style="margin:0 0 8px; font-size:20px; color:#111;">¿Eliminar empleado?</h3>
                            <p style="color:#888; font-size:14px; margin:0 0 28px;">
                                Esta acción no se puede deshacer. Se eliminará a
                                <strong style="color:#333;"><?= htmlspecialchars(($emp['nombre'] ?? '') . ' ' . ($emp['apellido'] ?? '')) ?></strong>
                                del sistema permanentemente.
                            </p>
                            <div style="display:flex; gap:12px; justify-content:center;">
                                <button onclick="document.getElementById('modal-eliminar').style.display='none'"
                                        style="padding:10px 24px; border-radius:10px; border:2px solid #e5e7eb; background:#fff; font-size:14px; font-weight:600; cursor:pointer; color:#555;">
                                    Cancelar
                                </button>
                                <form method="POST" action="editar-empleado.php?id=<?= $id ?>" style="margin:0;">
                                    <button type="submit" name="eliminar" value="1"
                                            style="padding:10px 24px; border-radius:10px; border:none; background:#dc2626; color:#fff; font-size:14px; font-weight:600; cursor:pointer;">
                                        Sí, eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- TOAST -->
<?php if ($mensaje): ?>
<div class="toast <?= $tipo_mensaje ?>">
    <i class="fas <?= $tipo_mensaje === 'exito' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
    <?= htmlspecialchars($mensaje) ?>
</div>
<?php endif; ?>

<script src="../../js/menu.js"></script>
<script>
    // Preview al seleccionar nueva foto
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

    // Drag & drop
    const dropZone = document.getElementById('drop-zone');
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.style.borderColor = '#e53935'; });
    dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = ''; });
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