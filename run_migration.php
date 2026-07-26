<?php
require 'config/conexion.php';
$db = conexion();
$sql = file_get_contents('migration.sql');
if(mysqli_multi_query($db, $sql)) {
    echo "<h1>¡Base de datos actualizada con éxito!</h1>";
    echo "<p>Ya puedes cerrar esta ventana.</p>";
} else {
    echo "<h1>Error</h1>";
    echo "<p>" . mysqli_error($db) . "</p>";
}
