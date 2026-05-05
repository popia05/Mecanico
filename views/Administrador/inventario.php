<?php ?>

<div class="pagina-titulo">
    <h2>Inventario de Herramientas</h2>
    <p>Control de herramientas y equipos del taller</p>
</div>

<!-- Resumen -->
<div class="tarjetas-resumen" style="grid-template-columns: repeat(3, 1fr);">
    <div class="tarjeta-resumen">
        <div class="tarjeta-icono verde"><i class="fas fa-check-circle"></i></div>
        <div class="tarjeta-info"><h3>8</h3><span>Disponibles</span></div>
    </div>
    <div class="tarjeta-resumen">
        <div class="tarjeta-icono azul"><i class="fas fa-hand-holding"></i></div>
        <div class="tarjeta-info"><h3>3</h3><span>En Uso</span></div>
    </div>
    <div class="tarjeta-resumen">
        <div class="tarjeta-icono naranja"><i class="fas fa-tools"></i></div>
        <div class="tarjeta-info"><h3>2</h3><span>Mantenimiento</span></div>
    </div>
</div>

<!-- Tabla de inventario -->
<div class="tarjeta">
    <div class="tarjeta-header">
        <h3>Lista de Herramientas</h3>
    </div>
    <div class="tarjeta-body" style="padding: 0;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>HERRAMIENTA</th>
                    <th>CANTIDAD</th>
                    <th>ESTADO</th>
                    <th>UBICACION</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>H-001</strong></td>
                    <td>Llave de torque</td>
                    <td style="text-align:center;">3</td>
                    <td><span class="badge badge-verde">Disponible</span></td>
                    <td>Estante A</td>
                </tr>
                <tr>
                    <td><strong>H-002</strong></td>
                    <td>Gato hidraulico 3 ton</td>
                    <td style="text-align:center;">2</td>
                    <td><span class="badge badge-verde">Disponible</span></td>
                    <td>Zona de trabajo 1</td>
                </tr>
                <tr>
                    <td><strong>H-003</strong></td>
                    <td>Scanner automotriz OBD2</td>
                    <td style="text-align:center;">1</td>
                    <td><span class="badge badge-azul">En Uso</span></td>
                    <td>Bahia 2</td>
                </tr>
                <tr>
                    <td><strong>H-004</strong></td>
                    <td>Juego de dados metricas</td>
                    <td style="text-align:center;">4</td>
                    <td><span class="badge badge-verde">Disponible</span></td>
                    <td>Carro herramientas B</td>
                </tr>
                <tr>
                    <td><strong>H-005</strong></td>
                    <td>Pistola neumatica</td>
                    <td style="text-align:center;">2</td>
                    <td><span class="badge badge-verde">Disponible</span></td>
                    <td>Estante C</td>
                </tr>
                <tr>
                    <td><strong>H-006</strong></td>
                    <td>Multimetro digital</td>
                    <td style="text-align:center;">2</td>
                    <td><span class="badge badge-azul">En Uso</span></td>
                    <td>Bahia 1</td>
                </tr>
                <tr>
                    <td><strong>H-007</strong></td>
                    <td>Extractor de poleas</td>
                    <td style="text-align:center;">1</td>
                    <td><span class="badge badge-naranja">Mantenimiento</span></td>
                    <td>Taller de reparacion</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>