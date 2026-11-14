<?php
require_once __DIR__ . '/../app/Helpers/Database.php';

echo "Iniciando migración de datos de pago...\n";

$db = Database::getConnection();

function getExistingColumns($table) {
    global $db;
    $res = mysqli_query($db, "DESCRIBE `$table`");
    $cols = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $cols[] = $row['Field'];
    }
    return $cols;
}

$colsUsuarios = getExistingColumns('usuarios');

$nuevasColumnasUsuarios = [
    'cedula'              => 'VARCHAR(25) NULL AFTER `apellido`',
    'telefono'            => 'VARCHAR(25) NULL AFTER `cedula`',
    'banco'               => 'VARCHAR(100) NULL AFTER `rol`',
    'cuenta_bancaria'     => 'VARCHAR(25) NULL AFTER `banco`',
    'pago_movil_banco'    => 'VARCHAR(100) NULL AFTER `cuenta_bancaria`',
    'pago_movil_telefono' => 'VARCHAR(25) NULL AFTER `pago_movil_banco`',
    'pago_movil_cedula'   => 'VARCHAR(25) NULL AFTER `pago_movil_telefono`'
];

foreach ($nuevasColumnasUsuarios as $col => $def) {
    if (!in_array($col, $colsUsuarios)) {
        mysqli_query($db, "ALTER TABLE `usuarios` ADD COLUMN `$col` $def");
        echo "Añadida columna '$col' a tabla 'usuarios'.\n";
    } else {
        echo "Columna '$col' ya existe en 'usuarios'.\n";
    }
}

$colsContactos = getExistingColumns('contactos');

$nuevasColumnasContactos = [
    'cedula'              => 'VARCHAR(25) NULL AFTER `categoria`',
    'banco'               => 'VARCHAR(100) NULL AFTER `direccion`',
    'cuenta_bancaria'     => 'VARCHAR(25) NULL AFTER `banco`',
    'pago_movil_banco'    => 'VARCHAR(100) NULL AFTER `cuenta_bancaria`',
    'pago_movil_telefono' => 'VARCHAR(25) NULL AFTER `pago_movil_banco`',
    'pago_movil_cedula'   => 'VARCHAR(25) NULL AFTER `pago_movil_telefono`'
];

foreach ($nuevasColumnasContactos as $col => $def) {
    if (!in_array($col, $colsContactos)) {
        mysqli_query($db, "ALTER TABLE `contactos` ADD COLUMN `$col` $def");
        echo "Añadida columna '$col' a tabla 'contactos'.\n";
    } else {
        echo "Columna '$col' ya existe en 'contactos'.\n";
    }
}

echo "Migración completada exitosamente.\n";
