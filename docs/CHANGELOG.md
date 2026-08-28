# Historial de Versiones (Changelog) — Sistema Impobiomedical

Todas las actualizaciones, mejoras arquitectónicas, parches de seguridad y versiones del sistema se documentan en este archivo.

---

## [v2.6.0] - 2026-08-27
### Añadido
- **Nueva Orden de Compra Directa (sin cotización previa) — "Nueva Orden Directa":**
  - Vista `app/views/ordenes/crear_directa.php` con buscador predictivo del catálogo médico (miniatura, título, categoría, código) y badge `+ Agregar`.
  - Botón `+ Agregar Producto Manual` para líneas libres sin catálogo.
  - Botón `Limpiar Productos` estilizado con confirmación de seguridad.
  - Botón `Cancelar` corregido sin subrayado de texto.
  - Resumen en tiempo real (Subtotal, Descuento $ o %, Retención %, Flete, Total).
  - Métodos `crearDirecta()` y `crearDirectaGuardar()` en `OrdenCompraController.php`.
  - Rutas `crear_directa` y `crear_directa_guardar` registradas en `index.php`.
- **Autocompletado y Auto-limpieza de Datos de Proveedor:**
  - Al escribir un proveedor ya registrado se autocompletan NIT, tipo de contribuyente, condiciones de pago y datos bancarios de la última orden.
  - Al borrar o cambiar el campo Proveedor, todos los datos bancarios y NIT se limpian automáticamente (reactivo sin recarga de página).
  - Aplicado en **ambas vistas**: `crear_directa.php` (Orden Directa) y `seleccionar_items.php` (Orden desde Cotización).
- **Badge de Estado de Proveedor reubicado al encabezado de la Sección 2:**
  - Mueve el badge `🟢 Proveedor Registrado / 🟡 Proveedor Nuevo` al lado del título `2. Datos de la Orden`, liberando espacio y nivelando los inputs.
  - Aplicado en `crear_directa.php` y `seleccionar_items.php`.
- **Búsqueda AJAX en Vivo en Catálogo de Productos (`productos/lista.php`):**
  - Búsqueda asíncrona en tiempo real con debounce de 200ms contra toda la base de datos sin recargar la página.
  - Consulta multicampo en `ProductoModel.php` cubriendo `titulo`, `codigo_producto`, `categoria` y `descripcion`.
  - Acción `ajax_buscar` en `ProductoController.php` y ruta registrada en `index.php`.
- **Módulo de Estadísticas y Gráficos:**
  - **Top 5 Productos Cotizados**: Actualizado con `LEFT JOIN` y `COALESCE(p.titulo, i.titulo)` para incluir productos manuales y sumar `SUM(i.cantidad)` de unidades reales. Leyenda con nombres cortos y total de unidades.
  - **Top 5 Clientes Recurrentes**: Truncado inteligente a 25 caracteres con puntos suspensivos en el eje Y de Chart.js y `layout.padding` para evitar que los nombres largos queden cortados. Tooltip flotante con nombre legal completo.

### Corregido
- **Expiración de Sesión sin Aviso:**
  - `verificar_autenticacion()` ahora redirige siempre con `?timeout=1` cuando no hay sesión activa (antes redirigía a login sin parámetro, omitiendo el mensaje de aviso).
  - Banner `⚠️ Su sesión ha expirado por inactividad. Por favor inicie sesión nuevamente.` ahora se muestra en todos los casos de sesión caducada.
  - CSS del banner en `auth.css` ajustado: `min-height` adaptativo, color ámbar de advertencia y texto legible completo.
- **Preservación del Modo Modificación y Purga de Borradores en Cotizaciones:**
  - Corrección del reinicio involuntario al navegar entre Paso 1 (Ítems), Editar Producto y Paso 2 (Datos del Cliente).
  - El clon temporal de modificación ahora se descarta únicamente si el usuario navega a otro módulo del sistema o cancela explícitamente.
  - El botón `Descartar Borrador` ahora ejecuta una purga integral en la base de datos eliminando todos los borradores incompletos huérfanos del usuario (`eliminarTodosBorradoresDelUsuario`), garantizando un lienzo 100% en blanco.
