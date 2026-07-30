<?php

use Dompdf\Dompdf;

class AdminController {
    public static function handle() {
        Session::redirectIfNotAdmin();
        Session::start();

        $tasas = DivisasHelper::obtenerTasas();
        $tasa_cambio = $tasas['usd'];

        // Carga unificada de configuraciones del sistema
        $config_res = Database::query("SELECT clave, valor FROM sistema_config WHERE clave IN ('tasa_iva', 'social_whatsapp', 'social_instagram', 'social_facebook', 'social_tiktok', 'tasa_sync_anterior')");
        $configs = [];
        foreach (Database::fetchAll($config_res) as $cfg) {
            $configs[$cfg['clave']] = $cfg['valor'];
        }

        $tasa_iva = isset($configs['tasa_iva']) ? floatval($configs['tasa_iva']) : 0;
        $redes = [
            'social_whatsapp'  => $configs['social_whatsapp'] ?? '',
            'social_instagram' => $configs['social_instagram'] ?? '',
            'social_facebook'  => $configs['social_facebook'] ?? '',
            'social_tiktok'    => $configs['social_tiktok'] ?? '',
        ];

        // Auto-sync precios y costos con tasa BCV
        $sync_anterior = isset($configs['tasa_sync_anterior']) ? floatval($configs['tasa_sync_anterior']) : 0;
        $precios_sincronizados = false;
        if ($sync_anterior > 0 && abs($sync_anterior - $tasa_cambio) > 0.01) {
            if ($tasa_cambio < $sync_anterior * 0.5) {
                self::logBitacora('Auto-sync', "Omitido: tasa Bs.$tasa_cambio es sospechosa vs anterior Bs.$sync_anterior", 'sistema_config');
            } else {
                $db = Database::getConnection();
                mysqli_begin_transaction($db);
                try {
                    Database::query("UPDATE productos SET precio = ROUND(precio_usd * ?, 2), costo = ROUND(costo_usd * ?, 2) WHERE precio_usd > 0 OR costo_usd > 0", [$tasa_cambio, $tasa_cambio]);
                    Database::query("UPDATE sistema_config SET valor = ? WHERE clave = 'tasa_sync_anterior'", [(string)$tasa_cambio]);
                    mysqli_commit($db);
                    $precios_sincronizados = true;
                } catch (Exception $e) {
                    mysqli_rollback($db);
                    self::logBitacora('Auto-sync', "Error en transacción: " . $e->getMessage(), 'sistema_config');
                }
            }
        } elseif ($sync_anterior == 0) {
            Database::query("UPDATE sistema_config SET valor = ? WHERE clave = 'tasa_sync_anterior'", [(string)$tasa_cambio]);
        }

        $vista = $_GET['vista'] ?? 'Inicio';
        $reporte = $_GET['reporte'] ?? '';
        $rol_usuario = Session::rol();
        $usuario_nombre = Session::nombre();
        $usuario_id = Session::id();
        $usuario_correo = Session::get('correo');
        $mensaje_exito = isset($_GET['exito']) && $_GET['exito'] == 1 ? 'Operacion realizada con exito.' : '';
        $mensaje_error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';

        if ($vista === 'Personal' && $rol_usuario !== 'admin') {
            header("Location: index.php?action=admin&vista=Inicio");
            exit();
        }

        if ($rol_usuario === 'vendedor' && !in_array($vista, ['Inicio', 'Productos', 'Pedidos'])) {
            header("Location: index.php?action=admin&vista=Inicio");
            exit();
        }

        self::procesarAcciones($vista, $rol_usuario);

        $total_productos = Database::fetchColumn("SELECT COUNT(*) FROM productos");
        $ventas_totales = Database::fetchColumn("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE estado='Completado'");
        $res_alertas_productos = Database::query("SELECT nombre, stock, stock_minimo FROM productos WHERE stock < stock_minimo AND estado != 'Inactivo'");
        $alertas_productos = Database::getResult($res_alertas_productos);
        $res_alertas_materia = Database::query("SELECT nombre, cantidad, unidad, minimo FROM materia_prima WHERE cantidad < minimo");
        $alertas_materia = Database::getResult($res_alertas_materia);
        $total_alertas = mysqli_num_rows($alertas_productos) + mysqli_num_rows($alertas_materia);

        $rentabilidad_total = Database::fetchColumn("SELECT COALESCE(SUM(precio - costo), 0) FROM productos WHERE estado != 'Inactivo' AND precio > 0 AND costo > 0") ?? 0;

        $res_top = Database::query(
            "SELECT p.nombre, SUM(dp.cantidad) as total_ventas 
             FROM detalles_pedido dp JOIN productos p ON dp.id_producto = p.id 
             GROUP BY p.id ORDER BY total_ventas DESC LIMIT 5"
        );
        $top_productos = Database::getResult($res_top);

        $hoy = date('Y-m-d');
        $res_hoy = Database::query(
            "SELECT COUNT(*) as total_pedidos, COALESCE(SUM(total),0) as suma 
             FROM pedidos WHERE DATE(fecha) = ? AND estado != 'Cancelado'",
            [$hoy]
        );
        $ventas_hoy = Database::fetch($res_hoy) ?: ['total_pedidos' => 0, 'suma' => 0];

        $res_ultimos = Database::query(
            "SELECT p.id, p.total, p.estado, p.fecha, c.nombre as cliente_nombre 
             FROM pedidos p LEFT JOIN contactos c ON p.id_contacto = c.id 
             ORDER BY p.id DESC LIMIT 5"
        );
        $ultimos_pedidos = Database::getResult($res_ultimos);

        // Query string for the table based on vista
        $reporte_map = ['clientes' => 'R. Clientes', 'bitacora' => 'Bitácora', 'iva' => 'R. IVA', 'perdidas' => 'R. Perdidas'];
        $vista_query = ($vista === 'Reportes' && $reporte) ? ($reporte_map[$reporte] ?? null) : $vista;
        $tabla_query = $vista_query ? self::getQueryForVista($vista_query) : null;
        $res_datos = null;
        $datos = null;
        $documentos_array = null;
        $pag = 1;
        $total_pags = 1;

        if ($vista === 'Productos') {
            $res_datos = Database::query(
                "SELECT p.*, c.nombre as categoria_nombre FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.estado != 'Inactivo' ORDER BY p.id DESC"
            );
            $datos = Database::getResult($res_datos);
        } elseif ($vista === 'Documentos') {
            // Uploaded documents from DB
            $docs = [];
            $ruta_docs = __DIR__ . '/../../public/uploads/documentos/';
            $ruta_fact = __DIR__ . '/../../public/uploads/facturas_generadas/';
            $r_docs = Database::query("SELECT d.*, c.nombre as contacto_nombre, u.nombre as usuario_nombre FROM documentos d LEFT JOIN contactos c ON d.id_contacto = c.id LEFT JOIN usuarios u ON d.id_usuario = u.id WHERE d.estado = 'Activo' ORDER BY d.fecha_subida DESC");
            foreach (Database::fetchAll($r_docs) as $d) {
                $archivo = $d['archivo_real'];
                if (!empty($archivo) && !file_exists($ruta_docs . $archivo) && !file_exists($ruta_fact . $archivo)) {
                    // Archivo eliminado del disco → auto-papelera
                    Database::query("UPDATE documentos SET estado = 'Inactivo' WHERE id = ?", [$d['id']]);
                    continue;
                }
                if (!empty($archivo)) {
                    $d['doc_tipo'] = file_exists($ruta_fact . $archivo) ? 'Generado' : 'Subido';
                } else {
                    $d['doc_tipo'] = 'Impreso';
                }
                $docs[] = $d;
            }
            // Talonarios PDF from directory
            $talonarios = [];
            $dir = __DIR__ . '/../../public/uploads/facturas_generadas';
            if (is_dir($dir)) {
                $files = scandir($dir);
                foreach ($files as $f) {
                    if (pathinfo($f, PATHINFO_EXTENSION) === 'pdf') {
                        $mtime = filemtime("$dir/$f");
                        $talonarios[] = [
                            'id' => 0,
                            'nombre_archivo' => $f,
                            'archivo_real' => $f,
                            'contacto_nombre' => null,
                            'tipo' => str_starts_with($f, 'nota_') ? 'Nota de Entrega' : 'Factura',
                            'usuario_nombre' => null,
                            'fecha_subida' => date('Y-m-d H:i:s', $mtime),
                            'doc_tipo' => 'Generado',
                        ];
                    }
                }
            }
            // Deduplicar: omitir talonarios cuyo archivo_real ya exista en documentos (cualquier estado)
            $r_todos_archivos = Database::query("SELECT archivo_real FROM documentos");
            $archivos_en_db = [];
            foreach (Database::fetchAll($r_todos_archivos) as $row) {
                $archivos_en_db[] = $row['archivo_real'];
            }
            $talonarios_filtrados = array_filter($talonarios, function ($t) use ($archivos_en_db) {
                return !in_array($t['archivo_real'], $archivos_en_db);
            });
            // Merge and sort by date desc
            $merged = array_merge($docs, $talonarios_filtrados);
            usort($merged, function ($a, $b) {
                return strtotime($b['fecha_subida']) - strtotime($a['fecha_subida']);
            });
            $documentos_array = $merged;
        } elseif ($tabla_query) {
            $res_datos = Database::query($tabla_query['sql'], $tabla_query['params'] ?? []);
            $datos = Database::getResult($res_datos);
        }

        require_once __DIR__ . '/../../public/views/layouts/admin.php';
    }

