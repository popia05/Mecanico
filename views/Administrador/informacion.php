<?php
// informacion.php — se incluye desde index.php
// Solo contiene el contenido interno de la página, sin <html>, <head> ni <body>
?>

<div class="pagina-titulo">
    <h2>Detalles de Administrador</h2>
    <p>Datos del administrador registrado en el sistema</p>
</div>

<div class="tarjeta">
    <div class="tarjeta-header">
        <h3>Perfil del Administrador</h3>
        <button class="btn btn-primario">
            <i class="fas fa-edit"></i> Editar
        </button>
    </div>
    <div class="tarjeta-body">
        <div style="display: flex; gap: 30px; align-items: flex-start;">
            <div style="text-align: center;">
                <div style="width:120px;height:120px;background:var(--rosa);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:40px;font-weight:bold;margin-bottom:10px;">
                    DG
                </div>
                <h3 style="font-size: 18px;">Daniel Garcia Olivas</h3>
                <span class="badge badge-verde">Activo</span>
            </div>
            <div style="flex: 1; display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                <div class="form-grupo">
                    <label for="info-nombre">Nombre Completo</label>
                    <input type="text" id="info-nombre" value="Daniel Garcia Olivas" readonly style="background: var(--fondo);">
                </div>
                <div class="form-grupo">
                    <label for="info-email">Email</label>
                    <input type="email" id="info-email" value="daniel@automaster.com" readonly style="background: var(--fondo);">
                </div>
                <div class="form-grupo">
                    <label for="info-telefono">Telefono</label>
                    <input type="text" id="info-telefono" value="(430) 065-7387" readonly style="background: var(--fondo);">
                </div>
                <div class="form-grupo">
                    <label for="info-rol">Rol</label>
                    <input type="text" id="info-rol" value="Administrador" readonly style="background: var(--fondo);">
                </div>
                <div class="form-grupo">
                    <label for="info-ingreso">Fecha de Ingreso</label>
                    <input type="text" id="info-ingreso" value="15 de Enero, 2024" readonly style="background: var(--fondo);">
                </div>
                <div class="form-grupo">
                    <label for="info-ordenes">Ordenes Completadas</label>
                    <input type="text" id="info-ordenes" value="156" readonly style="background: var(--fondo);">
                </div>
            </div>
        </div>
    </div>
</div>