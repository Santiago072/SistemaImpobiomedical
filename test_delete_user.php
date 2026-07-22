<?php
require __DIR__ . '/config/conexion.php';
$db = conexion();
mysqli_query($db, "INSERT INTO usuarios (codigo, documento, nombre, correo, password, cargo, rol, estado) VALUES ('TEST', '111111', 'Test', 'test@test.com', 'pwd', 'Test', 'usuario', 'activo')");
$id = mysqli_insert_id($db);
echo "Created ID $id\n";
require_once __DIR__ . '/app/models/UsuarioModel.php';
$model = new UsuarioModel($db);
$result = $model->eliminar($id);
echo "Delete result: " . ($result ? "SUCCESS" : "FAILED") . "\n";
