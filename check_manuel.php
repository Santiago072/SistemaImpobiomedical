<?php
require __DIR__ . '/config/conexion.php';
$db = conexion();
$res = mysqli_query($db, "SELECT * FROM usuarios WHERE nombre = 'Manuel Cardenas'");
while($row = mysqli_fetch_assoc($res)) {
    echo "ID: " . $row['id'] . "\n";
    $c_res = mysqli_query($db, "SELECT COUNT(*) as c FROM cotizaciones WHERE usuario_id = " . $row['id']);
    $c_row = mysqli_fetch_assoc($c_res);
    echo "Cotizaciones: " . $c_row['c'] . "\n";
}
