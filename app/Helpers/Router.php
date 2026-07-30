<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Session.php';

function autoloadAppClasses(string $className) {
    $dirs = [
        __DIR__ . '/../Controllers/',
        __DIR__ . '/../Models/',
        __DIR__ . '/../Helpers/',
    ];
    foreach ($dirs as $dir) {
        $file = $dir . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
}
spl_autoload_register('autoloadAppClasses');

function dispatch(string $action) {
    switch ($action) {
        // Auth
        case 'login':
            AuthController::login();
            break;
        case 'logout':
            AuthController::logout();
            break;

        // Admin
        case 'admin':
            AdminController::handle();
            break;

        // AJAX / API
        case 'ajax-detalle-pedido':
            AdminController::ajaxDetallePedido();
            break;
        case 'ajax-tasa':
            AdminController::ajaxTasa();
            break;

        // Paginas legales
        case 'politicas-privacidad':
            include __DIR__ . '/../../public/views/politicas-privacidad.php';
            break;
        case 'terminos-uso':
            include __DIR__ . '/../../public/views/terminos-uso.php';
            break;

        // PDF
        case 'descargar-pdf':
            AdminController::descargarPDF();
            break;
        case 'llenar-talonario':
            AdminController::llenarTalonario();
            break;
        case 'generar-talonario-pdf':
            AdminController::generarPDFTalonario();
            break;

        default:
            LandingController::index();
            break;
    }
}
