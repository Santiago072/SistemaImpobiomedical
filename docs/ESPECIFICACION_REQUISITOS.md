# 📋 Especificación de Requisitos y Alcance Funcional — Sistema Impobiomedical

**Versión del Sistema:** v3.3.0  
**Fecha:** Septiembre 2026  
**Tecnología:** PHP 8.2 (PDO, MVC, Arquitectura Modular) · MariaDB / MySQL 8.0 · Vanilla CSS Modular (`css/components/`) · DomPDF · PHPUnit 10

Este documento formaliza los requisitos funcionales (RF), requisitos no funcionales (RNF), control de acceso por roles y reglas de negocio del **Sistema Impobiomedical**.

---

## 1. Control de Acceso y Visibilidad por Roles

El sistema cuenta con tres roles claramente estructurados:

* **Administrador (`admin`):** Acceso total al sistema. Dispone del menú de **Administración** (Gestión de Usuarios, Gestión de Productos, Gestión de Clientes y Gestión de Proveedores con potestad exclusiva para su eliminación/desactivación), menú de **Cotizaciones** (Nueva Cotización, Consultar, Órdenes de Compra y Estadísticas/Reportes), potestad para eliminar cualquier cotización u orden en cualquier estado comercial, y ajuste/modificación de cualquier cotización u orden de compra.
* **Encargado de Compras (`compras`):** Acceso enfocado a la gestión de aprovisionamiento y compras. Puede visualizar la totalidad de cotizaciones de todos los usuarios, eliminar cotizaciones en cualquier estado comercial, ajustar/modificar cotizaciones globales, gestionar, ajustar y eliminar órdenes de compra, y acceder al directorio de **Proveedores**.
* **Usuario / Asesor Comercial (`usuario`):** Acceso enfocado a su operación comercial. Dispone del menú **Cotizaciones** con los submódulos de **Nueva Cotización**, **Consultar** (sus propias cotizaciones con indicadores visuales de estado), **Órdenes de Compra** (con potestad para ajustar órdenes de su autoría), y acceso de consulta, creación y edición en el directorio de **Proveedores** (sin permisos de eliminación/desactivación). Únicamente puede ajustar o modificar cotizaciones de su propia autoría. No tiene acceso a los módulos de administración de usuarios ni a estadísticas generales.

---

## 2. Requisitos Funcionales (RF)

### 🔐 Autenticación y Seguridad de Acceso
* **RF01:** El sistema debe permitir el inicio de sesión mediante documento o código de usuario y contraseña cifrada.
* **RF02:** El sistema debe validar el rol del usuario autenticado y restringir el acceso a los módulos según sus permisos asignados.
* **RF03:** El sistema debe detectar automáticamente cuando un usuario ingresa con su número de documento como contraseña inicial y desplegar una ventana para sugerirle personalizar su contraseña, permitiendo la opción de omitir el cambio para esa sesión.
* **RF04:** El sistema debe validar que la nueva contraseña tenga una longitud mínima de 6 caracteres, coincida con su confirmación y sea diferente a su número de documento.
* **RF05:** El sistema debe permitir alternar la visibilidad de los caracteres en los campos de contraseña mediante un botón interactivo.
* **RF06:** El sistema debe bloquear intentos reiterados de acceso no autorizado aplicando límite de peticiones por tiempo e IP.
* **RF07:** El sistema debe permitir el cierre seguro de sesión destruyendo la información almacenada y las cookies asociadas.

### 📊 Panel de Control (Dashboard)
* **RF08:** El sistema debe mostrar indicadores numéricos clave: cotizaciones totales, cotizaciones del mes en curso, total de clientes registrados y productos activos en catálogo.
* **RF09:** El sistema debe filtrar los indicadores numéricos del dashboard para mostrar únicamente los datos correspondientes al asesor autenticado cuando se trate de un usuario con rol asesor.
* **RF10:** El sistema debe presentar accesos directos rápidos acordes a los permisos del usuario conectado.
* **RF11:** El sistema debe mostrar un saludo de bienvenida con la insignia del código asignado al asesor comercial.

