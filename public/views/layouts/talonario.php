<?php
// Variables inyectadas por el controlador AdminController (action llenar-talonario)
$tipo_documento = $tipo_documento ?? 'Factura';
$modo           = $modo ?? 'nuevo';
$id_factura     = $id_factura ?? 0;
$id_documento   = $id_documento ?? 0;
$datos          = $datos ?? [];

$debug = intval($_GET['debug'] ?? 0);
$esNota = ($tipo_documento === 'Nota de Entrega');
$titulo = $esNota ? 'Nota de Entrega' : 'Factura';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talonario de <?php echo $titulo; ?> - Teke'fritos</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #f0f0f0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5px;
            color: #000;
            padding: 15px;
        }

        /* ── TOOLBAR ── */
        .toolbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            background: white; border-bottom: 1px solid #cbd5e1;
            padding: 8px 20px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        }
        .toolbar-title { font-weight: 700; font-size: 0.9rem; color: #dc3545; margin-right: auto; }
        .toolbar .btn { font-size: 0.8rem; padding: 6px 14px; cursor: pointer; text-decoration: none; }
        .btn-outline-secondary { background: transparent; color: #475569; border: 1px solid #cbd5e1; border-radius: 4px; }
        .btn-outline-secondary:hover { background: #f1f5f9; }
        .btn-danger { background: #dc3545; color: #fff; border: 1px solid #dc3545; border-radius: 4px; }
        .btn-danger:hover { background: #bb2d3b; }
        #msg-accion { font-size: 0.75rem; color: #64748b; margin-left: 8px; }

        /* ── CONTENEDOR FACTURA ── */
        .page-wrap {
            margin-top: 52px;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        .invoice-outer {
            max-width: 780px;
            width: 100%;
            background: #fff;
            padding: 10px 10px 4px 10px;
            border: 1px solid #000;
        }

        /* ── BOOTSTRAP GRID (mínimo) ── */
        .row { display: flex; flex-wrap: wrap; }
        .row.g-0 { gap: 0; }
        .align-items-center { align-items: center; }
        .text-center { text-align: center; }
        .col-3 { flex: 0 0 25%; max-width: 25%; }
        .col-4 { flex: 0 0 33.333%; max-width: 33.333%; }
        .col-5 { flex: 0 0 41.667%; max-width: 41.667%; }
        .col-6 { flex: 0 0 50%; max-width: 50%; }

        /* ── HEADER FACTURA ── */
        .logo-box { text-align: left; }
        .logo-img { max-width: 140px; height: auto; display: block; }
        .header-center h1 { font-size: 21px; font-weight: bold; margin: 0; font-family: "Times New Roman", Times, serif; }
        .header-center h2 { font-size: 13.5px; font-weight: bold; margin: 2px 0 3px 0; }
        .header-center p { font-size: 8.5px; margin: 0; line-height: 1.25; }

        /* ── TABLA RIF ── */
        .rif-table { width: 100%; border-collapse: collapse; border: 1px solid #000; text-align: center; table-layout: fixed; }
        .rif-table td, .rif-table th { border: 1px solid #000; padding: 3px 2px; font-weight: normal; }
        .rif-title { font-size: 11px; font-weight: bold !important; padding: 4px !important; }
        .rif-labels th { font-size: 7.5px; font-weight: normal; white-space: nowrap; }
        .rif-values td { height: 20px; max-height: 20px; overflow: hidden; white-space: nowrap; padding-left: 2px !important; padding-right: 2px !important; }

        /* ── FILA NÚMEROS ── */
        .numbers-row { margin: 8px 0 5px 0; font-size: 11px; font-weight: normal; }
        .num-large { font-size: 16px; font-weight: bold; }
        .text-red { color: #e60000; font-size: 18px; font-weight: bold; margin-left: 4px; font-family: monospace; }
        .negrita-solicitada { font-weight: bold; }
        .texto-negrita-solicitado { font-weight: bold; }

        /* ── TABLA CLIENTE (Factura) ── */
        .client-container-table { width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 8px; table-layout: fixed; }
        .client-container-table td { border: 1px solid #000; padding: 4px 6px; font-weight: normal; font-size: 9px; vertical-align: middle; }
        .editable-field { display: inline-block; width: 100%; outline: none; word-wrap: break-word; }

        .imprenta-contenedor { border: 1px solid #000; border-top: none; margin-top: -1px; padding: 4px; font-size: 8px; font-weight: normal; text-align: center; line-height: 1.35; }
        .leyenda-fiscal { text-align: center; font-size: 7.5px; font-weight: bold; margin-top: 2px; margin-bottom: 0px; }

        /* CONFIGURACIÓN ESPECÍFICA PARA LA IMPRESIÓN */
        @media print {
            @page {
                size: letter;
                margin: 5mm;
            }
            body {
                background-color: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .invoice-outer {
                border: 1px solid #000 !important;
                box-shadow: none !important;
                max-width: 100% !important;
                margin: 0 auto !important;
            }
            .no-print {
                display: none !important;
            }
        }

        /* ── TABLA PRODUCTOS ── */
        .main-items-table { width: 100%; border-collapse: collapse; border: 1px solid #000; }
        .main-items-table th { border: 1px solid #000; padding: 5px 2px; text-align: center; font-weight: normal; font-size: 10px; letter-spacing: 1px; }
        .main-items-table td { border: 1px solid #000; height: 20px; }

        /* ── PIE FACTURA ── */
        .tabla-pie { width: 100%; border-collapse: collapse !important; border: 1px solid #000 !important; margin-top: -1px; table-layout: fixed; }
        .tabla-pie > tbody > tr > td { border: 1px solid #000 !important; vertical-align: top; height: 100px; padding: 6px; }
        .tabla-pie > tbody > tr > td.col-totales { padding: 0 !important; }
        .check-factura { margin-right: 2px; cursor: pointer; width: 11px; height: 11px; accent-color: #000; }

        /* ── TABLA TOTALES ── */
        .tabla-totales { width: 100%; border-collapse: collapse; height: 100%; border: none; }
        .tabla-totales td { padding: 1px 6px; font-weight: normal; font-size: 9.5px; vertical-align: middle; }
        .tabla-totales tr:not(:last-child) td { border-bottom: 1px solid #000; }
        .col-labels { text-align: right; width: 55%; border-right: 1px solid #000; }
        .col-linea { width: 45%; }

        /* ── INPUTS ── */
        input.doc {
            font-family: inherit; font-size: inherit; color: inherit;
            background: transparent; border: none; outline: none;
            width: 100%; padding: 0 2px; min-height: 14px;
        }
        input.doc:focus { background-color: #fffde7; }
        input.doc.c { text-align: center; }
        input.doc.r { text-align: right; }
        input.doc.mono { font-family: 'Courier New', monospace; }
        input[type="search"]::-webkit-search-cancel-button { display: none; }
        input[type="search"] { -webkit-appearance: none; appearance: none; }

        /* ── NOTA DE ENTREGA: estilos originales ── */
        .x { display: grid; grid-template-columns: 14fr 8fr 8fr 32fr 14fr 14fr 14fr 14fr 14fr 14fr; }
        .c1  { grid-column: span 1; } .c2  { grid-column: span 2; }
        .c3  { grid-column: span 3; } .c4  { grid-column: span 4; }
        .c5  { grid-column: span 5; } .c6  { grid-column: span 6; }
        .c7  { grid-column: span 7; } .c8  { grid-column: span 8; }
        .c9  { grid-column: span 9; } .c10 { grid-column: span 10; }
        .r       { text-align: right; }
        .c       { text-align: center; }
        .b       { font-weight: 700; }
        .rojo    { color: #CC0000; }
        .s8      { font-size: 8px; }
        .s7      { font-size: 7px; }
        .bb      { border-bottom: 1px solid #000; }
        .bt      { border-top: 1px solid #000; }
        .gray    { background: #F2F2F2; }

        .spacer { height: 2px; }

        input.doc.bb { border-bottom: 1px solid #888; }
        input.doc.bb:focus { border-bottom-color: #CC0000; }
        input.doc.bb2 { border-bottom: 1px solid #000; }
        input.doc.rojo { color: #CC0000; font-weight: 700; }

        .prod-table { width: 100%; border-collapse: collapse; margin-top: 0; table-layout: fixed; }
<?php if (!empty($modo_pdf)): ?>
        .prod-table th {
            font-size: 10px; font-weight: 700; text-align: center;
            background: #fff; padding: 4px 3px;
            border-top: 1px solid #000; border-bottom: 1px solid #000;
        }
        .prod-table th:first-child { border-left: 1px solid #000; }
        .prod-table th:last-child { border-right: 1px solid #000; }
        .prod-table td { border: none; padding: 2px 3px; height: 16px; }
        .prod-table td:first-child { border-left: 1px solid #000; }
        .prod-table td:last-child { border-right: 1px solid #000; }
        .prod-table tbody tr:last-child td { border-bottom: 1px solid #000; }
<?php else: ?>
        .prod-table th {
            font-size: 10px; font-weight: 700; text-align: center;
            background: #fff; padding: 4px 3px;
            border: 1px solid #000;
        }
        .prod-table td { border: 1px solid #000; padding: 2px 3px; height: 16px; }
        .prod-table tbody tr:last-child td { border-bottom: 1px solid #000; }
<?php endif; ?>
        .prod-table td input {
            font-family: inherit; font-size: inherit; color: inherit;
            background: transparent; border: none; outline: none;
            width: 100%; padding: 1px 2px;
        }
        .prod-table td input.c { text-align: center; }
        .prod-table td input.r { text-align: right; }

        /* ── CLIENT ROWS (Nota) ── */
        .cliente-row { display: flex; border: 1px solid #D0D0D0; min-height: 20px; font-size: 10px; }
        .cliente-left, .cliente-right { flex: 1; display: flex; }
        .cliente-left { border-right: 1px solid #D0D0D0; }
        .cliente-left > *, .cliente-right > * { padding: 2px 3px; display: flex; align-items: center; }
        .cliente-left > :first-child, .cliente-right > :first-child { padding-right: 1px; border-right: 1px solid #D0D0D0; }
        .cliente-left > :last-child, .cliente-right > :last-child { padding-left: 1px; }
        .cliente-lb { font-weight: 700; white-space: nowrap; font-size: 8px; }
        .cliente-left > :nth-child(2) { flex: 1; }
        .cliente-right > :nth-child(2) { flex: 1; }

        /* ── CLIENT HEADER ── */
        .client-hdr {
            background: #F2F2F2; border: 1px solid #000; text-align: center;
            font-size: 9px; font-weight: 700; color: #CC0000; padding: 3px;
        }
        .sect-divider { border-top: 1px solid #ddd; margin: 3px 0; }

        .oculto { display: none !important; }

<?php if ($esNota): ?>
        /* ── NOTA DE ENTREGA: bordes gris claro ── */
        .cliente-row { border-color: #DCDCDC !important; }
        .cliente-left { border-right-color: #DCDCDC !important; }
        .cliente-right > :first-child { border-right-color: #DCDCDC !important; }
        .cliente-left > :first-child { border-right-color: #DCDCDC !important; }
        .prod-table td { border-color: #A6A6A6 !important; }
        .prod-table th { border-color: #A6A6A6 !important; }
<?php endif; ?>
    </style>
</head>
<body>

<!-- TOOLBAR -->
<div class="toolbar no-print">
    <span class="toolbar-title">Talonario de <?php echo $titulo; ?></span>
    <button class="btn btn-outline-secondary btn-sm" onclick="window.history.back()">← Volver</button>
    <button class="btn btn-danger btn-sm" onclick="generarPDF()"><?php echo $modo === 'editar' ? 'Regenerar PDF' : 'Generar PDF'; ?></button>
    <span id="msg-accion"></span>
</div>

<div class="page-wrap">
<div class="invoice-outer">

<div id="talonario-wrap">

    <input type="hidden" autocomplete="off" id="f_tipo_documento" value="<?php echo $tipo_documento; ?>">

    <!-- ═══════════ EMISOR ═══════════ -->
<?php if ($esNota):
$logoDataPng = base64_encode(file_get_contents(__DIR__ . '/../../../public/img/Logo.webp'));
$logoMime = 'image/webp';
?>
<?php if (!empty($modo_pdf)): ?>
    <table style="width:100%;border-collapse:collapse;margin:2px 0;font-family:'Times New Roman',serif;color:#000;table-layout:fixed;">
        <tr>
            <td rowspan="4" style="width:10%;vertical-align:middle;padding:0 4px;text-align:center;">
                <img src="data:image/webp;base64,<?php echo $logoDataPng; ?>" alt="TEKE'FRITOS" style="width:100%;max-width:65px;max-height:55px;height:auto;">
            </td>
            <td style="font-size:18px;font-weight:900;padding:1px 4px;">TEKE'FRITOS</td>
            <td rowspan="4" style="text-align:right;vertical-align:top;font-size:11px;font-weight:700;padding:1px 4px;white-space:nowrap;width:150px;"><span style="white-space:nowrap;">Fecha: <?php echo htmlspecialchars($datos['f_fecha_escrita'] ?? ''); ?></span></td>
        </tr>
        <tr>
            <td colspan="2" style="font-size:11px;font-weight:700;padding:1px 4px;">VICENTE SANTORSOLA PLUCHINO (F.P.)</td>
        </tr>
        <tr>
            <td colspan="2" style="font-size:11px;font-weight:700;padding:1px 4px;">RIF. V-11185860-8</td>
        </tr>
        <tr>
            <td colspan="2" style="font-size:10px;padding:1px 4px;">AV. BRICEÑO MÉNDEZ NRO ANEXO B, ZONA CANDELARIA VALENCIA EDO. CARABOBO</td>
        </tr>
    </table>
<?php else: ?>
    <table style="width:100%;border-collapse:collapse;margin:2px 0;font-family:'Times New Roman',serif;color:#000;table-layout:fixed;">
        <tr>
            <td rowspan="4" style="width:10%;vertical-align:middle;padding:0 4px;text-align:center;">
                <img src="data:image/webp;base64,<?php echo $logoDataPng; ?>" alt="TEKE'FRITOS" style="width:100%;max-width:65px;max-height:55px;height:auto;">
            </td>
            <td style="font-size:18px;font-weight:900;padding:1px 4px;">TEKE'FRITOS</td>
            <td rowspan="4" style="text-align:right;vertical-align:top;font-size:11px;font-weight:700;padding:1px 4px;white-space:nowrap;width:150px;"><span style="white-space:nowrap;">Fecha: <input type="search" id="f_fecha_escrita" name="f_fecha_escrita" value="<?php echo htmlspecialchars($datos['f_fecha_escrita'] ?? ''); ?>" style="width:100px;border:none;border-bottom:1px solid #ccc;background:transparent;font-size:11px;text-align:center;"></span></td>
        </tr>
        <tr>
            <td colspan="2" style="font-size:11px;font-weight:700;padding:1px 4px;">VICENTE SANTORSOLA PLUCHINO (F.P.)</td>
        </tr>
        <tr>
            <td colspan="2" style="font-size:11px;font-weight:700;padding:1px 4px;">RIF. V-11185860-8</td>
        </tr>
        <tr>
            <td colspan="2" style="font-size:10px;padding:1px 4px;">AV. BRICEÑO MÉNDEZ NRO ANEXO B, ZONA CANDELARIA VALENCIA EDO. CARABOBO</td>
        </tr>
    </table>
<?php endif; ?>
    <input type="hidden" autocomplete="off" id="f_fecha_completa" value="<?php echo htmlspecialchars($datos['f_fecha_completa'] ?? date('d/m/Y')); ?>">
<?php else: ?>
    <!-- ═══════════ HEADER FACTURA: estilo archivo definitivo ═══════════ -->
    <div class="row align-items-center g-0">
        <div class="col-3 logo-box">
            <img src="public/img/logo_talonario.png" alt="Teke'Fritos" class="logo-img">
        </div>
        <div class="col-5 text-center header-center">
            <h1>Vicente Santorsola Pluchino</h1>
            <h2>Teke'Fritos Vicente Santorsola (F.P.)</h2>
            <p>Av. Briceño Méndez, Local Nro. Anexo B, Zona Candelaria.<br>Valencia - Carabobo. Zona Postal 2001<br>
            <span class="texto-negrita-solicitado">Cel.: 0414-424.00.80 / E-mail: tekefritosvfp@gmail.com</span></p>
        </div>
        <div class="col-4">
            <table class="rif-table">
                <colgroup>
                    <col style="width:64%;">
                    <col style="width:11%;">
                    <col style="width:11%;">
                    <col style="width:14%;">
                </colgroup>
                <tr><td colspan="4" class="rif-title">RIF.: V-11815860-8</td></tr>
                <tr class="rif-labels">
                    <th>LUGAR DE EMISIÓN</th>
                    <th>DÍA</th>
                    <th>MES</th>
                    <th>AÑO</th>
                </tr>
<?php if (!empty($modo_pdf)): ?>
                <tr class="rif-values">
                    <td><?php echo htmlspecialchars($datos['f_lugar'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($datos['f_dia'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($datos['f_mes'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($datos['f_anio'] ?? ''); ?></td>
                </tr>
<?php else: ?>
                <tr class="rif-values">
                    <td><input type="search" class="doc c" autocomplete="off" id="f_lugar" value="<?php echo htmlspecialchars($datos['f_lugar'] ?? ''); ?>" style="width:100%;text-align:center;"></td>
                    <td><input type="search" class="doc c" autocomplete="off" id="f_dia" value="<?php echo htmlspecialchars($datos['f_dia'] ?? ''); ?>" style="width:100%;text-align:center;"></td>
                    <td><input type="search" class="doc c" autocomplete="off" id="f_mes" value="<?php echo htmlspecialchars($datos['f_mes'] ?? ''); ?>" style="width:100%;text-align:center;"></td>
                    <td><input type="search" class="doc c" autocomplete="off" id="f_anio" value="<?php echo htmlspecialchars($datos['f_anio'] ?? ''); ?>" style="width:100%;text-align:center;"></td>
                </tr>
<?php endif; ?>
            </table>
        </div>
    </div>

    <div class="row numbers-row g-0">
        <div class="col-6 text-center"><span class="negrita-solicitada">N° DE CONTROL</span> <span class="num-large">00 -</span> <?php if (!empty($modo_pdf)): ?><span class="text-red"><?php echo htmlspecialchars($datos['f_control'] ?? ''); ?></span><?php else: ?><input type="search" class="doc text-red mono" autocomplete="off" id="f_control" value="<?php echo htmlspecialchars($datos['f_control'] ?? ''); ?>" style="display:inline-block;width:100px;font-size:18px;font-weight:bold;color:#e60000;text-align:center;border-bottom:1px solid #ccc;"></span><?php endif; ?></div>
        <div class="col-6 text-center"><span class="negrita-solicitada">FACTURA No.</span> <?php if (!empty($modo_pdf)): ?><span class="text-red"><?php echo htmlspecialchars($datos['f_factura'] ?? ''); ?></span><?php else: ?><input type="search" class="doc text-red mono" autocomplete="off" id="f_factura" value="<?php echo htmlspecialchars($datos['f_factura'] ?? ''); ?>" style="display:inline-block;width:100px;font-size:18px;font-weight:bold;color:#e60000;text-align:center;border-bottom:1px solid #ccc;"></span><?php endif; ?></div>
    </div>
<?php endif; ?>

<?php if ($esNota): ?>
    <div style="text-align:center;font-weight:900;font-size:13px;color:#000;font-family:Arial,sans-serif;margin:4px 0;letter-spacing:0.3px;">ORDEN DE ENTREGA</div>
<?php endif; ?>

    <!-- ═══════════ DATOS DEL CLIENTE ═══════════ -->
<?php if ($esNota): ?>
<?php if (!empty($modo_pdf)): ?>
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="width:15%;border:none;padding:3px 6px;font-weight:700;font-size:8px;">Cliente :</td>
            <td style="width:35%;border:none;padding:3px 6px;font-size:9px;"><?php echo htmlspecialchars($datos['f_cliente_nombre']??''); ?></td>
            <td style="width:15%;border:none;padding:3px 6px;font-weight:700;font-size:8px;">NOTA DE ENTREGA :</td>
            <td style="width:35%;border:none;padding:3px 6px;font-size:9px;"><?php echo htmlspecialchars($datos['f_nota_numero_val'] ?? ''); ?></td>
        </tr>
        <tr>
            <td style="border:none;padding:3px 6px;font-weight:700;font-size:8px;">R.I.F.:</td>
            <td style="border:none;padding:3px 6px;font-size:9px;"><?php echo htmlspecialchars($datos['f_cliente_rif']??''); ?></td>
            <td style="border:none;padding:3px 6px;font-weight:700;font-size:8px;">Fecha Emisión:</td>
            <td style="border:none;padding:3px 6px;font-size:9px;"><?php echo htmlspecialchars($datos['f_fecha_emision']??''); ?></td>
        </tr>
        <tr>
            <td style="border:none;padding:3px 6px;font-weight:700;font-size:8px;">Teléfonos:</td>
            <td style="border:none;padding:3px 6px;font-size:9px;"><?php echo htmlspecialchars($datos['f_cliente_tel']??''); ?></td>
            <td style="border:none;padding:3px 6px;font-weight:700;font-size:8px;">Fecha Venc.:</td>
            <td style="border:none;padding:3px 6px;font-size:9px;"><?php echo htmlspecialchars($datos['f_fecha_venc']??''); ?></td>
        </tr>
        <tr>
            <td style="border:none;padding:3px 6px;font-weight:700;font-size:8px;">Dirección:</td>
            <td style="border:none;padding:3px 6px;font-size:9px;"><?php echo htmlspecialchars($datos['f_cliente_domicilio']??''); ?></td>
            <td style="border:none;padding:3px 6px;font-weight:700;font-size:8px;">Vendedor:</td>
            <td style="border:none;padding:3px 6px;font-size:9px;"><?php echo htmlspecialchars($datos['f_vendedor']??''); ?></td>
        </tr>
        <tr>
            <td style="border:none;padding:3px 6px;" colspan="2"></td>
            <td style="border:none;padding:3px 6px;font-weight:700;font-size:8px;">Credito:</td>
            <td style="border:none;padding:3px 6px;font-size:9px;"><?php echo htmlspecialchars($datos['f_credito']??''); ?></td>
        </tr>
    </table>
<?php else: ?>
<div class="nota-client">
    <div class="spacer"></div>
    <div class="cliente-row">
        <span class="cliente-left">
            <span class="cliente-lb" id="lb-cliente">Cliente :</span>
            <span class="cliente-vl"><input type="search" class="doc" autocomplete="off" id="f_cliente_nombre" value="<?php echo htmlspecialchars($datos['f_cliente_nombre'] ?? ''); ?>"></span>
        </span>
        <span class="cliente-right">
            <span class="cliente-lb" id="lb-nota">NOTA DE ENTREGA :</span>
            <span class="cliente-vr"><input type="search" class="doc" autocomplete="off" id="f_nota_numero_val" value="<?php echo htmlspecialchars($datos['f_nota_numero_val'] ?? ''); ?>" placeholder=""></span>
        </span>
    </div>
    <div class="cliente-row">
        <span class="cliente-left">
            <span class="cliente-lb" id="lb-rif">R.I.F.:</span>
            <span class="cliente-vl"><input type="search" class="doc" autocomplete="off" id="f_cliente_rif" value="<?php echo htmlspecialchars($datos['f_cliente_rif'] ?? ''); ?>"></span>
        </span>
        <span class="cliente-right">
            <span class="cliente-lb" id="lb-fec-emision">Fecha Emisión:</span>
            <span class="cliente-vr"><input type="search" class="doc" autocomplete="off" id="f_fecha_emision" value="<?php echo htmlspecialchars($datos['f_fecha_emision'] ?? ''); ?>"></span>
        </span>
    </div>
    <div class="cliente-row">
        <span class="cliente-left">
            <span class="cliente-lb" id="lb-tlf">Teléfonos:</span>
            <span class="cliente-vl"><input type="search" class="doc" autocomplete="off" id="f_cliente_tel" value="<?php echo htmlspecialchars($datos['f_cliente_tel'] ?? ''); ?>"></span>
        </span>
        <span class="cliente-right">
            <span class="cliente-lb" id="lb-fec-venc">Fecha Venc.:</span>
            <span class="cliente-vr"><input type="search" class="doc" autocomplete="off" id="f_fecha_venc" value="<?php echo htmlspecialchars($datos['f_fecha_venc'] ?? ''); ?>"></span>
        </span>
    </div>
    <div class="cliente-row">
        <span class="cliente-left">
            <span class="cliente-lb" id="lb-direccion">Dirección:</span>
            <span class="cliente-vl"><input type="search" class="doc" autocomplete="off" id="f_cliente_domicilio" value="<?php echo htmlspecialchars($datos['f_cliente_domicilio'] ?? ''); ?>"></span>
        </span>
        <span class="cliente-right">
            <span class="cliente-lb" id="lb-vendedor">Vendedor:</span>
            <span class="cliente-vr"><input type="search" class="doc" autocomplete="off" id="f_vendedor" value="<?php echo htmlspecialchars($datos['f_vendedor'] ?? ''); ?>"></span>
        </span>
    </div>
    <div class="cliente-row">
        <span class="cliente-left"></span>
        <span class="cliente-right">
            <span class="cliente-lb" id="lb-credito">Credito:</span>
            <span class="cliente-vr"><input type="search" class="doc" autocomplete="off" id="f_credito" value="<?php echo htmlspecialchars($datos['f_credito'] ?? ''); ?>"></span>
        </span>
    </div>
    </div>
<?php endif; ?>
<?php else: ?>
    <!-- ═══════════ CLIENTE FACTURA: estilo archivo definitivo ═══════════ -->
<?php if (!empty($modo_pdf)): ?>
    <table class="client-container-table">
        <tr><td colspan="3" style="border-bottom: 1px solid #000;">NOMBRE APELLIDO O RAZÓN SOCIAL: <?php echo htmlspecialchars($datos['f_cliente_nombre'] ?? ''); ?></td></tr>
        <tr style="height:18px"><td colspan="3" style="border-bottom: 1px solid #000;"><?php echo htmlspecialchars($datos['f_cliente_domicilio'] ?? ''); ?></td></tr>
        <tr><td colspan="3" style="border-bottom: 1px solid #000;">DOMICILIO FISCAL: <?php echo htmlspecialchars($datos['f_cliente_domicilio'] ?? ''); ?></td></tr>
        <tr style="height:18px">
            <td colspan="2" style="width: 75%; border-bottom: 1px solid #000; border-right: 1px solid #000;"><?php echo htmlspecialchars($datos['f_condiciones_pago'] ?? ''); ?></td>
            <td style="width: 25%; text-align: center; border-bottom: 1px solid #000;">CONDICIONES DE PAGO</td>
        </tr>
        <tr>
            <td style="width: 38%; border-right: 1px solid #000;">RIF/C.I.: <?php echo htmlspecialchars($datos['f_cliente_rif'] ?? ''); ?></td>
            <td style="width: 37%; border-right: 1px solid #000;">TELÉFONO: <?php echo htmlspecialchars($datos['f_cliente_tel'] ?? ''); ?></td>
            <td style="width: 25%;"><?php echo htmlspecialchars($datos['f_col_extra'] ?? ''); ?></td>
        </tr>
    </table>
<?php else: ?>
    <table class="client-container-table">
        <tr><td colspan="3" style="border-bottom: 1px solid #000;">NOMBRE APELLIDO O RAZÓN SOCIAL: <input type="search" class="doc editable-field" autocomplete="off" id="f_cliente_nombre" value="<?php echo htmlspecialchars($datos['f_cliente_nombre'] ?? ''); ?>" style="width:60%;display:inline-block;"></td></tr>
        <tr style="height:18px"><td colspan="3" style="border-bottom: 1px solid #000;"><input type="search" class="doc" autocomplete="off" id="f_cliente_domicilio" value="<?php echo htmlspecialchars($datos['f_cliente_domicilio'] ?? ''); ?>" style="width:100%;display:inline-block;"></td></tr>
        <tr><td colspan="3" style="border-bottom: 1px solid #000;">DOMICILIO FISCAL: <input type="search" class="doc editable-field" autocomplete="off" id="f_domicilio_fiscal" value="<?php echo htmlspecialchars($datos['f_cliente_domicilio'] ?? ''); ?>" style="width:70%;display:inline-block;"></td></tr>
        <tr style="height:18px">
            <td colspan="2" style="width: 75%; border-bottom: 1px solid #000; border-right: 1px solid #000;"><input type="search" class="doc" autocomplete="off" id="f_condiciones_pago" value="<?php echo htmlspecialchars($datos['f_condiciones_pago'] ?? ''); ?>" style="width:100%;display:inline-block;"></td>
            <td style="width: 25%; text-align: center; border-bottom: 1px solid #000;">CONDICIONES DE PAGO</td>
        </tr>
        <tr>
            <td style="width: 38%; border-right: 1px solid #000;">RIF/C.I.: <input type="search" class="doc editable-field" autocomplete="off" id="f_cliente_rif" value="<?php echo htmlspecialchars($datos['f_cliente_rif'] ?? ''); ?>" style="width:70%;display:inline-block;"></td>
            <td style="width: 37%; border-right: 1px solid #000;">TELÉFONO: <input type="search" class="doc editable-field" autocomplete="off" id="f_cliente_tel" value="<?php echo htmlspecialchars($datos['f_cliente_tel'] ?? ''); ?>" style="width:65%;display:inline-block;"></td>
            <td style="width: 25%;"><input type="search" class="doc" autocomplete="off" id="f_col_extra" value="<?php echo htmlspecialchars($datos['f_col_extra'] ?? ''); ?>" style="width:100%;display:inline-block;"></td>
        </tr>
    </table>
<?php endif; ?>
<?php endif; ?>

    <!-- ═══════════ TABLA DE PRODUCTOS ═══════════ -->
<?php if ($esNota): ?>
    <div class="spacer"></div>
    <table class="prod-table">
        <thead>
            <tr>
                <th style="width:10%;">Código</th>
                <th colspan="3" style="width:50%;">Descripción</th>
                <th style="width:10%;">Cantidad</th>
                <th style="width:10%;">Unid.</th>
                <th style="width:10%;">Precio</th>
                <th style="width:10%;">Neto</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($i = 1; $i <= 8; $i++): ?>
            <tr>
                <td style="text-align:center;"><input type="search" class="c" autocomplete="off" id="f_codigo_<?php echo $i; ?>" value="<?php echo htmlspecialchars($datos["f_codigo_{$i}"] ?? ''); ?>"></td>
                <td colspan="3"><input type="search" autocomplete="off" id="f_concepto_<?php echo $i; ?>" value="<?php echo htmlspecialchars($datos["f_concepto_{$i}"] ?? ''); ?>" style="width:100%;"></td>
                <td style="text-align:center;"><input type="search" class="c" autocomplete="off" id="f_cant_<?php echo $i; ?>" value="<?php echo htmlspecialchars($datos["f_cant_{$i}"] ?? ''); ?>"></td>
                <td style="text-align:center;"><input type="search" class="c" autocomplete="off" id="f_unidad_<?php echo $i; ?>" value="<?php echo htmlspecialchars($datos["f_unidad_{$i}"] ?? ''); ?>"></td>
                <td style="text-align:right;"><input type="search" class="r" autocomplete="off" id="f_vlr_<?php echo $i; ?>" value="<?php echo htmlspecialchars($datos["f_vlr_{$i}"] ?? ''); ?>"></td>
                <td style="text-align:right;"><input type="search" class="r" autocomplete="off" id="f_nvl_<?php echo $i; ?>" value="<?php echo htmlspecialchars($datos["f_nvl_{$i}"] ?? ''); ?>"></td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>
<?php else: ?>
    <table class="main-items-table">
        <thead>
            <tr>
                <th style="width:8%;">CANT</th>
                <th style="width:60%;">C O N C E P T O &nbsp; O &nbsp; D E S C R I P C I Ó N</th>
                <th style="width:15%;">PRECIO UNT.</th>
                <th style="width:17%;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
<?php for ($i = 1; $i <= 8; $i++): ?>
            <tr>
<?php if (!empty($modo_pdf)): ?>
                <td style="text-align:center;"><?php echo htmlspecialchars($datos["f_cant_{$i}"] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($datos["f_concepto_{$i}"] ?? ''); ?></td>
                <td style="text-align:right;"><?php echo htmlspecialchars($datos["f_vlr_{$i}"] ?? ''); ?></td>
                <td style="text-align:right;"><?php echo htmlspecialchars($datos["f_ttl_{$i}"] ?? ''); ?></td>
<?php else: ?>
                <td><input type="search" class="doc c" autocomplete="off" id="f_cant_<?php echo $i; ?>" value="<?php echo htmlspecialchars($datos["f_cant_{$i}"] ?? ''); ?>"></td>
                <td><input type="search" class="doc" autocomplete="off" id="f_concepto_<?php echo $i; ?>" value="<?php echo htmlspecialchars($datos["f_concepto_{$i}"] ?? ''); ?>"></td>
                <td><input type="search" class="doc r" autocomplete="off" id="f_vlr_<?php echo $i; ?>" value="<?php echo htmlspecialchars($datos["f_vlr_{$i}"] ?? ''); ?>"></td>
                <td><input type="search" class="doc r" autocomplete="off" id="f_ttl_<?php echo $i; ?>" value="<?php echo htmlspecialchars($datos["f_ttl_{$i}"] ?? ''); ?>"></td>
<?php endif; ?>
            </tr>
<?php endfor; ?>
        </tbody>
    </table>
<?php endif; ?>

    <!-- ═══════════ PIE ═══════════ -->
<?php if ($esNota): ?>
    <!-- NOTA DE ENTREGA: totales originales -->
    <div class="spacer"></div>
<?php if (!empty($modo_pdf)): ?>
    <div style="text-align:right;">
        <table style="border-collapse:collapse;width:20%;float:right;table-layout:fixed;">
            <tr>
                <td style="border:1px solid #000;padding:3px 6px;font-weight:700;font-size:8px;text-align:right;width:40%;">Sub-Total:</td>
                <td style="border:1px solid #000;padding:1px 2px;font-size:10px;text-align:right;width:60%;"><span style="display:block;text-align:right;width:100%;"><?php echo htmlspecialchars($datos['f_sst_nota'] ?? ''); ?></span></td>
            </tr>
            <tr>
                <td style="border:1px solid #000;padding:3px 6px;font-weight:700;font-size:8px;text-align:right;width:40%;">Neto:</td>
                <td style="border:1px solid #000;padding:1px 2px;font-size:10px;text-align:right;width:60%;"><span style="display:block;text-align:right;width:100%;"><?php echo htmlspecialchars($datos['f_nfn_nota'] ?? ''); ?></span></td>
            </tr>
        </table>
    </div>
<?php else: ?>
    <div style="display:flex;justify-content:flex-end;margin-top:2px;">
        <div style="border:1px solid #D0D0D0;width:20%;">
            <div style="display:flex;align-items:center;">
                <span class="cliente-lb" style="padding:2px 4px;border-right:1px solid #D0D0D0;">Sub-Total:</span>
                <span style="flex:1;padding:1px 2px;"><input type="search" class="doc r b" autocomplete="off" id="f_sst_nota" value="<?php echo htmlspecialchars($datos['f_sst_nota'] ?? ''); ?>"></span>
            </div>
            <div style="display:flex;align-items:center;border-top:1px solid #D0D0D0;">
                <span class="cliente-lb" style="padding:2px 4px;border-right:1px solid #D0D0D0;">Neto:</span>
                <span style="flex:1;padding:1px 2px;"><input type="search" class="doc r b" autocomplete="off" id="f_nfn_nota" value="<?php echo htmlspecialchars($datos['f_nfn_nota'] ?? ''); ?>"></span>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php else: ?>
    <!-- FACTURA: pie estilo archivo definitivo -->
    <table class="tabla-pie">
        <tr>
            <td style="padding:6px;width:40%;">
    <?php if (!empty($modo_pdf)): ?>
                <div style="font-size:9px;margin-bottom:8px;white-space:nowrap;"><b>FORMA DE PAGO:</b>
                    <?php echo !empty($datos['f_mpago']) ? htmlspecialchars($datos['f_mpago']) : ''; ?>
                </div>
                <div style="border-bottom:1px solid #000;margin-bottom:8px;">Nº: <?php echo htmlspecialchars($datos['f_refnro'] ?? ''); ?></div>
                <div style="border-bottom:1px solid #000;margin-bottom:8px;">BANCO: <?php echo htmlspecialchars($datos['f_refbco'] ?? ''); ?></div>
                <div style="margin-bottom:6px;">IGTF</div>
                <div>Base de calculo <?php echo htmlspecialchars($datos['f_igtfb'] ?? ''); ?> % <?php echo htmlspecialchars($datos['f_igtfp'] ?? ''); ?></div>
    <?php else: ?>
                <div style="font-size:9px;margin-bottom:8px;white-space:nowrap;">
                    <b>FORMA DE PAGO:</b>
                    <input type="checkbox" class="check-factura" id="f_chk_efectivo" <?php echo (stripos($datos['f_mpago'] ?? '', 'efectivo') !== false) ? 'checked' : ''; ?>>EFECTIVO
                    <input type="checkbox" class="check-factura" id="f_chk_transf" <?php echo (stripos($datos['f_mpago'] ?? '', 'transf') !== false) ? 'checked' : ''; ?>>TRANSF.
                    <input type="checkbox" class="check-factura" id="f_chk_pago_movil" <?php echo (stripos($datos['f_mpago'] ?? '', 'pago') !== false || stripos($datos['f_mpago'] ?? '', 'movil') !== false) ? 'checked' : ''; ?>>P. MOVIL
                    <input type="hidden" id="f_mpago" value="<?php echo htmlspecialchars($datos['f_mpago'] ?? ''); ?>">
                </div>
                <div style="border-bottom:1px solid #000;margin-bottom:8px;">Nº: <input type="search" class="doc" autocomplete="off" id="f_refnro" value="<?php echo htmlspecialchars($datos['f_refnro'] ?? ''); ?>" style="width:80%;display:inline-block;"></div>
                <div style="border-bottom:1px solid #000;margin-bottom:8px;">BANCO: <input type="search" class="doc" autocomplete="off" id="f_refbco" value="<?php echo htmlspecialchars($datos['f_refbco'] ?? ''); ?>" style="width:70%;display:inline-block;"></div>
                <div style="margin-bottom:6px;"><input type="checkbox" class="check-factura" id="f_chk_igtf" <?php echo !empty($datos['f_igtfb']) ? 'checked' : ''; ?>> IGTF</div>
                <div>Base de calculo <input type="search" class="doc" autocomplete="off" id="f_igtfb" value="<?php echo htmlspecialchars($datos['f_igtfb'] ?? ''); ?>" style="border-bottom:1px solid #000;width:45px;display:inline-block;"> % <input type="search" class="doc" autocomplete="off" id="f_igtfp" value="<?php echo htmlspecialchars($datos['f_igtfp'] ?? ''); ?>" style="border-bottom:1px solid #000;width:45px;display:inline-block;"></div>
    <?php endif; ?>
            </td>

            <td style="text-align:center;padding:6px 6px 0 6px;width:22%;">
                <div style="font-weight:bold;margin-bottom:3px;font-size:8.5px;">ESTA FACTURA VA SIN TACHADURA NI ENMENDADURA</div>
                <div style="margin-top:58px;border-top:1px solid #000;width:70%;margin-left:auto;margin-right:auto;padding-top:2px;">Recibido Conforme</div>
            </td>

            <td class="col-totales" style="width:38%;">
                <table class="tabla-totales">
                    <tr><td class="col-labels">Sub-Total</td><td class="col-linea"><?php if (!empty($modo_pdf)): ?><?php echo htmlspecialchars($datos['f_ssub'] ?? ''); ?><?php else: ?><input type="search" class="doc r" autocomplete="off" id="f_ssub" value="<?php echo htmlspecialchars($datos['f_ssub'] ?? ''); ?>"><?php endif; ?></td></tr>
                    <tr><td class="col-labels">Otros</td><td class="col-linea"><?php if (!empty($modo_pdf)): ?><?php echo htmlspecialchars($datos['f_otros'] ?? ''); ?><?php else: ?><input type="search" class="doc r" autocomplete="off" id="f_otros" value="<?php echo htmlspecialchars($datos['f_otros'] ?? ''); ?>"><?php endif; ?></td></tr>
                    <tr><td class="col-labels">Ajustes</td><td class="col-linea"><?php if (!empty($modo_pdf)): ?><?php echo htmlspecialchars($datos['f_ajustes'] ?? ''); ?><?php else: ?><input type="search" class="doc r" autocomplete="off" id="f_ajustes" value="<?php echo htmlspecialchars($datos['f_ajustes'] ?? ''); ?>"><?php endif; ?></td></tr>
                    <tr><td class="col-labels">Base Imponible</td><td class="col-linea"><?php if (!empty($modo_pdf)): ?><?php echo htmlspecialchars($datos['f_bimp'] ?? ''); ?><?php else: ?><input type="search" class="doc r" autocomplete="off" id="f_bimp" value="<?php echo htmlspecialchars($datos['f_bimp'] ?? ''); ?>"><?php endif; ?></td></tr>
                    <tr><td class="col-labels" style="white-space:nowrap;">I.V.A. <?php if (!empty($modo_pdf)): ?><?php echo htmlspecialchars($datos['f_ivat'] ?? ''); ?><?php else: ?><input type="search" class="doc" autocomplete="off" id="f_ivat" value="<?php echo htmlspecialchars($datos['f_ivat'] ?? ''); ?>" style="border-bottom:1px solid #000;width:20px;display:inline-block;"><?php endif; ?> % Sobre</td><td class="col-linea"><?php if (!empty($modo_pdf)): ?><?php echo htmlspecialchars($datos['f_ivam'] ?? ''); ?><?php else: ?><input type="search" class="doc r" autocomplete="off" id="f_ivam" value="<?php echo htmlspecialchars($datos['f_ivam'] ?? ''); ?>"><?php endif; ?></td></tr>
                    <tr><td class="col-labels" style="font-weight:bold;">TOTAL A PAGAR</td><td class="col-linea"><?php if (!empty($modo_pdf)): ?><span style="font-weight:bold;"><?php echo htmlspecialchars($datos['f_ttl'] ?? ''); ?></span><?php else: ?><input type="search" class="doc r" autocomplete="off" id="f_ttl" value="<?php echo htmlspecialchars($datos['f_ttl'] ?? ''); ?>" style="font-weight:bold;"><?php endif; ?></td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="imprenta-contenedor">IMPRESOS COLORAMA C.A. RIF J-07527584-5 &bull; Av. Bolívar Norte, C.C. París, Local 07, Valencia. Telefax: 0241/822.96.72 Nro. Providencia 0071. SENIAT GRT/10/00666 del 27/03/2008<br>
    &bull; Fecha: <?php echo date('d/m/Y'); ?> &bull; REGIÓN: CENTRAL</div>
    <div class="leyenda-fiscal">ORIGINAL Blanca - COPIA - SIN DERECHO A CREDITO FISCAL - Color</div>
<?php endif; ?>

</div>
</div>
</div>

<script>

const TALONARIO_MODO = '<?php echo $modo; ?>';
const TALONARIO_ID = <?php echo $id_factura ?: '0'; ?>;
const TALONARIO_TIPO = '<?php echo $tipo_documento; ?>';

function syncPagoCheckboxes() {
    var mpago = document.getElementById('f_mpago');
    if (!mpago) return;
    var partes = [];
    if (document.getElementById('f_chk_efectivo') && document.getElementById('f_chk_efectivo').checked) partes.push('EFECTIVO');
    if (document.getElementById('f_chk_transf') && document.getElementById('f_chk_transf').checked) partes.push('TRANSF.');
    if (document.getElementById('f_chk_pago_movil') && document.getElementById('f_chk_pago_movil').checked) partes.push('PAGO MOVIL');
    mpago.value = partes.join(' / ');
}

function generarPDF() {
    var btn = document.querySelector('.btn-danger');
    btn.disabled = true;
    btn.innerHTML = 'Guardando...';
    document.getElementById('msg-accion').textContent = 'Guardando datos...';

    syncPagoCheckboxes();

    var form = document.getElementById('talonario-wrap');
    var inputs = form.querySelectorAll('input');
    var datos = {};

    inputs.forEach(function(inp) {
        if (inp.type === 'checkbox') return;
        datos[inp.id] = inp.value;
    });

    var fMpago = document.getElementById('f_mpago');
    if (fMpago) datos['f_mpago'] = fMpago.value;

    var fFact = document.getElementById('f_factura');
    var fNota = document.getElementById('f_nota_numero_val');
    if (fFact && fFact.value) {
        datos['_numero'] = parseInt(fFact.value, 10) || 0;
    } else if (fNota && fNota.value) {
        var m = fNota.value.match(/(\d+)$/);
        datos['_numero'] = m ? parseInt(m[1], 10) : 0;
    } else {
        datos['_numero'] = 0;
    }
    datos['_modo'] = TALONARIO_MODO;
    datos['_id'] = TALONARIO_ID;
    datos['_id_documento'] = <?php echo $id_documento ?: '0'; ?>;
    datos['_tipo_documento'] = TALONARIO_TIPO;

    fetch('index.php?action=generar-talonario-pdf', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(datos)
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success && data.id_documento) {
            document.getElementById('msg-accion').textContent = 'Datos guardados. Abriendo impresion...';
            
            // Redirigir a "Documentos" al cerrar o aceptar el cuadro de impresión
            window.onafterprint = function() {
                window.location.href = 'index.php?action=admin&vista=Documentos';
            };
            
            window.print();
        } else {
            document.getElementById('msg-accion').textContent = 'Error al guardar.';
        }
    })
    .catch(function(e) {
        document.getElementById('msg-accion').textContent = 'Error: ' + e.message;
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = TALONARIO_MODO === 'editar' ? 'Regenerar PDF' : 'Generar PDF';
    });
}
</script>

</body>
</html>