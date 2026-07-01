<?php
/**
 * Layout footer — cierra el body/html e incluye el script global.
 * Variables esperadas (opcionales):
 *   $basePath  string — ruta raíz para cargar script.js
 */
$basePath = $basePath ?? '/SistemaImpobiomedical/';
?>
<script src="<?= $basePath ?>public/js/script.js?v=<?= time() ?>"></script>
<script src="<?= $basePath ?>public/js/ajax_tables.js?v=<?= time() ?>"></script>
</body>
</html>
