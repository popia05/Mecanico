function toggleSubmenu(el) {
    el.classList.toggle('abierto');
    const submenu = el.nextElementSibling;
    if (submenu && submenu.classList.contains('submenu')) {
        submenu.classList.toggle('abierto');
    }
}

function cambiarTab(btn, tabId) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('activo'));
    document.querySelectorAll('.tab-contenido').forEach(c => c.classList.remove('activo'));
    btn.classList.add('activo');
    document.getElementById(tabId).classList.add('activo');
}