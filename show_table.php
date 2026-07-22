<?php
require __DIR__ . '/config/conexion.php';
$db = conexion();
$res = mysqli_query($db, "SHOW CREATE TABLE ordenes_compra");
$row = mysqli_fetch_array($res);
echo $row[1];
