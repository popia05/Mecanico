<div class="grid-2">
    <div class="seccion">
        <div style="display:flex;justify-content:space-between;margin-bottom:20px;">
            <h3>Nota de Remision</h3>
            <button class="btn btn-rojo"><i class="fas fa-edit"></i> Editar</button>
        </div>

        <div style="display:flex;justify-content:space-between;padding-bottom:15px;border-bottom:1px solid var(--borde);margin-bottom:20px;">
            <strong style="font-size:18px;">Auto Master</strong>
            <i class="fas fa-car" style="font-size:24px;color:var(--rojo);"></i>
        </div>

        <div class="campo"><label>Cliente:</label><div><?= $_GET['trabajo'] ?? 'Alonso Martinez Osorio' ?></div></div>
        <div class="campo"><label>Placas:</label><div>V4HS T7JW</div></div>
        <div class="campo"><label>Asignacion de personal:</label><div>Mecanico 1</div></div>

        <div class="campo">
            <label>Descripcion del servicio</label>
            <textarea rows="6" readonly style="background:var(--fondo);">1 Afinacion Mayor: Cambio de aceite sintetico, filtros y bujias. $2,200
1 Limpieza de Inyectores: Lavado por presurizacion. $650
1 Revision de Frenos: Inspeccion de balatas. $400
Garantia: 30 dias en mano de obra.</textarea>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
            <div class="campo"><label>Fecha de ingreso</label><input type="date" value="2026-02-10"></div>
            <div class="campo"><label>Fecha de salida</label><input type="date" value="2026-02-15"></div>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
            <button class="btn btn-gris" onclick="window.print()"><i class="fas fa-file-pdf"></i> PDF</button>
            <button class="btn btn-gris">Cancelar</button>
            <button class="btn btn-rojo" onclick="alert('Nota guardada')"><i class="fas fa-save"></i> Guardar</button>
        </div>
    </div>

    <div>
        <div style="background:linear-gradient(135deg,var(--rojo),#991b1b);color:white;border-radius:12px;padding:20px;text-align:center;">
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:10px;">
                <span>TOTAL</span>
                <span><?= date('d/m/Y') ?></span>
            </div>
            <div style="font-size:32px;font-weight:700;margin-bottom:15px;">$3,250</div>
            <button style="width:100%;padding:12px;background:rgba(0,0,0,0.3);color:white;border:none;border-radius:6px;cursor:pointer;font-size:14px;font-weight:600;" onclick="alert('Procesando pago...')">PAGAR</button>
        </div>
    </div>
</div>