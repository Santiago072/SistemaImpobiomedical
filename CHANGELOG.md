# Historial de Versiones (Changelog) - Sistema Impobiomedical

Todas las actualizaciones, cambios y versiones importantes del sistema se documentarán en este archivo para mantener un control estricto del desarrollo.

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
