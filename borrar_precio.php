<?php
require 'config/EnvLoader.php';
require 'config/conexion.php';
$db = conexion();

$sql = "ALTER TABLE productos DROP COLUMN precio";
if (mysqli_query($db, $sql)) {
    echo "Columna 'precio' borrada correctamente de la tabla productos.\n";
} else {
    echo "Error borrando columna (quizás ya fue borrada): " . mysqli_error($db) . "\n";
}
