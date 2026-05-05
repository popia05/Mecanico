<?php
require_once '../../php/db_conexion.php';

$mensaje = '';
$tipo_mensaje = '';

// Guardar nota de remisión
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_nota'])) {
    $id_orden       = intval($_POST['id_orden'] ?? 0);
    $id_cliente     = intval($_POST['id_cliente'] ?? 0);
    $id_servicio    = intval($_POST['id_servicio'] ?? 0) ?: null;
    $fecha_ingreso  = $_POST['fecha_ingreso'] ?? '';
    $fecha_salida   = $_POST['fecha_salida'] ?? '';
    $descripcion    = trim($_POST['descripcion'] ?? '');
    $costo_total    = floatval($_POST['costo_total'] ?? 0);
    $garantia       = trim($_POST['garantia'] ?? '');
    $observaciones  = trim($_POST['observaciones'] ?? '');

    try {
        // Actualizar servicio si existe, o insertar
        if ($id_servicio) {
            $sql = "UPDATE servicios SET descripcion_falla=:d, reparacion_realizada=:r, costo_total=:c, fecha_ingreso=:fi WHERE id_servicio=:id";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':d'=>$descripcion,':r'=>$observaciones,':c'=>$costo_total,':fi'=>$fecha_ingreso,':id'=>$id_servicio]);
        } else {
            // Obtener id_vehiculo y id_empleado de la orden
            $ord = $conexion->prepare("SELECT id_mecanico FROM ordenes WHERE id_orden=:id");
            $ord->execute([':id'=>$id_orden]);
            $orden_data = $ord->fetch(PDO::FETCH_ASSOC);
            $sql = "INSERT INTO servicios (fecha_ingreso, descripcion_falla, reparacion_realizada, costo_total, id_empleado) VALUES (:fi,:d,:r,:c,:e)";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':fi'=>$fecha_ingreso,':d'=>$descripcion,':r'=>$observaciones,':c'=>$costo_total,':e'=>$orden_data['id_mecanico'] ?? null]);
        }
        $mensaje = '¡Nota de remisión guardada!';
        $tipo_mensaje = 'exito';
    } catch (PDOException $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}

// Obtener clientes para el select
$clientes = [];
try {
    $stmt = $conexion->query("SELECT id_cliente, nombre, apellido, placa, marca_carro, modelo_carro FROM clientes ORDER BY nombre ASC");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $clientes = []; }