- **Blindaje y Resiliencia en Generación de PDFs (Cotizaciones y Órdenes de Compra):**
  - Validación profunda de imágenes con `getimagesize()` y soporte de conversión al vuelo de formatos `.webp` a JPEG en memoria para DomPDF.
  - Bloque `try-catch` con fallback automático: si una imagen falla o está corrupta, el PDF se genera limpiamente en lugar de arrojar error 500.
  - Aplicado en `app/views/cotizaciones/generar_pdf.php` y `app/views/ordenes/generar_pdf.php`.
- **Espacio del campo IVA en cotizaciones cortado:** Anchos de columna corregidos (`width: 100%; min-width: 105px;`) en la tabla de ítems de `crear_directa.php`.
- **`OrdenCompraModel::insertarItem()`** acepta ahora `?int $cotizacionItemId` (`nullable`) para ítems de órdenes directas sin cotización de origen.

### Cambiado
- **`BD.txt` sincronizado al 100%** con el esquema real de MariaDB del contenedor VPS, eliminando discrepancias de columnas históricas.

---

## [v2.5.1] - 2026-08-26
### Corregido
- **Persistencia de Borradores de Cotización**:
  - Eliminación de la sobreescritura destructiva `&nueva=1` en los enlaces de navegación del menú lateral y panel de control, garantizando que los ítems agregados se conserven al vencerse la sesión o navegar entre módulos.
  - Implementación del botón explícito `[ ❌ Cancelar Modificación y Empezar una Nueva ]` en `app/views/cotizaciones/crear.php` para descartar revisiones clonadas y reiniciar en limpio solo bajo acción voluntaria del asesor.
- **Visualizador de PDF en Modales y Compatibilidad de Sesión**:
  - Configuración de `session.cookie_samesite = 'Lax'` en `config/seguridad.php` para permitir que la cookie de sesión viaje correctamente en peticiones internas de visores PDF embebidos (`<iframe>`).
  - Inclusión de script *Framebuster* en la vista de Login para evitar renderizado anidado si la sesión expira con un modal abierto.
  - Optimización del motor de generación DomPDF en cotizaciones y órdenes de compra (`isRemoteEnabled=false`, `isHtml5ParserEnabled=true`), eliminando bloqueos de red y acelerando la renderización de PDFs al instante.

### Cambiado
- **Información Institucional en Cotización PDF**:
  - Actualización de dirección de sede Sabaneta, Antioquia y teléfonos de contacto en el encabezado formal de `app/views/cotizaciones/generar_pdf.php`.

---

## [v2.5.0] - 2026-08-25
### Añadido
- **Nuevo Rol Especializado: `compras` (Encargado de Compras)**:
  - Capacidad para consultar y supervisar **todas las órdenes de compra consolidadas** en pestañas *Pendientes* y *Completadas*, al igual que el Administrador.
  - Habilitación de selector de cambio de estado interactivo (🟡 *Pendiente* ⟷ 🟢 *Completada*) y exportación granular a PDF y Excel (.xls).
  - Inclusión en los selectores de gestión de usuarios (`app/views/usuarios/lista.php`) y en la definición DDL de base de datos (`BD.txt`).
- **Acceso Global al Catálogo de Productos y Directorio de Clientes**:
  - Habilitación de los módulos de *Catálogo de Productos* y *Directorio de Clientes* en el menú lateral y dashboard principal para todos los usuarios.
  - Permisos de consulta, creación y edición abiertos para todo el equipo operativo, manteniendo la acción de eliminación (🗑️) blindada exclusivamente para el Administrador.

---

## [v2.4.0] - 2026-08-22
### Añadido
- **Manual de Usuario Interactivo y Adaptativo por Rol**:
  - Botón interactivo `[ ❓ Ayuda ]` en la barra superior (`topbar.php`) accesible de forma global desde todos los módulos.
  - Modal con pestañas temáticas: *1. Nueva Cotización*, *2. Consultar y Modificar*, *3. Órdenes de Compra*, *4. Clientes*, *5. Gestión Administrativa* y *6. Estadísticas y Reportes*.
  - Renderizado adaptativo que muestra funciones operativas para asesores y la suite completa de administración para usuarios con rol `admin`.