### 📝 Cotizaciones y Calculadora Comercial
* **RF12:** El sistema debe permitir crear cotizaciones buscando productos del catálogo o ingresando productos de manera manual.
* **RF13:** El sistema debe incluir una calculadora dinámica de ganancias que permita registrar operaciones multietapa sobre el costo del proveedor: utilidad, fletes, calibración y estampillas.
* **RF14:** El sistema debe permitir la edición de productos agregados a la lista temporal distribuyendo el formulario en dos columnas: datos y precio unitario del producto a la izquierda, y calculadora de ganancias con cálculo de valor con IVA a la derecha.
* **RF15:** El sistema debe mantener los productos agregados en la lista temporal de la cotización mientras el usuario navega entre diferentes módulos del sistema.
* **RF16:** El sistema debe permitir aplicar IVA del 19% o registrar el producto como exento de IVA.
* **RF17:** El sistema debe calcular el número consecutivo mensual mediante la detección del valor máximo secuencial del mes, asegurando un avance continuo (`01`, `02`, `03`...) precedido por el código del asesor comercial.
* **RF18:** El sistema debe presentar al hacer clic en Modificar un diálogo con dos alternativas:
  * **Ajustar Cotización:** Corrección directa de la cotización finalizada conservando el mismo número de cotización sin alterar el consecutivo mensual ni emitir sufijos de versión.
  * **Nueva Versión / Revisión:** Generación de un clon derivado (`_01`, `_02`...) que preserve intacta la cotización original en el histórico comercial.
* **RF18.1:** El sistema debe inhabilitar la opción de crear revisiones derivadas sobre cotizaciones que ya son revisiones (evitando generar anidamientos como `EB01_01_01`), admitiendo exclusivamente el ajuste directo sobre las mismas o la edición de la cotización original base.
* **RF18.2:** El sistema debe restringir el ajuste y modificación de cotizaciones a sus autores originales, a excepción de los roles `admin` y `compras` que pueden ajustar o modificar cualquier cotización.
* **RF18.3:** El sistema debe restituir automáticamente a estado finalizada cualquier cotización en modo ajuste si el usuario decide cancelar o si navega hacia otro módulo del aplicativo.
* **RF19:** El sistema debe permitir actualizar el **Estado Comercial** de las cotizaciones entre *Pendiente*, *Concluida* y *Descartada* en tiempo real; mientras permanezca en *Pendiente* mostrará la etiqueta *"Sin cambio"*, y al cambiar a *Concluida* o *Descartada* mostrará la fecha y hora exacta del cambio de estado.
* **RF20:** El sistema debe permitir gestionar el **Estado de Entrega** de las cotizaciones entre *Pendiente*, *En Tránsito* y *Entregado* en tiempo real; mientras esté *Pendiente* mostrará *"Por despachar"*, al pasar a *En Tránsito* indicará *"En camino"*, y al marcarse como *Entregado* registrará y mostrará la fecha de entrega efectiva junto al tiempo transcurrido en días.
* **RF21:** El sistema debe generar documentos PDF oficiales para el cliente con diseño corporativo y hojas de respaldo confidencial con costos y proveedores.
* **RF22:** El sistema debe permitir la exportación de cotizaciones a Excel en un formato estructurado y ultrarrápido sin imágenes para optimizar tiempos en listas extensas.
* **RF22.1:** El sistema debe permitir a los roles `admin` y `compras` consultar las cotizaciones emitidas por todos los usuarios del sistema, así como eliminar cotizaciones obsoletas en cualquier estado comercial (`pendiente`, `concluida`, `descartada`) con verificación CSRF.

