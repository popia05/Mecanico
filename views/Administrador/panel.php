<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel - Auto Master</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/estilos-generales.css">
</head>
<body>

    <div class="contenedor">

        <?php include '../includes/menu_admin.php'; ?>

        <main class="contenido">
            <header class="cabecera">
                <div class="cabecera-titulo">
                    <h1>Panel Principal</h1>
                </div>
                <div class="cabecera-acciones">
                    <button><i class="fas fa-search"></i></button>
                    <button><i class="fas fa-bell"></i></button>
                    <button><i class="fas fa-question-circle"></i></button>
                </div>
            </header>

            <div class="pagina">
                <div class="pagina-titulo">
                    <h2>Bienvenido al Panel</h2>
                    <p>Sistema de gestion - Fuel Injection Auto Master</p>
                </div>

                <!-- Tarjetas de resumen -->
                <div class="tarjetas-resumen">
                    <div class="tarjeta-resumen">
                        <div class="tarjeta-icono azul"><i class="fas fa-car"></i></div>
                        <div class="tarjeta-info"><h3>12</h3><span>Ordenes Activas</span></div>
                    </div>
                    <div class="tarjeta-resumen">
                        <div class="tarjeta-icono verde"><i class="fas fa-check-circle"></i></div>
                        <div class="tarjeta-info"><h3>45</h3><span>Completadas</span></div>
                    </div>
                    <div class="tarjeta-resumen">
                        <div class="tarjeta-icono naranja"><i class="fas fa-clock"></i></div>
                        <div class="tarjeta-info"><h3>3</h3><span>Pendientes</span></div>
                    </div>
                    <div class="tarjeta-resumen">
                        <div class="tarjeta-icono rojo"><i class="fas fa-tools"></i></div>
                        <div class="tarjeta-info"><h3>8</h3><span>Herramientas</span></div>
                    </div>
                </div>

                <!-- Accesos rapidos -->
                <div class="seccion-titulo">
                    <h3>Accesos Rapidos</h3>
                </div>
                <div class="accesos-rapidos">
                    <a href="index.php?p=gestion" class="acceso-rapido">
                        <i class="fas fa-plus-circle"></i>
                        <span>Nueva Orden</span>
                    </a>
                    <a href="index.php?p=inventario" class="acceso-rapido">
                        <i class="fas fa-boxes"></i>
                        <span>Ver Inventario</span>
                    </a>
                    <a href="index.php?p=nota-remision" class="acceso-rapido">
                        <i class="fas fa-file-alt"></i>
                        <span>Crear Nota</span>
                    </a>
                    <a href="index.php?p=ordenes" class="acceso-rapido">
                        <i class="fas fa-list-check"></i>
                        <span>Mis Tareas</span>
                    </a>
                </div>

            </div>
        </main>

    </div>

    <script src="../JS/menu.js"></script>
</body>
</html>