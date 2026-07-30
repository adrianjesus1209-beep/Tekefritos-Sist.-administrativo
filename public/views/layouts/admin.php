<!DOCTYPE html>
<?php
// Inicializacion de variables inyectadas por AdminController::handle().
// Se definen aquí con valores por defecto para satisfacer el análisis estático del IDE.
// En tiempo de ejecucion, el controlador siempre les asigna el valor real antes de incluir este archivo.
$rol_usuario          = $rol_usuario          ?? '';
$vista                = $vista                ?? '';
$registros            = $registros            ?? [];
$usuario              = $usuario              ?? [];
$config               = $config               ?? [];
$proveedores          = $proveedores          ?? [];
$usuario_nombre       = $usuario_nombre       ?? '';
$mensaje_exito        = $mensaje_exito        ?? '';
$mensaje_error        = $mensaje_error        ?? '';
$tasas                = $tasas                ?? ['usd' => 0, 'eur' => 0, 'fecha' => ''];
$tasa_cambio          = $tasa_cambio          ?? 0;
$tasa_iva             = $tasa_iva             ?? 0;
$precios_sincronizados = $precios_sincronizados ?? false;
$total_productos      = $total_productos      ?? 0;
$ventas_totales       = $ventas_totales       ?? 0;
$ventas_hoy           = $ventas_hoy           ?? ['suma' => 0, 'total_pedidos' => 0];
$rentabilidad_total   = $rentabilidad_total   ?? 0;
$total_alertas        = $total_alertas        ?? 0;
$alertas_productos    = $alertas_productos    ?? null;
$alertas_materia      = $alertas_materia      ?? null;
$top_productos        = $top_productos        ?? null;
$ultimos_pedidos      = $ultimos_pedidos      ?? null;
$reporte              = $reporte              ?? '';
$datos                = $datos                ?? null;
$documentos_array     = $documentos_array     ?? null;
$redes                = $redes                ?? [];
$usuario_id           = $usuario_id           ?? 0;
$usuario_correo       = $usuario_correo       ?? '';
?>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teke'fritos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="public/css/estilos_admin.css">
    <link rel="icon" type="image/webp" href="data:image/webp;base64,<?php echo base64_encode(file_get_contents(__DIR__ . '/../../../public/img/Logo.webp')); ?>">
</head>
<body>

