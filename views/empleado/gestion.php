<div class="seccion-titulo">
    <h2>Gestion de Ordenes</h2>
    <p>Vista general de todas tus ordenes de trabajo</p>
</div>

<div class="busqueda-fila">
    <div class="input-busqueda">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Buscar por vehiculo, cliente o ID...">
    </div>
    <button class="btn btn-gris-borde"><i class="fas fa-filter"></i> Filtrar</button>
</div>

<div class="tabla-tarjeta">
<table class="tabla">
<thead>
<tr><th>ID</th><th>VEHICULO</th><th>CLIENTE</th><th>ESTADO</th><th>FECHA</th><th>ACCION</th></tr>
</thead>
<tbody>
<?php
$ordenes = [
    [1,'ORD-001','Dodge Atitud 2020','Carlos Mendoza','Pendiente','rojo','Mar 03, 2026'],
    [2,'ORD-002','Nissan Altima 2019','Maria Rodriguez','En Progreso','azul','Mar 02, 2026'],
    [3,'ORD-003','Nissan Altima 2018','Juan Lopez','Terminado','verde','Mar 01, 2026'],
    [4,'ORD-004','Toyota Corolla 2021','Ana Gutierrez','Pendiente','rojo','Mar 03, 2026'],
    [5,'ORD-005','Ford F-150 2022','Roberto Sanchez','En Progreso','azul','Feb 28, 2026'],
    [6,'ORD-006','Chevrolet Spark 2020','Laura Martinez','Terminado','verde','Feb 27, 2026'],
];
foreach ($ordenes as $o): ?>
<tr data-id="<?= $o[0] ?>">
    <td><?= $o[1] ?></td>
    <td><strong><?= $o[2] ?></strong></td>
    <td><?= $o[3] ?></td>
    <td><span class="badge badge-<?= $o[5] ?> celda-estado"><?= $o[4] ?></span></td>
    <td><?= $o[6] ?></td>
    <td>
        <a href="index.php?p=ordenes&id=<?= $o[0] ?>" class="link-ver">Ver</a>
        <?php if($o[4] == 'Pendiente'): ?>
            <button class="btn btn-rojo btn-sm celda-accion" onclick="iniciarOrden(this)">Iniciar</button>
        <?php elseif($o[4] == 'En Progreso'): ?>
            <button class="btn btn-rojo btn-sm celda-accion" onclick="completarOrden(this)">Completar</button>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>