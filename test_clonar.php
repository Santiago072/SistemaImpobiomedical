<?php
require 'config/EnvLoader.php';
require 'config/conexion.php';
require 'app/models/CotizacionModel.php';

$db = conexion();
$model = new CotizacionModel($db);

$model->clonarItems(1, 2);
echo "OK\n";