<div class="d-flex vh-100 overflow-hidden">

    <!-- Sidebar (Offcanvas) -->
    <nav class="offcanvas offcanvas-start text-white" tabindex="-1" id="adminSidebar" aria-label="Navegación" style="width:250px;background:#15161D;">
        <div class="offcanvas-header border-bottom border-secondary py-1">
            <div>
                <a href="index.php" class="text-white text-decoration-none fw-bold fs-5">
                    <img src="public/img/Logo.webp" alt="Teke'fritos" style="height:46px;width:auto;vertical-align:middle;margin-right:8px;">Teke'fritos
                </a>
                <div class="small text-secondary mt-1"><?php echo htmlspecialchars($rol_usuario); ?></div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body p-2 d-flex flex-column overflow-hidden">
            <?php $items = [
                ['Inicio', 'bi-house-door'],
                ['Productos', 'bi-box-seam'],
                ['Pedidos', 'bi-cart3'],
            ];
            if ($rol_usuario !== 'vendedor') {
                $items[] = ['Clientes', 'bi-people'];
            }
            if ($rol_usuario === 'admin') {
                $items[] = ['Personal', 'bi-person-badge'];
            }
            if ($rol_usuario !== 'vendedor') {
                $items = array_merge($items, [
                    ['Proveedores', 'bi-truck'],
                    ['MateriaPrima', 'bi-database'],
                    ['Perdidas', 'bi-exclamation-triangle'],
                    ['Documentos', 'bi-file-earmark-text'],
                    ['Configuración', 'bi-gear'],
                    ['Reportes', 'bi-graph-up'],
                    ['Papelera', 'bi-trash'],
                ]);
            } ?>
            <?php foreach ($items as $it): ?>
                <?php $url = 'index.php?action=admin&vista=' . urlencode($it[0]); ?>
                <a href="<?php echo $url; ?>"
                   class="d-flex align-items-center gap-1 px-3 py-1 rounded text-decoration-none mb-1
                   <?php echo ($vista === $it[0]) ? 'bg-danger text-white' : 'text-white-50 hover-bg' ?>">
                    <i class="bi <?php echo $it[1]; ?>"></i>
                    <span><?php echo $it[0]; ?></span>
                </a>
            <?php endforeach; ?>
            <div class="mt-auto pt-1 border-top border-secondary">
                <a href="#" id="btn-perfil-top" class="d-flex align-items-center gap-1 mb-1 text-white text-decoration-none">
                    <i class="bi bi-person-circle fs-5"></i>
                    <span><?php echo htmlspecialchars($usuario_nombre); ?></span>
                </a>
                <a href="index.php?action=logout" class="btn btn-outline-light btn-sm w-100">
                    <i class="bi bi-box-arrow-right me-1"></i>Salir
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow-1 d-flex flex-column overflow-hidden bg-light main-area">

        <!-- Topbar -->
        <header class="bg-white border-bottom px-4 py-2 d-flex justify-content-between align-items-center flex-shrink-0">
            <div class="d-flex align-items-center gap-2">
                <button class="btn d-md-none p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-label="Menú" style="font-size:1.5rem;line-height:1;border:none;background:none;color:#1e293b;">
                    <i class="bi bi-list"></i>
                </button>
                <h5 class="mb-0 fw-bold"><?php echo htmlspecialchars($vista); ?></h5>
            </div>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <!-- Desktop: badges individuales -->
                <div class="tasa-badge topbar-badge d-none d-md-flex" id="badge-usd">
                    <span style="font-weight:800;font-size:0.85rem;">$</span>
                    <span style="font-size:0.6rem;font-weight:600;text-transform:uppercase;">USD</span>
                    <span class="tasa-valor" style="font-weight:700;font-size:0.9rem;"><?php echo number_format($tasas['usd'], 2); ?></span>
                    <span style="font-size:0.65rem;">Bs</span>
                </div>
                <div class="tasa-badge-eur topbar-badge d-none d-md-flex" id="badge-eur">
                    <span style="font-weight:800;font-size:0.85rem;">€</span>
                    <span style="font-size:0.6rem;font-weight:600;text-transform:uppercase;">EUR</span>
                    <span class="tasa-valor" style="font-weight:700;font-size:0.9rem;"><?php echo number_format($tasas['eur'], 2); ?></span>
                    <span style="font-size:0.65rem;">Bs</span>
                </div>
                <!-- Mobile: badge combinado -->
                <div class="tasa-badge-mobile d-md-none">
                    <span style="font-weight:700;font-size:0.7rem;">$<?php echo number_format($tasas['usd'], 2); ?></span>
                    <span style="color:var(--color-muted);font-size:0.6rem;">/</span>
                    <span style="font-weight:700;font-size:0.7rem;">€<?php echo number_format($tasas['eur'], 2); ?></span>
                    <span style="color:var(--color-muted);font-size:0.6rem;">Bs</span>
                    <button class="btn p-0 ms-1" onclick="refrescarTasa()" style="font-size:0.65rem;line-height:1;border:none;background:none;color:var(--color-muted);" title="Actualizar">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
                <button id="btn-refresh-tasa" class="btn btn-sm btn-outline-secondary px-2 py-0" onclick="refrescarTasa()" title="Actualizar tasa">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
                <small class="text-muted d-none d-md-inline" id="tasa-timestamp" style="font-size:0.65rem;">
                    <?php if (!empty($tasas['_desactualizado'])): ?><i class="bi bi-exclamation-triangle text-warning" title="Dato desactualizado"></i><?php endif; ?>
                    <?php if (!empty($tasas['_estimado'])): ?><i class="bi bi-exclamation-circle text-danger" title="Valor estimado"></i><?php endif; ?>
                    <?php echo $tasas['fecha'] ?? ''; ?>
                </small>
            </div>
        </header>

        <!-- Page Content -->
        <div class="page-content flex-grow-1 overflow-auto p-4 d-flex flex-column">

            <div class="page-alerts">
            <?php if ($mensaje_exito): ?>
                <div id="alerta-exito" class="alert alert-success alerta-success fade show d-flex align-items-center">
                    <?php echo $mensaje_exito; ?>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" style="font-size:0.7rem;"></button>
                </div>
                <script>setTimeout(function(){var e=document.getElementById('alerta-exito');if(e){var btn=e.querySelector('.btn-close');if(btn)btn.click();}},3000);</script>
            <?php endif; ?>
            <?php if ($mensaje_error): ?>
                <div class="alert alert-danger alerta-error fade show d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-circle"></i> <?php echo $mensaje_error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" style="font-size:0.7rem;"></button>
                </div>
            <?php endif; ?>
            <?php if (!empty($precios_sincronizados)): ?>
                <div id="alerta-sync" class="alert alert-info alerta-info fade show d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-repeat"></i> Precios y costos sincronizados con la tasa BCV: <strong>$1 = Bs. <?php echo number_format($tasa_cambio, 2); ?></strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" style="font-size:0.7rem;"></button>
                </div>
                <script>setTimeout(function(){var e=document.getElementById('alerta-sync');if(e){var btn=e.querySelector('.btn-close');if(btn)btn.click();}},5000);</script>
            <?php endif; ?>

            <script>if(window.location.search.match(/[?&](exito|error|info)=/)){var u=new URL(window.location);u.searchParams.delete('exito');u.searchParams.delete('error');u.searchParams.delete('info');window.history.replaceState({},document.title,u.pathname+u.search+u.hash);}</script>
            </div>

            <?php if ($vista === 'Inicio'): ?>
            <div class="page-section">

            <!-- Metric Cards -->
            <div class="row g-3 mb-4">
                <div class="col">
                    <div class="card metric-card h-100" style="background:#eef2ff;border:2px solid #b4bdf5;border-radius:var(--radio);">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="metric-icon metric-icon-blue"><i class="bi bi-box-seam fs-4"></i></div>
                            <div>
                                <div class="metric-label">Productos</div>
                                <div class="metric-value"><?php echo $total_productos; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card metric-card h-100" style="background:#f0fdf4;border:2px solid #a7ecc3;border-radius:var(--radio);">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="metric-icon metric-icon-green"><i class="bi bi-currency-dollar fs-4"></i></div>
                            <div>
                                <div class="metric-label">Ventas Completadas</div>
                                <div class="metric-value" style="font-size:1.1rem;">Bs. <?php echo number_format($ventas_totales, 2); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card metric-card h-100" style="background:#fffbeb;border:2px solid #fde68a;border-radius:var(--radio);cursor:pointer;" onclick="window.location='index.php?action=admin&vista=Pedidos'">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="metric-icon" style="background:#fef3c7;color:#d97706;"><i class="bi bi-cash-stack fs-4"></i></div>
                            <div>
                                <div class="metric-label">Ventas de Hoy</div>
                                <div class="metric-value" style="font-size:1.3rem;">Bs. <?php echo number_format($ventas_hoy['suma'], 2); ?> <span style="font-size:0.8rem;color:var(--color-info);font-weight:400;">· <?php echo $ventas_hoy['total_pedidos']; ?> <?php echo $ventas_hoy['total_pedidos'] == 1 ? 'pedido' : 'pedidos'; ?></span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card metric-card h-100" style="background:#f3e8ff;border:2px solid #c49df0;border-radius:var(--radio);">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="metric-icon" style="background:#e9d5ff;color:#9333ea;"><i class="bi bi-graph-up fs-4"></i></div>
                            <div>
                                <div class="metric-label">Rentabilidad</div>
                                <div class="metric-value" style="font-size:1.1rem;">Bs. <?php echo number_format($rentabilidad_total, 2); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card metric-card h-100" style="background:<?php echo $total_alertas > 0 ? '#fef2f2' : '#f8fafc'; ?>;border:2px solid <?php echo $total_alertas > 0 ? '#fdb8b8' : '#e2e8f0'; ?>;border-radius:var(--radio);">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="metric-icon <?php echo $total_alertas > 0 ? 'metric-icon-red' : 'metric-icon-gray'; ?>"><i class="bi bi-exclamation-triangle fs-4"></i></div>
                            <div>
                                <div class="metric-label">Alertas Stock</div>
                                <div class="metric-value" style="<?php echo $total_alertas > 0 ? 'color:var(--color-danger)' : 'color:var(--color-muted)'; ?>"><?php echo $total_alertas; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory + Top Products -->
            <div class="row g-3 mb-4">
                <div class="col-md-7">
                    <div class="card card-custom h-100">
                        <div class="card-custom-header">
                            <i class="bi bi-exclamation-circle" style="color:var(--color-danger);"></i>
                            Inventario Critico
                        </div>
                        <div class="card-body p-0">
                            <div class="d-flex gap-3 px-3 py-2" style="border-bottom:1px solid var(--color-hover);background:#fafafa;">
                                <span class="metric-sub d-flex align-items-center gap-1">
                                    <span style="width:8px;height:8px;border-radius:50%;background:var(--color-danger);display:inline-block;"></span>
                                    Productos (bajo stock)
                                </span>
                                <span class="metric-sub d-flex align-items-center gap-1">
                                    <span style="width:8px;height:8px;border-radius:50%;background:var(--color-warning);display:inline-block;"></span>
                                    Insumos (baja cantidad)
                                </span>
                            </div>
                            <?php if ($total_alertas > 0): ?>
                                <div class="list-scroll">
                                    <?php while($alertas_productos instanceof mysqli_result && ($p = mysqli_fetch_assoc($alertas_productos))): ?>
                                        <div class="list-item">
                                            <div class="list-item-label">
                                                <i class="bi bi-box" style="color:var(--color-muted);font-size:0.8rem;"></i>
                                                <span><?php echo htmlspecialchars($p['nombre']); ?></span>
                                            </div>
                                            <span class="badge" style="background:#fef2f2;color:var(--color-danger);font-size:0.75rem;font-weight:600;"><?php echo $p['stock']; ?>/<?php echo $p['stock_minimo']; ?> uds</span>
                                        </div>
                                    <?php endwhile; ?>
                                    <?php while($alertas_materia instanceof mysqli_result && ($m = mysqli_fetch_assoc($alertas_materia))): ?>
                                        <div class="list-item">
                                            <div class="list-item-label">
                                                <i class="bi bi-droplet" style="color:#3b82f6;font-size:0.8rem;"></i>
                                                <span><?php echo htmlspecialchars($m['nombre']); ?> <span class="metric-sub">(Insumo)</span></span>
                                            </div>
                                            <span class="badge" style="background:#fffbeb;color:var(--color-warning);font-size:0.75rem;font-weight:600;"><?php echo $m['cantidad']; ?>/<?php echo $m['minimo']; ?> <?php echo htmlspecialchars($m['unidad']); ?></span>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4" style="color:var(--color-muted);">
                                    <i class="bi bi-emoji-smile fs-2 d-block mb-2"></i>
                                    Todo el inventario esta saludable
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card card-custom h-100">
                        <div class="card-custom-header">
                            <i class="bi bi-graph-up" style="color:var(--color-success);"></i>
                            Top Mas Vendidos
                        </div>
                        <div class="card-body p-0">
                            <?php
                            $rank = 0;
                            while($top_productos instanceof mysqli_result && ($top = mysqli_fetch_assoc($top_productos))):
                                $rank++;
                                $medalla = $rank . '.';
                            ?>
                                <div class="list-item">
                                    <div class="list-item-label">
                                        <span style="min-width:24px;color:var(--color-muted);font-weight:600;"><?php echo $medalla; ?></span>
                                        <span><?php echo htmlspecialchars($top['nombre']); ?></span>
                                    </div>
                                    <span class="fw-bold" style="color:var(--color-success);font-size:0.85rem;"><?php echo $top['total_ventas']; ?> vendidos</span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ultimos Pedidos -->
            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <div class="card card-custom h-100">
                        <div class="card-custom-header">
                            <i class="bi bi-receipt" style="color:#9333ea;"></i>
                            Ultimos Pedidos
                        </div>
                        <div class="card-body p-0" style="max-height:140px;overflow-y:auto;">
                            <?php while($ultimos_pedidos instanceof mysqli_result && ($u = mysqli_fetch_assoc($ultimos_pedidos))): ?>
                                <div class="list-item">
                                    <div class="list-item-label">
                                        <span style="font-weight:600;color:var(--color-muted);font-size:0.8rem;">#<?php echo $u['id']; ?></span>
                                        <span><?php echo htmlspecialchars($u['cliente_nombre'] ?? 'Mostrador'); ?></span>
                                    </div>
                                    <span class="fw-bold list-item-value" style="color:var(--color-danger);">Bs. <?php echo number_format($u['total'], 2); ?></span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div style="height:1rem;"></div>
            </div>

            <?php endif; ?>

            <?php if ($vista === 'Reportes' && !$reporte): ?>
            <div class="page-section">
            <!-- Landing tarjetas Reportes -->
            <div class="row g-3">
                <div class="col-md-4">
                    <a href="?action=admin&vista=Reportes&reporte=clientes" class="text-decoration-none">
                        <div class="card metric-card h-100" style="cursor:pointer;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-people fs-1 text-danger"></i>
                                <h5 class="mt-3 mb-1">R. Clientes</h5>
                                <p class="metric-sub mb-0">Compras, totales y frecuencia</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="?action=admin&vista=Reportes&reporte=bitacora" class="text-decoration-none">
                        <div class="card metric-card h-100" style="cursor:pointer;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-clock-history fs-1 text-danger"></i>
                                <h5 class="mt-3 mb-1">Bitácora</h5>
                                <p class="metric-sub mb-0">Últimos 500 eventos del sistema</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="?action=admin&vista=Reportes&reporte=iva" class="text-decoration-none">
                        <div class="card metric-card h-100" style="cursor:pointer;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-receipt fs-1 text-danger"></i>
                                <h5 class="mt-3 mb-1">R. IVA</h5>
                                <p class="metric-sub mb-0">Ventas gravadas, exentas e IVA por período</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="?action=admin&vista=Reportes&reporte=perdidas" class="text-decoration-none">
                        <div class="card metric-card h-100" style="cursor:pointer;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-exclamation-triangle fs-1 text-danger"></i>
                                <h5 class="mt-3 mb-1">R. Perdidas</h5>
                                <p class="metric-sub mb-0">Historial de productos perdidos o desechados</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12 mt-2">
                    <a href="index.php?action=descargar-pdf&tipo=todos" target="_blank" class="btn btn-danger w-100 py-3">
                        <i class="bi bi-file-pdf fs-5 me-2"></i> Descargar PDF Unificado (Clientes + Bitácora + IVA)
                    </a>
                </div>
            </div>
            <?php elseif ($vista !== 'Inicio' && ($datos || is_array($documentos_array))): ?>
            <?php
                $reporte_titulos = ['clientes' => 'Clientes', 'bitacora' => 'Bitácora', 'iva' => 'IVA', 'perdidas' => 'Perdidas'];
                $titulo_data = $reporte_titulos[$reporte] ?? $vista;
                $pdf_tipos = ['clientes' => 'R. Clientes', 'bitacora' => 'Bitácora', 'iva' => 'R. IVA', 'perdidas' => 'R. Perdidas'];
            ?>
            <div class="card card-custom">
                <div class="card-custom-header d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-bottom:1px solid var(--color-borde);">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="mb-0">Lista de <?php echo $titulo_data; ?></h5>
                        <?php if($vista === 'Productos'): ?>
                            <div class="input-group input-group-sm" style="width:250px">
                                <span class="input-group-text" style="background:#e8eaed;border-color:#a0a7b0;border-right:none;"><i class="bi bi-search text-muted" style="opacity:0.6;"></i></span>
                                <input type="text" id="buscador-productos" class="form-control form-control-sm" placeholder="Buscar..." style="background:#e8eaed;border-color:#a0a7b0;border-left:none;" onkeyup="filtrarTabla()">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <?php if($reporte): ?>
                            <a href="?action=admin&vista=Reportes" class="btn btn-sm btn-outline-secondary">← Volver</a>
                            <a href="?action=admin&vista=Reportes&reporte=clientes" class="btn btn-sm <?php echo $reporte==='clientes'?'btn-danger':'btn-outline-danger'; ?>">R. Clientes</a>
                            <a href="?action=admin&vista=Reportes&reporte=bitacora" class="btn btn-sm <?php echo $reporte==='bitacora'?'btn-danger':'btn-outline-danger'; ?>">Bitácora</a>
                            <a href="?action=admin&vista=Reportes&reporte=iva" class="btn btn-sm <?php echo $reporte==='iva'?'btn-danger':'btn-outline-danger'; ?>">R. IVA</a>
                            <a href="?action=admin&vista=Reportes&reporte=perdidas" class="btn btn-sm <?php echo $reporte==='perdidas'?'btn-danger':'btn-outline-danger'; ?>">R. Perdidas</a>
                        <?php endif; ?>
                    <?php if($vista === 'Documentos'): ?>
                        <a href="index.php?action=llenar-talonario&tipo=Factura" class="btn btn-danger btn-sm"><i class="bi bi-plus-lg"></i> Nueva Factura</a>
                        <a href="index.php?action=llenar-talonario&tipo=Nota+de+Entrega" class="btn btn-outline-danger btn-sm"><i class="bi bi-plus-lg"></i> Nueva Nota de Entrega</a>
                    <?php elseif(in_array($vista, ['Productos', 'Categorias', 'Clientes', 'Proveedores', 'Pedidos', 'MateriaPrima', 'Perdidas', 'Personal', 'Configuración'])): ?>
                        <?php if($vista === 'Categorias'): ?>
                            <a href="?action=admin&vista=Productos" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Volver a Productos</a>
                        <?php endif; ?>
                        <?php if(($vista !== 'Categorias' || $rol_usuario === 'admin') && !($rol_usuario === 'vendedor' && in_array($vista, ['Productos', 'Clientes']))): ?>
                        <button class="btn btn-danger btn-sm" onclick="abrirModal('<?php echo $vista; ?>')">
                            <i class="bi bi-plus-lg"></i> <?php echo match($vista) {
                                'Productos'   => 'Nuevo Producto',
                                'Categorias'  => 'Nueva Categoria',
                                'Clientes'    => 'Nuevo Cliente',
                                'Proveedores' => 'Nuevo Proveedor',
                                'Pedidos'     => 'Nuevo Pedido',
                                'MateriaPrima'=> 'Nuevo Insumo',
                                'Perdidas'    => 'Registrar Perdida',
                                'Personal'    => 'Nuevo Usuario',
                                'Configuración' => 'Editar Configuración',
                                default       => 'Nuevo Registro',
                            }; ?>
                        </button>
                        <?php endif; ?>
                        <?php if($vista === 'Productos' && $rol_usuario === 'admin'): ?>
                            <a href="?action=admin&vista=Categorias" class="btn btn-outline-danger btn-sm"><i class="bi bi-tags"></i> Categorias</a>
                        <?php endif; ?>
                    <?php elseif($vista === 'Papelera' && $rol_usuario === 'admin'): ?>
                        <form method="POST" onsubmit="return vaciarPapeleraConfirmar(event)">
                            <input type="hidden" name="accion" value="vaciar_papelera">
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-trash3-fill me-1"></i> Vaciar Papelera
                            </button>
                        </form>
                    <?php endif; ?>
                    <?php if($reporte): ?>
                        <a href="index.php?action=descargar-pdf&tipo=<?php echo urlencode($pdf_tipos[$reporte]); ?>" class="btn btn-outline-danger btn-sm" target="_blank">
                            <i class="bi bi-file-pdf"></i> Descargar PDF
                        </a>
                    <?php endif; ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if($vista === 'Documentos'): ?>
                    <div class="p-2 border-bottom d-flex align-items-center gap-2 flex-wrap" style="background:#fff;">
                        <i class="bi bi-search text-muted"></i>
                        <input type="text" id="doc-search" class="form-control form-control-sm" placeholder="Buscar por nombre..." style="max-width:250px;" onkeyup="filtrarDocumentos()">
                        <span class="text-muted" style="font-size:0.8rem;">Desde:</span>
                        <input type="date" id="doc-date-from" class="form-control form-control-sm" style="max-width:160px;" onchange="filtrarDocumentos()">
                        <span class="text-muted" style="font-size:0.8rem;">Hasta:</span>
                        <input type="date" id="doc-date-to" class="form-control form-control-sm" style="max-width:160px;" onchange="filtrarDocumentos()">
                    </div>
                    <?php endif; ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 table-custom" id="tabla-datos">
                            <thead>
                                <tr>
                                    <?php if($vista === 'Documentos'): ?>
                                        <th>Archivo</th><th>Contacto</th><th>Tipo</th><th>Subido por</th><th>Fecha</th><th>Acciones</th>
                                    <?php elseif($vista === 'Productos'): ?>
                                        <th>Imagen</th><th>Nombre</th><th>Código</th><th>Peso</th><th>Precio Venta</th><th>Rentabilidad</th><th>Stock</th><th>Stock Mínimo</th><th>Tipo</th><th>IVA</th><th>Acciones</th>
                                    <?php elseif($vista === 'Categorias'): ?>
                                        <th>Nombre</th><th>Descripcion</th><th>Acciones</th>
                                    <?php elseif($vista === 'Clientes'): ?>
                                        <th>Nombre</th><th>Telefono</th><th>Correo</th><th>Categoria</th><th>Direccion</th><th>Acciones</th>
                                    <?php elseif($vista === 'Proveedores'): ?>
                                        <th>Nombre</th><th>Telefono</th><th>Correo</th><th>Direccion</th><th>Acciones</th>
                                    <?php elseif($vista === 'Pedidos'): ?>
                                        <th>ID</th><th>Cliente</th><th>Items</th><th>Total</th><th>Entrega</th><th>Estado</th><th>Fecha</th><th>Acciones</th>
                                    <?php elseif($vista === 'Perdidas'): ?>
                                        <th>Producto</th><th>Cantidad</th><th>Motivo</th><th>Fecha</th><th>Acciones</th>
                                    <?php elseif($vista === 'MateriaPrima'): ?>
                                        <th>ID</th><th>Nombre</th><th>Cantidad Actual</th><th>Proveedor</th><th>Acciones</th>
                                    <?php elseif($vista === 'Personal'): ?>
                                        <th>ID</th><th>Nombre</th><th>Correo</th><th>Rol</th><th>Acciones</th>
                                    <?php elseif($vista === 'Papelera'): ?>
                                        <th>Tipo</th><th>Nombre del Registro</th><th>Acciones</th>
                                    <?php elseif($reporte === 'clientes'): ?>
                                        <th>Cliente</th><th>Teléfono</th><th>Correo</th><th>Pedidos</th><th>Total Gastado</th><th>Ticket Prom.</th><th>Última Compra</th>
                                    <?php elseif($reporte === 'bitacora'): ?>
                                        <th>Fecha</th><th>Acción</th><th>Descripción</th><th>Usuario</th>
                                    <?php elseif($reporte === 'iva'): ?>
                                        <th>Período</th><th>Pedidos</th><th>Ventas Gravadas</th><th>IVA Generado</th><th>Ventas Exentas</th><th>Total Facturado</th>
                                    <?php elseif($reporte === 'perdidas'): ?>
                                        <th>Producto</th><th>Cantidad</th><th>Motivo</th><th>Fecha</th>
                                    <?php else: ?>
                                        <th>Detalle</th><th>Informacion</th><th>Acciones</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($vista === 'Documentos'): ?>
                                    <?php if (is_array($documentos_array) && count($documentos_array) > 0): ?>
                                        <?php foreach((array)$documentos_array as $row): ?>
                                            <?php
                                            $doc_tipo = $row['tipo'] ?? 'Otro';
                                            $icono_doc = $doc_tipo === 'Factura' ? 'bi-file-earmark-pdf' : ($doc_tipo === 'Nota de Entrega' ? 'bi-file-earmark-text' : 'bi-file-earmark');
                                            $doc_path = ($row['doc_tipo'] === 'Generado') ? 'public/uploads/facturas_generadas/' : 'public/uploads/documentos/';
                                            $has_file = !empty($row['archivo_real']);
                                            ?>
                                        <tr class="doc-row">
                                            <td><?php if ($has_file): ?><a href="<?php echo $doc_path . $row['archivo_real']; ?>" target="_blank" class="text-decoration-none fw-bold" style="color:var(--color-danger);"><i class="bi <?php echo $icono_doc; ?> me-1"></i> <?php echo htmlspecialchars($row['nombre_archivo']); ?></a><?php else: ?><span class="text-decoration-none fw-bold" style="color:var(--color-danger);"><i class="bi <?php echo $icono_doc; ?> me-1"></i> <?php echo htmlspecialchars($row['nombre_archivo']); ?></span><?php endif; ?></td>
                                            <td><?php echo htmlspecialchars($row['contacto_nombre'] ?? '—'); ?></td>
                                            <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($doc_tipo); ?></span></td>
                                            <td class="metric-sub"><?php echo htmlspecialchars($row['usuario_nombre'] ?? '—'); ?></td>
                                            <td class="metric-sub doc-fecha" style="white-space:nowrap;"><?php echo date('d/m/Y H:i', strtotime($row['fecha_subida'])); ?></td>
                                            <td>
                                                 <div class="d-inline-flex gap-1">
                                                    <a href="index.php?action=llenar-talonario&tipo=<?php echo urlencode($row['tipo']); ?>&id_documento=<?php echo $row['id']; ?>" class="btn-action btn-action-edit" title="Editar"><i class="bi bi-pencil"></i></a>
                                                    <form method="POST" style="display:inline" class="form-confirmar" data-msg="¿Mover este documento a la papelera?">
                                                        <input type="hidden" name="accion" value="eliminar_documento">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" class="btn-action btn-action-delete" title="Eliminar"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center text-muted py-3">No hay documentos.</td></tr>
                                    <?php endif; ?>
                                <?php elseif ($datos instanceof mysqli_result && mysqli_num_rows($datos) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($datos)): ?>
                                    <?php if($vista === 'Productos'):
                                            $min = intval($row['stock_minimo']);
                                            $stock_val = intval($row['stock']);
                                            $cl_stock = ($stock_val <= 0 || $stock_val <= $min) ? 'stock-danger' : (($stock_val < $min * 1.10) ? 'stock-warning' : 'stock-ok');
                                            $img_src = ($row['imagen'] && $row['imagen'] !== 'placeholder.png') ? 'public/uploads/productos/'.$row['imagen'] : 'public/img/masa-tequenos.png';
                                            $ganancia = $row['precio'] - $row['costo'];
                                            $cl_ganancia = ($ganancia > 0) ? 'text-success' : 'text-danger';
                                            $aplica_iva_row = intval($row['aplica_iva'] ?? 0);
                                            $tiene_iva = $aplica_iva_row > 0 && $tasa_iva > 0;
                                            $precio_total_iva = $tiene_iva ? $row['precio'] * (1 + $tasa_iva / 100) : $row['precio'];
                                            $margen_pct = $row['precio'] > 0 ? round($ganancia / $row['precio'] * 100, 1) : 0;
                                            $ganancia_usd = $tasa_cambio > 0 ? $ganancia / $tasa_cambio : 0;
                                        ?>
                                            <td><img src="<?php echo $img_src; ?>" style="width:40px;height:40px;object-fit:cover;border-radius:6px;" onerror="this.src='https://placehold.co/40x40?text=P'"></td>
                                            <td><strong><?php echo htmlspecialchars($row['nombre']); ?></strong><div class="text-muted" style="font-size:0.75rem;"><?php echo htmlspecialchars($row['categoria_nombre'] ?? 'Sin categoría'); ?></div></td>
                                            <td><code style="font-size:0.75rem;"><?php echo htmlspecialchars($row['codigo'] ?? ''); ?></code></td>
                                            <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($row['peso']); ?></span></td>
                                            <td><span class="fw-bold" style="font-size:0.9rem;">Bs. <?php echo number_format($precio_total_iva, 2); ?></span><?php if ($tiene_iva): ?><div class="text-muted" style="font-size:0.65rem;">(Neto: Bs. <?php echo number_format($row['precio'], 2); ?> + IVA <?php echo $tasa_iva; ?>%)</div><?php endif; ?><div style="font-size:0.65rem;color:var(--color-info);">$<?php echo number_format($row['precio_usd'] ?? 0, 2); ?></div></td>
                                            <td><span class="fw-bold <?php echo $ganancia >= 0 ? 'ganancia-positiva' : 'ganancia-negativa'; ?>" style="font-size:0.9rem;"><?php echo $ganancia >= 0 ? '+' : ''; ?>Bs. <?php echo number_format($ganancia, 2); ?></span><div class="text-muted" style="font-size:0.65rem;">(Costo: Bs. <?php echo number_format($row['costo'], 2); ?> | Margen: <?php echo $margen_pct; ?>%)</div><div style="font-size:0.65rem;color:var(--color-info);">$<?php echo number_format($ganancia_usd, 2); ?></div></td>
                                            <td><span class="<?php echo $cl_stock; ?>"><?php echo $row['stock']; ?></span></td>
                                            <td><span class="badge bg-light text-dark"><?php echo $row['stock_minimo']; ?></span></td>
                                            <td><span class="badge <?php echo ($row['tipo'] ?? 'Elaboracion') === 'Sin elaboracion' ? 'badge-tipo-sinelab' : 'badge-tipo-elab'; ?>"><?php echo ($row['tipo'] ?? 'Elaboracion') === 'Sin elaboracion' ? 'Sin elaboración' : 'Elaboración'; ?></span></td>
                                            <td><span class="badge <?php echo intval($row['aplica_iva'] ?? 0) > 0 ? 'badge-iva-si' : 'badge-iva-no'; ?>"><?php echo intval($row['aplica_iva'] ?? 0) > 0 ? 'Sí' : 'No'; ?></span></td>
                                        <?php elseif($vista === 'Categorias'): ?>
                                            <td><strong><?php echo htmlspecialchars($row['nombre']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['descripcion'] ?? ''); ?></td>
                                        <?php elseif($vista === 'Clientes'): ?>
                                            <td><strong><?php echo htmlspecialchars($row['nombre']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                                            <td><?php echo htmlspecialchars($row['correo']); ?></td>
                                            <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($row['categoria'] ?? 'Regular'); ?></span></td>
                                            <td><?php echo htmlspecialchars($row['direccion']); ?></td>
                                        <?php elseif($vista === 'Proveedores'): ?>
                                            <td><strong><?php echo htmlspecialchars($row['nombre']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                                            <td><?php echo htmlspecialchars($row['correo']); ?></td>
                                            <td><?php echo htmlspecialchars($row['direccion']); ?></td>
                                        <?php elseif($vista === 'Pedidos'):
                                            $est_cl = match($row['estado']) {
                                                'Completado' => 'estado-completado',
                                                'Cancelado' => 'estado-cancelado',
                                                default => 'estado-default',
                                            };
                                        ?>
                                            <td>#<?php echo $row['id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($row['cliente_nombre'] ?? 'Mostrador'); ?></strong></td>
                                            <td class="metric-sub"><?php echo htmlspecialchars($row['items'] ?? ''); ?></td>
                                            <td>Bs. <?php echo number_format($row['total'], 2); ?></td>
                                            <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($row['tipo_entrega'] ?? 'Local'); ?></span></td>
                                            <td><span class="badge <?php echo $est_cl; ?>"><?php echo htmlspecialchars($row['estado']); ?></span></td>
                                            <td class="metric-sub" style="white-space:nowrap;"><?php echo date('d/m/Y H:i', strtotime($row['fecha'])); ?></td>
                                        <?php elseif($vista === 'Perdidas'): ?>
                                            <td><strong><?php echo htmlspecialchars($row['prod_nombre']); ?></strong></td>
                                            <td><?php echo $row['cantidad']; ?></td>
                                            <td><?php echo htmlspecialchars($row['motivo']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></td>
                                        <?php elseif($vista === 'MateriaPrima'): ?>
                                            <td>#<?php echo $row['id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($row['nombre']); ?></strong></td>
                                            <td><?php echo $row['cantidad'] . ' ' . htmlspecialchars($row['unidad']); ?></td>
                                            <td><?php echo !empty($row['proveedor_nombre']) ? '<span class="badge bg-light text-dark border">' . htmlspecialchars($row['proveedor_nombre']) . '</span>' : '<span class="text-muted" style="font-size:0.8rem;">Sin proveedor</span>'; ?></td>
                                        <?php elseif($vista === 'Personal'):
                                            $badge_rol_cl = match($row['rol']) {
                                                'admin' => 'bg-danger text-white',
                                                'trabajador' => 'bg-primary text-white',
                                                'vendedor' => 'bg-success text-white',
                                                default => 'bg-secondary text-white',
                                            };
                                        ?>
                                            <td>#<?php echo $row['id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($row['nombre']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['correo']); ?></td>
                                            <td><span class="badge <?php echo $badge_rol_cl; ?>"><?php echo htmlspecialchars(strtoupper($row['rol'])); ?></span></td>
                                        <?php elseif($vista === 'Papelera'): ?>
                                            <td><strong><?php echo htmlspecialchars($row['nombre']); ?></strong> <span class="badge bg-danger bg-opacity-10 text-danger">Inhabilitado</span></td>
                                            <td><span class="badge" style="background:#e0e0e0;color:#555;"><?php echo htmlspecialchars($row['tipo_orig']); ?></span> | <?php echo htmlspecialchars($row['info_1']); ?></td>
                                        <?php elseif($reporte === 'clientes'): ?>
                                            <td><strong><?php echo htmlspecialchars($row['nombre']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['telefono'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['correo'] ?? ''); ?></td>
                                            <td><?php echo $row['total_pedidos']; ?></td>
                                            <td class="fw-bold text-success">Bs. <?php echo number_format($row['total_gastado'], 2); ?></td>
                                            <td>Bs. <?php echo number_format($row['ticket_promedio'], 2); ?></td>
                                            <td><?php echo $row['ultima_compra'] ? date('d/m/Y', strtotime($row['ultima_compra'])) : 'N/A'; ?></td>
                                        <?php elseif($reporte === 'bitacora'): ?>
                                            <td><?php echo date('d/m/Y H:i', strtotime($row['fecha'])); ?></td>
                                            <td><span class="badge bg-info text-white"><?php echo htmlspecialchars($row['accion']); ?></span></td>
                                            <td style="max-width:300px;white-space:normal;"><?php echo htmlspecialchars($row['descripcion'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['usuario_nombre'] ?? 'Sistema'); ?></td>
                                        <?php elseif($reporte === 'iva'): ?>
                                            <td><strong><?php echo htmlspecialchars($row['mes']); ?></strong></td>
                                            <td><?php echo $row['num_pedidos']; ?></td>
                                            <td class="fw-bold">Bs. <?php echo number_format($row['ventas_gravadas'], 2); ?></td>
                                            <td class="fw-bold text-success">Bs. <?php echo number_format($row['iva_generado'], 2); ?></td>
                                            <td>Bs. <?php echo number_format($row['ventas_exentas'], 2); ?></td>
                                            <td class="fw-bold text-danger">Bs. <?php echo number_format($row['total'], 2); ?></td>
                                        <?php elseif($reporte === 'perdidas'): ?>
                                            <td><strong><?php echo htmlspecialchars($row['prod_nombre']); ?></strong></td>
                                            <td><span class="badge bg-danger bg-opacity-10 text-danger"><?php echo $row['cantidad']; ?> uds</span></td>
                                            <td><?php echo htmlspecialchars($row['motivo']); ?></td>
                                            <td class="metric-sub"><?php echo htmlspecialchars($row['fecha_fmt']); ?></td>
                                        <?php endif; ?>

                                        <?php if(in_array($vista, ['Perdidas'])): ?>
                                        <td>
                                            <form method="POST" style="display:inline" class="form-confirmar" data-msg="Esta perdida se movera a la Papelera. Podras restaurarla o eliminarla permanentemente desde ahi.">
                                                <input type="hidden" name="accion" value="eliminar_perdida">
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" class="btn-action btn-action-delete" title="Mover a Papelera"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                        <?php elseif(!$reporte): ?>
                                        <td>
                                            <?php if($vista === 'Papelera'): ?>
                                                <div class="d-inline-flex gap-1">
                                                <form method="POST" style="display:inline">
                                                    <input type="hidden" name="accion" value="activar">
                                                    <input type="hidden" name="modulo_orig" value="<?php echo htmlspecialchars($row['tipo_orig']); ?>">
                                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-circle"></i> Reestablecer</button>
                                                </form>
                                                <?php if($rol_usuario !== 'vendedor'): ?>
                                                    <?php if($row['tipo_orig'] === 'Documentos'): ?>
                                                    <form method="POST" style="display:inline" class="form-confirmar" data-msg="¿Eliminar definitivamente este archivo? Se liberara espacio." data-danger="true">
                                                        <input type="hidden" name="accion" value="eliminar_permanente_documento">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar permanentemente"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                    <?php else: ?>
                                                    <form method="POST" style="display:inline" class="form-confirmar" data-msg="¿Eliminar definitivamente? Esta accion no se puede deshacer." data-danger="true">
                                                        <input type="hidden" name="accion" value="eliminar_permanente">
                                                        <input type="hidden" name="modulo_orig" value="<?php echo htmlspecialchars($row['tipo_orig']); ?>">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar permanentemente"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                </div>
                                            <?php elseif ($rol_usuario === 'vendedor' && in_array($vista, ['Productos', 'Clientes'])): ?>
                                            <?php else: ?>
                                                <div class="d-inline-flex gap-1 align-items-center">
                                                    <?php if (array_key_exists('estado', $row) && $rol_usuario !== 'vendedor' && $vista === 'Productos'): ?>
                                                    <form method="POST" style="display:inline" title="Cambiar Disponibilidad" onsubmit="return confirmarToggle(event)">
                                                        <input type="hidden" name="accion" value="toggle_estado_producto">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <input type="hidden" name="estado_actual" value="<?php echo htmlspecialchars($row['estado']); ?>">
                                                        <button type="submit" class="btn-action <?php echo $row['estado'] === 'Disponible' ? 'btn-action-toggle-on' : 'btn-action-toggle-off'; ?>">
                                                            <i class="bi bi-<?php echo $row['estado'] === 'Disponible' ? 'toggle-on' : 'toggle-off'; ?>"></i>
                                                        </button>
                                                    </form>
                                                    <?php endif; ?>
                                                    <button onclick='editarRegistro(<?php echo json_encode($row); ?>, "<?php echo htmlspecialchars($vista, ENT_QUOTES); ?>")' class="btn-action btn-action-edit"><i class="bi bi-pencil"></i></button>
                                                    <form method="POST" style="display:inline" onsubmit="confirmarForm(event, '<?php
                                                        $msg_eliminar = match($vista) {
                                                            'Productos'   => 'El producto se movera a la Papelera. Podras restaurarlo o eliminarlo permanentemente desde ahi.',
                                                            'Categorias'  => 'Esta categoria se eliminara permanentemente. Los productos asociados quedaran sin categoria.',
                                                            'Clientes'    => 'El cliente se movera a la Papelera. Podras restaurarlo o eliminarlo permanentemente desde ahi.',
                                                            'Proveedores' => 'El proveedor se movera a la Papelera. Podras restaurarlo o eliminarlo permanentemente desde ahi.',
                                                            'MateriaPrima'=> 'Este insumo se movera a la Papelera. Podras restaurarlo o eliminarlo permanentemente desde ahi.',
                                                            'Perdidas'    => 'Esta perdida se movera a la Papelera. Podras restaurarla o eliminarla permanentemente desde ahi.',
                                                            'Personal'    => 'Este usuario se inhabilitara y se movera a la Papelera. Podras restaurarlo o eliminarlo permanentemente desde ahi.',
                                                            'Pedidos'     => 'Este pedido se movera a la Papelera. Podras restaurarlo o eliminarlo permanentemente desde ahi.',
                                                            default       => 'Este registro se movera a la Papelera. Podras restaurarlo desde ahi.',
                                                        };
                                                        echo $msg_eliminar;
                                                    ?>')">
                                                        <input type="hidden" name="accion" value="<?php echo match($vista) {'Categorias'=>'eliminar_categoria',default=>'eliminar'}; ?>">
                                                        <input type="hidden" name="modulo" value="<?php echo $vista; ?>">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" class="btn-action btn-action-delete"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endwhile; ?>

                                <?php else: ?>
                                    <tr><td colspan="10" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>No hay datos registrados en esta seccion.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php elseif ($vista === 'Configuración'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Configuración del Sistema</h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="form-confirmar" data-msg="Guardar cambios en la configuracion?">
                        <input type="hidden" name="accion" value="guardar_config">
                        <div class="row g-3" style="max-width:500px;">
                            <div class="col-12 mt-3">
                                <label class="form-label fw-bold">Tasa de IVA (%)</label>
                                <div class="input-group">
                                    <input type="number" name="tasa_iva" class="form-control" value="<?php echo (float)$tasa_iva > 0 ? $tasa_iva : ''; ?>" step="0.01" min="0" max="100" placeholder="Ej: 16" required>
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="form-text text-muted">Porcentaje de IVA vigente. Debes configurarlo manualmente según la tasa del SENIAT.</div>
                            </div>

                            <div class="col-12">
                                <hr class="my-3 border-secondary opacity-25">
                                <h6 class="fw-bold mb-3">Redes Sociales y Contacto</h6>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-bold"><i class="bi bi-whatsapp text-success me-1"></i> WhatsApp</label>
                                <input type="text" name="social_whatsapp" class="form-control" value="<?php echo htmlspecialchars($redes['social_whatsapp'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-12 mt-2">
                                <label class="form-label fw-bold"><i class="bi bi-instagram text-danger me-1"></i> Instagram</label>
                                <input type="url" name="social_instagram" class="form-control" value="<?php echo htmlspecialchars($redes['social_instagram'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-12 mt-2">
                                <label class="form-label fw-bold"><i class="bi bi-facebook text-primary me-1"></i> Facebook</label>
                                <input type="url" name="social_facebook" class="form-control" value="<?php echo htmlspecialchars($redes['social_facebook'] ?? ''); ?>">
                            </div>

                            <div class="col-12 mt-2">
                                <label class="form-label fw-bold"><i class="bi bi-tiktok text-dark me-1"></i> TikTok</label>
                                <input type="url" name="social_tiktok" class="form-control" value="<?php echo htmlspecialchars($redes['social_tiktok'] ?? ''); ?>">
                            </div>
                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-save me-1"></i> Guardar Configuración
                                </button>
                            </div>
                        </div>
                    </form>
                    <hr class="my-3">
                    <div class="small text-muted">
                        <i class="bi bi-arrow-repeat me-1"></i> Los precios en Bs. se sincronizan automáticamente al entrar al panel cuando cambia la tasa BCV.
                    </div>
                </div>
            </div>
            <?php elseif ($vista !== 'Inicio' && !$datos && $documentos_array === null): ?>
                <div class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>Selecciona una seccion del menu.</div>
            <?php endif; ?>

        </div>
    </main>
</div>

<!-- MODAL UNICO PARA CRUD -->
<div id="modal-crud" class="modal">
    <div class="modal-contenido">
        <div class="modal-header">
            <h2 id="modal-titulo">Nuevo Registro</h2>
            <button type="button" class="modal-close btn-cerrar-modal">&times;</button>
        </div>
        <form id="form-crud" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="accion" id="f-accion">
            <input type="hidden" name="id" id="f-id">
            <input type="hidden" name="modulo" id="f-modulo">

            <div id="campos-dinamicos"></div>

            <div class="botones-modal">
                <button type="button" class="btn-secundario btn-cerrar-modal">Cancelar</button>
                <button type="submit" class="btn-primario">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL CONFIRMACION -->
<div id="modal-confirmacion" class="modal">
    <div class="modal-contenido" style="max-width:400px;">
        <div class="modal-header">
            <h2>Confirmar</h2>
            <button type="button" class="btn-cerrar-confirmacion" style="background:none;border:none;font-size:1.2rem;line-height:1;cursor:pointer;color:#64748b;padding:2px 6px;">&times;</button>
        </div>
        <div class="campo-form">
            <p id="confirmacion-mensaje" style="font-size:0.9rem;color:#334155;margin:0.5rem 0;"></p>
        </div>
        <div class="botones-modal">
            <button type="button" class="btn-secundario btn-cerrar-confirmacion" style="flex:1;">Cancelar</button>
            <button type="button" id="btn-confirmar-aceptar" class="btn-primario" style="flex:1;">Aceptar</button>
        </div>
    </div>
</div>

<!-- (modal editar eliminado — ahora redirige a llenar-talonario) -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
<?php
// === Datos pre-computados para JS (evita PHP inline en funciones) ===

// Tasas y config
$iva_label = $tasa_iva > 0 ? $tasa_iva . '%' : '0% (config. en Ajustes)';
$doc_reload = ($vista === 'Documentos');

// Categorías (options HTML)
$cat_opts = '<option value="" disabled selected>Seleccionar</option>';
$cat_res = Database::query("SELECT id, nombre FROM categorias ORDER BY id ASC");
$cats_r = Database::getResult($cat_res);
while ($c = mysqli_fetch_assoc($cats_r)) {
    $cat_opts .= '<option value="' . $c['id'] . '">' . htmlspecialchars($c['nombre'], ENT_QUOTES) . '</option>';
}

// Clientes para Pedidos (options HTML, Regular y Negocio)
$cs_res = Database::query("SELECT id, nombre, categoria FROM contactos WHERE tipo='Cliente' AND estado!='Inactivo' ORDER BY categoria, nombre");
$cs_data = Database::fetchAll($cs_res);
$clientes_reg = '';
$clientes_neg = '';
foreach ($cs_data as $c) {
    $opt = '<option value="' . $c['id'] . '">' . htmlspecialchars($c['nombre']) . '</option>';
    if (($c['categoria'] ?? 'Regular') === 'Negocio') {
        $clientes_neg .= $opt;
    } else {
        $clientes_reg .= $opt;
    }
}

// Productos disponibles para Pedido (options HTML)
$prod_ped_res = Database::query("SELECT id, nombre, precio, tipo, aplica_iva, stock, codigo FROM productos WHERE estado = 'Disponible' AND stock > 0 ORDER BY nombre");
$prod_ped_r = Database::getResult($prod_ped_res);
$prod_pedido_opts = '';
while ($p = mysqli_fetch_assoc($prod_ped_r)) {
    $prod_pedido_opts .= '<option value="' . $p['id'] . '" data-precio="' . $p['precio'] . '" data-tipo="' . ($p['tipo'] ?? 'Elaboracion') . '" data-aplica-iva="' . intval($p['aplica_iva'] ?? 0) . '" data-stock="' . intval($p['stock']) . '" data-codigo="' . htmlspecialchars($p['codigo'] ?? '') . '">' . htmlspecialchars($p['nombre']) . '</option>';
}

// Productos para Pérdidas (options HTML)
$prod_perd_res = Database::query("SELECT id, nombre FROM productos WHERE estado != 'Inactivo' ORDER BY nombre");
$prod_perd_r = Database::getResult($prod_perd_res);
$prod_perdida_opts = '';
while ($po = mysqli_fetch_assoc($prod_perd_r)) {
    $prod_perdida_opts .= '<option value="' . $po['id'] . '">' . htmlspecialchars($po['nombre']) . '</option>';
}

// Proveedores (JSON array)
$_prov_res = Database::query("SELECT id, nombre FROM contactos WHERE tipo='Proveedor' AND estado != 'Inactivo' ORDER BY nombre");
$_prov_list = Database::fetchAll($_prov_res);
?>
window.TASA_VAL = <?php echo $tasa_cambio; ?>;
window._tasaIva = <?php echo $tasa_iva; ?>;
window._CAT_OPTS = <?php echo json_encode($cat_opts); ?>;
window._CLIENTES_REG = <?php echo json_encode($clientes_reg); ?>;
window._CLIENTES_NEG = <?php echo json_encode($clientes_neg); ?>;
window._PROD_PEDIDO_OPTS = <?php echo json_encode($prod_pedido_opts); ?>;
window._PROD_PERDIDA_OPTS = <?php echo json_encode($prod_perdida_opts); ?>;
window._PROVEEDORES = <?php echo json_encode($_prov_list, JSON_UNESCAPED_UNICODE); ?>;
window._USUARIO_ID = <?php echo $usuario_id; ?>;
window._USUARIO_NOMBRE = <?php echo json_encode($usuario_nombre, JSON_UNESCAPED_UNICODE); ?>;
window._USUARIO_CORREO = <?php echo json_encode($usuario_correo, JSON_UNESCAPED_UNICODE); ?>;
window._IVA_LABEL = <?php echo json_encode($iva_label); ?>;
window._DOC_AUTO_RELOAD = <?php echo $doc_reload ? 'true' : 'false'; ?>;

function abrirModal(tipo) {
    const modal = document.getElementById('modal-crud');
    const campos = document.getElementById('campos-dinamicos');
    const id = document.getElementById('f-id');
    const accion = document.getElementById('f-accion');
    const modulo = document.getElementById('f-modulo');

    id.value = 0;
    modulo.value = tipo;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    const titulos = { 'Categorias': 'Nueva Categoria', 'Perdidas': 'Registrar Perdida', 'Productos': 'Nuevo Producto', 'MateriaPrima': 'Nuevo Insumo', 'Pedidos': 'Nuevo Pedido', 'Clientes': 'Nuevo Cliente', 'Proveedores': 'Nuevo Proveedor', 'Personal': 'Nuevo Usuario' };
    document.getElementById('modal-titulo').innerText = titulos[tipo] || "Nuevo " + tipo;

    setTimeout(() => { const fi = campos.querySelector('input[type="text"], input[type="number"]'); if(fi) fi.focus(); }, 100);

    if (tipo === 'Categorias') {
        accion.value = 'guardar_categoria';
        campos.innerHTML = `
            <div class="campo-form"><label>Nombre de la Categoria</label><input type="text" name="nombre" required></div>
            <div class="campo-form"><label>Descripcion (opcional)</label><input type="text" name="descripcion" placeholder="Ej: Productos fritos y empanizados"></div>`;
    } else if (tipo === 'Productos') {
        accion.value = 'guardar_producto';
        campos.innerHTML = `
            <div class="grid-formulario ocupa-2" style="grid-template-columns:1.5fr 1fr 1.5fr;">
                <div class="campo-form"><label>Nombre del Producto</label><input type="text" name="nombre" required></div>
                <div class="campo-form"><label>Codigo</label><input type="text" name="codigo" placeholder="Ej: PROD-001"></div>
                <div class="campo-form"><label>Categoria</label>
                    <div class="custom-select-wrapper" style="width:100%;border:1px solid #94a3b8;border-radius:5px;padding:6px 10px;min-height:36px;box-sizing:border-box;">
                        <div class="custom-select-display" id="display-cat">Seleccionar</div>
                        <select name="categoria_id" class="custom-select-real" required onchange="document.getElementById('display-cat').innerText=this.options[this.selectedIndex].text">
                            ${window._CAT_OPTS}
                        </select>
                        <i class="bi bi-chevron-down custom-select-arrow"></i>
                    </div>
                </div>
            </div>
            <div class="grid-formulario ocupa-2">
                <div class="campo-form">
                    <label>Precio Base ($)</label>
                    <div class="input-grupo">
                        <span class="input-addon" style="background:#a7ecc3;color:#166534;border-color:#86efac;">$</span>
                        <input type="number" name="precio_usd" id="precio_usd" step="0.01" placeholder="0.00" required onkeyup="convertirPrecio(this,'usd')">
                    </div>
                </div>
                <div class="campo-form">
                    <label>Precio Base (Bs.)</label>
                    <div class="input-grupo">
                        <span class="input-addon" style="background:#a7ecc3;color:#166534;border-color:#86efac;">Bs</span>
                        <input type="number" step="0.01" name="precio" id="precio_bs" readonly style="background:#f1f5f9;cursor:not-allowed;">
                    </div>
                    <span class="etiqueta-conversion">Tasa: <span id="tasa-display">${window.TASA_VAL}</span> Bs/$</span>
                </div>
            </div>
            <div class="grid-formulario ocupa-2" style="grid-template-columns:1fr 1fr 1.2fr;">
                <div class="campo-form"><label>Stock Actual</label><input type="number" name="stock" required></div>
                <div class="campo-form"><label>Stock Mínimo</label><input type="number" name="stock_minimo" value="10" required></div>
                <div class="campo-form"><label>IVA</label>
                    <div style="display:flex;align-items:center;gap:8px;height:36px;">
                        <input type="checkbox" name="aplica_iva" id="f-aplica-iva" value="1" style="width:18px;height:18px;cursor:pointer;">
                        <label for="f-aplica-iva" style="font-size:0.85rem;cursor:pointer;margin:0;">${window._IVA_LABEL}</label>
                    </div>
                </div>
            </div>
            <div class="grid-formulario ocupa-2">
                <div class="campo-form"><label>Tipo</label>
                    <div class="custom-select-wrapper" style="width:100%;border:1px solid #94a3b8;border-radius:5px;padding:6px 10px;min-height:36px;box-sizing:border-box;">
                        <div class="custom-select-display" id="display-tipo">Elaboración</div>
                        <select name="tipo" class="custom-select-real" onchange="document.getElementById('display-tipo').innerText=this.options[this.selectedIndex].text;actualizarLabelCosto(this.value)">
                            <option value="Elaboracion">Elaboración</option>
                            <option value="Sin elaboracion">Sin elaboración</option>
                        </select>
                        <i class="bi bi-chevron-down custom-select-arrow"></i>
                    </div>
                </div>
                <div class="campo-form">
                    <label>Peso / Medida</label>
                    <div class="input-grupo">
                        <input type="number" name="val_peso" step="0.01" placeholder="Cant." style="flex:1.2;min-width:0;border-radius:5px 0 0 5px!important;">
                        <div class="custom-select-wrapper">
                            <div class="custom-select-display" id="display-u">kg</div>
                            <select name="unidad_peso" class="custom-select-real" onchange="document.getElementById('display-u').innerText=this.value">
                                <option value="kg">kg</option>
                                <option value="litros">litros</option>
                                <option value="unidades">unidades</option>
                                <option value="bultos">bultos</option>
                            </select>
                            <i class="bi bi-chevron-down custom-select-arrow"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid-formulario ocupa-2">
                <div class="campo-form"><label id="label-costo-usd">Costo de Fabricación ($)</label>
                    <div class="input-grupo">
                        <span class="input-addon-costo">$</span>
                        <input type="number" name="costo_usd" id="costo_usd" step="0.01" placeholder="0.00" onkeyup="convertirCosto(this,'usd')">
                    </div>
                </div>
                <div class="campo-form"><label id="label-costo-bs">Costo de Fabricación (Bs.)</label>
                    <div class="input-grupo">
                        <span class="input-addon-costo">Bs</span>
                        <input type="number" step="0.01" name="costo" id="costo_bs" placeholder="0.00" onkeyup="convertirCosto(this,'bs')">
                    </div>
                </div>
            </div>
            <div class="grid-formulario ocupa-2">
                <div class="campo-form">
                    <label style="font-size:0.7rem;margin-bottom:3px;">Imagen del Producto</label>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <label for="upload-file" class="btn btn-outline-secondary btn-sm" style="cursor:pointer;display:inline-flex;align-items:center;gap:4px;font-weight:600;font-size:0.75rem;">
                            <i class="bi bi-image"></i> Subir
                        </label>
                        <input type="file" id="upload-file" name="imagen" accept="image/*" style="display:none;" onchange="previewImage(this);">
                        <div id="preview-container" style="display:none;vertical-align:middle;"><img id="img-preview" src="" style="width:40px;height:40px;border-radius:6px;object-fit:cover;border:1px solid #e2e8f0;vertical-align:middle;"></div>
                        <input type="hidden" name="img_actual" id="f-img-actual">
                    </div>
                </div>
                <div class="campo-form"><label style="font-size:0.7rem;margin-bottom:3px;">Descripcion Corta</label><input type="text" name="descripcion" placeholder="Ej: Masa crujiente con queso"></div>
            </div>`;
    } else if (tipo === 'MateriaPrima') {
        accion.value = 'guardar_materiaprima';
        campos.innerHTML = `
            <div class="campo-form"><label>Nombre del Ingrediente</label><input type="text" name="nombre" required></div>
            <div class="campo-form"><label>Cantidad en Stock</label><input type="number" step="0.01" name="cantidad" required></div>
            <div class="campo-form"><label>Cantidad Mínima</label><input type="number" step="0.01" name="minimo" value="5" required></div>
            <div class="campo-form"><label>Unidad de Medida</label>
                <div class="custom-select-wrapper" style="width:100%;border:1px solid #94a3b8;border-radius:5px;padding:6px 10px;min-height:36px;box-sizing:border-box;">
                    <div class="custom-select-display" id="display-u-mp">kg</div>
                    <select name="unidad" class="custom-select-real" onchange="document.getElementById('display-u-mp').innerText=this.options[this.selectedIndex].text">
                        <option value="g">Gramos (g)</option>
                        <option value="kg">Kilogramos (kg)</option>
                        <option value="litros">Litros (l)</option>
                        <option value="uds">Unidades (uds)</option>
                        <option value="paq">Paquetes (paq)</option>
                        <option value="bultos">Bultos</option>
                    </select>
                    <i class="bi bi-chevron-down custom-select-arrow"></i>
            </div>
            <div class="campo-form"><label>Proveedor (opcional)</label>
                <select name="id_proveedor" id="sel-proveedor-mp">
                    <option value="">-- Sin proveedor --</option>
                </select>
            </div>`;
        // Llenar el select de proveedores con datos pre-cargados
        var selProv = document.getElementById('sel-proveedor-mp');
        if (selProv && window._PROVEEDORES) {
            window._PROVEEDORES.forEach(function(p) {
                var opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.nombre;
                selProv.appendChild(opt);
            });
        }
    } else if (tipo === 'Personal') {
        accion.value = 'guardar_usuario';
        campos.innerHTML = `
            <div class="campo-form"><label>Nombre Completo</label><input type="text" name="nombre" required></div>
            <div class="campo-form"><label>Correo Electronico</label><input type="email" name="correo" required></div>
            <div class="campo-form"><label>Contrasena (dejar vacio al editar)</label><input type="password" name="clave" minlength="6"></div>
            <div class="campo-form"><label>Asignar Rol</label>
                <select name="rol"><option value="vendedor">Vendedor</option><option value="trabajador">Trabajador</option><option value="admin">Administrador</option></select></div>
            <p style="font-size:0.8rem;color:#64748b;margin-top:0.5rem;">Cuidado: Cambiar el rol afectara los permisos de acceso.</p>`;
    } else if (tipo === 'Pedidos') {
        accion.value = 'guardar_pedido';
        campos.innerHTML = `
            <div class="campo-form"><label>Cliente</label>
                <select name="id_contacto" onchange="onClienteChange(this)">
                    <option value="0">Mostrador (General)</option>
                    <optgroup label="--- Regular ---">
                    ${window._CLIENTES_REG}
                    </optgroup>
                    <optgroup label="--- Negocio ---">
                    ${window._CLIENTES_NEG}
                    </optgroup>
                </select></div>
            <div class="campo-form"><label>Tipo de Entrega</label>
                <select name="tipo_entrega" onchange="toggleDireccion(this.value)">
                    <option value="Local">Local / Para llevar</option>
                    <option value="Negocio externo">Negocio externo</option>
                </select></div>
            <div class="campo-form" id="campo-direccion" style="display:none;">
                <label>Direccion</label>
                <textarea name="direccion" rows="2" placeholder="Direccion completa del domicilio..."></textarea></div>
            <hr style="margin:0.75rem 0;border-color:#e2e8f0;">
            <div class="campo-form" style="margin-bottom:0;">
                <div style="display:grid;grid-template-columns:1fr 50px 70px 70px auto;grid-template-rows:auto auto;gap:2px 6px;">
                    <span style="font-size:0.7rem;font-weight:600;color:#475569;grid-row:1;grid-column:1;">Producto</span>
                    <span style="font-size:0.7rem;font-weight:600;color:#475569;grid-row:1;grid-column:2;text-align:center;">Cant</span>
                    <span style="font-size:0.7rem;font-weight:600;color:#475569;grid-row:1;grid-column:3;text-align:center;">Bs/U</span>
                    <span style="font-size:0.7rem;font-weight:600;color:#475569;grid-row:1;grid-column:4;text-align:right;">Total</span>
                    <span style="grid-row:1;grid-column:5;"></span>
                    <select id="sel-producto-pedido" style="grid-row:2;grid-column:1;padding:6px 10px;border:1px solid #94a3b8;border-radius:6px;font-size:0.85rem;height:36px;width:100%;box-sizing:border-box;">
                        <option value="">Seleccionar...</option>
                        ${window._PROD_PEDIDO_OPTS}
                    </select>
                    <input type="number" id="cant-item-pedido" min="1" value="1" oninput="calcularTotalItem()" style="grid-row:2;grid-column:2;padding:6px 4px;border:1px solid #94a3b8;border-radius:6px;font-size:0.85rem;height:36px;width:100%;box-sizing:border-box;text-align:center;">
                    <input type="number" id="precio-item-pedido" step="0.01" min="0" oninput="calcularTotalItem()" style="grid-row:2;grid-column:3;padding:6px 4px;border:1px solid #94a3b8;border-radius:6px;font-size:0.85rem;height:36px;width:100%;box-sizing:border-box;text-align:center;" placeholder="0.00">
                    <span id="total-item-preview" style="grid-row:2;grid-column:4;display:flex;align-items:center;justify-content:flex-end;font-size:0.85rem;font-weight:700;color:var(--color-primario);height:36px;padding:0 4px;">0.00</span>
                    <button type="button" onclick="addItemPedido()" class="btn btn-sm btn-danger" style="grid-row:2;grid-column:5;height:36px;white-space:nowrap;">
                        <i class="bi bi-plus-lg"></i> Agregar
                    </button>
                </div></div>
            <div id="lista-items-pedido-form" style="margin:0.5rem 0;">
                <table class="table table-sm mb-1" style="font-size:0.8rem;">
                    <thead><tr><th>Producto</th><th>Cant</th><th>Bs/U</th><th>Base</th><th>IVA</th><th>Total</th><th style="width:32px;"></th></tr></thead>
                    <tbody id="items-pedido-body"></tbody>
                </table>
            </div>
            <div style="padding:0.5rem;background:#f8fafc;border-radius:6px;margin-bottom:0.75rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.8rem;">
                    <span>SUBTOTAL (Base):</span>
                    <span id="subtotal-sin-iva" style="font-weight:600;">Bs. 0.00</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.8rem;color:#64748b;">
                    <span id="iva-label">IVA 0%:</span>
                    <span id="total-iva" style="font-weight:600;">Bs. 0.00</span>
                </div>
                <hr style="margin:2px 0;border-color:#e2e8f0;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <strong>TOTAL:</strong>
                    <span id="total-pedido-calculado" style="font-size:1.2rem;font-weight:900;color:var(--color-primario);">Bs. 0.00</span>
                </div>
            </div>
            <div style="display:flex;gap:8px;">
                <div class="campo-form" style="flex:1;"><label>Estado</label>
                    <select name="estado">
                        <option value="Completado">Completado</option>
                        <option value="Cancelado">Cancelado</option>
                    </select></div>
            </div>
            <input type="hidden" name="items_json" id="items-json-hidden" value="[]">`;
        window._itemsPedido = [];
        _autoAsignarEntrega = true;
        document.getElementById('items-json-hidden').value = '[]';
        renderItemsPedido();
    } else if (tipo === 'Perdidas') {
        accion.value = 'guardar_perdida';
        campos.innerHTML = `
            <div class="campo-form"><label>Producto</label>
                <select name="id_producto" required>
                    <option value="" disabled selected>Seleccionar Producto</option>
                    ${window._PROD_PERDIDA_OPTS}
                </select></div>
            <div class="campo-form"><label>Cantidad Perdida</label><input type="number" name="cantidad" min="1" required></div>
             <div class="campo-form"><label>Motivo</label>
                <select name="motivo" required>
                    <option value="" disabled selected>Seleccionar Motivo</option>
                    <option value="Caducado">Caducado</option>
                    <option value="Consumo interno">Consumo interno</option>
                    <option value="Donación">Donación</option>
                    <option value="Error de inventario">Error de inventario</option>
                    <option value="Error de registro">Error de registro</option>
                    <option value="Fallas del proveedor">Fallas del proveedor</option>
                    <option value="Devolucion">Devolucion</option>
                    <option value="Merma en produccion">Merma en produccion</option>
                    <option value="Mercancía dañada o rota">Mercancía dañada o rota</option>
                    <option value="Mercancía extraviada">Mercancía extraviada</option>
                    <option value="Muestra comercial">Muestra comercial</option>
                    <option value="Robo o hurto">Robo o hurto</option>
                    <option value="Otro">Otro</option>
                </select></div>
            <div class="campo-form"><label>Fecha</label><input type="date" name="fecha" style="width:100%;padding:6px 10px;border:1px solid #94a3b8;border-radius:5px;font-size:0.85rem;height:36px;box-sizing:border-box;"></div>`;
    } else {
        accion.value = 'guardar_contacto';
        campos.innerHTML = `
            <div class="campo-form"><label>Nombre Completo</label><input type="text" name="nombre" required></div>
            <div class="campo-form"><label>Telefono</label><input type="text" name="telefono"></div>
            <div class="campo-form"><label>Correo Electronico</label><input type="email" name="correo"></div>
            ${tipo === 'Clientes' ? `
            <div class="campo-form"><label>Categoria</label>
                <select name="categoria">
                    <option value="Regular">Regular</option>
                    <option value="Negocio">Negocio</option>
                </select></div>` : ''}
            <div class="campo-form"><label>Direccion</label><input type="text" name="direccion"></div>
            <input type="hidden" name="tipo" value="${tipo === 'Clientes' ? 'Cliente' : 'Proveedor'}">`;
    }
}

function abrirModalPerfil() {
    const modal = document.getElementById('modal-crud');
    const campos = document.getElementById('campos-dinamicos');
    const id = document.getElementById('f-id');
    const accion = document.getElementById('f-accion');
    const modulo = document.getElementById('f-modulo');

    id.value = window._USUARIO_ID;
    accion.value = 'guardar_perfil';
    modulo.value = 'Perfil';
    document.getElementById('modal-titulo').innerText = 'Mi Perfil';
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    campos.innerHTML = `
        <div class="campo-form"><label>Nombre Completo</label><input type="text" name="nombre" value="${window._USUARIO_NOMBRE}" required></div>
        <div class="campo-form"><label>Correo Electronico</label><input type="email" name="correo" value="${window._USUARIO_CORREO}" required></div>
        <div class="campo-form"><label>Nueva Contraseña (dejar vacio para mantener)</label><input type="password" name="clave" minlength="6"></div>
        <p style="font-size:0.8rem;color:#64748b;margin-top:0.5rem;">No puedes cambiar tu rol desde aquí.</p>`;

    setTimeout(() => { const fi = campos.querySelector('input[type="text"]'); if(fi) fi.focus(); }, 100);
}

function editarRegistro(datos, tipo) {
    abrirModal(tipo);
    document.getElementById('modal-titulo').innerText = "Editar " + (tipo === 'Pedidos' ? 'Pedido' : tipo);
    document.getElementById('f-id').value = datos.id;
    const form = document.getElementById('form-crud');
    if (!form) return;
    for (let key in datos) {
        let input = form.querySelector(`[name="${key}"]`);
        if (input) {
            if (input.type === 'file') continue;
            if (input.type === 'checkbox') { input.checked = parseInt(datos[key]) === 1; }
            else { input.value = datos[key]; }
        }
    }
    if (tipo === 'Productos') {
        ['display-cat', 'display-tipo'].forEach(function(id) {
            const d = document.getElementById(id);
            if (!d) return;
            const name = id.replace('display-', '');
            const sel = form.querySelector('[name="' + name + '"]');
            if (sel) d.innerText = sel.options[sel.selectedIndex].text;
        });
        // price_usd ya se cargó vía el loop, price_bs también (readonly)
        if (form.elements['costo_usd']) {
            const cUsd = parseFloat(datos.costo_usd) || 0;
            if (cUsd > 0) {
                form.elements['costo_usd'].value = cUsd;
                if (form.elements['costo']) form.elements['costo'].value = (cUsd * window.TASA_VAL).toFixed(2);
            } else {
                // fallback: calcular desde costo Bs
                const c = datos.costo || 0;
                if (form.elements['costo']) form.elements['costo'].value = c;
                form.elements['costo_usd'].value = (parseFloat(c) / window.TASA_VAL).toFixed(2);
            }
        }
        if (form.elements['img_actual']) form.elements['img_actual'].value = datos.imagen || 'placeholder.png';
        const previewCont = document.getElementById('preview-container');
        const imgPrev = document.getElementById('img-preview');
        if (previewCont && imgPrev && datos.imagen && datos.imagen !== 'placeholder.png') {
            previewCont.style.display = 'inline-flex';
            imgPrev.src = 'public/uploads/productos/' + datos.imagen;
        }
        const pesoStr = datos.peso || "0 g";
        const partes = pesoStr.split(' ');
        if (partes.length >= 2) {
            const v_peso = form.querySelector('[name="val_peso"]');
            const u_peso = form.querySelector('[name="unidad_peso"]');
            if (v_peso) v_peso.value = partes[0];
            if (u_peso) { u_peso.value = partes[1]; const dU = document.getElementById('display-u'); if (dU) dU.innerText = partes[1]; }
        }
        if (datos.tipo) actualizarLabelCosto(datos.tipo);
    }
    if (tipo === 'Pedidos') {
        _autoAsignarEntrega = false;
        if (datos.tipo_entrega) toggleDireccion(datos.tipo_entrega);
        cargarItemsPedido(datos.id);
    }
    
    // Sincronizar todos los selects custom del form (UI)
    form.querySelectorAll('.custom-select-real').forEach(sel => {
        const d = sel.parentElement.querySelector('.custom-select-display');
        if (d && sel.options[sel.selectedIndex]) d.innerText = sel.options[sel.selectedIndex].text;
    });
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-container').style.display = 'inline-flex';
            document.getElementById('img-preview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function convertirPrecio(input, origen) {
    if (input.value.length > 1 && input.value[0] === '0' && input.value[1] !== '.') {
        input.value = input.value.substring(1);
    }
    const val = parseFloat(input.value) || 0;
    if (origen === 'usd') { document.getElementById('precio_bs').value = (val * window.TASA_VAL).toFixed(2); }
    else if (origen === 'bs') { document.getElementById('precio_usd').value = (val / window.TASA_VAL).toFixed(2); }
}
function actualizarLabelCosto(tipo) {
    const esSinElab = tipo === 'Sin elaboracion';
    const txt = esSinElab ? 'Adquisición' : 'Fabricación';
    const l1 = document.getElementById('label-costo-usd');
    const l2 = document.getElementById('label-costo-bs');
    if (l1) l1.textContent = 'Costo de ' + txt + ' ($)';
    if (l2) l2.textContent = 'Costo de ' + txt + ' (Bs.)';
}

function convertirCosto(input, origen) {
    if (input.value.length > 1 && input.value[0] === '0' && input.value[1] !== '.') {
        input.value = input.value.substring(1);
    }
    const val = parseFloat(input.value) || 0;
    if (origen === 'usd') { document.getElementById('costo_bs').value = (val * window.TASA_VAL).toFixed(2); }
    else { document.getElementById('costo_usd').value = (val / window.TASA_VAL).toFixed(2); }
}

// ===== Pedidos: Line Items =====
window._itemsPedido = [];
var _autoAsignarEntrega = true;

function toggleDireccion(val) {
    const campo = document.getElementById('campo-direccion');
    if (campo) campo.style.display = 'none'; // A domicilio eliminado
}

function onClienteChange(sel) {
    if (!_autoAsignarEntrega) return;
    const val = sel.value;
    const tipoEntrega = document.querySelector('[name="tipo_entrega"]');
    if (!tipoEntrega) return;
    if (val === '0') {
        tipoEntrega.value = 'Local';
        toggleDireccion('Local');
    } else {
        const opt = sel.options[sel.selectedIndex];
        const group = opt ? opt.parentNode.label : '';
        if (group.includes('Negocio')) {
            tipoEntrega.value = 'Negocio externo';
        } else {
            tipoEntrega.value = 'Local';
        }
        toggleDireccion(tipoEntrega.value);
    }
}

document.addEventListener('change', function(e) {
    if (e.target.name === 'tipo_entrega') {
        _autoAsignarEntrega = false;
    }
    if (e.target.id === 'sel-producto-pedido') {
        const sel = e.target;
        const opt = sel.options[sel.selectedIndex];
        const precio = opt ? opt.getAttribute('data-precio') : '';
        const precioInput = document.getElementById('precio-item-pedido');
        if (precioInput && precio) precioInput.value = precio;
        calcularTotalItem();
    }
});

function calcularTotalItem() {
    const cant = parseFloat(document.getElementById('cant-item-pedido').value) || 0;
    const precio = parseFloat(document.getElementById('precio-item-pedido').value) || 0;
    const totalSpan = document.getElementById('total-item-preview');
    if (totalSpan) totalSpan.textContent = (cant * precio).toFixed(2);
}

function addItemPedido() {
    const sel = document.getElementById('sel-producto-pedido');
    const cantInput = document.getElementById('cant-item-pedido');
    const precioInput = document.getElementById('precio-item-pedido');
    const id = parseInt(sel.value);
    if (!id) { mostrarConfirmacion('Selecciona un producto', function(){}); return; }
    const opt = sel.options[sel.selectedIndex];
    const nombre = opt.text;
    const codigo = opt.getAttribute('data-codigo') || '';
    const tipo = opt.getAttribute('data-tipo') || 'Elaboracion';
    const aplicaIva = parseInt(opt.getAttribute('data-aplica-iva')) || 0;
    const iva = aplicaIva ? window._tasaIva : 0;
    const cantidad = parseInt(cantInput.value) || 1;
    const stockDisponible = parseInt(opt.getAttribute('data-stock')) || 0;
    // Calcular cuántas unidades de este producto ya están en la lista
    const yaAgregadas = window._itemsPedido.reduce(function(sum, it) {
        return it.id_producto === id ? sum + it.cantidad : sum;
    }, 0);
    if (cantidad + yaAgregadas > stockDisponible) {
        mostrarConfirmacion('Stock insuficiente. Disponible: ' + (stockDisponible - yaAgregadas) + ' unidades.', function(){});
        return;
    }
    const precio = parseFloat(precioInput.value) || 0;
    if (cantidad < 1) { mostrarConfirmacion('Cantidad invalida', function(){}); return; }
    window._itemsPedido.push({ id_producto: id, prod_nombre: nombre, codigo: codigo, cantidad: cantidad, precio_unitario: precio, tipo: tipo, iva: iva });
    renderItemsPedido();
    sel.value = '';
    cantInput.value = 1;
    precioInput.value = '';
}

function removeItemPedido(idx) {
    window._itemsPedido.splice(idx, 1);
    renderItemsPedido();
}

function renderItemsPedido() {
    const body = document.getElementById('items-pedido-body');
    const hidden = document.getElementById('items-json-hidden');
    if (!body) return;
    let html = '';
    let totalConIva = 0;
    let totalSinIva = 0;
    let totalIva = 0;
    window._itemsPedido.forEach(function(it, i) {
        const subBase = it.cantidad * it.precio_unitario;
        const ivaRate = parseFloat(it.iva) || 0;
        const ivaAmount = ivaRate > 0 ? subBase * ivaRate / 100 : 0;
        totalSinIva += subBase;
        totalIva += ivaAmount;
        totalConIva += subBase + ivaAmount;
        const badge = it.tipo === 'Sin elaboracion' ? '<span class="badge bg-info" style="font-size:0.6rem;margin-left:4px;vertical-align:middle;">Sin prep.</span>' : '';
        const codigoTag = it.codigo ? '<code style="font-size:0.65rem;color:#475569;margin-right:4px;">[' + it.codigo + ']</code>' : '';
        html += '<tr>' +
            '<td>' + codigoTag + it.prod_nombre + badge + '</td>' +
            '<td>' + it.cantidad + '</td>' +
            '<td>Bs. ' + it.precio_unitario.toFixed(2) + '</td>' +
            '<td>Bs. ' + subBase.toFixed(2) + '</td>' +
            '<td>' + (ivaRate > 0 ? 'Bs. ' + ivaAmount.toFixed(2) : '-') + '</td>' +
            '<td>Bs. ' + (subBase + ivaAmount).toFixed(2) + '</td>' +
            '<td><button type="button" onclick="removeItemPedido(' + i + ')" style="background:none;border:none;color:#dc3545;cursor:pointer;padding:2px 4px;">&times;</button></td>' +
            '</tr>';
    });
    if (window._itemsPedido.length === 0) {
        html = '<tr><td colspan="7" class="text-center text-muted py-2" style="font-size:0.8rem;">No hay productos agregados</td></tr>';
    }
    body.innerHTML = html;
    const totalSpan = document.getElementById('total-pedido-calculado');
    if (totalSpan) totalSpan.innerText = 'Bs. ' + totalConIva.toFixed(2);
    const subSpan = document.getElementById('subtotal-sin-iva');
    if (subSpan) subSpan.innerText = 'Bs. ' + totalSinIva.toFixed(2);
    const ivaSpan = document.getElementById('total-iva');
    if (ivaSpan) ivaSpan.innerText = 'Bs. ' + totalIva.toFixed(2);
    const ivaLabel = document.getElementById('iva-label');
    if (ivaLabel) {
        const hasIva = window._itemsPedido.some(function(it) { return parseFloat(it.iva) > 0; });
        ivaLabel.innerText = hasIva ? ('IVA ' + window._tasaIva + '%:') : 'IVA 0%:';
    }
    if (hidden) hidden.value = JSON.stringify(window._itemsPedido);
    checkAllSinElab();
}

function checkAllSinElab() {
    const estadoSel = document.querySelector('[name="estado"]');
    if (!estadoSel || window._itemsPedido.length === 0) return;
    const idField = document.getElementById('f-id');
    if (idField && parseInt(idField.value) > 0) return;
    const allSin = window._itemsPedido.every(function(it) { return it.tipo === 'Sin elaboracion'; });
    if (allSin) {
        estadoSel.value = 'Completado';
    }
}

async function cargarItemsPedido(id_pedido) {
    try {
        const res = await fetch('index.php?action=ajax-detalle-pedido&id=' + id_pedido);
        const items = await res.json();
        window._itemsPedido = items.map(function(it) {
            return {
                id_producto: parseInt(it.id_producto),
                prod_nombre: it.prod_nombre,
                codigo: it.codigo || '',
                cantidad: parseInt(it.cantidad),
                precio_unitario: parseFloat(it.precio_unitario),
                tipo: it.tipo || 'Elaboracion',
                iva: parseFloat(it.iva_aplicado) || 0
            };
        });
        renderItemsPedido();
    } catch (e) {
        console.error('Error loading items:', e);
    }
}

function cerrarModal() {
    document.getElementById('modal-crud').style.display = 'none';
    document.body.style.overflow = '';
}

var _formPendiente = null;

function mostrarConfirmacion(mensaje, callback, isDanger = false) {
    document.getElementById('confirmacion-mensaje').innerHTML = mensaje;
    let btnAceptar = document.getElementById('btn-confirmar-aceptar');
    btnAceptar.style.backgroundColor = isDanger ? '#dc3545' : '';
    btnAceptar.style.borderColor = isDanger ? '#dc3545' : '';
    
    document.getElementById('modal-confirmacion').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    _formPendiente = callback;
}

function cerrarConfirmacion() {
    document.getElementById('modal-confirmacion').style.display = 'none';
    document.body.style.overflow = '';
    _formPendiente = null;
}

document.addEventListener('click', function(e) {
    if (e.target.id === 'btn-confirmar-aceptar' && _formPendiente) {
        let callbackObj = _formPendiente;
        cerrarConfirmacion(); // Cerramos primero para limpiar el estado
        callbackObj(); // Ejecutamos el callback (si hay otro modal, se abrirá sin conflicto)
    }
});

function confirmarForm(event, mensaje, isDanger = false) {
    event.preventDefault();
    mostrarConfirmacion(mensaje, function() {
        event.target.submit();
    }, isDanger);
}

// Delegado global: intercepta todos los forms con class="form-confirmar"
document.addEventListener('submit', function(e) {
    if (e.target.classList.contains('form-confirmar')) {
        var msg = e.target.dataset.msg || '¿Confirmar esta accion?';
        var danger = e.target.dataset.danger === 'true';
        confirmarForm(e, msg, danger);
    }
});

function vaciarPapeleraConfirmar(event) {
    event.preventDefault();
    const form = event.target;
    // Primer aviso
    mostrarConfirmacion(
        '<span style="color:#dc3545;"><i class="bi bi-exclamation-triangle-fill me-1"></i>¿Vaciar la Papelera? Se eliminaran <strong>permanentemente</strong> TODOS los registros eliminados. Esta accion es IRREVERSIBLE.</span>',
        function() {
            // Segundo aviso
            mostrarConfirmacion(
                '<span style="color:#dc3545;"><i class="bi bi-x-circle-fill me-1"></i>¿Estas completamente seguro? <strong>No habra forma de recuperar estos datos</strong> una vez eliminados.</span>',
                function() {
                    form.submit();
                },
                true // Segundo botón en rojo también
            );
        },
        true // Primer botón en rojo
    );
    return false;
}

function confirmarToggle(event) {
    event.preventDefault();
    var input = event.target.querySelector('input[name=estado_actual]');
    if (!input) return false;
    var msg = input.value === 'Disponible'
        ? 'Si ocultas este producto, tus clientes ya no podrán verlo en la tienda. ¿Quieres hacerlo?'
        : '¿Restaurar este producto en el menú público?';
    mostrarConfirmacion(msg, function() {
        event.target.submit();
    });
    return false;
}

document.getElementById('form-crud').addEventListener('submit', function(event) {
    const accion = document.getElementById('f-accion');
    if (accion && accion.value === 'guardar_pedido') {
        if (window._itemsPedido.length === 0) {
            event.preventDefault();
            mostrarConfirmacion('Debes agregar al menos un producto al pedido.', function(){});
        }
    }
});

function filtrarTabla() {
    const input = document.getElementById('buscador-productos');
    const filtro = input.value.toLowerCase().trim();
    const tabla = document.getElementById('tabla-datos');
    if (!tabla) return;
    const tbody = tabla.querySelector('tbody');
    if (!tbody) return;
    const filas = tbody.querySelectorAll('tr');
    let visibles = 0;
    for (let i = 0; i < filas.length; i++) {
        const celdas = filas[i].querySelectorAll('td');
        let coincide = filtro === '';
        for (let j = 0; j < celdas.length && !coincide; j++) {
            if (celdas[j].textContent.toLowerCase().includes(filtro)) coincide = true;
        }
        filas[i].style.display = coincide ? '' : 'none';
        if (coincide) visibles++;
    }
    let noResult = tbody.querySelector('.tr-sin-resultados');
    if (visibles === 0 && filtro !== '') {
        if (!noResult) {
            noResult = document.createElement('tr');
            noResult.className = 'tr-sin-resultados';
            const td = document.createElement('td');
            td.colSpan = filas.length > 0 ? filas[0].querySelectorAll('td').length : 1;
            td.className = 'text-center text-muted py-4';
            td.textContent = 'No se encontraron resultados.';
            noResult.appendChild(td);
            tbody.appendChild(noResult);
        }
        noResult.style.display = '';
    } else if (noResult) {
        noResult.style.display = 'none';
    }
}

async function refrescarTasa() {
    const btn = document.getElementById('btn-refresh-tasa');
    const icon = btn.querySelector('i');
    icon.className = 'bi bi-arrow-clockwise spin';
    try {
        const r = await fetch('index.php?action=ajax-tasa');
        const t = await r.json();
        document.querySelector('#badge-usd .tasa-valor').innerText = parseFloat(t.usd).toFixed(2);
        document.querySelector('#badge-eur .tasa-valor').innerText = parseFloat(t.eur).toFixed(2);
        document.getElementById('tasa-timestamp').innerHTML = t.fecha || '';
        if (window.TASA_VAL !== undefined) {
            window.TASA_VAL = parseFloat(t.usd);
        }
    } catch (err) {
        document.getElementById('tasa-timestamp').innerHTML = '<span class="text-danger">Error al actualizar</span>';
    } finally {
        icon.className = 'bi bi-arrow-clockwise';
    }
}
function filtrarDocumentos() {
    var q = document.getElementById('doc-search').value.toLowerCase();
    var dateFrom = document.getElementById('doc-date-from').value;
    var dateTo = document.getElementById('doc-date-to').value;
    var rows = document.querySelectorAll('#tabla-datos .doc-row');
    for (var i = 0; i < rows.length; i++) {
        var r = rows[i];
        var nombre = r.cells[0] ? r.cells[0].textContent.toLowerCase() : '';
        var fechaText = r.cells[4] ? r.cells[4].textContent.trim() : '';
        var textMatch = nombre.indexOf(q) !== -1;
        var dateMatch = true;
        if (dateFrom || dateTo) {
            var parts = fechaText.split(' ')[0].split('/');
            if (parts.length === 3) {
                var rowDate = parts[2] + '-' + parts[1].padStart(2, '0') + '-' + parts[0].padStart(2, '0');
                if (dateFrom && rowDate < dateFrom) dateMatch = false;
                if (dateTo && rowDate > dateTo) dateMatch = false;
            }
        }
        r.style.display = (textMatch && dateMatch) ? '' : 'none';
    }
}

if (window._DOC_AUTO_RELOAD) {
// Auto recarga la página de Documentos cada 15 segundos
setInterval(function() {
    let activeEl = document.activeElement;
    // Evitar recargar si el usuario esta escribiendo en la barra de búsqueda o usando los filtros de fecha
    if (activeEl && (activeEl.id === 'doc-search' || activeEl.id === 'doc-date-from' || activeEl.id === 'doc-date-to')) {
        return;
    }
    // Evitar recargar si hay un modal abierto (ej. confirmación de borrar)
    if (document.getElementById('modal-confirmacion').style.display === 'flex' || document.getElementById('modal-crud').style.display === 'flex') {
        return;
    }
    window.location.reload();
}, 15000);
}

// --- Listeners de Interfaz (Desacoplados del HTML) ---
document.addEventListener('DOMContentLoaded', function() {
    var btnPerfilTop = document.getElementById('btn-perfil-top');
    if(btnPerfilTop) {
        btnPerfilTop.addEventListener('click', function(e) {
            e.preventDefault();
            abrirModalPerfil();
        });
    }

    document.querySelectorAll('.btn-cerrar-modal').forEach(function(btn) {
        btn.addEventListener('click', cerrarModal);
    });

    document.querySelectorAll('.btn-cerrar-confirmacion').forEach(function(btn) {
        btn.addEventListener('click', cerrarConfirmacion);
    });
});
</script>
</body>
</html>
