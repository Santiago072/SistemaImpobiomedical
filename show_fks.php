<?php
require __DIR__ . '/config/conexion.php';
$db = conexion();
$res = mysqli_query($db, "SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA = 'sistema_impobiomedical' AND REFERENCED_TABLE_NAME = 'usuarios'");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['TABLE_NAME'] . '.' . $row['COLUMN_NAME'] . "\n";
}
