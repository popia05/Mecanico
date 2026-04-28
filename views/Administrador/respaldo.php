<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respaldo del Sistema - Auto Master</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/estilos-generales.css">
    <style>
        .respaldo-seccion { background: var(--blanco); border: 1px solid var(--borde); border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .respaldo-seccion h3 { font-size: 16px; font-weight: 600; margin-bottom: 5px; display: flex; align-items: center; gap: 8px; }
        .respaldo-seccion p { font-size: 13px; color: var(--texto-claro); margin-bottom: 15px; }
        .ultimo-respaldo { border: 2px solid #06b6d4; border-radius: 10px; padding: 14px 20px; display: flex; align-items: center; gap: 10px; color: #06b6d4; font-weight: 600; margin-bottom: 20px; background: var(--blanco); }
        .tipo-btns { display: flex; gap: 10px; margin-bottom: 15px; }
        .btn-tipo { padding: 8px 20px; border-radius: 6px; border: 1px solid var(--borde); background: var(--fondo); cursor: pointer; font-size: 14px; }
        .btn-tipo.activo { background: var(--rojo); color: white; border-color: var(--rojo); }
        .toggle-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--borde); font-size: 14px; }
        .toggle-row:last-child { border-bottom: none; }
        .toggle { position: relative; display: inline-block; width: 44px; height: 24px; }
        .toggle input { opacity: 0; width: 0; height: 0; }
        .toggle-slider { position: absolute; cursor: pointer; inset: 0; background: #ccc; border-radius: 24px; transition: .3s; }
        .toggle-slider::before { content: ''; position: absolute; width: 18px; height: 18px; left: 3px; bottom: 3px; background: white; border-radius: 50%; transition: .3s; }
        .toggle input:checked + .toggle-slider { background: var(--rojo); }
        .toggle input:checked + .toggle-slider::before { transform: translateX(20px); }
        .advertencia-restaurar { background: #fffbeb; border: 1px solid #f59e0b; border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #92400e; display: flex; align-items: center; gap: 8px; flex: 1; }
        .restaurar-row { display: flex; align-items: center; gap: 15px; }
        .historial-tabla { width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 10px; }
        .historial-tabla th { text-align: left; padding: 10px 8px; color: var(--texto-claro); font-size: 12px; font-weight: 600; border-bottom: 1px solid var(--borde); }
        .historial-tabla td { padding: 12px 8px; border-bottom: 1px solid var(--borde); }
        .historial-tabla tr:last-child td { border-bottom: none; }
        .estado-completado { color: #10b981; font-weight: 500; }
        .estado-fallido    { color: var(--rojo); font-weight: 500; }
        .tipo-automatico { color: #06b6d4; }
        .tipo-completo   { color: #f59e0b; }
        .tipo-parcial    { color: #8b5cf6; }
    </style>
</head>
<body>

    <div class="contenedor">

        <?php include '../includes/menu_admin.php'; ?>

        <main class="contenido">
            <header class="cabecera">
                <div class="cabecera-titulo">
                    <h1>Respaldo del Sistema</h1>
                </div>
                <div class="cabecera-acciones">
                    <button><i class="fas fa-search"></i></button>
                    <button><i class="fas fa-bell"></i></button>
                    <button><i class="fas fa-question-circle"></i></button>
                </div>
            </header>

            <div class="pagina">

                <div class="pagina-titulo">
                    <h2>Respaldo del Sistema</h2>
                    <p>Gestiona los respaldos y restauraciones de datos del taller</p>
                </div>

                <!-- Último respaldo -->
                <div class="ultimo-respaldo">
                    <i class="fas fa-clock"></i>
                    Mar 05, 2026 &nbsp;Ultimo respaldo
                </div>

                <!-- Crear nuevo respaldo -->
                <div class="respaldo-seccion">
                    <h3><i class="fas fa-database" style="color:var(--rojo);"></i> Crear nuevo respaldo</h3>
                    <p>Genera un respaldo manual de los datos del sistema</p>
                    <div style="font-size:14px;font-weight:500;margin-bottom:10px;">Tipo de respaldo</div>
                    <div class="tipo-btns">
                        <button class="btn-tipo activo" onclick="seleccionarTipo(this)">
                            <i class="fas fa-database"></i> Completo
                        </button>
                    </div>
                    <button class="btn btn-primario" id="btn-crear-respaldo" onclick="crearRespaldo()">
                        <i class="fas fa-download"></i> Crear respaldo ahora
                    </button>
                </div>

                <!-- Respaldo automático -->
                <div class="respaldo-seccion">
                    <h3><i class="fas fa-shield-alt" style="color:var(--rojo);"></i> Respaldo automático</h3>
                    <p>Programacion de respaldos automáticos</p>
                    <div class="toggle-row">
                        <span>Respaldo diario (2:00 AM)</span>
                        <label class="toggle">
                            <input type="checkbox" id="toggle-diario">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <span>Respaldo semanal (Domingos)</span>
                        <label class="toggle">
                            <input type="checkbox" id="toggle-semanal" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <span>Notificar si falla un respaldo</span>
                        <label class="toggle">
                            <input type="checkbox" id="toggle-notificar" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- Restaurar datos -->
                <div class="respaldo-seccion">
                    <h3><i class="fas fa-history" style="color:#f59e0b;"></i> Restaurar datos</h3>
                    <p>Restaura el sistema a un punto anterior desde un respaldo</p>
                    <div class="restaurar-row">
                        <div class="advertencia-restaurar">
                            <i class="fas fa-exclamation-triangle"></i>
                            Restaurar un respaldo reemplazara los datos actuales del sistema. Esta acción no se puede deshacer.
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="toggle-restaurar" onchange="confirmarRestauracion(this)">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- Historial de respaldos -->
                <div class="respaldo-seccion">
                    <h3>Historial de respaldos</h3>
                    <p>Registro de todos los respaldos realizados</p>
                    <table class="historial-tabla">
                        <thead>
                            <tr>
                                <th>RESPALDO</th>
                                <th>FECHA</th>
                                <th>TIPO</th>
                                <th>ESTADO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Respaldo completo del sistema</td>
                                <td>Mar 05, 2026 - 02:00</td>
                                <td><span class="tipo-automatico">Automatico</span></td>
                                <td><span class="estado-completado">Completado</span></td>
                            </tr>
                            <tr>
                                <td>Respaldo de inventario</td>
                                <td>Mar 02, 2026 - 14:00</td>
                                <td><span class="tipo-completo">Completo</span></td>
                                <td><span class="estado-completado">Completado</span></td>
                            </tr>
                            <tr>
                                <td>Respaldo de ordenes de trabajo</td>
                                <td>Mar 01, 2026 - 02:00</td>
                                <td><span class="tipo-parcial">Parcial</span></td>
                                <td><span class="estado-fallido">Fallido</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>

    </div>

    <script src="../JS/menu.js"></script>
    <script>
        function seleccionarTipo(btn) {
            document.querySelectorAll('.btn-tipo').forEach(b => b.classList.remove('activo'));
            btn.classList.add('activo');
        }

        function crearRespaldo() {
            const btn = document.getElementById('btn-crear-respaldo');
            btn.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i> Creando respaldo...';
            btn.disabled = true;

            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-download"></i> Crear respaldo ahora';
                btn.disabled = false;
                mostrarNotificacion('Respaldo completado', 'verde');
            }, 2500);
        }

        function confirmarRestauracion(checkbox) {
            if (checkbox.checked) {
                const confirmar = confirm('¿Estás seguro? Esta acción reemplazará todos los datos actuales del sistema y no se puede deshacer.');
                if (!confirmar) {
                    checkbox.checked = false;
                } else {
                    mostrarNotificacion('Restauración completada', 'verde');
                }
            }
        }

        function mostrarNotificacion(mensaje, tipo) {
            const color = tipo === 'verde' ? '#10b981' : '#ef4444';
            const div = document.createElement('div');
            div.style.cssText = `position:fixed;top:20px;right:20px;background:${color};color:white;padding:12px 20px;border-radius:8px;font-size:14px;font-weight:500;z-index:9999;display:flex;align-items:center;gap:8px;`;
            div.innerHTML = `<i class="fas fa-check-circle"></i> ${mensaje}`;
            document.body.appendChild(div);
            setTimeout(() => div.remove(), 3000);
        }
    </script>
</body>
</html>