    public static function ajaxDetallePedido() {
        Session::redirectIfNotAdmin();
        $id_ped = intval($_GET['id'] ?? 0);
        if (!$id_ped) { echo json_encode([]); exit(); }

        $res = Database::query(
            "SELECT dp.*, p.nombre as prod_nombre, p.tipo, p.codigo
             FROM detalles_pedido dp JOIN productos p ON dp.id_producto = p.id 
             WHERE dp.id_pedido = ?",
            [$id_ped]
        );
        $items = Database::fetchAll($res);
        header('Content-Type: application/json');
        echo json_encode($items);
        exit();
    }

    public static function ajaxTasa() {
        Session::redirectIfNotAdmin();
        header('Content-Type: application/json');
        $tasas = DivisasHelper::obtenerTasas(true);
        echo json_encode($tasas);
        exit();
    }

    public static function llenarTalonario() {
        Session::redirectIfNotAdmin();
        if (Session::rol() === 'vendedor') {
            header("Location: index.php?action=admin");
            exit();
        }
        Session::start();

        $tipo_param = $_GET['tipo'] ?? '';
        $tipo_documento = ($tipo_param === 'Nota de Entrega') ? 'Nota de Entrega' : 'Factura';
        $id_documento = intval($_GET['id_documento'] ?? 0);

        if ($id_documento > 0) {
            // Cargar datos desde la BD para editar
            $doc = Documento::getById($id_documento);
            if ($doc && $doc['datos']) {
                $saved = json_decode($doc['datos'], true);
                $datos = [];
                foreach ($saved as $k => $v) {
                    if (str_starts_with($k, 'f_')) {
                        $datos[$k] = $v;
                    }
                }
                $tipo_documento = $saved['_tipo_documento'] ?? $doc['tipo'] ?? $tipo_documento;
            } else {
                // Sin datos guardados: formulario vacío
                $datos = self::datosVacios();
            }
            $modo = 'editar';
            $id_factura = 0;
            $numero = intval($_GET['numero'] ?? 0);
            $num_formateado = str_pad($numero, 6, '0', STR_PAD_LEFT);
        } else {
            if ($tipo_documento === 'Nota de Entrega') {
                $datos = self::datosVacios();
            } else {
                $datos = self::datosVacios();
            }
            $modo = 'nuevo';
            $id_factura = 0;
            $numero = 0;
            $num_formateado = '000000';
        }

        require __DIR__ . '/../../public/views/layouts/talonario.php';
        exit();
    }

    private static function datosVacios() {
        return [
            'f_cliente_nombre' => '', 'f_cliente_rif' => '', 'f_cliente_tel' => '', 'f_cliente_domicilio' => '',
            'f_domicilio_fiscal' => '', 'f_condiciones_pago' => '', 'f_col_extra' => '',
            'f_vendedor' => '', 'f_control' => '', 'f_factura' => '', 'f_nota_numero_val' => '',
            'f_fecha_emision' => '', 'f_fecha_venc' => '', 'f_credito' => '',
            'f_lugar' => '', 'f_dia' => '', 'f_mes' => '', 'f_anio' => '',
            'f_fecha_completa' => '', 'f_fecha_escrita' => '',
            'f_mpago' => '', 'f_refnro' => '', 'f_refbco' => '', 'f_igtfb' => '', 'f_igtfp' => '',
            'f_ssub' => '', 'f_otros' => '', 'f_ajustes' => '', 'f_bimp' => '', 'f_ivat' => '', 'f_ivam' => '', 'f_ttl' => '',
            'f_codigo_1' => '', 'f_concepto_1' => '', 'f_cant_1' => '', 'f_unidad_1' => '', 'f_vlr_1' => '', 'f_nvl_1' => '', 'f_ttl_1' => '',
            'f_codigo_2' => '', 'f_concepto_2' => '', 'f_cant_2' => '', 'f_unidad_2' => '', 'f_vlr_2' => '', 'f_nvl_2' => '', 'f_ttl_2' => '',
            'f_codigo_3' => '', 'f_concepto_3' => '', 'f_cant_3' => '', 'f_unidad_3' => '', 'f_vlr_3' => '', 'f_nvl_3' => '', 'f_ttl_3' => '',
            'f_codigo_4' => '', 'f_concepto_4' => '', 'f_cant_4' => '', 'f_unidad_4' => '', 'f_vlr_4' => '', 'f_nvl_4' => '', 'f_ttl_4' => '',
            'f_codigo_5' => '', 'f_concepto_5' => '', 'f_cant_5' => '', 'f_unidad_5' => '', 'f_vlr_5' => '', 'f_nvl_5' => '', 'f_ttl_5' => '',
            'f_codigo_6' => '', 'f_concepto_6' => '', 'f_cant_6' => '', 'f_unidad_6' => '', 'f_vlr_6' => '', 'f_nvl_6' => '', 'f_ttl_6' => '',
            'f_codigo_7' => '', 'f_concepto_7' => '', 'f_cant_7' => '', 'f_unidad_7' => '', 'f_vlr_7' => '', 'f_nvl_7' => '', 'f_ttl_7' => '',
            'f_codigo_8' => '', 'f_concepto_8' => '', 'f_cant_8' => '', 'f_unidad_8' => '', 'f_vlr_8' => '', 'f_nvl_8' => '', 'f_ttl_8' => '',
            'f_sst_nota' => '', 'f_nfn_nota' => '',
        ];
    }

    public static function generarPDFTalonario() {
        Session::redirectIfNotAdmin();
        if (Session::rol() === 'vendedor') {
            header("Location: index.php?action=admin");
            exit();
        }
        Session::start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.0 405 Method Not Allowed');
            exit();
        }

        $datos = $_POST;
        $modo = $datos['_modo'] ?? 'nuevo';
        $id_existente = intval($datos['_id'] ?? 0);
        $id_documento = intval($datos['_id_documento'] ?? 0);
        $numero_recibido = intval($datos['_numero'] ?? 0);
        $tipo_documento = $datos['_tipo_documento'] ?? 'Factura';

        // Guardar/actualizar datos del formulario en documentos
        $id_usuario = $_SESSION['usuario_id'] ?? null;
        $datos_json = json_encode($_POST, JSON_UNESCAPED_UNICODE);

        if ($tipo_documento === 'Nota de Entrega') {
            $nota_val = trim($datos['f_nota_numero_val'] ?? '');
            if ($nota_val !== '') {
                $identificador = preg_replace('/[^A-Za-z0-9\-_\.]/', '_', $nota_val);
                $identificador = substr($identificador, 0, 50);
            } else {
                $identificador = str_pad($numero_recibido, 6, '0', STR_PAD_LEFT);
            }
            $nombre_archivo_db = 'Nota de Entrega #' . $identificador;
        } else {
            $num_formateado = str_pad($numero_recibido, 6, '0', STR_PAD_LEFT);
            $nombre_archivo_db = 'Factura #' . $num_formateado;
        }