// Obtener órdenes con mecánico
$ordenes = [];
try {
    $stmt = $conexion->query("
        SELECT o.id_orden, o.vehiculo, o.cliente, o.servicio, o.estado, o.fecha_creacion,
               CONCAT(e.nombre,' ',e.apellido) AS mecanico_nombre
        FROM ordenes o
        LEFT JOIN empleados e ON o.id_mecanico = e.id_empleado
        ORDER BY o.fecha_creacion DESC
    ");
    $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $ordenes = []; }

// Orden seleccionada (si viene por GET)
$orden_sel = null;
$cliente_sel = null;
$servicio_sel = null;

if (isset($_GET['id_orden'])) {
    $id_ord = intval($_GET['id_orden']);
    try {
        $stmt = $conexion->prepare("
            SELECT o.*, CONCAT(e.nombre,' ',e.apellido) AS mecanico_nombre, e.puesto AS mecanico_puesto
            FROM ordenes o
            LEFT JOIN empleados e ON o.id_mecanico = e.id_empleado
            WHERE o.id_orden = :id
        ");
        $stmt->execute([':id' => $id_ord]);
        $orden_sel = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($orden_sel) {
            // Buscar cliente por placa (vehiculo contiene la placa o nombre)
            $stmt2 = $conexion->query("SELECT * FROM clientes LIMIT 1");
            // intentar match por nombre del cliente en la orden
            $stmt2 = $conexion->prepare("SELECT * FROM clientes WHERE CONCAT(nombre,' ',apellido) LIKE :c OR placa LIKE :p LIMIT 1");
            $stmt2->execute([':c' => '%'.$orden_sel['cliente'].'%', ':p' => '%'.$orden_sel['vehiculo'].'%']);
            $cliente_sel = $stmt2->fetch(PDO::FETCH_ASSOC);

            // Buscar servicio asociado al empleado de esta orden
            $stmt3 = $conexion->prepare("SELECT * FROM servicios WHERE id_empleado = :e ORDER BY id_servicio DESC LIMIT 1");
            $stmt3->execute([':e' => $orden_sel['id_mecanico'] ?? 0]);
            $servicio_sel = $stmt3->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) { $orden_sel = null; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota de Remisión - Auto Master</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/estilos-generales.css">
    <style>
        .nota-layout {
            display: grid;
            grid-template-columns: 1fr 260px;
            gap: 24px;
            align-items: start;
        }
        @media (max-width: 768px) { .nota-layout { grid-template-columns: 1fr; } }

        /* ── Selector de orden ── */
        .select-orden {
            background: var(--tarjeta,#fff);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            margin-bottom: 20px;
            display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
        }
        .select-orden .form-grupo { flex: 1; min-width: 200px; margin: 0; }
        .select-style { width:100%; padding:10px 14px; border-radius:10px; border:1.5px solid var(--borde,#e5e7eb); font-size:14px; background:#fff; }

        /* ── Documento nota ── */
        .nota-doc {
            background: var(--tarjeta,#fff);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }

        /* Header empresa */
        .nota-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--borde,#f0f0f0);
        }
        .nota-empresa h3 { font-size: 18px; font-weight: 800; margin: 0 0 4px; color: var(--texto,#111); }
        .nota-empresa p  { font-size: 12px; color: var(--color-gris,#888); margin: 0; }
        .nota-logo {
            width: 64px; height: 64px; border-radius: 12px;
            background: #e05a6e; display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 24px;
        }
        .nota-titulo {
            text-align: center; font-size: 13px; font-weight: 700;
            color: var(--color-gris,#888); letter-spacing: 0.08em;
            text-transform: uppercase; margin-bottom: 20px;
        }

        /* Campos del documento */
        .nota-campo {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--borde,#f5f5f5);
        }
        .nota-campo:last-of-type { border-bottom: none; }
        .nota-campo-icono {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; flex-shrink: 0;
        }
        .nota-campo-icono.verde  { background: #dcfce7; color: #16a34a; }
        .nota-campo-icono.azul   { background: #dbeafe; color: #2563eb; }
        .nota-campo-icono.rosa   { background: #fce7eb; color: #e05a6e; }
        .nota-campo-icono.naranja{ background: #ffedd5; color: #ea580c; }
        .nota-campo-icono.gris   { background: #f3f4f6; color: #6b7280; }

        .nota-campo-contenido { flex: 1; }
        .nota-campo-label { font-size: 11px; color: var(--color-gris,#888); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
        .nota-campo-valor { font-size: 14px; color: var(--texto,#222); font-weight: 500; }
        .nota-campo-input {
            width: 100%; border: none; border-bottom: 1.5px solid var(--borde,#e5e7eb);
            padding: 4px 0; font-size: 14px; background: transparent;
            color: var(--texto,#222); outline: none;
        }
        .nota-campo-input:focus { border-bottom-color: #e05a6e; }
        .nota-campo-textarea {
            width: 100%; border: 1.5px solid var(--borde,#e5e7eb);
            border-radius: 8px; padding: 8px 10px;
            font-size: 13px; background: var(--fondo,#f9f9f9);
            color: var(--texto,#222); outline: none; resize: vertical;
            min-height: 90px; font-family: inherit;
        }
        .nota-campo-textarea:focus { border-color: #e05a6e; }

        /* Panel derecho — total */
        .nota-panel {
            display: flex; flex-direction: column; gap: 16px;
        }
        .panel-estado {
            background: var(--tarjeta,#fff);
            border-radius: 16px; padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            display: flex; flex-direction: column; gap: 12px;
        }
        .panel-estado-item {
            display: flex; align-items: center; justify-content: space-between;
            font-size: 13px; padding: 8px 0;
            border-bottom: 1px solid var(--borde,#f0f0f0);
        }
        .panel-estado-item:last-child { border-bottom: none; }
        .panel-estado-icono { font-size: 20px; }

        .panel-total {
            background: #e05a6e;
            border-radius: 16px; padding: 24px 20px;
            box-shadow: 0 4px 20px rgba(224,90,110,0.35);
            color: #fff; text-align: center;
        }
        .panel-total-label { font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; opacity: 0.85; margin-bottom: 8px; }
        .panel-total-monto { font-size: 36px; font-weight: 800; margin-bottom: 16px; }
        .panel-total-fecha { font-size: 11px; opacity: 0.75; margin-bottom: 16px; }
        .btn-pagar {
            width: 100%; padding: 12px; border-radius: 10px;
            background: #111; color: #fff; border: none;
            font-size: 14px; font-weight: 700; cursor: pointer;
            letter-spacing: 0.05em; transition: background 0.2s;
        }
        .btn-pagar:hover { background: #333; }

        /* Botones pie */
        .nota-footer {
            display: flex; justify-content: flex-end; gap: 12px;
            margin-top: 24px; padding-top: 20px;
            border-top: 1px solid var(--borde,#f0f0f0);
        }

        /* Toast */
        .toast { position:fixed; top:24px; right:24px; z-index:9999; display:flex; align-items:center; gap:12px; padding:16px 24px; border-radius:12px; font-size:15px; font-weight:600; box-shadow:0 8px 32px rgba(0,0,0,0.18); animation:slideIn 0.4s ease, fadeOut 0.5s ease 3.5s forwards; pointer-events:none; }
        .toast.exito { background:#22c55e; color:#fff; }
        .toast.error { background:#ef4444; color:#fff; }
        @keyframes slideIn { from{opacity:0;transform:translateX(60px)} to{opacity:1;transform:translateX(0)} }
        @keyframes fadeOut { to{opacity:0;transform:translateX(60px)} }

        /* Print */
        @media print {
            .sidebar, .cabecera, .select-orden, .nota-footer, .panel-estado, .btn-pagar { display: none !important; }
            .nota-layout { grid-template-columns: 1fr; }
            .panel-total { display: block; }
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
            <div class="nav-item submenu-toggle" onclick="toggleSubmenu('clientes')">
                <i class="fas fa-users"></i><span>Clientes</span>
                <i class="fas fa-chevron-down flecha" id="flecha-clientes"></i>
            </div>
            <div class="submenu" id="submenu-clientes">
                <a href="informacion-clientes.php" class="nav-item"><i class="fas fa-address-card"></i><span>Ver Clientes</span></a>
            </div>
            <a href="nota-remision.php" class="nav-item activo"><i class="fas fa-file-invoice"></i><span>Notas de Remisión</span></a>
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
            <div class="pagina-titulo" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2>Nota de remisión</h2>
                    <p>Genera la nota de servicio a partir de una orden existente.</p>
                </div>
                <button class="btn btn-secundario" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir / PDF
                </button>
            </div>

            <!-- Selector de orden -->
            <div class="select-orden">
                <div class="form-grupo" style="flex:2; margin:0;">
                    <label>Seleccionar Orden de Servicio</label>
                    <select id="sel-orden" class="select-style" onchange="cargarOrden(this.value)">
                        <option value="">-- Elige una orden --</option>
                        <?php foreach ($ordenes as $o): ?>
                        <option value="<?= $o['id_orden'] ?>"
                            <?= (isset($_GET['id_orden']) && $_GET['id_orden'] == $o['id_orden']) ? 'selected' : '' ?>>
                            #<?= $o['id_orden'] ?> — <?= htmlspecialchars($o['vehiculo']) ?> | <?= htmlspecialchars($o['cliente']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-primario" onclick="cargarOrden(document.getElementById('sel-orden').value)" style="white-space:nowrap;">
                    <i class="fas fa-arrow-right"></i> Cargar
                </button>
            </div>

            <?php if ($orden_sel): ?>
            <form method="POST" action="nota-remision.php" id="form-nota">
                <input type="hidden" name="id_orden" value="<?= $orden_sel['id_orden'] ?>">
                <input type="hidden" name="id_cliente" value="<?= $cliente_sel['id_cliente'] ?? 0 ?>">
                <input type="hidden" name="id_servicio" value="<?= $servicio_sel['id_servicio'] ?? 0 ?>">

                <div class="nota-layout">
                    <!-- Columna izquierda: documento -->
                    <div class="nota-doc">
                        <!-- Header empresa -->
                        <div class="nota-header">
                            <div class="nota-empresa">
                                <h3>Auto Master</h3>
                                <p>Fuel Injection · Agua Prieta, Sonora, MX</p>
                                <p style="margin-top:4px; font-size:11px; color:var(--color-gris,#aaa);">Folio #<?= str_pad($orden_sel['id_orden'], 4, '0', STR_PAD_LEFT) ?></p>
                            </div>
                            <div class="nota-logo"><i class="fas fa-wrench"></i></div>
                        </div>

                        <div class="nota-titulo">Nota de Remisión de Servicio</div>

                        <!-- Cliente -->
                        <div class="nota-campo">
                            <div class="nota-campo-icono verde"><i class="fas fa-user"></i></div>
                            <div class="nota-campo-contenido">
                                <div class="nota-campo-label">Cliente</div>
                                <div class="nota-campo-valor">
                                    <?php if ($cliente_sel): ?>
                                        <?= htmlspecialchars($cliente_sel['nombre'] . ' ' . $cliente_sel['apellido']) ?>
                                    <?php else: ?>
                                        <?= htmlspecialchars($orden_sel['cliente']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Placas -->
                        <div class="nota-campo">
                            <div class="nota-campo-icono azul"><i class="fas fa-car"></i></div>
                            <div class="nota-campo-contenido">
                                <div class="nota-campo-label">Vehículo · Placas</div>
                                <div class="nota-campo-valor">
                                    <?= htmlspecialchars($orden_sel['vehiculo']) ?>
                                    <?php if ($cliente_sel && !empty($cliente_sel['placa'])): ?>
                                        · <span style="background:#1e293b; color:#fff; padding:1px 8px; border-radius:4px; font-family:monospace; font-size:12px;"><?= htmlspecialchars($cliente_sel['placa']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Mecánico -->
                        <div class="nota-campo">
                            <div class="nota-campo-icono naranja"><i class="fas fa-user-gear"></i></div>
                            <div class="nota-campo-contenido">
                                <div class="nota-campo-label">Asignación de personal</div>
                                <div class="nota-campo-valor"><?= htmlspecialchars($orden_sel['mecanico_nombre'] ?? 'Sin asignar') ?></div>
                            </div>
                        </div>

                        <!-- Descripción del servicio -->
                        <div class="nota-campo">
                            <div class="nota-campo-icono rosa"><i class="fas fa-pen"></i></div>
                            <div class="nota-campo-contenido">
                                <div class="nota-campo-label">Descripción del servicio</div>
                                <textarea name="descripcion" class="nota-campo-textarea"
                                          placeholder="Describe el servicio realizado, costos parciales, observaciones..."
                                ><?= htmlspecialchars($servicio_sel['descripcion_falla'] ?? $orden_sel['servicio'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Fechas -->
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:8px;">
                            <div class="nota-campo">
                                <div class="nota-campo-icono verde"><i class="fas fa-calendar-check"></i></div>
                                <div class="nota-campo-contenido">
                                    <div class="nota-campo-label">Fecha de Ingreso *</div>
                                    <input type="date" name="fecha_ingreso" class="nota-campo-input"
                                           value="<?= $servicio_sel['fecha_ingreso'] ?? date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div class="nota-campo">
                                <div class="nota-campo-icono gris"><i class="fas fa-calendar-xmark"></i></div>
                                <div class="nota-campo-contenido">
                                    <div class="nota-campo-label">Fecha de Salida</div>
                                    <input type="date" name="fecha_salida" class="nota-campo-input"
                                           value="<?= $_POST['fecha_salida'] ?? '' ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Costo y garantía -->
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:4px;">
                            <div class="nota-campo">
                                <div class="nota-campo-icono verde"><i class="fas fa-dollar-sign"></i></div>
                                <div class="nota-campo-contenido">
                                    <div class="nota-campo-label">Costo Total</div>
                                    <input type="number" name="costo_total" id="inp-costo" class="nota-campo-input"
                                           step="0.01" min="0" placeholder="0.00"
                                           value="<?= $servicio_sel['costo_total'] ?? '' ?>"
                                           oninput="actualizarTotal(this.value)">
                                </div>
                            </div>
                            <div class="nota-campo">
                                <div class="nota-campo-icono azul"><i class="fas fa-shield-halved"></i></div>
                                <div class="nota-campo-contenido">
                                    <div class="nota-campo-label">Garantía</div>
                                    <input type="text" name="garantia" class="nota-campo-input"
                                           placeholder="Ej: 30 días en mano de obra"
                                           value="<?= htmlspecialchars($_POST['garantia'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="nota-footer">
                            <a href="nota-remision.php" class="btn btn-secundario">Cancelar</a>
                            <button type="submit" name="guardar_nota" class="btn btn-primario">
                                <i class="fas fa-floppy-disk"></i> Guardar nota
                            </button>
                        </div>
                    </div>

                    <!-- Columna derecha: panel estado + total -->
                    <div class="nota-panel">
                        <!-- Estado de la orden -->
                        <div class="panel-estado">
                            <p style="font-size:12px; font-weight:700; color:var(--color-gris,#888); text-transform:uppercase; letter-spacing:0.05em; margin:0 0 4px;">Estado de la orden</p>
                            <div class="panel-estado-item">
                                <span>Vehículo registrado</span>
                                <span class="panel-estado-icono" style="color:#16a34a;">✓</span>
                            </div>
                            <div class="panel-estado-item">
                                <span>Mecánico asignado</span>
                                <span class="panel-estado-icono" style="color:<?= $orden_sel['mecanico_nombre'] ? '#16a34a' : '#dc2626' ?>;">
                                    <?= $orden_sel['mecanico_nombre'] ? '✓' : '✗' ?>
                                </span>
                            </div>
                            <div class="panel-estado-item">
                                <span>Descripción del servicio</span>
                                <span class="panel-estado-icono" style="color:#2563eb;">✎</span>
                            </div>
                            <div class="panel-estado-item">
                                <span>Estado: <strong><?= $orden_sel['estado'] ?></strong></span>
                                <?php
                                $ico = ['Pendiente'=>'⏳','En Progreso'=>'🔧','Terminado'=>'✓'];
                                echo '<span class="panel-estado-icono">' . ($ico[$orden_sel['estado']] ?? '?') . '</span>';
                                ?>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="panel-total">
                            <div class="panel-total-label">Total</div>
                            <div class="panel-total-monto" id="total-display">
                                $<?= number_format($servicio_sel['costo_total'] ?? 0, 0, '.', ',') ?>
                            </div>
                            <div class="panel-total-fecha"><?= date('d/m/Y') ?></div>
                            <button type="button" class="btn-pagar">PAGAR</button>
                        </div>
                    </div>
                </div>
            </form>

            <?php else: ?>
            <!-- Estado vacío -->
            <div style="text-align:center; padding:60px 20px; color:var(--color-gris,#888);">
                <i class="fas fa-file-invoice" style="font-size:52px; margin-bottom:16px; display:block; color:#e0e0e0;"></i>
                <p style="font-size:16px; font-weight:600; color:#555;">Selecciona una orden para generar la nota</p>
                <p style="font-size:13px;">Elige una orden del menú desplegable de arriba y haz clic en "Cargar".</p>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php if ($mensaje): ?>
<div class="toast <?= $tipo_mensaje ?>">
    <i class="fas <?= $tipo_mensaje==='exito' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
    <?= htmlspecialchars($mensaje) ?>
</div>
<?php endif; ?>

<script src="../../js/menu.js"></script>
<script>
    function cargarOrden(id) {
        if (!id) return;
        window.location.href = 'nota-remision.php?id_orden=' + id;
    }

    function actualizarTotal(val) {
        const n = parseFloat(val) || 0;
        document.getElementById('total-display').textContent =
            '$' + n.toLocaleString('es-MX', {minimumFractionDigits: 0, maximumFractionDigits: 0});
    }
</script>
</body>
</html>