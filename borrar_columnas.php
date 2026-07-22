<?php
require 'config/EnvLoader.php';
require 'config/conexion.php';
$db = conexion();

$sql = "ALTER TABLE productos DROP COLUMN precio, DROP COLUMN iva, DROP COLUMN porcentaje_iva";
if (mysqli_query($db, $sql)) {
    echo "Columnas borradas correctamente de la tabla productos.\n";
} else {
    echo "Error borrando columnas (quizás ya fueron borradas): " . mysqli_error($db) . "\n";
}
