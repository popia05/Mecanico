<?php
$tareas = [
    ['id'=>1,'veh'=>'Dodge Atitud 2020','hora'=>'14:00 - Mar 03, 2026','desc'=>'Viene a un servicio completo: cambio de bujias, cambio de aceite, cambio de filtros (aceite y aire), tambien escanear el sistema del carro para ver errores.','estado'=>'Pendiente','badge'=>'rojo','iniciales'=>['DG','RM']],
    ['id'=>2,'veh'=>'Nissan Altima 2019','hora'=>'14:30 - Mar 02, 2026','desc'=>'Cambio de amortiguadores a carro Nissan Altima, motor 4 cilindros.','estado'=>'En Progreso','badge'=>'azul','iniciales'=>['DG']],
    ['id'=>3,'veh'=>'Nissan Altima 2018','hora'=>'14:00 - Mar 01, 2026','desc'=>'Se realizo cambio de llantas a carro Nissan Altima, motor 4 cilindros.','estado'=>'Terminado','badge'=>'verde','iniciales'=>['DG','RM','LP']],
    ['id'=>4,'veh'=>'Toyota Corolla 2021','hora'=>'10:00 - Mar 03, 2026','desc'=>'Servicio de mantenimiento a carro Toyota Corolla. Cambio de aceite y revision general.','estado'=>'Pendiente','badge'=>'rojo','iniciales'=>['DG']],
    ['id'=>5,'veh'=>'Ford F-150 2022','hora'=>'09:00 - Feb 28, 2026','desc'=>'Cambio de balatas delanteras y traseras. Revision de discos de freno.','estado'=>'En Progreso','badge'=>'azul','iniciales'=>['DG','RM']],
    ['id'=>6,'veh'=>'Chevrolet Spark 2020','hora'=>'11:30 - Feb 27, 2026','desc'=>'Diagnostico de falla en sistema electrico. El carro no enciende correctamente.','estado'=>'Terminado','badge'=>'verde','iniciales'=>['DG']],
];
$idResaltada = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>
<h2 style="text-align:center;margin-bottom:25px;">Ordenes Asignadas</h2>

<div class="tabs-tareas">
    <button class="tab-tarea activo" onclick="filtrarTareas(this,'todos')">Todos</button>
    <button class="tab-tarea" onclick="filtrarTareas(this,'pendiente')">Pendientes</button>
    <button class="tab-tarea" onclick="filtrarTareas(this,'en progreso')">En Progreso</button>
    <button class="tab-tarea" onclick="filtrarTareas(this,'terminado')">Terminados</button>
</div>

<div class="tareas-titulo"><span>Tareas</span></div>

<div class="lista-tareas">
<?php foreach ($tareas as $t): ?>
<div class="tarea-card <?= $idResaltada == $t['id'] ? 'resaltada' : '' ?>" data-id="<?= $t['id'] ?>" data-estado="<?= strtolower($t['estado']) ?>" id="tarea-<?= $t['id'] ?>">
    <div class="tarea-header" onclick="toggleTarea(this)">
        <div class="tarea-icono"><i class="fas fa-clock"></i></div>
        <div class="tarea-info">
            <strong><?= $t['veh'] ?></strong>
            <span><?= $t['hora'] ?></span>
        </div>
        <span class="badge badge-<?= $t['badge'] ?> badge-estado">
            <?= $t['estado'] ?>
        </span>
        <i class="fas fa-chevron-down flecha-tarea"></i>
    </div>
    <div class="tarea-body">
        <div class="caja-mensaje"><?= $t['desc'] ?></div>
        <div class="iniciales">
            <?php foreach ($t['iniciales'] as $ini): ?><span><?= $ini ?></span><?php endforeach; ?>
        </div>

        <div class="cambiar-estado">
            <h4>Cambiar Estado</h4>
            <div class="botones-estado">
                <button class="btn-estado <?= $t['estado']=='Pendiente'?'activo-rojo':'' ?>" onclick="cambiarEstado(this,'Pendiente','rojo')">Pendiente</button>
                <button class="btn-estado <?= $t['estado']=='En Progreso'?'activo-azul':'' ?>" onclick="cambiarEstado(this,'En Progreso','azul')">En progreso</button>
                <button class="btn-estado <?= $t['estado']=='Terminado'?'activo-verde':'' ?>" onclick="cambiarEstado(this,'Terminado','verde')">Terminado</button>
            </div>
        </div>

        <div class="especificaciones">
            <h4>Especificaciones del trabajo</h4>
            <textarea placeholder="Describe lo que se le hizo al vehiculo... Ej: se cambio aceite sintetico 5W-30, se remplazo filtro de aceite etc..."></textarea>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:10px;">
                <button class="btn btn-gris">Cancelar</button>
                <button class="btn btn-rojo" onclick="guardarEspec(this)">Guardar especificaciones</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>