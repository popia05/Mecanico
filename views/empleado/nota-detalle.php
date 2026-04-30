<div style="display:flex;align-items:center;gap:15px;margin-bottom:25px;">
    <button class="btn-volver" onclick="window.location.href='index.php?p=nota-remision'"><i class="fas fa-arrow-left"></i></button>
    <h2 style="margin:0;flex:1;">Nota de remision</h2>
    <button class="btn btn-rojo"><i class="fas fa-edit"></i> Editar</button>
</div>

<div class="grid-nota">
    <div class="seccion">
        <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:15px;border-bottom:2px solid var(--borde);margin-bottom:20px;">
            <h3 style="margin:0;">Auto Master</h3>
            <div class="logo-mini">
                <img src="../../logo.png" alt="" onerror="this.style.display='none'">
            </div>
        </div>

        <div class="campo-fila"><label>Cliente:</label><span>Alonso Martinez Osorio</span></div>
        <div class="campo-fila"><label>Placas:</label><span>V4HS T7JW</span></div>
        <div class="campo-fila"><label>Asignacion de personal:</label><span>Mecanico 1</span></div>

        <div class="campo">
            <label>Descripcion del servicio</label>
            <textarea rows="8" readonly>1 Afinacion Mayor: Cambio de aceite sintetico, filtros (aceite/aire) y bujias. $2,200
1 Limpieza de Inyectores: Lavado por presurizacion y cuerpo de aceleracion. $650
1 Revision de Frenos: Inspeccion de balatas y ajuste de freno de mano. $400
Observaciones: Se recomienda cambio de llantas delanteras en 3,000 km.
Garantia: 30 dias en mano de obra.</textarea>
        </div>

        <div class="grid-2">
            <div class="campo">
                <label>Fecha de Ingreso *</label>
                <input type="date" value="2026-02-10">
            </div>
            <div class="campo">
                <label>Fecha de salida</label>
                <input type="date" value="2026-02-15">
            </div>
        </div>
    </div>

    <div class="columna-iconos">
        <div class="icono-paso verde"><i class="fas fa-check"></i></div>
        <div class="icono-paso verde"><i class="fas fa-car"></i></div>
        <div class="icono-paso gris"><i class="fas fa-user"></i></div>
        <div class="icono-paso amarillo"><i class="fas fa-pencil-alt"></i></div>
        <div class="icono-paso verde"><i class="fas fa-check"></i></div>
        <div class="icono-paso gris"><i class="fas fa-times"></i></div>
    </div>
</div>

<div class="card-total">
    <div class="card-total-header">
        <span>TOTAL</span>
        <span><?= date('d/m/Y') ?></span>
    </div>
    <div class="card-total-monto">$3,250</div>
    <button class="btn-pagar">PAGAR</button>
</div>

<div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
    <button class="btn btn-gris-borde"><i class="fas fa-file-pdf"></i> PDF</button>
    <button class="btn btn-gris-borde">Cancelar</button>
    <button class="btn btn-rojo"><i class="fas fa-save"></i> Guardar nota</button>
</div>