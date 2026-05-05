<?php
require_once '../../php/db_conexion.php';

// Obtener todos los empleados
$empleados = [];
try {
    $stmt = $conexion->query("SELECT * FROM empleados ORDER BY nombre ASC");
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empleados - Auto Master</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/estilos-generales.css">
    <style>
        /* ── Grid de tarjetas ── */
        .empleados-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
            margin-top: 24px;
        }

        /* ── Tarjeta individual ── */
        .emp-card {
            background: var(--tarjeta, #fff);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
        }
        .emp-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 28px rgba(0,0,0,0.13);
        }

        /* Franja decorativa superior — ahora más alta para alojar el avatar */
        .emp-card-banner {
            height: 110px;
            background: linear-gradient(135deg, #c0c8e8 0%, #d8d0f0 100%);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Avatar centrado DENTRO del banner */
        .emp-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #e05a6e;
            color: #fff;
            font-size: 30px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid #fff;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(224,90,110,0.4);
            /* Sin position absolute — fluye dentro del banner */
        }
        .emp-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Botón editar sobre la tarjeta */
        .emp-btn-editar {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255,255,255,0.85);
            border: none;
            border-radius: 8px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 600;
            color: #e05a6e;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            transition: background 0.2s;
        }
        .emp-btn-editar:hover { background: #fff; }

        /* Cuerpo de la tarjeta */
        .emp-card-body {
            padding: 16px 20px 20px;
            text-align: center;
        }
        .emp-nombre {
            font-size: 16px;
            font-weight: 700;
            color: #e05a6e;
            margin: 0 0 4px;
        }
        .emp-puesto {
            font-size: 13px;
            color: var(--color-gris, #888);
            margin: 0 0 16px;
        }

        /* Info general */
        .emp-info-titulo {
            font-size: 12px;
            font-weight: 700;
            color: var(--color-gris, #888);
            text-align: left;
            margin-bottom: 10px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .emp-info-lista {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: left;
        }
        .emp-info-lista li {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            padding: 7px 0;
            border-bottom: 1px solid var(--borde, #f0f0f0);
            color: var(--texto, #333);
        }
        .emp-info-lista li:last-child { border-bottom: none; }
        .emp-info-lista li i {
            width: 18px;
            color: var(--color-gris, #aaa);
            font-size: 13px;
            flex-shrink: 0;
        }
        .emp-info-lista .etiqueta {
            color: var(--color-gris, #888);
            min-width: 90px;
            font-size: 12px;
        }
        .emp-info-lista .valor {
            color: var(--texto, #222);
            font-weight: 500;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .emp-info-lista .valor.link {
            color: #e05a6e;
            font-weight: 600;
        }

        /* Badge estatus */
        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }
        .badge-activo   { background: #dcfce7; color: #16a34a; }
        .badge-inactivo { background: #fee2e2; color: #dc2626; }

        /* Cabecera de página */
        .pagina-cabecera {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        /* Sin empleados */
        .sin-empleados {
            text-align: center;
            padding: 60px 20px;
            color: var(--color-gris, #888);
        }
        .sin-empleados i {
            font-size: 48px;
            margin-bottom: 16px;
            display: block;
        }
    </style>
</head>
<body>
<div class="contenedor">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="sidebar-logo-img"><i class="fas fa-wrench"></i></div>
            <div class="sidebar-logo-texto"><h2>Menu</h2><span>Categorias</span></div>
        </div>
        <nav class="sidebar-nav">
            <a href="panel.php" class="nav-item"><i class="fas fa-th-large"></i><span>Panel</span></a>

            <div class="nav-item submenu-toggle" onclick="toggleSubmenu('perfil')">
                <i class="fas fa-user"></i><span>Usuario</span>
                <i class="fas fa-chevron-down flecha" id="flecha-perfil"></i>
            </div>
            <div class="submenu" id="submenu-perfil">
                <a href="informacion-admin.php" class="nav-item"><i class="fas fa-info-circle"></i><span>Administrador</span></a>
                <a href="informacion-empleados.php" class="nav-item activo"><i class="fas fa-info-circle"></i><span>Empleados</span></a>
                <a href="agregar-empleado.php" class="nav-item"><i class="fas fa-user-plus"></i><span>Agregar Empleados</span></a>
            </div>

            <div class="nav-item submenu-toggle" onclick="toggleSubmenu('tareas')">
                <i class="fas fa-tasks"></i><span>Gestion de Tareas</span>
                <i class="fas fa-chevron-down flecha" id="flecha-tareas"></i>
            </div>
            <div class="submenu" id="submenu-tareas">
                <a href="gestion-ordenes.php" class="nav-item"><i class="fas fa-info-circle"></i><span>Gestion de Ordenes</span></a>
                <a href="inventario.php" class="nav-item"><i class="fas fa-wrench"></i><span>Ver Inventario</span></a>
                <a href="auditoria.php" class="nav-item"><i class="fas fa-shield-alt"></i><span>Auditoria</span></a>
            </div>

            <a href="nota-remision.php" class="nav-item"><i class="fas fa-file-invoice"></i><span>Notas de Remisión</span></a>
            <a href="login.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Cerrar Sesión</span></a>
        </nav>
        <div class="sidebar-usuario">
            <div class="sidebar-usuario-avatar">DG</div>
            <div class="sidebar-usuario-info"><h4>Daniel G.</h4><span>Administrador</span></div>
        </div>
    </aside>

    <!-- CONTENIDO -->
    <main class="contenido">
        <header class="cabecera">
            <div class="cabecera-acciones">
                <button><i class="fas fa-search"></i></button>
                <button><i class="fas fa-bell"></i></button>
                <button><i class="fas fa-question-circle"></i></button>
            </div>
        </header>

        <div class="pagina">
            <div class="pagina-titulo">
                <h2>Detalles de Empleados</h2>
                <p>Consulta la información de todos los empleados registrados.</p>
            </div>

            <?php if (!empty($empleados)): ?>
            <div class="empleados-grid">
                <?php foreach ($empleados as $emp): 
                    // Iniciales para avatar
                    $iniciales = strtoupper(
                        substr($emp['nombre'] ?? '', 0, 1) .
                        substr($emp['apellido'] ?? '', 0, 1)
                    );
                    // Nombre completo
                    $nombre_completo = htmlspecialchars(trim(($emp['nombre'] ?? '') . ' ' . ($emp['apellido'] ?? '')));
                    // Puesto (usa 'puesto' o 'cargo' según lo que tenga)
                    $puesto = htmlspecialchars($emp['puesto'] ?? $emp['cargo'] ?? 'Empleado');
                    // Correo, teléfono, fecha
                    $correo  = htmlspecialchars($emp['correo'] ?? '—');
                    $telefono = htmlspecialchars($emp['telefono'] ?? '(430) 065-7387');
                    $fecha   = !empty($emp['fecha_ingreso']) ? date('d/m/Y', strtotime($emp['fecha_ingreso'])) : '—';
                    // Foto
                    $foto_src = !empty($emp['foto']) ? '../../uploads/empleados/' . $emp['foto'] : null;
                ?>
                <div class="emp-card">
                    <div class="emp-card-banner">
                        <div class="emp-avatar">
                            <?php if ($foto_src): ?>
                                <img src="<?= $foto_src ?>" alt="<?= $nombre_completo ?>">
                            <?php else: ?>
                                <?= $iniciales ?>
                            <?php endif; ?>
                        </div>
                        <a href="editar-empleado.php?id=<?= $emp['id_empleado'] ?>" class="emp-btn-editar">
                            <i class="fas fa-pen"></i> Editar
                        </a>
                    </div>

                    <div class="emp-card-body">
                        <p class="emp-nombre"><?= $nombre_completo ?></p>
                        <p class="emp-puesto"><?= $puesto ?></p>

                        <p class="emp-info-titulo">General info</p>
                        <ul class="emp-info-lista">
                            <li>
                                <i class="fas fa-circle-dot"></i>
                                <span class="etiqueta">Estatus</span>
                                <span class="valor">
                                    <span class="badge badge-activo">Activo</span>
                                </span>
                            </li>
                            <li>
                                <i class="fas fa-building"></i>
                                <span class="etiqueta">Compañía</span>
                                <span class="valor link">Auto Master <i class="fas fa-arrow-up-right-from-square" style="font-size:10px;"></i></span>
                            </li>
                            <li>
                                <i class="fas fa-phone"></i>
                                <span class="etiqueta">Numero celular</span>
                                <span class="valor"><?= $telefono ?></span>
                            </li>
                            <li>
                                <i class="fas fa-envelope"></i>
                                <span class="etiqueta">Email</span>
                                <span class="valor link"><?= $correo ?></span>
                            </li>
                            <li>
                                <i class="fas fa-location-dot"></i>
                                <span class="etiqueta">Dirección</span>
                                <span class="valor">Agua Prieta, Sonora, Mx</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php else: ?>
            <div class="sin-empleados">
                <i class="fas fa-users-slash"></i>
                <p>No hay empleados registrados aún.</p>
                <a href="agregar-empleado.php" class="btn btn-primario" style="margin-top:16px;">Agregar primer empleado</a>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script src="../../js/menu.js"></script>
</body>
</html>