        if ($id_documento > 0) {
            Database::query(
                "UPDATE documentos SET nombre_archivo = ?, tipo = ?, datos = ? WHERE id = ?",
                [$nombre_archivo_db, $tipo_documento, $datos_json, $id_documento]
            );
        } else {
            Database::query(
                "INSERT INTO documentos (id_contacto, nombre_archivo, tipo, id_usuario, estado, datos) VALUES (?, ?, ?, ?, 'Activo', ?)",
                [null, $nombre_archivo_db, $tipo_documento, $id_usuario, $datos_json]
            );
            $id_documento = Database::insertId();
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'id_documento' => $id_documento]);
        exit();
    }

    private static function getQueryForVista(string $vista) {
        $queries = [
            'Productos'   => ['sql' => "SELECT p.*, c.nombre as categoria_nombre FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.estado != 'Inactivo' ORDER BY p.id DESC"],
            'Categorias'  => ['sql' => "SELECT * FROM categorias ORDER BY id ASC"],
            'Clientes'    => ['sql' => "SELECT * FROM contactos WHERE tipo='Cliente' AND estado != 'Inactivo' ORDER BY id DESC"],
            'Proveedores' => ['sql' => "SELECT * FROM contactos WHERE tipo='Proveedor' AND estado != 'Inactivo' ORDER BY id DESC"],
            'Pedidos'     => ['sql' => "SELECT p.*, c.nombre as cliente_nombre, (SELECT GROUP_CONCAT(CONCAT('[', COALESCE(pr.codigo, ''), '] ', pr.nombre, ' (', dp.cantidad, ')') SEPARATOR ', ') FROM detalles_pedido dp JOIN productos pr ON dp.id_producto = pr.id WHERE dp.id_pedido = p.id) as items FROM pedidos p LEFT JOIN contactos c ON p.id_contacto = c.id WHERE p.estado != 'Inactivo' ORDER BY p.id DESC"],
            'Perdidas'    => ['sql' => "SELECT p.*, pr.nombre as prod_nombre FROM perdidas p JOIN productos pr ON p.id_producto = pr.id WHERE p.estado != 'Inactivo' ORDER BY p.fecha DESC"],
            'MateriaPrima'=> ['sql' => "SELECT mp.*, c.nombre as proveedor_nombre FROM materia_prima mp LEFT JOIN contactos c ON mp.id_proveedor = c.id WHERE mp.estado != 'Inactivo' ORDER BY mp.nombre ASC"],
            'Personal'    => ['sql' => "SELECT id, nombre, correo, rol FROM usuarios WHERE estado != 'Inactivo' ORDER BY rol DESC, nombre ASC"],
            'Papelera'    => ['sql' => "(SELECT id, nombre, 'Productos' as tipo_orig, 'Sistema' as info_1 FROM productos WHERE estado = 'Inactivo') UNION (SELECT id, nombre, tipo as tipo_orig, correo as info_1 FROM contactos WHERE estado = 'Inactivo') UNION (SELECT id, nombre_archivo as nombre, 'Documentos' as tipo_orig, tipo as info_1 FROM documentos WHERE estado = 'Inactivo') UNION (SELECT id, nombre, 'MateriaPrima' as tipo_orig, 'Sistema' as info_1 FROM materia_prima WHERE estado = 'Inactivo') UNION (SELECT p.id, CONCAT(pr.nombre, ' (', p.cantidad, ' uds)') as nombre, 'Perdidas' as tipo_orig, p.motivo as info_1 FROM perdidas p JOIN productos pr ON p.id_producto = pr.id WHERE p.estado = 'Inactivo') UNION (SELECT id, CONCAT('Pedido #', id) as nombre, 'Pedidos' as tipo_orig, 'Sistema' as info_1 FROM pedidos WHERE estado = 'Inactivo') UNION (SELECT id, nombre, 'Personal' as tipo_orig, rol as info_1 FROM usuarios WHERE estado = 'Inactivo')"],
            'R. Clientes' => ['sql' => "SELECT c.id, c.nombre, c.telefono, c.correo, COUNT(p.id) as total_pedidos, COALESCE(SUM(CASE WHEN p.estado = 'Completado' THEN p.total ELSE 0 END), 0) as total_gastado, MAX(p.fecha) as ultima_compra, COALESCE(AVG(CASE WHEN p.estado = 'Completado' THEN p.total END), 0) as ticket_promedio FROM contactos c LEFT JOIN pedidos p ON c.id = p.id_contacto WHERE c.tipo = 'Cliente' AND c.estado != 'Inactivo' GROUP BY c.id ORDER BY total_gastado DESC"],
            'R. IVA' => ['sql' => "SELECT 
                DATE_FORMAT(p.fecha, '%Y-%m') as mes,
                COUNT(DISTINCT p.id) as num_pedidos,
                COALESCE(SUM(CASE WHEN dp.iva_aplicado > 0 THEN dp.cantidad * dp.precio_unitario ELSE 0 END), 0) as ventas_gravadas,
                COALESCE(SUM(CASE WHEN dp.iva_aplicado = 0 THEN dp.cantidad * dp.precio_unitario ELSE 0 END), 0) as ventas_exentas,
                COALESCE(SUM(CASE WHEN dp.iva_aplicado > 0 THEN ROUND(dp.cantidad * dp.precio_unitario * dp.iva_aplicado / 100, 2) ELSE 0 END), 0) as iva_generado,
                COALESCE(SUM(dp.cantidad * dp.precio_unitario + CASE WHEN dp.iva_aplicado > 0 THEN (dp.cantidad * dp.precio_unitario * dp.iva_aplicado / 100) ELSE 0 END), 0) as total
            FROM pedidos p
            JOIN detalles_pedido dp ON p.id = dp.id_pedido
            JOIN productos pr ON dp.id_producto = pr.id
            WHERE p.estado = 'Completado'
            GROUP BY DATE_FORMAT(p.fecha, '%Y-%m')
            ORDER BY mes DESC"],
            'R. Perdidas' => ['sql' => "SELECT p.id, pr.nombre as prod_nombre, p.cantidad, p.motivo, DATE_FORMAT(p.fecha, '%d/%m/%Y %H:%i') as fecha_fmt FROM perdidas p JOIN productos pr ON p.id_producto = pr.id WHERE p.estado != 'Inactivo' ORDER BY p.fecha DESC"],
            'Bitácora'    => ['sql' => "SELECT b.*, u.nombre as usuario_nombre FROM bitacora b LEFT JOIN usuarios u ON b.id_usuario = u.id ORDER BY b.fecha DESC LIMIT 500"],

        ];
        return $queries[$vista] ?? null;
    }

    private static function logBitacora(string $accion, string $descripcion, ?string $tabla = null, ?int $id_registro = null) {
        $id_usuario = Session::id();
        Database::query(
            "INSERT INTO bitacora (accion, descripcion, id_usuario, tabla, id_registro) VALUES (?, ?, ?, ?, ?)",
            [$accion, $descripcion, $id_usuario, $tabla, $id_registro]
        );
    }

    private static function ajustarStockPedido(int $pedido_id, string $operacion) {
        $items = Database::fetchAll(Database::query(
            "SELECT id_producto, cantidad FROM detalles_pedido WHERE id_pedido = ?", [$pedido_id]
        ));
        foreach ($items as $it) {
            $pid = intval($it['id_producto']);
            $cant = intval($it['cantidad']);
            if ($pid <= 0 || $cant <= 0) continue;
            if ($operacion === 'restar') {
                Database::query("UPDATE productos SET stock = GREATEST(stock - ?, 0) WHERE id = ?", [$cant, $pid]);
            } elseif ($operacion === 'sumar') {
                Database::query("UPDATE productos SET stock = stock + ? WHERE id = ?", [$cant, $pid]);
            }
        }
    }

    private static function procesarAcciones(string $vista, string $rol_usuario) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $accion = $_POST['accion'] ?? '';
        $modulo_post = $_POST['modulo'] ?? ($_POST['modulo_orig'] ?? $vista);

        if ($accion === 'eliminar' && $rol_usuario !== 'vendedor') {
            $id = intval($_POST['id']);
            $tabla = '';
            if (in_array($modulo_post, ['Productos'])) $tabla = 'productos';
            if (in_array($modulo_post, ['Clientes', 'Proveedores'])) $tabla = 'contactos';
            if ($modulo_post === 'MateriaPrima') $tabla = 'materia_prima';
            if ($modulo_post === 'Perdidas') $tabla = 'perdidas';
            if ($modulo_post === 'Personal') $tabla = 'usuarios';
            if ($modulo_post === 'Pedidos') $tabla = 'pedidos';

            if ($tabla) {
                if ($modulo_post === 'Personal' && ($id === Session::id() || $id === 1)) {
                    // Evita bloquearse a sí mismo o al admin principal
                    header("Location: index.php?action=admin&vista=$modulo_post&exito=0&error=" . urlencode('No puedes eliminarte a ti mismo.'));
                    exit();
                }
                
                $nom_res = Database::query("SELECT " . ($tabla === 'pedidos' ? "id as nombre" : "nombre") . " FROM $tabla WHERE id=?", [$id]);
                $nom_fila = Database::fetch($nom_res);
                $nombre_reg = $nom_fila ? ($tabla === 'pedidos' ? "Pedido #" . $nom_fila['nombre'] : $nom_fila['nombre']) : "ID $id";
                
                Database::query("UPDATE $tabla SET estado = 'Inactivo' WHERE id = ?", [$id]);
                self::logBitacora('Mover a Papelera', "Movió a papelera $nombre_reg ($modulo_post)", $tabla, $id);
                header("Location: index.php?action=admin&vista=$modulo_post&exito=1");
                exit();
            }
        }
        
        if ($accion === 'eliminar_permanente' && $rol_usuario !== 'vendedor') {
            $id = intval($_POST['id']);
            $modulo_orig = $_POST['modulo_orig'] ?? '';
            $tabla = match($modulo_orig) {
                'Productos' => 'productos',
                'MateriaPrima' => 'materia_prima',
                'Perdidas' => 'perdidas',
                'Cliente', 'Proveedor' => 'contactos',
                'Personal' => 'usuarios',
                'Pedidos' => 'pedidos',
                default => ''
            };
            if ($tabla) {
                if ($modulo_orig === 'Perdidas') {
                    // Para perdidas, buscar el nombre del producto asociado
                    $nom_res = Database::query("SELECT CONCAT(pr.nombre, ' (', p.cantidad, ' uds)') as nombre FROM perdidas p JOIN productos pr ON p.id_producto = pr.id WHERE p.id=?", [$id]);
                } else if ($modulo_orig === 'Pedidos') {
                    $nom_res = Database::query("SELECT id as nombre FROM pedidos WHERE id=?", [$id]);
                } else {
                    $nom_res = Database::query("SELECT nombre FROM $tabla WHERE id=?", [$id]);
                }
                
                $nom_fila = Database::fetch($nom_res);
                $nombre_reg = $nom_fila ? ($modulo_orig === 'Pedidos' ? "Pedido #" . $nom_fila['nombre'] : ($nom_fila['nombre'] ?? "ID $id")) : "ID $id";
                
                if ($modulo_orig === 'Pedidos') {
                    Database::query("DELETE FROM detalles_pedido WHERE id_pedido = ?", [$id]);
                } else if (in_array($modulo_orig, ['Cliente', 'Proveedor'])) {
                    Database::query("UPDATE pedidos SET id_contacto = NULL WHERE id_contacto = ?", [$id]);
                } else if ($modulo_orig === 'Productos') {
                    Database::query("UPDATE detalles_pedido SET id_producto = NULL WHERE id_producto = ?", [$id]);
                    Database::query("UPDATE perdidas SET id_producto = NULL WHERE id_producto = ?", [$id]);
                }
                
                Database::query("DELETE FROM $tabla WHERE id = ?", [$id]);
                self::logBitacora('Eliminar Permanente', "Eliminó definitivamente $nombre_reg ($modulo_orig)", $tabla, $id);
                header("Location: index.php?action=admin&vista=Papelera&exito=1");
                exit();
            }
        }

        if ($accion === 'guardar_producto' && $rol_usuario !== 'vendedor') {
            $id = intval($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre']);
            $descripcion = trim($_POST['descripcion'] ?? '');
            $stock = intval($_POST['stock'] ?? 0);
            $stock_minimo = intval($_POST['stock_minimo'] ?? 10);
            $cat_id = !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : null;
            $val_p = trim($_POST['val_peso'] ?? '0');
            $uni_p = $_POST['unidad_peso'] ?? 'g';
            $peso = $val_p . ' ' . $uni_p;
            $costo = floatval($_POST['costo'] ?? 0);
            $costo_usd = floatval($_POST['costo_usd'] ?? 0);
            $aplica_iva = intval($_POST['aplica_iva'] ?? 0);
            $codigo = !empty(trim($_POST['codigo'] ?? '')) ? trim($_POST['codigo']) : null;
            $precio_usd = floatval($_POST['precio_usd'] ?? 0);
            // precio (Bs.) y costo (Bs.) se recalculan desde USD × tasa
            $tasa_actual = 0;
            $tasa_res = Database::query("SELECT valor FROM sistema_config WHERE clave = 'tasa_sync_anterior'");
            $tasa_row = Database::fetch($tasa_res);
            if ($tasa_row) $tasa_actual = floatval($tasa_row['valor']);
            if ($tasa_actual <= 0) $tasa_actual = DivisasHelper::obtenerTasas()['usd'];
            $precio_bs = round($precio_usd * $tasa_actual, 2);
            if ($costo_usd > 0) {
                $costo = round($costo_usd * $tasa_actual, 2);
            } elseif ($costo > 0) {
                $costo_usd = round($costo / $tasa_actual, 2);
            }

            $img_ruta = $_POST['img_actual'] ?? 'placeholder.png';
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                $max_size = 5 * 1024 * 1024;
                if ($_FILES['imagen']['size'] > $max_size) {
                    header("Location: index.php?action=admin&vista=$modulo_post&exito=0&error=" . urlencode("La imagen supera el tamaño máximo de 5MB."));
                    exit();
                }
                $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = 'application/octet-stream';
                if ($finfo !== false) {
                    $mime = finfo_file($finfo, $_FILES['imagen']['tmp_name']);
                } else {
                    $mime = mime_content_type($_FILES['imagen']['tmp_name']);
                }
                if (!in_array($mime, $allowed_mimes)) {
                    header("Location: index.php?action=admin&vista=$modulo_post&exito=0&error=" . urlencode("Solo se permiten imágenes JPG, PNG, GIF o WebP."));
                    exit();
                }
                $nombre_img = time() . '_' . uniqid() . '.webp';
                $destino = __DIR__ . '/../../public/uploads/productos/' . $nombre_img;
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
                    $img_ruta = $nombre_img;
                }
            }

            if ($codigo !== null) {
                $dup_count = Database::fetchColumn("SELECT COUNT(*) FROM productos WHERE codigo = ? AND id != ?", [$codigo, $id]);
                if ($dup_count > 0) {
                    header("Location: index.php?action=admin&vista=$modulo_post&exito=0&error=" . urlencode("El código '$codigo' ya está en uso por otro producto."));
                    exit();
                }
            }

            $tipo = $_POST['tipo'] ?? 'Elaboracion';
            $estado_auto = $stock > 0 ? 'Disponible' : 'Agotado';

            if ($id > 0) {
                // Obtener stock anterior para decidir estado
                $old_res = Database::query("SELECT stock FROM productos WHERE id=?", [$id]);
                $old_row = Database::fetch($old_res);
                $old_stock = $old_row ? intval($old_row['stock']) : 0;
                $estado = $_POST['estado'] ?? null;
                if ($estado === null) {
                    $cur_res = Database::query("SELECT estado FROM productos WHERE id=?", [$id]);
                    $cur_row = Database::fetch($cur_res);
                    $estado = $cur_row ? $cur_row['estado'] : 'Disponible';
                }
                // Solo auto-asignar si el stock cruza la frontera de 0
                if (($old_stock <= 0 && $stock > 0) || ($old_stock > 0 && $stock <= 0)) {
                    $estado = $stock > 0 ? 'Disponible' : 'Agotado';
                }
                if ($img_ruta !== $_POST['img_actual'] && $_POST['img_actual'] && $_POST['img_actual'] !== 'placeholder.png') {
                    $old_path = __DIR__ . '/../../public/uploads/productos/' . $_POST['img_actual'];
                    if (file_exists($old_path)) { @unlink($old_path); }
                }
                Database::query(
                    "UPDATE productos SET nombre=?, descripcion=?, codigo=?, precio=?, precio_usd=?, aplica_iva=?, costo=?, costo_usd=?, stock=?, stock_minimo=?, tipo=?, estado=?, categoria_id=?, peso=?, imagen=? WHERE id=?",
                    [$nombre, $descripcion, $codigo, $precio_bs, $precio_usd, $aplica_iva, $costo, $costo_usd, $stock, $stock_minimo, $tipo, $estado, $cat_id, $peso, $img_ruta, $id]
                );
                self::logBitacora('Editar producto', "Editó producto $nombre (ID $id)", 'productos', $id);
            } else {
                Database::query(
                    "INSERT INTO productos (nombre, descripcion, codigo, precio, precio_usd, aplica_iva, costo, costo_usd, stock, stock_minimo, tipo, estado, categoria_id, peso, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$nombre, $descripcion, $codigo, $precio_bs, $precio_usd, $aplica_iva, $costo, $costo_usd, $stock, $stock_minimo, $tipo, $estado_auto, $cat_id, $peso, $img_ruta]
                );
                $nuevo_id = Database::insertId();
                self::logBitacora('Crear producto', "Creó producto $nombre", 'productos', $nuevo_id);
            }
            header("Location: index.php?action=admin&vista=$modulo_post&exito=1");
            exit();
        }

        if ($accion === 'guardar_contacto' && $rol_usuario !== 'vendedor') {
            $id = intval($_POST['id']);
            $nombre = trim($_POST['nombre']);
            $tipo = $_POST['tipo'];
            $tel = $_POST['telefono'];
            $correo = trim($_POST['correo']);
            $direccion = trim($_POST['direccion']);
            $categoria = ($tipo === 'Cliente') ? ($_POST['categoria'] ?? 'Regular') : null;
            if ($id > 0) {
                Database::query("UPDATE contactos SET nombre=?, tipo=?, categoria=?, telefono=?, correo=?, direccion=? WHERE id=?", [$nombre, $tipo, $categoria, $tel, $correo, $direccion, $id]);
                self::logBitacora('Editar contacto', "Editó $tipo $nombre (ID $id)", 'contactos', $id);
            } else {
                Database::query("INSERT INTO contactos (nombre, tipo, categoria, telefono, correo, direccion) VALUES (?, ?, ?, ?, ?, ?)", [$nombre, $tipo, $categoria, $tel, $correo, $direccion]);
                $nuevo_id = Database::insertId();
                self::logBitacora('Crear contacto', "Creó $tipo $nombre", 'contactos', $nuevo_id);
            }
            header("Location: index.php?action=admin&vista=$modulo_post&exito=1");
            exit();
        }

        if ($accion === 'guardar_materiaprima') {
            $id = intval($_POST['id']);
            $nombre = trim($_POST['nombre']);
            $cantidad = floatval($_POST['cantidad']);
            $unidad = $_POST['unidad'];
            $minimo = floatval($_POST['minimo'] ?? 5);
            $id_proveedor = !empty($_POST['id_proveedor']) ? intval($_POST['id_proveedor']) : null;
            if ($id > 0) {
                Database::query("UPDATE materia_prima SET nombre=?, cantidad=?, unidad=?, minimo=?, id_proveedor=? WHERE id=?", [$nombre, $cantidad, $unidad, $minimo, $id_proveedor, $id]);
                self::logBitacora('Editar insumo', "Editó insumo $nombre (ID $id)", 'materia_prima', $id);
            } else {
                Database::query("INSERT INTO materia_prima (nombre, cantidad, unidad, minimo, id_proveedor) VALUES (?, ?, ?, ?, ?)", [$nombre, $cantidad, $unidad, $minimo, $id_proveedor]);
                $nuevo_id = Database::insertId();
                self::logBitacora('Crear insumo', "Creó insumo $nombre", 'materia_prima', $nuevo_id);
            }
            header("Location: index.php?action=admin&vista=MateriaPrima&exito=1");
            exit();
        }

        if ($accion === 'guardar_usuario' && $rol_usuario === 'admin') {
            $id = intval($_POST['id']);
            $nombre = trim($_POST['nombre']);
            $correo = trim($_POST['correo']);
            $rol = $_POST['rol'];
            $clave = $_POST['clave'] ?? '';
            if ($id > 0) {
                if ($clave) {
                    $clave_hash = password_hash($clave, PASSWORD_DEFAULT);
                    Database::query("UPDATE usuarios SET nombre=?, correo=?, contrasena=?, rol=? WHERE id=?", [$nombre, $correo, $clave_hash, $rol, $id]);
                } else {
                    Database::query("UPDATE usuarios SET nombre=?, correo=?, rol=? WHERE id=?", [$nombre, $correo, $rol, $id]);
                }
                self::logBitacora('Editar usuario', "Editó usuario $nombre (ID $id)", 'usuarios', $id);
            } elseif ($nombre && $correo && $clave) {
                $clave_hash = password_hash($clave, PASSWORD_DEFAULT);
                Database::query(
                    "INSERT INTO usuarios (nombre, correo, contrasena, rol) VALUES (?, ?, ?, ?)",
                    [$nombre, $correo, $clave_hash, $rol]
                );
                $nuevo_id = Database::insertId();
                self::logBitacora('Crear usuario', "Creó usuario $nombre (rol: $rol)", 'usuarios', $nuevo_id);
            }
            header("Location: index.php?action=admin&vista=Personal&exito=1");
            exit();
        }

        if ($accion === 'guardar_pedido') {
            $id = intval($_POST['id']);
            $id_cont = intval($_POST['id_contacto']);
            $estado = $_POST['estado'];
            $tipo_entrega = $_POST['tipo_entrega'] ?? 'Local';
            $direccion = $_POST['direccion'] ?? null;
            if ($direccion === '') $direccion = null;
            $id_cont_val = ($id_cont > 0) ? $id_cont : null;
            $user_id = Session::id();
            $items_json = $_POST['items_json'] ?? '[]';
            $items = json_decode($items_json, true) ?: [];
            // Determinar estado anterior si es edición
            $ant_estado = null;
            $old_items = [];
            if ($id > 0) {
                $anterior_res = Database::query("SELECT estado FROM pedidos WHERE id=?", [$id]);
                $anterior = Database::getResult($anterior_res);
                $ant_estado = ($anterior && $f = mysqli_fetch_assoc($anterior)) ? $f['estado'] : null;
                $old_res = Database::query("SELECT id_producto, cantidad FROM detalles_pedido WHERE id_pedido = ?", [$id]);
                $old_items = Database::fetchAll($old_res);
            }
            // Validar stock disponible (solo si no es edición de pedido ya Completado)
            if ($id === 0 || $ant_estado !== 'Completado') {
                foreach ($items as $it) {
                    $pid = intval($it['id_producto'] ?? 0);
                    $cant = intval($it['cantidad'] ?? 0);
                    if ($pid <= 0 || $cant <= 0) continue;
                    $stock_res = Database::query("SELECT stock FROM productos WHERE id = ?", [$pid]);
                    $stock_row = Database::fetch($stock_res);
                    $stock_actual = $stock_row ? intval($stock_row['stock']) : 0;
                    if ($cant > $stock_actual) {
                        header("Location: index.php?action=admin&vista=$modulo_post&exito=0&error=" . urlencode("Stock insuficiente para uno de los productos. Disponible: $stock_actual, solicitado: $cant."));
                        exit();
                    }
                }
            }
            // Calcular total desde los items (precio_unitario es base, IVA se suma)
            $total = 0;
            foreach ($items as $it) {
                $subBase = floatval($it['cantidad'] ?? 0) * floatval($it['precio_unitario'] ?? 0);
                $ivaRate = floatval($it['iva'] ?? 0);
                $total += $ivaRate > 0 ? round($subBase * (1 + $ivaRate / 100), 2) : $subBase;
            }
            if ($id > 0) {
                Database::query("UPDATE pedidos SET total=?, id_contacto=?, estado=?, tipo_entrega=?, direccion=? WHERE id=?", [$total, $id_cont_val, $estado, $tipo_entrega, $direccion, $id]);
                // Reemplazar detalles
                Database::query("DELETE FROM detalles_pedido WHERE id_pedido = ?", [$id]);
                foreach ($items as $it) {
                    $id_prod = intval($it['id_producto'] ?? 0);
                    $cantidad = intval($it['cantidad'] ?? 0);
                    $precio = floatval($it['precio_unitario'] ?? 0);
                    $iva_it = floatval($it['iva'] ?? 0);
                    if ($id_prod > 0 && $cantidad > 0) {
                        Database::query("INSERT INTO detalles_pedido (id_pedido, id_producto, cantidad, precio_unitario, iva_aplicado) VALUES (?, ?, ?, ?, ?)", [$id, $id_prod, $cantidad, $precio, $iva_it]);
                    }
                }
                // Ajustar stock según cambio de estado
                if ($ant_estado !== 'Completado' && $estado === 'Completado') {
                    self::ajustarStockPedido($id, 'restar');
                } elseif ($ant_estado === 'Completado' && $estado !== 'Completado') {
                    // Restaurar stock usando los items viejos
                    foreach ($old_items as $oit) {
                        $pid = intval($oit['id_producto']);
                        $cant = intval($oit['cantidad']);
                        if ($pid > 0 && $cant > 0) {
                            Database::query("UPDATE productos SET stock = stock + ? WHERE id = ?", [$cant, $pid]);
                        }
                    }
                } elseif ($ant_estado === 'Completado' && $estado === 'Completado') {
                    // Delta: comparar items viejos vs nuevos para ajustar stock
                    $old_map = [];
                    foreach ($old_items as $oit) {
                        $pid = intval($oit['id_producto']);
                        $old_map[$pid] = ($old_map[$pid] ?? 0) + intval($oit['cantidad']);
                    }
                    $new_map = [];
                    foreach ($items as $it) {
                        $pid = intval($it['id_producto'] ?? 0);
                        $qty = intval($it['cantidad'] ?? 0);
                        if ($pid > 0 && $qty > 0) {
                            $new_map[$pid] = ($new_map[$pid] ?? 0) + $qty;
                        }
                    }
                    $all_pids = array_unique(array_merge(array_keys($old_map), array_keys($new_map)));
                    foreach ($all_pids as $pid) {
                        $old_qty = $old_map[$pid] ?? 0;
                        $new_qty = $new_map[$pid] ?? 0;
                        $delta = $new_qty - $old_qty;
                        if ($delta > 0) {
                            Database::query("UPDATE productos SET stock = stock - ? WHERE id = ?", [$delta, $pid]);
                        } elseif ($delta < 0) {
                            Database::query("UPDATE productos SET stock = stock + ? WHERE id = ?", [-$delta, $pid]);
                        }
                    }
                }
                self::logBitacora('Editar pedido', "Editó pedido #$id (total: Bs.$total, estado: $estado, entrega: $tipo_entrega)", 'pedidos', $id);
            } else {
                Database::query("INSERT INTO pedidos (total, id_contacto, estado, tipo_entrega, direccion) VALUES (?, ?, ?, ?, ?)", [$total, $id_cont_val, $estado, $tipo_entrega, $direccion]);
                $nuevo_id = Database::insertId();
                foreach ($items as $it) {
                    $id_prod = intval($it['id_producto'] ?? 0);
                    $cantidad = intval($it['cantidad'] ?? 0);
                    $precio = floatval($it['precio_unitario'] ?? 0);
                    $iva_it = floatval($it['iva'] ?? 0);
                    if ($id_prod > 0 && $cantidad > 0) {
                        Database::query("INSERT INTO detalles_pedido (id_pedido, id_producto, cantidad, precio_unitario, iva_aplicado) VALUES (?, ?, ?, ?, ?)", [$nuevo_id, $id_prod, $cantidad, $precio, $iva_it]);
                    }
                }
                if ($estado === 'Completado') {
                    self::ajustarStockPedido($nuevo_id, 'restar');
                }
                self::logBitacora('Crear pedido', "Creó pedido #$nuevo_id (total: Bs.$total, estado: $estado, entrega: $tipo_entrega)", 'pedidos', $nuevo_id);
            }
            header("Location: index.php?action=admin&vista=Pedidos&exito=1");
            exit();
        }

        if ($accion === 'cambiar_estado') {
            $id = intval($_POST['id']);
            $nuevo = $_POST['nuevo_estado'];
            $v = $_POST['vista_actual'];
            $anterior_res = Database::query("SELECT estado FROM pedidos WHERE id=?", [$id]);
            $anterior = Database::getResult($anterior_res);
            $ant_estado = ($anterior && $f = mysqli_fetch_assoc($anterior)) ? $f['estado'] : null;
            $user_id = Session::id();
            Database::query("UPDATE pedidos SET estado=? WHERE id=?", [$nuevo, $id]);
            if ($ant_estado && $ant_estado !== $nuevo) {
                if ($ant_estado === 'Completado' && $nuevo !== 'Completado') {
                    self::ajustarStockPedido($id, 'sumar');
                } elseif ($ant_estado !== 'Completado' && $nuevo === 'Completado') {
                    self::ajustarStockPedido($id, 'restar');
                }
            }
            self::logBitacora('Cambiar estado', "Cambió pedido #$id: $ant_estado → $nuevo", 'pedidos', $id);
            header("Location: index.php?action=admin&vista=$v&exito=1");
            exit();
        }

        if ($accion === 'toggle_estado_producto' && $rol_usuario !== 'vendedor') {
            $id = intval($_POST['id']);
            $actual = $_POST['estado_actual'];
            $nuevo = ($actual === 'Disponible') ? 'Agotado' : 'Disponible';
            Database::query("UPDATE productos SET estado=? WHERE id=?", [$nuevo, $id]);
            $nom_res = Database::query("SELECT nombre FROM productos WHERE id=?", [$id]);
            $nom_fila = Database::fetch($nom_res);
            $nombre_prod = $nom_fila ? $nom_fila['nombre'] : "ID $id";
            self::logBitacora('Toggle producto', "Cambió $nombre_prod: $actual → $nuevo", 'productos', $id);
            header("Location: index.php?action=admin&vista=Productos&exito=1");
            exit();
        }

        if ($accion === 'guardar_categoria' && $rol_usuario === 'admin') {
            $id = intval($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre']);
            $descripcion = trim($_POST['descripcion'] ?? '');
            $orden = intval($_POST['orden'] ?? 0);
            if ($id > 0) {
                Categoria::update($id, ['nombre' => $nombre, 'descripcion' => $descripcion, 'orden' => $orden]);
                self::logBitacora('Editar categoría', "Editó categoría $nombre (ID $id)", 'categorias', $id);
            } else {
                Categoria::create(['nombre' => $nombre, 'descripcion' => $descripcion, 'orden' => $orden]);
                self::logBitacora('Crear categoría', "Creó categoría $nombre", 'categorias');
            }
            header("Location: index.php?action=admin&vista=Categorias&exito=1");
            exit();
        }

        if ($accion === 'eliminar_categoria' && $rol_usuario === 'admin') {
            $id = intval($_POST['id']);
            $nom_res = Database::query("SELECT nombre FROM categorias WHERE id=?", [$id]);
            $nom_fila = Database::fetch($nom_res);
            $nombre_cat = $nom_fila ? $nom_fila['nombre'] : "ID $id";
            Categoria::delete($id);
            self::logBitacora('Eliminar categoría', "Eliminó categoría $nombre_cat (ID $id)", 'categorias', $id);
            header("Location: index.php?action=admin&vista=Categorias&exito=1");
            exit();
        }

        if ($accion === 'activar' && $rol_usuario !== 'vendedor') {
            $id = intval($_POST['id']);
            $modulo_orig = $_POST['modulo_orig'];
            if ($modulo_orig === 'Productos') {
                $tabla = 'productos';
                $estado_restaurar = 'Disponible';
                $columna_nombre = 'nombre';
            } elseif ($modulo_orig === 'Documentos') {
                $tabla = 'documentos';
                $columna_nombre = 'nombre_archivo';
                $estado_restaurar = 'Activo';
            } elseif ($modulo_orig === 'MateriaPrima') {
                $tabla = 'materia_prima';
                $columna_nombre = 'nombre';
                $estado_restaurar = 'Disponible';
            } elseif ($modulo_orig === 'Perdidas') {
                $tabla = 'perdidas';
                $columna_nombre = 'motivo'; // usamos motivo como referencia visual
                $estado_restaurar = 'Disponible';
            } elseif ($modulo_orig === 'Personal') {
                $tabla = 'usuarios';
                $columna_nombre = 'nombre';
                $estado_restaurar = 'Activo';
            } elseif ($modulo_orig === 'Pedidos') {
                $tabla = 'pedidos';
                $columna_nombre = 'id';
                $estado_restaurar = 'Completado';
            } else {
                $tabla = 'contactos';
                $columna_nombre = 'nombre';
                $estado_restaurar = 'Activo';
            }

            if (!isset($columna_nombre)) $columna_nombre = 'nombre';
            $nom_res = Database::query("SELECT $columna_nombre FROM $tabla WHERE id=?", [$id]);
            $nom_fila = Database::fetch($nom_res);
            $nombre_reg = $nom_fila ? ($modulo_orig === 'Pedidos' ? "Pedido #" . $nom_fila[$columna_nombre] : $nom_fila[$columna_nombre]) : "ID $id";
            Database::query("UPDATE $tabla SET estado = ? WHERE id = ?", [$estado_restaurar, $id]);
            self::logBitacora('Activar', "Habilitó $nombre_reg desde Papelera", $tabla, $id);
            header("Location: index.php?action=admin&vista=Papelera&exito=1");
            exit();
        }

        if ($accion === 'guardar_perdida' && $rol_usuario !== 'vendedor') {
            $id_producto = intval($_POST['id_producto']);
            $cantidad = intval($_POST['cantidad']);
            $motivo = trim($_POST['motivo']);
            $fecha = $_POST['fecha'] ?? null;
            if ($fecha === '') $fecha = null;
            if ($id_producto > 0 && $cantidad > 0 && $motivo) {
                if ($fecha) {
                    Database::query(
                        "INSERT INTO perdidas (id_producto, cantidad, motivo, fecha) VALUES (?, ?, ?, ?)",
                        [$id_producto, $cantidad, $motivo, $fecha . ' 12:00:00']
                    );
                } else {
                    Database::query(
                        "INSERT INTO perdidas (id_producto, cantidad, motivo) VALUES (?, ?, ?)",
                        [$id_producto, $cantidad, $motivo]
                    );
                }
                $nuevo_id = Database::insertId();
                Database::query(
                    "UPDATE productos SET stock = GREATEST(stock - ?, 0) WHERE id = ?",
                    [$cantidad, $id_producto]
                );
                $nom_res = Database::query("SELECT nombre FROM productos WHERE id=?", [$id_producto]);
                $nom_fila = Database::fetch($nom_res);
                $nombre_prod = $nom_fila ? $nom_fila['nombre'] : "ID $id_producto";
                self::logBitacora('Registrar pérdida', "Registró pérdida: $cantidad uds de $nombre_prod ($motivo)", 'perdidas', $nuevo_id);
            }
            header("Location: index.php?action=admin&vista=Perdidas&exito=1");
            exit();
        }

        if ($accion === 'guardar_config' && $rol_usuario !== 'vendedor') {
            $nueva_tasa = floatval($_POST['tasa_iva'] ?? 0);
            if ($nueva_tasa < 0) $nueva_tasa = 0;
            if ($nueva_tasa > 100) $nueva_tasa = 100;
            
            // Insertar o actualizar
            Database::query("INSERT INTO sistema_config (clave, valor) VALUES ('tasa_iva', ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)", [(string)$nueva_tasa]);
            
            $campos_redes = ['social_whatsapp', 'social_instagram', 'social_facebook', 'social_tiktok'];
            foreach($campos_redes as $c) {
                $v = $_POST[$c] ?? '';
                Database::query("INSERT INTO sistema_config (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)", [$c, $v]);
            }

            self::logBitacora('Configurar', "Actualizó la configuración general", 'sistema_config');
            header("Location: index.php?action=admin&vista=Configuracion&exito=1");
            exit();
        }

        if ($accion === 'eliminar_perdida' && $rol_usuario !== 'vendedor') {
            $id = intval($_POST['id']);
            $nom_res = Database::query("SELECT CONCAT(pr.nombre, ' (', p.cantidad, ' uds)') as nombre FROM perdidas p JOIN productos pr ON p.id_producto = pr.id WHERE p.id=?", [$id]);
            $nom_fila = Database::fetch($nom_res);
            $nombre_ref = $nom_fila ? $nom_fila['nombre'] : "ID $id";
            Database::query("UPDATE perdidas SET estado = 'Inactivo' WHERE id = ?", [$id]);
            self::logBitacora('Mover a Papelera', "Movió a Papelera pérdida: $nombre_ref", 'perdidas', $id);
            header("Location: index.php?action=admin&vista=Perdidas&exito=1");
            exit();
        }

        if ($accion === 'guardar_documento' && $rol_usuario !== 'vendedor') {
            $id_contacto = !empty($_POST['id_contacto']) ? intval($_POST['id_contacto']) : null;
            $nombre_archivo = trim($_POST['nombre_archivo']);
            $tipo = $_POST['tipo'] ?? 'Otro';
            $id_usuario = Session::id();
            $archivo_real = '';

            if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
                $max_size = 10 * 1024 * 1024;
                if ($_FILES['archivo']['size'] > $max_size) {
                    header("Location: index.php?action=admin&vista=Documentos&exito=0&error=" . urlencode("El archivo supera el tamaño máximo de 10MB."));
                    exit();
                }
                $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['xlsx', 'xls', 'pdf', 'png', 'jpg', 'jpeg'])) {
                    header("Location: index.php?action=admin&vista=Documentos&exito=0&error=" . urlencode("Solo se permiten archivos Excel (.xlsx, .xls), PDF o imágenes."));
                    exit();
                }
                $archivo_real = time() . '_' . uniqid() . '.' . $ext;
                $destino = __DIR__ . '/../../public/uploads/documentos/' . $archivo_real;
                if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $destino)) {
                    header("Location: index.php?action=admin&vista=Documentos&exito=0&error=" . urlencode("Error al subir el archivo."));
                    exit();
                }
            } else {
                header("Location: index.php?action=admin&vista=Documentos&exito=0&error=" . urlencode("Debes seleccionar un archivo."));
                exit();
            }

            $data = [
                'id_contacto' => $id_contacto,
                'nombre_archivo' => $nombre_archivo,
                'archivo_real' => $archivo_real,
                'tipo' => $tipo,
                'id_usuario' => $id_usuario,
            ];
            $nuevo_id = Documento::create($data);
            self::logBitacora('Subir documento', "Subió documento $nombre_archivo ($tipo)", 'documentos', $nuevo_id);
            header("Location: index.php?action=admin&vista=Documentos&exito=1");
            exit();
        }

        if ($accion === 'eliminar_documento' && $rol_usuario !== 'vendedor') {
            $id = intval($_POST['id']);
            $doc = Documento::getById($id);
            $nombre_doc = $doc ? $doc['nombre_archivo'] : "ID $id";
            Documento::delete($id);
            self::logBitacora('Eliminar documento', "Envió documento $nombre_doc a la papelera", 'documentos', $id);
            header("Location: index.php?action=admin&vista=Documentos&exito=1");
            exit();
        }

        if ($accion === 'guardar_perfil') {
            $id = Session::id();
            if ($id) {
                $nombre = trim($_POST['nombre']);
                $correo = trim($_POST['correo']);
                $clave = $_POST['clave'] ?? '';
                if ($clave) {
                    $clave_hash = password_hash($clave, PASSWORD_DEFAULT);
                    Database::query("UPDATE usuarios SET nombre=?, correo=?, contrasena=? WHERE id=?", [$nombre, $correo, $clave_hash, $id]);
                } else {
                    Database::query("UPDATE usuarios SET nombre=?, correo=? WHERE id=?", [$nombre, $correo, $id]);
                }
                Session::set('nombre', $nombre);
                Session::set('correo', $correo);
                self::logBitacora('Editar perfil', "Editó su propio perfil (ID $id)", 'usuarios', $id);
            }
            header("Location: index.php?action=admin&exito=1");
            exit();
        }

        if ($accion === 'eliminar_permanente_documento' && $rol_usuario === 'admin') {
            $id = intval($_POST['id']);
            $doc = Documento::getById($id);
            $nombre_doc = $doc ? $doc['nombre_archivo'] : "ID $id";
            Documento::eliminarPermanente($id);
            self::logBitacora('Eliminar permanente', "Eliminó definitivamente documento $nombre_doc", 'documentos', $id);
            header("Location: index.php?action=admin&vista=Papelera&exito=1");
            exit();
        }

        if ($accion === 'vaciar_papelera' && $rol_usuario === 'admin') {
            // Eliminar documentos permanentemente (incluyendo archivos del disco)
            $docs_res = Database::query("SELECT id FROM documentos WHERE estado = 'Inactivo'");
            $docs_data = Database::getResult($docs_res);
            while ($doc_row = mysqli_fetch_assoc($docs_data)) {
                Documento::eliminarPermanente($doc_row['id']);
            }
            // Liberar claves foráneas antes de eliminar en bloque
            Database::query("UPDATE pedidos SET id_contacto = NULL WHERE id_contacto IN (SELECT id FROM contactos WHERE estado = 'Inactivo')");
            Database::query("UPDATE detalles_pedido SET id_producto = NULL WHERE id_producto IN (SELECT id FROM productos WHERE estado = 'Inactivo')");
            Database::query("UPDATE perdidas SET id_producto = NULL WHERE id_producto IN (SELECT id FROM productos WHERE estado = 'Inactivo')");
            
            // Eliminar dependencias de pedidos inactivos
            $peds_res = Database::query("SELECT id FROM pedidos WHERE estado = 'Inactivo'");
            $peds_data = Database::getResult($peds_res);
            while ($ped_row = mysqli_fetch_assoc($peds_data)) {
                Database::query("DELETE FROM detalles_pedido WHERE id_pedido = ?", [$ped_row['id']]);
            }

            // Eliminar el resto de tablas directamente
            Database::query("DELETE FROM productos WHERE estado = 'Inactivo'");
            Database::query("DELETE FROM contactos WHERE estado = 'Inactivo'");
            Database::query("DELETE FROM materia_prima WHERE estado = 'Inactivo'");
            Database::query("DELETE FROM perdidas WHERE estado = 'Inactivo'");
            Database::query("DELETE FROM pedidos WHERE estado = 'Inactivo'");
            Database::query("DELETE FROM usuarios WHERE estado = 'Inactivo'");

            self::logBitacora('Vaciar Papelera', 'Vació completamente la papelera del sistema', 'sistema');
            header("Location: index.php?action=admin&vista=Papelera&exito=1");
            exit();
        }

    }

    public static function descargarPDF() {
        Session::redirectIfNotAdmin();
        if (Session::rol() === 'vendedor') {
            header("Location: index.php?action=admin");
            exit();
        }
        $tipo = $_GET['tipo'] ?? '';

        require_once __DIR__ . '/../../vendor/autoload.php';

        $sql_clientes = "SELECT c.nombre, c.telefono, c.correo, COUNT(p.id) as total_pedidos, COALESCE(SUM(CASE WHEN p.estado = 'Completado' THEN p.total ELSE 0 END), 0) as total_gastado, MAX(p.fecha) as ultima_compra, COALESCE(AVG(CASE WHEN p.estado = 'Completado' THEN p.total END), 0) as ticket_promedio FROM contactos c LEFT JOIN pedidos p ON c.id = p.id_contacto WHERE c.tipo = 'Cliente' AND c.estado != 'Inactivo' GROUP BY c.id ORDER BY total_gastado DESC";
        $sql_bitacora = "SELECT b.fecha, b.accion, b.descripcion, COALESCE(u.nombre, 'Sistema') as usuario_nombre FROM bitacora b LEFT JOIN usuarios u ON b.id_usuario = u.id ORDER BY b.fecha DESC LIMIT 500";
        $sql_iva = "SELECT DATE_FORMAT(p.fecha, '%Y-%m') as mes, COUNT(DISTINCT p.id) as num_pedidos, COALESCE(SUM(CASE WHEN dp.iva_aplicado > 0 THEN dp.cantidad * dp.precio_unitario ELSE 0 END), 0) as ventas_gravadas, COALESCE(SUM(CASE WHEN dp.iva_aplicado = 0 THEN dp.cantidad * dp.precio_unitario ELSE 0 END), 0) as ventas_exentas, COALESCE(SUM(CASE WHEN dp.iva_aplicado > 0 THEN ROUND(dp.cantidad * dp.precio_unitario * dp.iva_aplicado / 100, 2) ELSE 0 END), 0) as iva_generado, COALESCE(SUM(dp.cantidad * dp.precio_unitario + CASE WHEN dp.iva_aplicado > 0 THEN (dp.cantidad * dp.precio_unitario * dp.iva_aplicado / 100) ELSE 0 END), 0) as total FROM pedidos p JOIN detalles_pedido dp ON p.id = dp.id_pedido JOIN productos pr ON dp.id_producto = pr.id WHERE p.estado = 'Completado' GROUP BY DATE_FORMAT(p.fecha, '%Y-%m') ORDER BY mes DESC";

        $sql_perdidas = "SELECT pr.nombre as prod_nombre, p.cantidad, p.motivo, DATE_FORMAT(p.fecha, '%d/%m/%Y %H:%i') as fecha_fmt FROM perdidas p JOIN productos pr ON p.id_producto = pr.id WHERE p.estado != 'Inactivo' ORDER BY p.fecha DESC";

        $queries = [
            'R. Clientes' => $sql_clientes,
            'Bitácora' => $sql_bitacora,
            'R. IVA' => $sql_iva,
            'R. Perdidas' => $sql_perdidas,
        ];

        $style = '<style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
            h1 { text-align: center; font-size: 18px; margin-bottom: 5px; color: #dc3545; }
            h2 { font-size: 14px; color: #333; margin-top: 25px; margin-bottom: 8px; border-bottom: 2px solid #dc3545; padding-bottom: 3px; }
            p.fecha { text-align: center; font-size: 10px; color: #666; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th { background: #dc3545; color: white; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
            td { padding: 5px 8px; border-bottom: 1px solid #ddd; font-size: 9px; }
            tr:nth-child(even) td { background: #f9f9f9; }
            .total { font-weight: bold; margin-top: 10px; font-size: 11px; }
            .page-break { page-break-before: always; }
        </style>';

        if ($tipo === 'todos') {
            $html = '<html><head><meta charset="UTF-8">' . $style . '</head><body>';
            $html .= '<h1>Reporte Unificado</h1>';
            $html .= '<p class="fecha">Generado: ' . date('d/m/Y H:i') . '</p>';

            $todos = [
                'R. Clientes' => ['sql' => $sql_clientes, 'titulo' => 'Reporte de Clientes', 'headers' => ['Cliente', 'Teléfono', 'Correo', 'Pedidos', 'Total Gastado', 'Ticket Prom.', 'Última Compra'], 'campos' => ['nombre', 'telefono', 'correo', 'total_pedidos', 'total_gastado_f', 'ticket_prom_f', 'ultima_compra_f']],
                'R. IVA' => ['sql' => $sql_iva, 'titulo' => 'Reporte de IVA', 'headers' => ['Período', 'Pedidos', 'Ventas Gravadas', 'Ventas Exentas', 'IVA Generado', 'Total Facturado'], 'campos' => ['mes', 'num_pedidos', 'ventas_g_f', 'ventas_e_f', 'iva_f', 'total_f']],
                'Bitácora' => ['sql' => $sql_bitacora, 'titulo' => 'Bitácora del Sistema', 'headers' => ['Fecha', 'Acción', 'Descripción', 'Usuario'], 'campos' => ['fecha_f', 'accion', 'descripcion', 'usuario_nombre']],
            ];

            $idx = 0;
            foreach ($todos as $clave => $conf) {
                if ($idx > 0) $html .= '<div class="page-break"></div>';
                $res = Database::query($conf['sql']);
                $rows = Database::fetchAll($res);
                $html .= '<h2>' . $conf['titulo'] . '</h2>';
                $html .= '<table><thead><tr>';
                foreach ($conf['headers'] as $h) $html .= "<th>$h</th>";
                $html .= '</tr></thead><tbody>';
                foreach ($rows as $r) {
                    $r['total_gastado_f'] = 'Bs. ' . number_format($r['total_gastado'] ?? 0, 2);
                    $r['ticket_prom_f'] = 'Bs. ' . number_format($r['ticket_promedio'] ?? 0, 2);
                    $r['ultima_compra_f'] = isset($r['ultima_compra']) ? date('d/m/Y', strtotime($r['ultima_compra'])) : 'N/A';
                    $r['ultima_accion_f'] = isset($r['ultima_accion']) ? date('d/m/Y H:i', strtotime($r['ultima_accion'])) : 'N/A';
                    $r['fecha_f'] = isset($r['fecha']) ? date('d/m/Y H:i', strtotime($r['fecha'])) : 'N/A';
                    $r['ventas_g_f'] = 'Bs. ' . number_format($r['ventas_gravadas'] ?? 0, 2);
                    $r['ventas_e_f'] = 'Bs. ' . number_format($r['ventas_exentas'] ?? 0, 2);
                    $r['iva_f'] = 'Bs. ' . number_format($r['iva_generado'] ?? 0, 2);
                    $r['total_f'] = 'Bs. ' . number_format($r['total'] ?? 0, 2);
                    $html .= '<tr>';
                    foreach ($conf['campos'] as $c) {
                        $val = htmlspecialchars($r[$c] ?? '');
                        $html .= "<td>$val</td>";
                    }
                    $html .= '</tr>';
                }
                $html .= '</tbody></table>';
                $html .= '<p class="total">Total: ' . count($rows) . ' registros</p>';
                $idx++;
            }

            $html .= '</body></html>';

        $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream("Reporte Unificado.pdf", ['Attachment' => false]);
            exit();
        }

        $sql = $queries[$tipo] ?? null;
        if (!$sql) {
            header('Content-Type: text/plain');
            echo 'Tipo de reporte no válido.';
            exit();
        }

        $res = Database::query($sql);
        $rows = Database::fetchAll($res);

        $titulo = match ($tipo) {
            'R. Clientes' => 'Reporte de Clientes',
            'Bitácora' => 'Bitácora del Sistema',
            'R. IVA' => 'Reporte de IVA',
            'R. Perdidas' => 'Reporte de Perdidas',
            default => 'Reporte'
        };

        $html = '<html><head><meta charset="UTF-8">' . $style . '</head><body>';
        $html .= "<h1>$titulo</h1>";
        $html .= '<p class="fecha">Generado: ' . date('d/m/Y H:i') . '</p>';
        $html .= '<table><thead><tr>';

        switch ($tipo) {
            case 'R. Clientes':
                $html .= '<th>Cliente</th><th>Teléfono</th><th>Correo</th><th>Pedidos</th><th>Total Gastado</th><th>Ticket Prom.</th><th>Última Compra</th>';
                $html .= '</tr></thead><tbody>';
                foreach ($rows as $r) {
                    $html .= '<tr>';
                    $html .= '<td>' . htmlspecialchars($r['nombre']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($r['telefono'] ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($r['correo'] ?? '') . '</td>';
                    $html .= '<td>' . $r['total_pedidos'] . '</td>';
                    $html .= '<td>Bs. ' . number_format($r['total_gastado'], 2) . '</td>';
                    $html .= '<td>Bs. ' . number_format($r['ticket_promedio'], 2) . '</td>';
                    $html .= '<td>' . ($r['ultima_compra'] ? date('d/m/Y', strtotime($r['ultima_compra'])) : 'N/A') . '</td>';
                    $html .= '</tr>';
                }
                break;

            case 'Bitácora':
                $html .= '<th>Fecha</th><th>Acción</th><th>Descripción</th><th>Usuario</th>';
                $html .= '</tr></thead><tbody>';
                foreach ($rows as $r) {
                    $html .= '<tr>';
                    $html .= '<td>' . date('d/m/Y H:i', strtotime($r['fecha'])) . '</td>';
                    $html .= '<td>' . htmlspecialchars($r['accion']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($r['descripcion'] ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($r['usuario_nombre']) . '</td>';
                    $html .= '</tr>';
                }
                break;

            case 'R. IVA':
                $html .= '<th>Período</th><th>Pedidos</th><th>Ventas Gravadas</th><th>Ventas Exentas</th><th>IVA Generado</th><th>Total Facturado</th>';
                $html .= '</tr></thead><tbody>';
                foreach ($rows as $r) {
                    $html .= '<tr>';
                    $html .= '<td>' . htmlspecialchars($r['mes']) . '</td>';
                    $html .= '<td>' . $r['num_pedidos'] . '</td>';
                    $html .= '<td>Bs. ' . number_format($r['ventas_gravadas'], 2) . '</td>';
                    $html .= '<td>Bs. ' . number_format($r['ventas_exentas'], 2) . '</td>';
                    $html .= '<td>Bs. ' . number_format($r['iva_generado'], 2) . '</td>';
                    $html .= '<td>Bs. ' . number_format($r['total'], 2) . '</td>';
                    $html .= '</tr>';
                }
                break;

            case 'R. Perdidas':
                $html .= '<th>Producto</th><th>Cantidad</th><th>Motivo</th><th>Fecha</th>';
                $html .= '</tr></thead><tbody>';
                foreach ($rows as $r) {
                    $html .= '<tr>';
                    $html .= '<td>' . htmlspecialchars($r['prod_nombre']) . '</td>';
                    $html .= '<td style="text-align:center;">' . intval($r['cantidad']) . ' uds</td>';
                    $html .= '<td>' . htmlspecialchars($r['motivo']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($r['fecha_fmt']) . '</td>';
                    $html .= '</tr>';
                }
                break;
        }

        $html .= '</tbody></table>';
        $html .= '<p class="total">Total de registros: ' . count($rows) . '</p>';
        $html .= '</body></html>';

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream("$titulo.pdf", ['Attachment' => false]);
        exit();
    }
}