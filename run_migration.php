<?php
require 'config/conexion.php';
$db = conexion();

$queries = [
    "ALTER TABLE ordenes_compra ADD COLUMN banco_nombre VARCHAR(100) DEFAULT NULL",
    "ALTER TABLE ordenes_compra ADD COLUMN banco_cuenta VARCHAR(100) DEFAULT NULL",
    "ALTER TABLE ordenes_compra ADD COLUMN banco_tipo_cuenta VARCHAR(100) DEFAULT NULL"
];

$all_success = true;
$errors = [];

foreach ($queries as $q) {
    if (!mysqli_query($db, $q)) {
        // Ignorar el error si la columna ya existe
        if (mysqli_errno($db) != 1060) {
            $all_success = false;
            $errors[] = mysqli_error($db);
        }
    }
}

if($all_success) {
    echo "<h1>¡Base de datos actualizada con éxito!</h1>";
    echo "<p>Ya puedes cerrar esta ventana y probar las Órdenes de Compra en el sistema.</p>";
} else {
    echo "<h1>Error</h1>";
    echo "<p>" . implode("<br>", $errors) . "</p>";
}

