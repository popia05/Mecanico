<h2 style="text-align:center;margin-bottom:25px;">Notas De Remision</h2>

<div class="seccion" style="background:var(--fondo-claro);">
    <h3 style="text-align:center;border-bottom:2px solid #6366f1;padding-bottom:15px;margin-bottom:25px;">
        Tareas Listas Para Remision
    </h3>

    <div id="contenedor-remision">
        <!-- JS llena esto dinamicamente -->
    </div>

    <div id="sin-tareas" style="display:none;text-align:center;padding:40px;color:var(--texto-claro);">
        <i class="fas fa-clipboard-check" style="font-size:48px;margin-bottom:15px;opacity:0.4;"></i>
        <p>No hay tareas terminadas pendientes de remision.</p>
        <p style="font-size:13px;margin-top:8px;">Termina una tarea en <strong>Tareas Asignadas</strong> o <strong>Gestion de Ordenes</strong> y aparecera aqui.</p>
    </div>
</div>

<script>
// Lista maestra de TODAS las tareas del sistema (debe coincidir con ordenes.php y gestion.php)
const todasLasTareas = [
    {id:1,veh:'Dodge Atitud 2020',hora:'14:00 - Mar 03, 2026',desc:'Viene a un servicio completo: cambio de bujias, cambio de aceite, cambio de filtros (aceite y aire), tambien escanear el sistema del carro para ver errores.',estadoInicial:'Pendiente'},
    {id:2,veh:'Nissan Altima 2019',hora:'14:30 - Mar 02, 2026',desc:'Cambio de amortiguadores a carro Nissan Altima, motor 4 cilindros.',estadoInicial:'En Progreso'},
    {id:3,veh:'Nissan Altima 2018',hora:'14:00 - Mar 01, 2026',desc:'Se realizo cambio de llantas a carro Nissan Altima, motor 4 cilindros.',estadoInicial:'Terminado'},
    {id:4,veh:'Toyota Corolla 2021',hora:'10:00 - Mar 03, 2026',desc:'Servicio de mantenimiento a carro Toyota Corolla. Cambio de aceite y revision general.',estadoInicial:'Pendiente'},
    {id:5,veh:'Ford F-150 2022',hora:'09:00 - Feb 28, 2026',desc:'Cambio de balatas delanteras y traseras. Revision de discos de freno.',estadoInicial:'En Progreso'},
    {id:6,veh:'Chevrolet Spark 2020',hora:'11:30 - Feb 27, 2026',desc:'Diagnostico de falla en sistema electrico. El carro no enciende correctamente.',estadoInicial:'Terminado'},
];

function renderRemisiones() {
    const cambios = JSON.parse(localStorage.getItem('estadosTareas') || '{}');
    const remisionesHechas = JSON.parse(localStorage.getItem('remisionesHechas') || '[]');

    // Filtrar tareas que estan terminadas Y que no tienen remision hecha
    const terminadas = todasLasTareas.filter(t => {
        const estadoActual = cambios[t.id] || t.estadoInicial;
        return estadoActual === 'Terminado' && !remisionesHechas.includes(t.id);
    });

    const cont = document.getElementById('contenedor-remision');
    const vacio = document.getElementById('sin-tareas');

    if (terminadas.length === 0) {
        cont.innerHTML = '';
        vacio.style.display = 'block';
        return;
    }

    vacio.style.display = 'none';
    cont.innerHTML = terminadas.map(t => `
        <div class="tarea-lista">
            <div class="tarea-icono-redondo"><i class="fas fa-tools"></i></div>
            <div class="tarea-lista-contenido">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <strong>${t.veh}</strong>
                    <span class="badge badge-verde">Terminado</span>
                </div>
                <span class="texto-claro" style="display:block;margin-bottom:10px;">${t.hora}</span>
                <div class="caja-mensaje" style="margin-bottom:10px;">${t.desc}</div>
                <div style="text-align:right;">
                    <a href="index.php?p=nota-detalle&id=${t.id}" class="btn btn-rojo">
                        <i class="fas fa-file-invoice"></i> Agregar Nota
                    </a>
                </div>
            </div>
        </div>
    `).join('');
}

document.addEventListener('DOMContentLoaded', renderRemisiones);
</script>