### 📦 Órdenes de Compra (P.O. - Purchase Orders)
* **RF23:** El sistema debe permitir generar órdenes de compra dirigidas a proveedores a partir de cotizaciones en estado pendiente o mediante la creación de Órdenes Directas de mostrador.
* **RF24:** El sistema debe bloquear la emisión de órdenes de compra para cotizaciones que se encuentren en estado concluida o descartada.
* **RF25:** El sistema debe permitir parametrizar IVA opcional (0% o 19%) sobre el costo de flete en órdenes de compra y persistir esta configuración en la base de datos (`flete_iva` y `flete_porcentaje_iva`).
* **RF26:** El sistema debe clasificar las órdenes de compra en pestañas de órdenes pendientes y órdenes completadas con contadores en tiempo real.
* **RF27:** El sistema debe permitir a los administradores actualizar el estado de las órdenes de compra entre pendiente y completada.
* **RF28:** El sistema debe permitir la selección individual y masiva de órdenes de compra mediante casillas de verificación persistentes a través de la paginación (`sessionStorage`) para su exportación consolidada a PDF y Excel.
* **RF29:** El sistema debe incluir un visor interactivo de documentos para previsualizar e imprimir la orden de compra directamente.
* **RF29.1:** El sistema debe permitir ajustar directamente cualquier orden de compra conservando estrictamente su número consecutivo P.O. original, precargando los ítems, cantidades, proveedor y valores financieros para su corrección atómica transaccional.
* **RF29.2:** El sistema debe restringir el ajuste de órdenes de compra al usuario creador de la misma o a roles con privilegio superior (`admin` y `compras`).
* **RF29.3:** El sistema debe redirigir tras el guardado o ajuste de una orden de compra directamente a la vista de consulta de órdenes con apertura automática del visor modal del PDF, de modo que al navegar hacia atrás o cerrar el documento el usuario permanezca en la tabla completa de órdenes.

### 🩺 Catálogo de Productos Médicos (Solo Administrador)
* **RF30:** El sistema debe permitir registrar, editar y listar productos médicos organizados en cuadrícula de tarjetas con foto, código, título, categoría (incluyendo *Servicio Calibración*), IVA y estado.
* **RF31:** El sistema debe permitir filtrar productos en el catálogo en tiempo real mediante búsqueda asíncrona (AJAX) mientras el usuario escribe en el campo de búsqueda sin recargar la página.
* **RF32:** El sistema debe validar y sanitizar los archivos de imagen subidos al catálogo comprobando tipos MIME reales y extensiones permitidas (JPG, PNG, WebP).
* **RF33:** El sistema debe permitir exportar el catálogo completo consolidado en formato PDF con fotografías miniatura, garantizando compatibilidad multiformato de imágenes WebP mediante un mecanismo de conversión en 3 capas (GD nativo -> Imagick -> ImageMagick CLI) y almacenamiento en caché de thumbnails (`uploads/thumbs/`) para prevenir caídas de memoria en DomPDF.

### 🏢 Directorio de Clientes y Entidades (Solo Administrador)
* **RF34:** El sistema debe permitir registrar y administrar clientes con NIT, departamento, municipio, persona de contacto, teléfono y correo electrónico.
* **RF35:** El sistema debe autocompletar la información del cliente y su ubicación durante el proceso de finalización de una cotización.

### 👥 Gestión de Usuarios (Solo Administrador)
* **RF36:** El sistema debe permitir registrar y administrar cuentas de usuario asignando nombre, código único de asesor, documento, cargo, correo, teléfono y rol.
* **RF37:** El sistema debe permitir restablecer la contraseña de cualquier usuario asignando por defecto su número de documento.

### 📈 Estadísticas, Métricas y Reportes Ejecutivos (Solo Administrador)
* **RF38:** El sistema debe generar métricas analíticas e indicadores financieros consolidados: monto total cotizado, facturación real por órdenes concluidas, total de cotizaciones y órdenes emitidas, ticket promedio y tasa de efectividad comercial.
* **RF39:** El sistema debe presentar rankings interactivos de clientes líderes (Top Clientes por facturación y número de pedidos), desglose de ventas por cliente mensualizado y comparativas de desempeño por asesor comercial.
* **RF40:** El sistema debe permitir exportar informes ejecutivos formales en formato PDF con diseño institucional, KPIs financieros, Top Clientes con porcentaje de participación (`%`) y tabla de evolución mensual.

