function toggleSubmenu(el) {
    el.classList.toggle('abierto');
    const sub = el.nextElementSibling;
    if (sub && sub.classList.contains('submenu')) sub.classList.toggle('abierto');
}

function toggleTarea(el) {
    el.parentElement.classList.toggle('abierto');
}

// Guarda el cambio de estado en localStorage para que persista entre paginas
function guardarEstadoTarea(id, estado) {
    const cambios = JSON.parse(localStorage.getItem('estadosTareas') || '{}');
    cambios[id] = estado;
    localStorage.setItem('estadosTareas', JSON.stringify(cambios));
}

function obtenerEstadoTarea(id, estadoDefault) {
    const cambios = JSON.parse(localStorage.getItem('estadosTareas') || '{}');
    return cambios[id] || estadoDefault;
}

// TAREAS ASIGNADAS: cambiar estado con botones
function cambiarEstado(btn, nuevoEstado, color) {
    const card = btn.closest('.tarea-card');
    const id = card.dataset.id;
    const badge = card.querySelector('.badge-estado');
    const botones = btn.parentElement.querySelectorAll('.btn-estado');

    botones.forEach(b => b.classList.remove('activo-rojo','activo-azul','activo-verde'));
    btn.classList.add('activo-' + color);

    badge.textContent = nuevoEstado;
    badge.className = 'badge badge-' + color + ' badge-estado';
    card.dataset.estado = nuevoEstado.toLowerCase();

    // Guardar en localStorage
    if (id) guardarEstadoTarea(id, nuevoEstado);
}

function guardarEspec(btn) {
    const textarea = btn.closest('.especificaciones').querySelector('textarea');
    if (textarea.value.trim() === '') {
        alert('Por favor escribe una descripcion del trabajo');
        return;
    }
    // Guardar especificaciones tambien
    const card = btn.closest('.tarea-card');
    const id = card.dataset.id;
    if (id) {
        const especs = JSON.parse(localStorage.getItem('especsTareas') || '{}');
        especs[id] = textarea.value;
        localStorage.setItem('especsTareas', JSON.stringify(especs));
    }
    alert('Especificaciones guardadas correctamente');
}

function filtrarTareas(btn, estado) {
    document.querySelectorAll('.tab-tarea').forEach(t => t.classList.remove('activo'));
    btn.classList.add('activo');

    document.querySelectorAll('.tarea-card').forEach(c => {
        if (estado === 'todos' || c.dataset.estado === estado) {
            c.style.display = '';
        } else {
            c.style.display = 'none';
        }
    });
}

function filtrarInv(btn, estado) {
    document.querySelectorAll('.btn-filtro').forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');

    document.querySelectorAll('#tbody-inv tr').forEach(tr => {
        if (estado === 'todos' || tr.dataset.estado === estado) {
            tr.style.display = '';
        } else {
            tr.style.display = 'none';
        }
    });
}

function buscarInventario() {
    const q = document.getElementById('buscar-inv').value.toLowerCase();
    document.querySelectorAll('#tbody-inv tr').forEach(tr => {
        tr.style.display = tr.dataset.nombre.includes(q) ? '' : 'none';
    });
}

// GESTION: Iniciar -> "En Progreso"
function iniciarOrden(btn) {
    const tr = btn.closest('tr');
    const id = tr.dataset.id;
    const badge = tr.querySelector('.celda-estado');

    badge.textContent = 'En Progreso';
    badge.className = 'badge badge-azul celda-estado';

    btn.textContent = 'Completar';
    btn.setAttribute('onclick', 'completarOrden(this)');

    if (id) guardarEstadoTarea(id, 'En Progreso');
}

// GESTION: Completar -> "Terminado" (aparece en Nota De Remision)
function completarOrden(btn) {
    const tr = btn.closest('tr');
    const id = tr.dataset.id;
    const badge = tr.querySelector('.celda-estado');

    badge.textContent = 'Terminado';
    badge.className = 'badge badge-verde celda-estado';

    btn.remove();

    if (id) {
        guardarEstadoTarea(id, 'Terminado');
        // Aviso visual
        mostrarAviso('Tarea terminada. Ya esta lista en Nota De Remision.');
    }
}

function mostrarAviso(msg) {
    const div = document.createElement('div');
    div.style.cssText = 'position:fixed;top:20px;right:20px;background:#16a34a;color:white;padding:14px 22px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.2);z-index:9999;font-size:14px;font-weight:500;';
    div.textContent = msg;
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 3500);
}

// Al cargar la pagina, sincronizar el estado guardado en localStorage con la UI
function sincronizarEstadosGuardados() {
    const cambios = JSON.parse(localStorage.getItem('estadosTareas') || '{}');
    const colorPorEstado = {'Pendiente':'rojo','En Progreso':'azul','Terminado':'verde'};

    // Sincronizar en Ordenes Asignadas
    document.querySelectorAll('.tarea-card').forEach(card => {
        const id = card.dataset.id;
        if (id && cambios[id]) {
            const estado = cambios[id];
            const color = colorPorEstado[estado];
            const badge = card.querySelector('.badge-estado');
            if (badge) {
                badge.textContent = estado;
                badge.className = 'badge badge-' + color + ' badge-estado';
            }
            card.dataset.estado = estado.toLowerCase();

            const botones = card.querySelectorAll('.btn-estado');
            botones.forEach(b => b.classList.remove('activo-rojo','activo-azul','activo-verde'));
            botones.forEach(b => {
                if (b.textContent.trim().toLowerCase() === estado.toLowerCase() ||
                    (estado === 'En Progreso' && b.textContent.trim() === 'En progreso')) {
                    b.classList.add('activo-' + color);
                }
            });
        }

        // Cargar especificaciones guardadas
        const especs = JSON.parse(localStorage.getItem('especsTareas') || '{}');
        if (id && especs[id]) {
            const textarea = card.querySelector('.especificaciones textarea');
            if (textarea) textarea.value = especs[id];
        }
    });

    // Sincronizar en Gestion de Ordenes
    document.querySelectorAll('tr[data-id]').forEach(tr => {
        const id = tr.dataset.id;
        if (id && cambios[id]) {
            const estado = cambios[id];
            const color = colorPorEstado[estado];
            const badge = tr.querySelector('.celda-estado');
            if (badge) {
                badge.textContent = estado;
                badge.className = 'badge badge-' + color + ' celda-estado';
            }

            // Actualizar botones de accion
            const accionTd = tr.querySelector('td:last-child');
            if (accionTd) {
                const btnAccion = accionTd.querySelector('.celda-accion');
                if (estado === 'Terminado' && btnAccion) {
                    btnAccion.remove();
                } else if (estado === 'En Progreso' && btnAccion) {
                    btnAccion.textContent = 'Completar';
                    btnAccion.setAttribute('onclick', 'completarOrden(this)');
                } else if (estado === 'Pendiente' && btnAccion) {
                    btnAccion.textContent = 'Iniciar';
                    btnAccion.setAttribute('onclick', 'iniciarOrden(this)');
                }
            }
        }
    });
}

// Auto-abrir tarea cuando vienes de Gestion con ?id=X
document.addEventListener('DOMContentLoaded', function() {
    sincronizarEstadosGuardados();

    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');
    if (id && window.location.search.includes('p=ordenes')) {
        const card = document.getElementById('tarea-' + id);
        if (card) {
            card.classList.add('abierto');
            setTimeout(() => {
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 200);
        }
    }
});