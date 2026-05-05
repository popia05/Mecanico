<?php
require_once '../../php/db_conexion.php';

$mensaje = '';
$tipo_mensaje = '';

// Guardar cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_cliente'])) {
    $nombre      = trim($_POST['nombre'] ?? '');
    $apellido    = trim($_POST['apellido'] ?? '');
    $telefono    = trim($_POST['telefono'] ?? '');
    $correo      = trim($_POST['correo'] ?? '');
    $placa       = strtoupper(trim($_POST['placa'] ?? ''));
    $marca       = trim($_POST['marca_carro'] ?? '');
    $modelo      = trim($_POST['modelo_carro'] ?? '');
    $anio        = intval($_POST['anio_carro'] ?? 0) ?: null;
    $estatus     = $_POST['estatus'] ?? 'Activo';
    try {
        $sql = "INSERT INTO clientes (nombre, apellido, telefono, correo, placa, marca_carro, modelo_carro, anio_carro, estatus)
                VALUES (:n,:a,:t,:c,:p,:m,:mo,:an,:e)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':n'=>$nombre,':a'=>$apellido,':t'=>$telefono,':c'=>$correo,':p'=>$placa,':m'=>$marca,':mo'=>$modelo,':an'=>$anio,':e'=>$estatus]);
        $mensaje = '¡Cliente agregado exitosamente!';
        $tipo_mensaje = 'exito';
    } catch (PDOException $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}

// Actualizar cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_cliente'])) {
    $id      = intval($_POST['id_cliente'] ?? 0);
    $nombre  = trim($_POST['nombre_e'] ?? '');
    $apellido= trim($_POST['apellido_e'] ?? '');
    $telefono= trim($_POST['telefono_e'] ?? '');
    $correo  = trim($_POST['correo_e'] ?? '');
    $placa   = strtoupper(trim($_POST['placa_e'] ?? ''));
    $marca   = trim($_POST['marca_e'] ?? '');
    $modelo  = trim($_POST['modelo_e'] ?? '');
    $anio    = intval($_POST['anio_e'] ?? 0) ?: null;
    $estatus = $_POST['estatus_e'] ?? 'Activo';
    try {
        $sql = "UPDATE clientes SET nombre=:n,apellido=:a,telefono=:t,correo=:c,placa=:p,marca_carro=:m,modelo_carro=:mo,anio_carro=:an,estatus=:e WHERE id_cliente=:id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':n'=>$nombre,':a'=>$apellido,':t'=>$telefono,':c'=>$correo,':p'=>$placa,':m'=>$marca,':mo'=>$modelo,':an'=>$anio,':e'=>$estatus,':id'=>$id]);
        $mensaje = '¡Cliente actualizado!';
        $tipo_mensaje = 'exito';
    } catch (PDOException $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}

