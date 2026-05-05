<div class="seccion">
    <h3 style="margin-bottom:20px;">Configuracion del Sistema</h3>

    <div class="grid-2">
        <div>
            <h4 style="margin-bottom:15px;">Notificaciones</h4>
            <div class="campo" style="display:flex;justify-content:space-between;align-items:center;">
                <labelgi>Notificar nuevas ordenes</label>
                <input type="checkbox" checked style="width:20px;height:20px;">
            </div>
            <div class="campo" style="display:flex;justify-content:space-between;align-items:center;">
                <label>Alertas de inventario bajo</label>
                <input type="checkbox" checked style="width:20px;height:20px;">
            </div>
            <div class="campo" style="display:flex;justify-content:space-between;align-items:center;">
                <label>Resumen diario por email</label>
                <input type="checkbox" style="width:20px;height:20px;">
            </div>
        </div>

        <div>
            <h4 style="margin-bottom:15px;">Seguridad</h4>
            <div class="campo" style="display:flex;justify-content:space-between;align-items:center;">
                <label>Autenticacion en dos pasos</label>
                <input type="checkbox" checked style="width:20px;height:20px;">
            </div>
            <div class="campo" style="display:flex;justify-content:space-between;align-items:center;">
                <label>Registrar IPs de acceso</label>
                <input type="checkbox" checked style="width:20px;height:20px;">
            </div>
            <div class="campo" style="display:flex;justify-content:space-between;align-items:center;">
                <label>Sesiones multiples</label>
                <input type="checkbox" style="width:20px;height:20px;">
            </div>
        </div>
    </div>

    <div style="margin-top:20px;text-align:right;">
        <button class="btn btn-rojo" onclick="alert('Configuracion guardada')"><i class="fas fa-save"></i> Guardar Cambios</button>
    </div>
</div>