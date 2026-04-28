<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tareas Asignadas - Auto Master</title>
    <!-- tareas-asignadas.php -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/estilos-generales.css">
</head>
<body>

    <div class="contenedor">

        <?php include '../includes/menu_admin.php'; ?>

        <main class="contenido">
            <header class="cabecera">
                <div class="cabecera-titulo">
                    <h1>Tareas Asignadas</h1>
                </div>
                <div class="cabecera-acciones">
                    <button><i class="fas fa-search"></i></button>
                    <button><i class="fas fa-bell"></i></button>
                    <button><i class="fas fa-question-circle"></i></button>
                </div>
            </header>

            <div class="pagina">
                <div class="pagina-titulo">
                    <h2>Mis Tareas Asignadas</h2>
                    <p>Lista de trabajos pendientes y en progreso</p>
                </div>

                <!-- Resumen -->
                <div class="tarjetas-resumen" style="grid-template-columns: repeat(3, 1fr);">
                    <div class="tarjeta-resumen">
                        <div class="tarjeta-icono naranja"><i class="fas fa-clock"></i></div>
                        <div class="tarjeta-info"><h3>3</h3><span>Pendientes</span></div>
                    </div>
                    <div class="tarjeta-resumen">
                        <div class="tarjeta-icono azul"><i class="fas fa-spinner"></i></div>
                        <div class="tarjeta-info"><h3>2</h3><span>En Progreso</span></div>
                    </div>
                    <div class="tarjeta-resumen">
                        <div class="tarjeta-icono verde"><i class="fas fa-check"></i></div>
                        <div class="tarjeta-info"><h3>15</h3><span>Completadas</span></div>
                    </div>
                </div>

                <!-- Lista de tareas -->
                <div class="tarjeta">
                    <div class="tarjeta-header">
                        <h3>Tareas Actuales</h3>
                    </div>
                    <div class="tarjeta-body" style="padding: 0;">
                        <table class="tabla">
                            <thead>
                                <tr>
                                    <th>ORDEN</th>
                                    <th>VEHICULO</th>
                                    <th>CLIENTE</th>
                                    <th>TAREA</th>
                                    <th>ESTADO</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>ORD-002</strong></td>
                                    <td>Nissan Altima 2019</td>
                                    <td>Maria Rodriguez</td>
                                    <td>Cambio de amortiguadores</td>
                                    <td><span class="badge badge-azul">En Progreso</span></td>
                                    <td>
                                        <button class="btn btn-secundario" style="padding:6px 12px;font-size:12px;">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>ORD-004</strong></td>
                                    <td>Toyota Corolla 2021</td>
                                    <td>Ana Gutierrez</td>
                                    <td>Afinacion mayor</td>
                                    <td><span class="badge badge-naranja">Pendiente</span></td>
                                    <td>
                                        <button class="btn btn-primario" style="padding:6px 12px;font-size:12px;">
                                            <i class="fas fa-play"></i> Iniciar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>ORD-005</strong></td>
                                    <td>Ford F-150 2022</td>
                                    <td>Roberto Sanchez</td>
                                    <td>Cambio de frenos</td>
                                    <td><span class="badge badge-azul">En Progreso</span></td>
                                    <td>
                                        <button class="btn btn-secundario" style="padding:6px 12px;font-size:12px;">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>ORD-006</strong></td>
                                    <td>Honda Civic 2020</td>
                                    <td>Pedro Martinez</td>
                                    <td>Revision general</td>
                                    <td><span class="badge badge-naranja">Pendiente</span></td>
                                    <td>
                                        <button class="btn btn-primario" style="padding:6px 12px;font-size:12px;">
                                            <i class="fas fa-play"></i> Iniciar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>

    </div>

    <script src="../JS/menu.js"></script>
</body>
</html>