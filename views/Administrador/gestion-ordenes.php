<?php
// gestion.php — se incluye desde index.php
// Solo contiene el contenido interno de la página, sin <html>, <head> ni <body>
?>

<!-- Título de página -->
<div class="pagina-titulo">
    <h2>Ordenes de Servicio</h2>
    <p>Administracion de ordenes de trabajo</p>
</div>

<!-- Acciones -->
<div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
    <div style="display: flex; gap: 10px;">
        <span class="badge badge-naranja" style="padding: 8px 16px;">3 Pendientes</span>
        <span class="badge badge-azul"    style="padding: 8px 16px;">2 En Progreso</span>
        <span class="badge badge-verde"   style="padding: 8px 16px;">5 Terminadas</span>
    </div>
    <button class="btn btn-primario">
        <i class="fas fa-plus"></i> Nueva Orden
    </button>
</div>

<!-- Tabla de órdenes -->
<div class="tarjeta">
    <div class="tarjeta-body" style="padding: 0;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>VEHICULO</th>
                    <th>CLIENTE</th>
                    <th>SERVICIO</th>
                    <th>ESTADO</th>
                    <th>MECANICO</th>
                    <th>FECHA</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>ORD-001</strong></td>
                    <td>Dodge Atitud 2020</td>
                    <td>Carlos Mendoza</td>
                    <td>Afinacion mayor</td>
                    <td><span class="badge badge-verde">Terminado</span></td>
                    <td><div class="avatar rosa" style="width:28px;height:28px;font-size:10px;">DG</div></td>
                    <td>Mar 03, 2026</td>
                    <td>
                        <button class="btn btn-secundario" style="padding:5px 10px;font-size:12px;"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-secundario" style="padding:5px 10px;font-size:12px;"><i class="fas fa-edit"></i></button>
                    </td>
                </tr>
                <tr>
                    <td><strong>ORD-002</strong></td>
                    <td>Nissan Altima 2019</td>
                    <td>Maria Rodriguez</td>
                    <td>Cambio de amortiguadores</td>
                    <td><span class="badge badge-azul">En Progreso</span></td>
                    <td><div class="avatar azul" style="width:28px;height:28px;font-size:10px;">RM</div></td>
                    <td>Mar 02, 2026</td>
                    <td>
                        <button class="btn btn-secundario" style="padding:5px 10px;font-size:12px;"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-secundario" style="padding:5px 10px;font-size:12px;"><i class="fas fa-edit"></i></button>
                    </td>
                </tr>
                <tr>
                    <td><strong>ORD-003</strong></td>
                    <td>Toyota Corolla 2021</td>
                    <td>Ana Gutierrez</td>
                    <td>Cambio de llantas</td>
                    <td><span class="badge badge-naranja">Pendiente</span></td>
                    <td><div class="avatar verde" style="width:28px;height:28px;font-size:10px;">LP</div></td>
                    <td>Mar 03, 2026</td>
                    <td>
                        <button class="btn btn-secundario" style="padding:5px 10px;font-size:12px;"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-secundario" style="padding:5px 10px;font-size:12px;"><i class="fas fa-edit"></i></button>
                    </td>
                </tr>
                <tr>
                    <td><strong>ORD-004</strong></td>
                    <td>Ford F-150 2022</td>
                    <td>Roberto Sanchez</td>
                    <td>Cambio de frenos</td>
                    <td><span class="badge badge-azul">En Progreso</span></td>
                    <td><div class="avatar rosa" style="width:28px;height:28px;font-size:10px;">DG</div></td>
                    <td>Feb 28, 2026</td>
                    <td>
                        <button class="btn btn-secundario" style="padding:5px 10px;font-size:12px;"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-secundario" style="padding:5px 10px;font-size:12px;"><i class="fas fa-edit"></i></button>
                    </td>
                </tr>
                <tr>
                    <td><strong>ORD-005</strong></td>
                    <td>Honda Civic 2020</td>
                    <td>Pedro Martinez</td>
                    <td>Revision general</td>
                    <td><span class="badge badge-naranja">Pendiente</span></td>
                    <td><div class="avatar morado" style="width:28px;height:28px;font-size:10px;">CH</div></td>
                    <td>Mar 01, 2026</td>
                    <td>
                        <button class="btn btn-secundario" style="padding:5px 10px;font-size:12px;"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-secundario" style="padding:5px 10px;font-size:12px;"><i class="fas fa-edit"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>