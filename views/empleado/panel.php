<?php
require_once '../../php/db_conexion.php';
?>

<style>
.panel-bienvenida {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    color: #fff;
    padding: 28px 32px;
    border-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}
.panel-bienvenida h2 { margin: 0 0 6px 0; font-size: 24px; color: #fff; }
.panel-bienvenida p { margin: 0; opacity: 0.85; font-size: 14px; }
.panel-fecha {
    background: rgba(255,255,255,0.12);
    padding: 10px 18px;
    border-radius: 30px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
}

.panel-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}
.panel-stat {
    background: #fff;
    padding: 18px 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 14px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.panel-stat-icono {
    width: 48px; height: 48px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 20px;
    flex-shrink: 0;
}
.panel-stat-icono.rojo { background: #dc2626; }
.panel-stat-icono.azul { background: #3b82f6; }
.panel-stat-icono.verde { background: #16a34a; }
.panel-stat h3 { margin: 0; font-size: 26px; color: #1f2937; line-height: 1; }
.panel-stat span { color: #64748b; font-size: 13px; display: block; margin-top: 4px; }

.panel-titulo {
    font-size: 18px; color: #1f2937;
    margin: 0 0 16px 0; font-weight: 600;
}

.panel-acceso-grande {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 18px;
    margin-bottom: 30px;
}
.panel-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 22px;
    text-decoration: none;
    color: inherit;
    display: flex;
    gap: 16px;
    transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.panel-card:hover {
    transform: translateY(-3px);
    border-color: #dc2626;
    box-shadow: 0 8px 20px rgba(220,38,38,0.12);
}
.panel-card-icono {
    width: 56px; height: 56px;
    border-radius: 10px;
    background: linear-gradient(135deg, #dc2626, #991b1b);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}
.panel-card-cont { flex: 1; }
.panel-card-cont h3 { margin: 0 0 6px 0; font-size: 17px; color: #1f2937; }
.panel-card-cont p { margin: 0 0 10px 0; font-size: 13px; color: #64748b; line-height: 1.5; }
.panel-card-link { color: #dc2626; font-weight: 600; font-size: 13px; }

.panel-acceso-mini {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 12px;
}
.panel-card-mini {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: inherit;
    transition: background 0.2s, border-color 0.2s;
}
.panel-card-mini:hover {
    background: #fef2f2;
    border-color: #dc2626;
}
.panel-card-mini-icono {
    width: 40px; height: 40px;
    background: #f3f4f6;
    color: #dc2626;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.panel-card-mini h4 { margin: 0 0 2px 0; font-size: 14px; color: #1f2937; }
.panel-card-mini span { font-size: 12px; color: #64748b; }
</style>

<div class="panel-bienvenida">
    <div>
        <h2>Bienvenido, <?= $nombreEmpleado ?></h2>
        <p>Aqui esta el resumen de tu jornada de hoy</p>
    </div>
    <div class="panel-fecha">
        <i class="fas fa-calendar-alt"></i>
        <span id="fecha-actual"></span>
    </div>
</div>

<div class="panel-stats">
    <div class="panel-stat">
        <div class="panel-stat-icono rojo"><i class="fas fa-clock"></i></div>
        <div>
            <h3 id="stat-pendientes"><?= $pendientes ?></h3>
            <span>Tareas Pendientes</span>
        </div>
    </div>
    <div class="panel-stat">
        <div class="panel-stat-icono azul"><i class="fas fa-tools"></i></div>
        <div>
            <h3 id="stat-progreso"><?= $enProgreso ?></h3>
            <span>En Progreso</span>
        </div>
    </div>
    <div class="panel-stat">
        <div class="panel-stat-icono verde"><i class="fas fa-check-circle"></i></div>
        <div>
            <h3 id="stat-terminadas"><?= $terminadas ?></h3>
            <span>Terminadas</span>
        </div>
    </div>
</div>

<h3 class="panel-titulo">Accesos Rapidos</h3>

<div class="panel-acceso-grande">
    <a href="index.php?p=ordenes" class="panel-card">
        <div class="panel-card-icono"><i class="fas fa-clipboard-list"></i></div>
        <div class="panel-card-cont">
            <h3>Tareas Asignadas</h3>
            <p>Revisa las ordenes que el administrador te asigno y actualiza su estado</p>
            <span class="panel-card-link">Ver tareas <i class="fas fa-arrow-right"></i></span>
        </div>
    </a>
    <a href="index.php?p=gestion" class="panel-card">
        <div class="panel-card-icono"><i class="fas fa-clipboard"></i></div>
        <div class="panel-card-cont">
            <h3>Gestión de Ordenes</h3>
            <p>Vista general de todas tus ordenes de trabajo en un solo lugar</p>
            <span class="panel-card-link">Ver ordenes <i class="fas fa-arrow-right"></i></span>
        </div>
    </a>
</div>

<h3 class="panel-titulo">Otras Opciones</h3>

<div class="panel-acceso-mini">
    <a href="index.php?p=inventario" class="panel-card-mini">
        <div class="panel-card-mini-icono"><i class="fas fa-wrench"></i></div>
        <div>
            <h4>Ver Inventario</h4>
            <span>Consultar refacciones</span>
        </div>
    </a>
    <a href="index.php?p=nota-remision" class="panel-card-mini">
        <div class="panel-card-mini-icono"><i class="fas fa-file-invoice"></i></div>
        <div>
            <h4>Nota De Remision</h4>
            <span>Generar notas de tareas</span>
        </div>
    </a>
    <a href="index.php?p=perfil" class="panel-card-mini">
        <div class="panel-card-mini-icono"><i class="fas fa-user"></i></div>
        <div>
            <h4>Mi Perfil</h4>
            <span>Ver mi informacion</span>
        </div>
    </a>
</div>

<script>
const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
const dias = ['Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado'];
const hoy = new Date();
document.getElementById('fecha-actual').textContent =
    `${dias[hoy.getDay()]}, ${hoy.getDate()} de ${meses[hoy.getMonth()]}`;

function actualizarContadores() {
    const tareas = [
        {id:1, estadoInicial:'Pendiente'},
        {id:2, estadoInicial:'En Progreso'},
        {id:3, estadoInicial:'Terminado'},
        {id:4, estadoInicial:'Pendiente'},
        {id:5, estadoInicial:'En Progreso'},
        {id:6, estadoInicial:'Terminado'},
    ];
    const cambios = JSON.parse(localStorage.getItem('estadosTareas') || '{}');
    let p = 0, e = 0, t = 0;
    tareas.forEach(tarea => {
        const estado = cambios[tarea.id] || tarea.estadoInicial;
        if (estado === 'Pendiente') p++;
        else if (estado === 'En Progreso') e++;
        else if (estado === 'Terminado') t++;
    });
    document.getElementById('stat-pendientes').textContent = p;
    document.getElementById('stat-progreso').textContent = e;
    document.getElementById('stat-terminadas').textContent = t;
}
actualizarContadores();
</script>