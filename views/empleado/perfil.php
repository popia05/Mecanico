<div style="display:flex;align-items:center;gap:15px;margin-bottom:25px;">
    <button class="btn-volver" onclick="history.back()"><i class="fas fa-arrow-left"></i></button>
    <h2 style="margin:0;">Detalles de Empleado</h2>
</div>

<div class="grid-perfil">
    <div class="seccion">
        <div class="perfil-avatar">
            <div class="avatar-grande">D</div>
        </div>
        <h3 style="text-align:center;color:var(--rojo);font-size:22px;margin-top:15px;">Daniel Garcia Olivas</h3>
        <p style="text-align:center;color:var(--texto-claro);margin-bottom:25px;">Mecanico</p>

        <h4 style="margin-bottom:15px;">General info</h4>
        <div class="info-fila">
            <span>Estatus</span>
            <span class="badge badge-verde">Activo</span>
        </div>
        <div class="info-fila">
            <span><i class="fas fa-building"></i> Compania</span>
            <strong style="color:var(--rojo);">Auto Master</strong>
        </div>
        <div class="info-fila">
            <span><i class="fas fa-phone"></i> Numero celular</span>
            <strong>(430) 065-7387</strong>
        </div>
        <div class="info-fila">
            <span><i class="fas fa-envelope"></i> Email</span>
            <strong>daniel@automaster.com</strong>
        </div>
        <div class="info-fila">
            <span><i class="fas fa-map-marker-alt"></i> Direccion</span>
            <strong style="color:var(--rojo);">Agua Prieta, Sonora, Mx</strong>
        </div>
    </div>

    <div class="seccion">
        <div class="tabs"><button class="tab activo">Actividad</button></div>
        <div class="timeline" id="timeline-actividad">
            <!-- JS llena esto leyendo localStorage -->
        </div>
    </div>
</div>

<script>
// MISMA lista maestra que ordenes.php y nota-remision.php
const tareasPerfil = [
    {id:1,veh:'Dodge Atitud 2020',hora:'14:00 - Mar 03, 2026',desc:'Viene a un servicio completo: cambio de bujias, cambio de aceite, cambio de filtros (aceite y aire), tambien escanear el sistema del carro para ver errores.',estadoInicial:'Pendiente',iniciales:['DG','RM']},
    {id:2,veh:'Nissan Altima 2019',hora:'14:30 - Mar 02, 2026',desc:'Cambio de amortiguadores a carro Nissan Altima, motor 4 cilindros.',estadoInicial:'En Progreso',iniciales:['DG']},
    {id:3,veh:'Nissan Altima 2018',hora:'14:00 - Mar 01, 2026',desc:'Se realizo cambio de llantas a carro Nissan Altima, motor 4 cilindros.',estadoInicial:'Terminado',iniciales:['DG','RM','LP']},
    {id:4,veh:'Toyota Corolla 2021',hora:'10:00 - Mar 03, 2026',desc:'Servicio de mantenimiento a carro Toyota Corolla. Cambio de aceite y revision general.',estadoInicial:'Pendiente',iniciales:['DG']},
    {id:5,veh:'Ford F-150 2022',hora:'09:00 - Feb 28, 2026',desc:'Cambio de balatas delanteras y traseras. Revision de discos de freno.',estadoInicial:'En Progreso',iniciales:['DG','RM']},
    {id:6,veh:'Chevrolet Spark 2020',hora:'11:30 - Feb 27, 2026',desc:'Diagnostico de falla en sistema electrico. El carro no enciende correctamente.',estadoInicial:'Terminado',iniciales:['DG']},
];

function renderActividad() {
    const cambios = JSON.parse(localStorage.getItem('estadosTareas') || '{}');
    const especs = JSON.parse(localStorage.getItem('especsTareas') || '{}');
    const colorPorEstado = {'Pendiente':'rojo','En Progreso':'azul','Terminado':'verde'};

    const html = tareasPerfil.map(t => {
        const estado = cambios[t.id] || t.estadoInicial;
        const color = colorPorEstado[estado];
        const iconoTipo = estado === 'Terminado' ? 'fa-wrench' : 'fa-clock';
        const especGuardada = especs[t.id];

        return `
            <div class="timeline-item">
                <div class="timeline-icono"><i class="fas ${iconoTipo}"></i></div>
                <div class="timeline-contenido">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <strong>${t.veh}</strong>
                        <span class="badge badge-${color}">${estado}</span>
                    </div>
                    <span class="texto-claro">${t.hora}</span>
                    <div class="caja-mensaje">${t.desc}</div>
                    ${especGuardada ? `
                        <div class="caja-mensaje" style="background:#f0f9ff;border-left:3px solid var(--azul);font-size:13px;">
                            <strong style="color:var(--azul);">Trabajo realizado:</strong><br>${especGuardada}
                        </div>
                    ` : ''}
                    <div class="iniciales">
                        ${t.iniciales.map(i => `<span>${i}</span>`).join('')}
                    </div>
                </div>
            </div>
        `;
    }).join('');

    document.getElementById('timeline-actividad').innerHTML = html;
}

document.addEventListener('DOMContentLoaded', renderActividad);
</script>