<div class="seccion-titulo">
    <h2>Inventario de Herramientas</h2>
    <p>Consulta las herramientas disponibles en el taller</p>
</div>

<div class="stats stats-3">
    <div class="stat-card stat-grande">
        <h3 class="numero-grande verde">8</h3>
        <span>Disponibles</span>
    </div>
    <div class="stat-card stat-grande">
        <h3 class="numero-grande azul">2</h3>
        <span>En Uso</span>
    </div>
    <div class="stat-card stat-grande">
        <h3 class="numero-grande naranja">2</h3>
        <span>Mantenimiento</span>
    </div>
</div>

<div class="busqueda-fila">
    <div class="input-busqueda">
        <i class="fas fa-search"></i>
        <input type="text" id="buscar-inv" placeholder="Buscar herramienta..." onkeyup="buscarInventario()">
    </div>
    <div class="botones-filtro">
        <button class="btn-filtro activo" onclick="filtrarInv(this,'todos')">Todos</button>
        <button class="btn-filtro" onclick="filtrarInv(this,'disponible')">Disponible</button>
        <button class="btn-filtro" onclick="filtrarInv(this,'en uso')">En Uso</button>
        <button class="btn-filtro" onclick="filtrarInv(this,'mantenimiento')">Mantenimiento</button>
    </div>
</div>

<div class="tabla-tarjeta">
<table class="tabla">
<thead>
<tr><th>HERRAMIENTA</th><th>CANTIDAD</th><th>ESTADO</th><th>UBICACION</th></tr>
</thead>
<tbody id="tbody-inv">
<?php
$herramientas = [
    ['Llave de torque','H-001',3,'Disponible','verde','Estante A'],
    ['Gato hidraulico 3 ton','H-002',2,'Disponible','verde','Zona de trabajo 1'],
    ['Scanner automotriz OBD2','H-003',1,'En Uso','azul','Bahia 2'],
    ['Juego de dados metricas','H-004',4,'Disponible','verde','Carro herramientas B'],
    ['Pistola neumatica','H-005',2,'Disponible','verde','Estante C'],
    ['Multimetro digital','H-006',2,'En Uso','azul','Bahia 1'],
    ['Compresometro','H-007',1,'Disponible','verde','Estante A'],
    ['Extractor de poleas','H-008',1,'Mantenimiento','naranja','Taller de reparacion'],
    ['Soldadora MIG','H-012',1,'Mantenimiento','naranja','Area de soldadura'],
];
foreach ($herramientas as $h): ?>
<tr data-estado="<?= strtolower($h[3]) ?>" data-nombre="<?= strtolower($h[0]) ?>">
    <td>
        <div class="herramienta-fila">
            <div class="herramienta-icono"><i class="fas fa-wrench"></i></div>
            <div>
                <strong><?= $h[0] ?></strong>
                <span class="texto-claro" style="display:block;font-size:12px;"><?= $h[1] ?></span>
            </div>
        </div>
    </td>
    <td><?= $h[2] ?></td>
    <td><span class="badge badge-<?= $h[4] ?>"><?= $h[3] ?></span></td>
    <td><?= $h[5] ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>