- **Actualización Documental Integral**:
  - Actualización de `docs/MANUAL_USUARIO.md` reflejando el flujo de trabajo en 2 pasos, la suite de órdenes de compra y el nuevo sistema de ayuda rápida.

### Corregido
- **Módulo de Cotizaciones**:
  - Corrección de la salida del modo *Modificar Cotización* al navegar a *Nueva Cotización* desde el menú lateral y panel principal.
  - Preservación del precio calculado de Estampillas al autocompletar con productos del catálogo médico.

---

## [v2.3.0] - 2026-08-18
### Añadido
- **Buscador en Tiempo Real en Catálogo de Productos**:
  - Filtrado interactivo en el cliente con debounce de 150ms sobre la cuadrícula `.prod-grid` sin recargar la página.
- **Layout en 2 Columnas para Edición de Ítems en Cotizaciones**:
  - Formulario estructurado con distribución balanceada: datos del producto y precio a la izquierda, calculadora de ganancias y valor con IVA en tiempo real a la derecha.
- **Diseño Unificado de Selectores de Estado (*Pill Badges*)**:
  - Estilos modernos para los selectores de estado en *Consultar Cotizaciones* y *Órdenes de Compra* (`.estado-comercial-select` y `.estado-orden-select`).
- **Modal y Flujo Seguro para Cambio Sugerido de Contraseña**:
  - Detección automática para usuarios que ingresan con su documento inicial.
  - Estilos dedicados en `css/modules/components.css` con efecto backdrop-blur, validaciones reactivas y botón de visualización de contraseña (*eye toggle*).
- **Tarjetas de Usuarios Modernizadas**:
  - Rediseño visual de las tarjetas en `app/views/usuarios/lista.php` con bordes suaves, iconos de contacto y centrado óptimo.

### Corregido
- **Persistencia de Ítems Temporales**:
  - Eliminación de la redirección destructiva `&nueva=1` en los menús y dashboard, garantizando que el borrador de cotización se mantenga intacto al navegar entre diferentes módulos.
- **Acciones en Órdenes de Compra**:
  - Remoción del botón de descarga directa duplicado, unificando la acción en el botón interactivo `👁️ Ver P.O.`.
- **Icono Toggle de Contraseña**:
  - Preservación de la clase de posicionamiento absoluto al alternar la visibilidad de la contraseña en el modal.

---

## [v2.2.0] - 2026-08-15
### Añadido
- **Pestañas y Gestión de Estados en Órdenes de Compra**:
  - Navegación por pestañas: 🟡 **Órdenes Pendientes** y 🟢 **Órdenes Completadas** con contadores numéricos en vivo.
  - Selector reactivo vía AJAX para cambiar de estado una orden (`pendiente` ⟷ `completada`) exclusivo para administradores con CSRF y rate limiting.
- **Selección Granular por Checkboxes y Exportación Segura**:
  - Casillas de verificación individuales y casilla maestra *"Seleccionar todas"* en la tabla de órdenes de compra.
  - Bloqueo y validación de seguridad si se intenta exportar sin seleccionar al menos una orden.
  - Exportación a **PDF** y nuevo generador a **Excel (.xls)** con formato estructurado y sumatoria total de pagos a proveedores.
- **Bloqueo Inteligente de Emisión de Órdenes**:
  - Deshabilitación funcional y visual del botón *Orden* en la tabla de cotizaciones cuando la cotización está en estado `concluida` o `descartada`.
- **Gráfico Evolutivo Comparativo en Estadísticas**:
  - Comparativa mensual en Chart.js de **Cotizaciones Totales vs. Cotizaciones Concluidas** para análisis directo de efectividad comercial.

---

## [v2.1.0] - 2026-08-15
### Añadido
- **Estados Comerciales de Cotizaciones (`estado_comercial`)**:
  - Clasificación de cotizaciones en `pendiente` (🟡), `concluida` (🟢) y `descartada` (🔴).
  - Selector interactivo en tiempo real vía AJAX para administradores en `consultar.php` con respuesta visual instantánea.
  - Filtro por estado comercial en el formulario de búsqueda de cotizaciones.
  - Indicador de estado comercial mediante badges visuales para asesores comerciales.