### 🚚 Gestión de Proveedores
* **RF41:** El sistema debe disponer de un directorio centralizado de proveedores con campos de NIT, Razón Social, Tipo de Contribuyente, Entidad Bancaria, Número de Cuenta, Tipo de Cuenta y Estado.
* **RF42:** El sistema debe permitir la búsqueda en vivo con debounce (400ms) de proveedores por NIT o Nombre comercial sin necesidad de presionar teclas de envío.
* **RF43:** El sistema debe restringir la acción de desactivación o eliminación de proveedores exclusivamente a usuarios con rol `admin`; los usuarios estándar solo podrán consultar, crear y editar.
* **RF44:** El sistema debe autocompletar predictivamente los datos del proveedor (NIT, Razón Social, cuenta y banco) al momento de cotizar productos o emitir órdenes de compra.
* **RF45:** El sistema debe clasificar cronológicamente a los proveedores en las órdenes de compra como `Nuevo` (cuando se trata de la primera orden emitida para dicho proveedor) o `Registrado` (cuando ya cuenta con una o más órdenes previas emitidas en el historial), preservando el autocompletado de datos desde el directorio centralizado.

### 🎨 Interfaz, Accesibilidad y Ayuda en Línea
* **RF46:** El sistema debe destacar visualmente todos los campos y etiquetas de entrada obligatorios con asterisco en color rojo vivo contrastado (`.required-star`, `#ef4444 !important`) tanto en renderizado estático como en formularios dinámicos.
* **RF47:** El sistema debe incluir un botón de ayuda rápida (`[ ? Ayuda ]`) accesible en el encabezado global para consultar el manual operativo adaptado a los permisos del usuario activo.
* **RF48:** El sistema debe actualizar reactivamente en el cliente los badges y colores de estado (comercial y de entrega) sin requerir recarga total de la pantalla.
* **RF49:** El sistema debe incorporar un favicon unificado oficial en formato vectorial (`favicon.svg`) en todas las interfaces públicas y privadas.
* **RF50:** El sistema debe preservar los datos de proveedor digitados en la calculadora dinámica de cotización al seleccionar o reutilizar ítems del catálogo médico.

---

## 3. Requisitos No Funcionales (RNF)

* **RNF01:** Todas las transacciones y consultas a la base de datos deben ejecutarse mediante PDO con sentencias preparadas y parámetros enlazados para prevenir ataques de inyección SQL.
* **RNF02:** Todas las solicitudes que modifiquen el estado del sistema deben validar obligatoriamente un token de seguridad contra ataques de falsificación de petición en sitios cruzados (CSRF).
* **RNF03:** La interfaz de usuario debe estar estructurada mediante hojas de estilo CSS modulares organizadas por componentes (`css/components/`) sin incrustar estilos inline en las vistas.
* **RNF04:** El sistema debe ser compatible para su ejecución en entornos web con PHP 8.2 y servidores de base de datos MySQL 8 o MariaDB, permitiendo despliegues continuos sin pérdida de información ni sobreescritura de datos persistentes montados en volúmenes Docker.
* **RNF05:** El código fuente debe contar con pruebas unitarias automatizadas para validar la lógica de cálculos comerciales, consecutivos e integridad de seguridad.
* **RNF06:** La interfaz debe ser adaptable y visualmente consistente para diferentes resoluciones de pantalla en computadores de escritorio y dispositivos móviles.
* **RNF07:** El subsistema de generación de PDF debe procesar catálogos extensos (más de 180 productos con imágenes) manteniendo el consumo de memoria dentro del umbral operativo (`memory_limit = 256M`), empleando miniaturas ligeras JPEG (72% de compresión) generadas una sola vez y reutilizadas desde disco.
* **RNF08:** Compatibilidad estricta con PHP 8.2+, evitando el uso de propiedades dinámicas no declaradas en controladores y servicios para mantener limpios los logs de advertencias y errores del servidor.
* **RNF09:** El sistema debe aplicar normalización automatizada en los campos de identificación tributaria (NIT) mediante algoritmos que remuevan puntos y espacios preservando el guion del dígito de verificación (`normalizar_nit()`).
* **RNF10:** El sistema debe aplicar protección contra sobrecarga y ataques de fuerza bruta mediante Rate Limiting por IP en todos los endpoints de modificación y búsqueda asíncrona (`verificar_rate_limit()`).
