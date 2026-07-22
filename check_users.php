<?php
require __DIR__ . '/config/conexion.php';
$db = conexion();
$res = mysqli_query($db, "SELECT id, nombre, rol, estado FROM usuarios");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['id'] . " - " . $row['nombre'] . " - " . $row['rol'] . "\n";
}
