<div class="seccion">
    <h3 style="text-align:center;border-bottom:3px solid var(--rojo);padding-bottom:15px;margin-bottom:25px;color:var(--rojo);">
        Tareas Listas Para Remision
    </h3>

    <?php
    $trabajos = [
        ['veh'=>'Nissan Altima 2019','fecha'=>'14:30 - Sep 28, 2026','desc'=>'Cambio de amortiguadores'],
        ['veh'=>'Ford F-150 2022','fecha'=>'14:00 - Sep 10, 2026','desc'=>'Servicio de mantenimiento'],
        ['veh'=>'Dodge Atitud 2020','fecha'=>'10:00 - Sep 05, 2026','desc'=>'Afinacion mayor con bujias y filtros'],
    ];
    foreach ($trabajos as $i => $t): ?>
    <div style="background:var(--fondo);padding:20px;border-radius:10px;margin-bottom:15px;">
        <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
            <div>
                <strong><?= $t['veh'] ?></strong>
                <div style="font-size:12px;color:var(--texto-claro);"><?= $t['fecha'] ?></div>
            </div>
            <span class="badge badge-verde">Terminado</span>
        </div>
        <p style="padding:10px;background:white;border-radius:6px;margin-bottom:10px;"><?= $t['desc'] ?></p>
        <div style="text-align:right;">
            <a href="index.php?p=factura&trabajo=<?= $i ?>" class="btn btn-rojo">Agregar Nota</a>
        </div>
    </div>
    <?php endforeach; ?>
</div>