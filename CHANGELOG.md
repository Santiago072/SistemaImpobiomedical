# Historial de Versiones (Changelog) - Sistema Impobiomedical

Todas las actualizaciones, cambios y versiones importantes del sistema se documentarán en este archivo para mantener un control estricto del desarrollo.

## [v1.2.1] - 2026-07-17
### Corregido
- **Bug crítico en `insertarItem`**: El string de tipos de `mysqli_stmt_bind_param()` en `CotizacionModel::insertarItem()` tenía un orden incorrecto de tipos (`precio` marcado como `s` en lugar de `d`, entre otros), causando que el INSERT fallara silenciosamente. El resultado visible era que al agregar el segundo producto, el primero "desaparecía" porque el primer ítem nunca se había guardado en BD y el sistema buscaba un nuevo borrador en cada request.
- **Manejo de errores en controller**: `procesarGuardarItem` ahora lanza `RuntimeException` si el INSERT falla, mostrando el mensaje de error al usuario en lugar de hacer redirect silencioso.
- **Alerta de error en vista**: La vista `crear.php` ahora muestra un mensaje de error visible cuando el ítem no pudo guardarse.

---

## [v1.2.0] - 2026-07-17
### Añadido
- **README.md**: Documentación técnica completa del repositorio (arquitectura, instalación local, Docker, variables de entorno, módulos, seguridad, despliegue).
- **MANUAL_USUARIO.md**: Manual de usuario paso a paso para todos los módulos del sistema (10 secciones, FAQ incluido).
- **AUDITORIA_SISTEMA.md**: Auditoría de requisitos con matriz de estado, bugs reportados y plan de próximas fases.

---

## [v1.1.0] - 2026-07-16
### Añadido
- **Calculadora Dinámica de Ganancias (Opción A - JSON)**: Nueva funcionalidad para capturar múltiples operaciones de cálculo por etapa (Utilidad, Flete, Calibración, Estampillas).
- Cada operación puede ser: suma de valor fijo ($), suma de porcentaje (%), o división entre un factor.
- Las operaciones se guardan en JSON en la columna `calc_ops` de `cotizacion_items`.
- En la hoja de respaldo se itera el JSON y se muestran todas las operaciones de cada etapa en líneas separadas.
- Compatibilidad hacia atrás: los valores antiguos se convierten automáticamente a la nueva estructura.
### Modificado
- Vista `respaldo.php` actualizada para mostrar las operaciones JSON desglosadas por etapa con colores diferenciados.
- Vista `editar_item.php` ahora permite modificar y agregar nuevas operaciones de cálculo.

## [v1.0.1] - 2026-07-13
### Añadido
- Se agregaron las columnas `% IVA` y `T/IVA` en la generación de cotizaciones en PDF.
### Modificado
- Se ajustó el cálculo matemático de `T/IVA` para que sume correctamente el IVA de la fila con el Subtotal (`V/T`), mostrando el Total de la Fila con IVA incluido.
- Redimensionamiento de las columnas CSS del PDF para asegurar que el formato (A4) mantenga las proporciones intactas.

---

## [v1.0.0] - 2026-07-09
### Estable (Release inicial en VPS)
- **Despliegue inicial** en servidor de producción (VPS) utilizando Docker y Nginx.
- Saneamiento de base de datos (`data_seed.sql`) garantizando codificación UTF-8 para las tildes y caracteres especiales.
- Corrección de restricción de caracteres en base de datos (Error de truncamiento SQL en Modo Estricto para la columna `tiempo_entrega`).
- Ajuste de puertos internos del `docker-compose.yml` para alinear con el proxy Nginx.
- Limpieza de historial y ocultamiento de información sensible en los scripts de despliegue (`deploy.sh`).