// Obtener clientes
$clientes = [];
try {
    $stmt = $conexion->query("SELECT * FROM clientes ORDER BY nombre ASC");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $clientes = []; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Auto Master</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/estilos-generales.css">
    <style>
        /* ── Cabecera de página ── */
        .pagina-cabecera {
            display: flex; align-items: center;
            justify-content: space-between; flex-wrap: wrap; gap: 12px;
            margin-bottom: 24px;
        }
        .busqueda-input {
            display: flex; align-items: center; gap: 8px;
            background: var(--tarjeta,#fff);
            border: 1.5px solid var(--borde,#e5e7eb);
            border-radius: 10px; padding: 8px 14px;
            font-size: 13px; color: var(--color-gris,#888);
        }
        .busqueda-input input {
            border: none; outline: none; background: transparent;
            font-size: 13px; color: var(--texto,#333); width: 160px;
        }

        /* ── Grid de tarjetas ── */
        .clientes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
        }

        /* ── Tarjeta cliente ── */
        .cli-card {
            background: var(--tarjeta,#fff);
            border-radius: 20px; overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }
        .cli-card:hover { transform: translateY(-4px); box-shadow: 0 8px 28px rgba(0,0,0,0.12); }

        .cli-banner {
            height: 100px;
            background: linear-gradient(135deg, #c0c8e8 0%, #d8d0f0 100%);
            display: flex; align-items: center; justify-content: center;
            position: relative;
        }
        .cli-avatar {
            width: 72px; height: 72px; border-radius: 50%;
            background: #e05a6e; color: #fff;
            font-size: 26px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            border: 4px solid #fff;
            box-shadow: 0 4px 14px rgba(224,90,110,0.35);
            overflow: hidden;
        }

        /* Botón editar flotante */
        .cli-btn-editar {
            position: absolute; bottom: 10px; right: 10px;
            width: 32px; height: 32px; border-radius: 50%;
            background: #e05a6e; color: #fff;
            border: none; cursor: pointer; font-size: 13px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 8px rgba(224,90,110,0.4);
            transition: transform 0.2s, background 0.2s;
        }
        .cli-btn-editar:hover { transform: scale(1.1); background: #c94a5e; }

        .cli-card-body { padding: 16px 18px 18px; }
        .cli-nombre { font-size: 15px; font-weight: 700; color: #e05a6e; margin: 0 0 2px; text-align: center; }
        .cli-rol { font-size: 12px; color: var(--color-gris,#888); text-align: center; margin-bottom: 14px; }

        .cli-info-titulo {
            font-size: 11px; font-weight: 700; color: var(--color-gris,#999);
            text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 10px;
        }
        .cli-info-lista { list-style: none; padding: 0; margin: 0; }
        .cli-info-lista li {
            display: flex; align-items: center; gap: 10px;
            font-size: 12px; padding: 6px 0;
            border-bottom: 1px solid var(--borde,#f0f0f0); color: var(--texto,#333);
        }
        .cli-info-lista li:last-child { border-bottom: none; }
        .cli-info-lista li i { width: 16px; color: var(--color-gris,#aaa); font-size: 12px; flex-shrink: 0; }
        .cli-info-lista .lbl { color: var(--color-gris,#888); min-width: 80px; font-size: 11px; }
        .cli-info-lista .val { font-weight: 600; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .cli-info-lista .val.link { color: #e05a6e; }
        .cli-info-lista .val.placa {
            background: #1e293b; color: #fff;
            padding: 1px 8px; border-radius: 4px;
            font-family: monospace; font-size: 12px; letter-spacing: 0.1em;
        }

        /* Badge */
        .badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-activo   { background: #dcfce7; color: #16a34a; }
        .badge-inactivo { background: #fee2e2; color: #dc2626; }

        /* Sin clientes */
        .sin-clientes { text-align: center; padding: 60px 20px; color: var(--color-gris,#888); }
        .sin-clientes i { font-size: 48px; margin-bottom: 16px; display: block; }

        /* Modal */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 9998; align-items: center; justify-content: center; }
        .modal-overlay.abierto { display: flex; }
        .modal-box { background: #fff; border-radius: 20px; padding: 32px; width: 90%; max-width: 540px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto; }
        .modal-box h3 { margin: 0 0 20px; font-size: 18px; }
        .modal-acciones { display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; }
        .select-style { width: 100%; padding: 10px 14px; border-radius: 10px; border: 1.5px solid var(--borde,#e5e7eb); font-size: 14px; background: #fff; }

        /* Toast */
        .toast { position: fixed; top: 24px; right: 24px; z-index: 9999; display: flex; align-items: center; gap: 12px; padding: 16px 24px; border-radius: 12px; font-size: 15px; font-weight: 600; box-shadow: 0 8px 32px rgba(0,0,0,0.18); animation: slideIn 0.4s ease, fadeOut 0.5s ease 3.5s forwards; pointer-events: none; }
        .toast.exito { background: #22c55e; color: #fff; }
        .toast.error { background: #ef4444; color: #fff; }
        @keyframes slideIn { from{opacity:0;transform:translateX(60px)} to{opacity:1;transform:translateX(0)} }
        @keyframes fadeOut { to{opacity:0;transform:translateX(60px)} }
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

            <!-- CLIENTES (activo) -->
            <div class="nav-item submenu-toggle activo" onclick="toggleSubmenu('clientes')">
                <i class="fas fa-users"></i><span>Clientes</span>
                <i class="fas fa-chevron-down flecha" id="flecha-clientes"></i>
            </div>
            <div class="submenu" id="submenu-clientes">
                <a href="informacion-clientes.php" class="nav-item activo"><i class="fas fa-address-card"></i><span>Ver Clientes</span></a>
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
            <div class="pagina-cabecera">
                <h2 style="margin:0;">Detalles de clientes</h2>
                <div style="display:flex; gap:10px; align-items:center;">
                    <div class="busqueda-input">
                        <i class="fas fa-search" style="color:var(--color-gris,#aaa);"></i>
                        <input type="text" id="buscador" placeholder="Search..." onkeyup="filtrarClientes()">
                    </div>
                    <button class="btn btn-primario" onclick="abrirModal()" style="white-space:nowrap;">
                        <i class="fas fa-user-plus"></i> Agregar Cliente
                    </button>
                </div>
            </div>

            <?php if (!empty($clientes)): ?>
            <div class="clientes-grid" id="grid-clientes">
                <?php foreach ($clientes as $cli):
                    $nombre_completo = htmlspecialchars(trim($cli['nombre'] . ' ' . $cli['apellido']));
                    $iniciales = strtoupper(substr($cli['nombre'],0,1) . substr($cli['apellido'],0,1));
                    $carro = htmlspecialchars(trim(($cli['marca_carro'] ?? '') . ', ' . ($cli['modelo_carro'] ?? '')));
                    $carro = trim($carro, ', ');
                    $badge = $cli['estatus'] === 'Activo' ? 'badge-activo' : 'badge-inactivo';
                    $placa = htmlspecialchars($cli['placa'] ?? '—');
                ?>
                <div class="cli-card" data-nombre="<?= strtolower($nombre_completo) ?>">
                    <div class="cli-banner">
                        <div class="cli-avatar"><?= $iniciales ?></div>
                        <button class="cli-btn-editar"
                                onclick="abrirEditar(<?= $cli['id_cliente'] ?>, <?= htmlspecialchars(json_encode($cli), ENT_QUOTES) ?>)"
                                title="Editar cliente">
                            <i class="fas fa-pen"></i>
                        </button>
                    </div>
                    <div class="cli-card-body">
                        <p class="cli-nombre"><?= $nombre_completo ?></p>
                        <p class="cli-rol">Cliente</p>

                        <p class="cli-info-titulo">General info</p>
                        <ul class="cli-info-lista">
                            <li>
                                <i class="fas fa-circle-dot"></i>
                                <span class="lbl">Estatus</span>
                                <span class="val"><span class="badge <?= $badge ?>"><?= $cli['estatus'] ?></span></span>
                            </li>
                            <li>
                                <i class="fas fa-phone"></i>
                                <span class="lbl">Número celular</span>
                                <span class="val link"><?= htmlspecialchars($cli['telefono'] ?? '—') ?></span>
                            </li>
                            <li>
                                <i class="fas fa-car"></i>
                                <span class="lbl">Carro</span>
                                <span class="val link" title="<?= $carro ?>"><?= $carro ?: '—' ?></span>
                            </li>
                            <?php if (!empty($cli['placa'])): ?>
                            <li>
                                <i class="fas fa-id-card"></i>
                                <span class="lbl">Placa</span>
                                <span class="val"><span class="placa"><?= $placa ?></span></span>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php else: ?>
            <div class="sin-clientes">
                <i class="fas fa-users-slash"></i>
                <p>No hay clientes registrados aún.</p>
                <button class="btn btn-primario" onclick="abrirModal()" style="margin-top:16px;">
                    Agregar primer cliente
                </button>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Modal AGREGAR cliente -->
<div class="modal-overlay" id="modal-agregar">
    <div class="modal-box">
        <form method="POST" action="informacion-clientes.php">
        <h3><i class="fas fa-user-plus" style="color:#e05a6e; margin-right:8px;"></i>Agregar Cliente</h3>
        <div style="display:grid; gap:14px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div class="form-grupo">
                    <label>Nombre *</label>
                    <input type="text" name="nombre" placeholder="Nombre" required>
                </div>
                <div class="form-grupo">
                    <label>Apellido *</label>
                    <input type="text" name="apellido" placeholder="Apellido" required>
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div class="form-grupo">
                    <label>Teléfono</label>
                    <input type="tel" name="telefono" placeholder="(000) 000-0000">
                </div>
                <div class="form-grupo">
                    <label>Correo</label>
                    <input type="email" name="correo" placeholder="correo@ejemplo.com">
                </div>
            </div>
            <div class="form-grupo">
                <label>Placa del vehículo *</label>
                <input type="text" name="placa" placeholder="ABC-123" style="text-transform:uppercase;" required>
                <small style="color:var(--color-gris,#888); font-size:11px;">La placa conecta al cliente con sus órdenes de servicio.</small>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr 80px; gap:14px;">
                <div class="form-grupo">
                    <label>Marca del carro</label>
                    <input type="text" name="marca_carro" placeholder="Nissan, Toyota...">
                </div>
                <div class="form-grupo">
                    <label>Modelo</label>
                    <input type="text" name="modelo_carro" placeholder="Altima, Corolla...">
                </div>
                <div class="form-grupo">
                    <label>Año</label>
                    <input type="number" name="anio_carro" placeholder="2020" min="1990" max="2030">
                </div>
            </div>
            <div class="form-grupo">
                <label>Estatus</label>
                <select name="estatus" class="select-style">
                    <option value="Activo">Activo</option>
                    <option value="No activo">No activo</option>
                </select>
            </div>
        </div>
        <div class="modal-acciones">
            <button type="button" class="btn btn-secundario" onclick="cerrarModal()">Cancelar</button>
            <button type="submit" name="guardar_cliente" class="btn btn-primario">Guardar Cliente</button>
        </div>
        </form>
    </div>
</div>

<!-- Modal EDITAR cliente -->
<div class="modal-overlay" id="modal-editar">
    <div class="modal-box">
        <form method="POST" action="informacion-clientes.php">
        <input type="hidden" name="id_cliente" id="e-id">
        <h3><i class="fas fa-pen" style="color:#e05a6e; margin-right:8px;"></i>Editar Cliente</h3>
        <div style="display:grid; gap:14px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div class="form-grupo">
                    <label>Nombre *</label>
                    <input type="text" name="nombre_e" id="e-nombre" required>
                </div>
                <div class="form-grupo">
                    <label>Apellido *</label>
                    <input type="text" name="apellido_e" id="e-apellido" required>
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div class="form-grupo">
                    <label>Teléfono</label>
                    <input type="tel" name="telefono_e" id="e-telefono">
                </div>
                <div class="form-grupo">
                    <label>Correo</label>
                    <input type="email" name="correo_e" id="e-correo">
                </div>
            </div>
            <div class="form-grupo">
                <label>Placa del vehículo *</label>
                <input type="text" name="placa_e" id="e-placa" style="text-transform:uppercase;" required>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr 80px; gap:14px;">
                <div class="form-grupo">
                    <label>Marca del carro</label>
                    <input type="text" name="marca_e" id="e-marca">
                </div>
                <div class="form-grupo">
                    <label>Modelo</label>
                    <input type="text" name="modelo_e" id="e-modelo">
                </div>
                <div class="form-grupo">
                    <label>Año</label>
                    <input type="number" name="anio_e" id="e-anio" min="1990" max="2030">
                </div>
            </div>
            <div class="form-grupo">
                <label>Estatus</label>
                <select name="estatus_e" id="e-estatus" class="select-style">
                    <option value="Activo">Activo</option>
                    <option value="No activo">No activo</option>
                </select>
            </div>
        </div>
        <div class="modal-acciones">
            <button type="button" class="btn btn-secundario" onclick="cerrarEditar()">Cancelar</button>
            <button type="submit" name="actualizar_cliente" class="btn btn-primario">Guardar Cambios</button>
        </div>
        </form>
    </div>
</div>

<!-- Toast -->
<?php if ($mensaje): ?>
<div class="toast <?= $tipo_mensaje ?>">
    <i class="fas <?= $tipo_mensaje==='exito' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
    <?= htmlspecialchars($mensaje) ?>
</div>
<?php endif; ?>

<script src="../../js/menu.js"></script>
<script>
    function abrirModal()   { document.getElementById('modal-agregar').classList.add('abierto'); }
    function cerrarModal()  { document.getElementById('modal-agregar').classList.remove('abierto'); }
    function cerrarEditar() { document.getElementById('modal-editar').classList.remove('abierto'); }

    function abrirEditar(id, d) {
        document.getElementById('e-id').value      = id;
        document.getElementById('e-nombre').value  = d.nombre;
        document.getElementById('e-apellido').value= d.apellido;
        document.getElementById('e-telefono').value= d.telefono || '';
        document.getElementById('e-correo').value  = d.correo || '';
        document.getElementById('e-placa').value   = d.placa || '';
        document.getElementById('e-marca').value   = d.marca_carro || '';
        document.getElementById('e-modelo').value  = d.modelo_carro || '';
        document.getElementById('e-anio').value    = d.anio_carro || '';
        document.getElementById('e-estatus').value = d.estatus || 'Activo';
        document.getElementById('modal-editar').classList.add('abierto');
    }

    // Filtro de búsqueda
    function filtrarClientes() {
        const q = document.getElementById('buscador').value.toLowerCase();
        document.querySelectorAll('.cli-card').forEach(card => {
            card.style.display = card.dataset.nombre.includes(q) ? '' : 'none';
        });
    }

    document.getElementById('modal-agregar').addEventListener('click', e => { if(e.target===e.currentTarget) cerrarModal(); });
    document.getElementById('modal-editar').addEventListener('click',  e => { if(e.target===e.currentTarget) cerrarEditar(); });

    // Abrir submenu clientes por defecto
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('submenu-clientes').style.display = 'block';
    });
</script>
</body>
</html>