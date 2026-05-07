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
<h2 style="text-align:center;margin-bottom:25px;">Notas De Remision</h2>

<div class="seccion" style="background:var(--fondo-claro);">
    <h3 style="text-align:center;border-bottom:2px solid #6366f1;padding-bottom:15px;margin-bottom:25px;">
        Tareas Listas Para Remision
    </h3>

    <div id="contenedor-remision">
        <!-- JS llena esto dinamicamente -->
    </div>

    <div id="sin-tareas" style="display:none;text-align:center;padding:40px;color:var(--texto-claro);">
        <i class="fas fa-clipboard-check" style="font-size:48px;margin-bottom:15px;opacity:0.4;"></i>
        <p>No hay tareas terminadas pendientes de remision.</p>
        <p style="font-size:13px;margin-top:8px;">Termina una tarea en <strong>Tareas Asignadas</strong> o <strong>Gestion de Ordenes</strong> y aparecera aqui.</p>
    </div>
</div>

<script>
// Lista maestra de TODAS las tareas del sistema (debe coincidir con ordenes.php y gestion.php)
const todasLasTareas = [
    {id:1,veh:'Dodge Atitud 2020',hora:'14:00 - Mar 03, 2026',desc:'Viene a un servicio completo: cambio de bujias, cambio de aceite, cambio de filtros (aceite y aire), tambien escanear el sistema del carro para ver errores.',estadoInicial:'Pendiente'},
    {id:2,veh:'Nissan Altima 2019',hora:'14:30 - Mar 02, 2026',desc:'Cambio de amortiguadores a carro Nissan Altima, motor 4 cilindros.',estadoInicial:'En Progreso'},
    {id:3,veh:'Nissan Altima 2018',hora:'14:00 - Mar 01, 2026',desc:'Se realizo cambio de llantas a carro Nissan Altima, motor 4 cilindros.',estadoInicial:'Terminado'},
    {id:4,veh:'Toyota Corolla 2021',hora:'10:00 - Mar 03, 2026',desc:'Servicio de mantenimiento a carro Toyota Corolla. Cambio de aceite y revision general.',estadoInicial:'Pendiente'},
    {id:5,veh:'Ford F-150 2022',hora:'09:00 - Feb 28, 2026',desc:'Cambio de balatas delanteras y traseras. Revision de discos de freno.',estadoInicial:'En Progreso'},
    {id:6,veh:'Chevrolet Spark 2020',hora:'11:30 - Feb 27, 2026',desc:'Diagnostico de falla en sistema electrico. El carro no enciende correctamente.',estadoInicial:'Terminado'},
];

function renderRemisiones() {
    const cambios = JSON.parse(localStorage.getItem('estadosTareas') || '{}');
    const remisionesHechas = JSON.parse(localStorage.getItem('remisionesHechas') || '[]');

    // Filtrar tareas que estan terminadas Y que no tienen remision hecha
    const terminadas = todasLasTareas.filter(t => {
        const estadoActual = cambios[t.id] || t.estadoInicial;
        return estadoActual === 'Terminado' && !remisionesHechas.includes(t.id);
    });

    const cont = document.getElementById('contenedor-remision');
    const vacio = document.getElementById('sin-tareas');

    if (terminadas.length === 0) {
        cont.innerHTML = '';
        vacio.style.display = 'block';
        return;
    }

    vacio.style.display = 'none';
    cont.innerHTML = terminadas.map(t => `
        <div class="tarea-lista">
            <div class="tarea-icono-redondo"><i class="fas fa-tools"></i></div>
            <div class="tarea-lista-contenido">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <strong>${t.veh}</strong>
                    <span class="badge badge-verde">Terminado</span>
                </div>
                <span class="texto-claro" style="display:block;margin-bottom:10px;">${t.hora}</span>
                <div class="caja-mensaje" style="margin-bottom:10px;">${t.desc}</div>
                <div style="text-align:right;">
                    <a href="index.php?p=nota-detalle&id=${t.id}" class="btn btn-rojo">
                        <i class="fas fa-file-invoice"></i> Agregar Nota
                    </a>
                </div>
            </div>
        </div>
    `).join('');
}

document.addEventListener('DOMContentLoaded', renderRemisiones);
</script>