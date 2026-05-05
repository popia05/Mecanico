/* ========================================
   FUNCIONALIDAD DEL MENU LATERAL
   ======================================== */

// Toggle submenu (abrir/cerrar)
function toggleSubmenu(id) {
    const submenu = document.getElementById('submenu-' + id);
    const flecha = document.getElementById('flecha-' + id);
    
    if (submenu) {
        submenu.classList.toggle('abierto');
    }
    
    if (flecha) {
        flecha.classList.toggle('rotado');
    }
}

// Abrir submenus que contienen el item activo
document.addEventListener('DOMContentLoaded', function() {
    const itemActivo = document.querySelector('.submenu .nav-item.activo');
    
    if (itemActivo) {
        const submenuPadre = itemActivo.closest('.submenu');
        if (submenuPadre) {
            submenuPadre.classList.add('abierto');
            
            const submenuId = submenuPadre.id.replace('submenu-', '');
            const flecha = document.getElementById('flecha-' + submenuId);
            if (flecha) {
                flecha.classList.add('rotado');
            }
        }
    }
});
