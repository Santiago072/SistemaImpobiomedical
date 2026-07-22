<?php
require __DIR__ . '/config/conexion.php';
$db = conexion();
$res = mysqli_query($db, "SELECT COUNT(*) as c FROM usuarios WHERE rol='admin'");
$row = mysqli_fetch_assoc($res);
if($row['c'] == 0) {
    $hash = password_hash('Admin2026*', PASSWORD_BCRYPT);
    mysqli_query($db, "INSERT INTO usuarios (codigo, documento, nombre, correo, password, cargo, rol, estado) VALUES ('ADM', '1000000000', 'Administrador', 'admin@impobiomedical.com', '$hash', 'Administrador', 'admin', 'activo')");
    echo 'Admin restored.';
} else {
    echo 'Admin exists.';
}
?>