- **Catálogo de Productos PDF con Imágenes**:
  - Inclusión de miniaturas de imagen de alta calidad optimizadas (`max-width: 65px`) en `app/views/productos/pdf.php`.
  - Eliminación de columnas no prioritarias (IVA y Estado) para un diseño de catálogo limpio y compacto.
  - Reglas de salto de página `page-break-inside: avoid` y control de memoria/timeout (`memory_limit: 256M`, `set_time_limit: 120`).
- **Campo Departamento en Cotizaciones**:
  - Incorporación del campo `cliente_departamento` en el paso de finalización de cotización (`finalizar.php`).
  - Autocompletado dinámico de departamento desde el catálogo de clientes e inserción/actualización automática en `FinalizarCotizacionService.php`.
- **Accesos Rápidos Ampliados en Dashboard**:
  - Incorporación de accesos directos a **Órdenes de Compra** (global) y **Estadísticas y Reportes** (administradores).
- **Badge de Código de Asesor Visible en Topbar**:
  - Insignia estilizada en turquesa institucional (`#10757e`) al lado del nombre de bienvenida en la cabecera superior con texto blanco nítido forzado.

### Corregido
- **Comportamiento del Menú Lateral y Cabecera Superior**:
  - Corrección del salto y espacio vacío a la izquierda al ocultar el menú lateral mediante transición fluida del 100% de ancho en `.cabecera-superior`.
  - Sincronización de clases `.completo` y `.menu-oculto` en `public/js/script.js`, `layout.css` y `components.css`.
- **Restricción de Enlace de Estadísticas**:
  - Ocultamiento de la opción de estadísticas en el menú desplegable lateral para usuarios con rol asesor, previniendo pantallas de acceso denegado.
- **Eliminación de Texto Duplicado**:
  - Limpieza de la doble bienvenida en el dashboard principal.

---

## [v2.0.0] - 2026-08-11
### Añadido
- **Suite de Pruebas Automatizadas (PHPUnit 10)**: 11 tests unitarios con 36 aserciones que validan cálculos comerciales, IVA, consecutivos, hashing y seguridad.
- **Pipeline de Integración Continua (CI)**: Configuración en GitHub Actions (`.github/workflows/ci.yml`) para chequeo de sintaxis PHP y tests en cada commit.
- **Licencia Comercial Propietaria (`LICENSE`)**: Cláusulas formales de uso comercial, protección intelectual y facultades explícitas para mantenimiento interno por parte del comprador o desarrolladores designados.
- **Modularización de Estilos CSS (`css/modules/`)**: Arquitectura SMACSS/ITCSS en 7 submódulos (`variables.css`, `base.css`, `layout.css`, `components.css`, `forms.css`, `auth.css`, `responsive.css`).
- **Seguridad HTTP & CSRF Universal**: Cabeceras de seguridad (`X-Frame-Options`, `nosniff`, `HSTS`, `CSP`) y validación estricta de tokens CSRF en todas las acciones de modificación y eliminación.
- **Toggle de Contraseñas (Ver/Ocultar)**: Icono de ojo interactivo 👁️ en formularios de login y gestión de usuarios.
- **Campos Flexibles en Usuarios**: Correo y teléfono opcionales al crear y editar usuarios.

### Modificado
- **Migración a PDO**: Capa de datos completamente migrada de `mysqli` a `PDO` con sentencias preparadas y parámetros nombrados, eliminando vulnerabilidades de inyección SQL.

---

## [v1.2.1] - 2026-07-17
### Corregido
- **Bug en inserción de ítems**: Corrección de tipos de parámetros en la inserción de ítems de cotización.
- **Manejo de errores**: Lanzamiento de excepciones con feedback visible en la interfaz ante fallos de persistencia.

---

## [v1.1.0] - 2026-07-16
### Añadido
- **Calculadora Dinámica de Ganancias**: Captura de múltiples operaciones por etapa (Utilidad, Flete, Calibración, Estampillas) almacenadas en estructura JSON (`calc_ops`).
- **Hoja de Respaldo Interna**: Desglose con identificación de proveedores y costos reales.

---

## [v1.0.0] - 2026-07-09
### Estable
- Despliegue inicial en producción mediante contenedores Docker y proxy inverso Nginx.
- Catálogo de productos médicos, directorio de entidades de salud y generación de cotizaciones en PDF con DomPDF.
