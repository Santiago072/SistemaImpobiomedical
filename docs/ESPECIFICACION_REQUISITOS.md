# 📋 Especificación de Requisitos y Alcance Funcional — Sistema Impobiomedical

**Versión del Sistema:** v2.8.0  
**Fecha:** Septiembre 2026  
**Tecnología:** PHP 8.2 (PDO, MVC, Arquitectura Modular) · MariaDB / MySQL 8.0 · Vanilla CSS Modular · DomPDF · PHPUnit 10

Este documento formaliza los requisitos funcionales (RF), requisitos no funcionales (RNF), control de acceso por roles y reglas de negocio del **Sistema Impobiomedical**.

---

## 1. Control de Acceso y Visibilidad por Roles

El sistema cuenta con dos roles claramente diferenciados:

* **Administrador (`admin`):** Acceso total al sistema. Dispone del menú de **Administración** (Gestión de Usuarios, Gestión de Productos y Gestión de Clientes), menú de **Cotizaciones** (Nueva Cotización, Consultar, Órdenes de Compra y Estadísticas/Reportes), y cambio de estados comerciales de cotizaciones y órdenes de compra.
* **Usuario / Asesor Comercial (`usuario`):** Acceso enfocado exclusivamente a su operación comercial. Dispone del menú **Cotizaciones** con los submódulos de **Nueva Cotización**, **Consultar** (sus propias cotizaciones con indicadores visuales de estado) y **Órdenes de Compra**. No tiene acceso a los módulos de administración ni a estadísticas generales.

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
* **RF18:** El sistema debe permitir modificar cotizaciones existentes generando una versión numerada de revisión que preserve el historial comercial original.
* **RF19:** El sistema debe permitir actualizar el **Estado Comercial** de las cotizaciones entre *Pendiente*, *Concluida* y *Descartada* en tiempo real; mientras permanezca en *Pendiente* mostrará la etiqueta *"Sin cambio"*, y al cambiar a *Concluida* o *Descartada* mostrará la fecha y hora exacta del cambio de estado.
* **RF20:** El sistema debe permitir gestionar el **Estado de Entrega** de las cotizaciones entre *Pendiente*, *En Tránsito* y *Entregado* en tiempo real; mientras esté *Pendiente* mostrará *"Por despachar"*, al pasar a *En Tránsito* indicará *"En camino"*, y al marcarse como *Entregado* registrará y mostrará la fecha de entrega efectiva junto al tiempo transcurrido en días.
* **RF21:** El sistema debe generar documentos PDF oficiales para el cliente con diseño corporativo y hojas de respaldo confidencial con costos y proveedores.
* **RF22:** El sistema debe permitir la exportación de cotizaciones a Excel en un formato estructurado y ultrarrápido sin imágenes para optimizar tiempos en listas extensas.

### 📦 Órdenes de Compra (P.O. - Purchase Orders)
* **RF23:** El sistema debe permitir generar órdenes de compra dirigidas a proveedores a partir de cotizaciones en estado pendiente o mediante la creación de Órdenes Directas de mostrador.
* **RF24:** El sistema debe bloquear la emisión de órdenes de compra para cotizaciones que se encuentren en estado concluida o descartada.
* **RF25:** El sistema debe permitir parametrizar IVA opcional (0% o 19%) sobre el costo de flete en órdenes de compra.
* **RF26:** El sistema debe clasificar las órdenes de compra en pestañas de órdenes pendientes y órdenes completadas con contadores en tiempo real.
* **RF27:** El sistema debe permitir a los administradores actualizar el estado de las órdenes de compra entre pendiente y completada.
* **RF28:** El sistema debe permitir la selección individual y masiva de órdenes de compra mediante casillas de verificación para su exportación consolidada a PDF y Excel.
* **RF29:** El sistema debe incluir un visor interactivo de documentos para previsualizar e imprimir la orden de compra directamente.

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

### 📈 Estadísticas y Reportes (Solo Administrador)
* **RF38:** El sistema debe permitir filtrar el volumen de cotizaciones y ventas reales por rango de fechas, asesor y cliente.
* **RF39:** El sistema debe presentar un visor analítico interactivo de **Ventas a Clientes por Mes** con selector de mes y consolidado acumulado, además del gráfico comparativo de evolución mensual y tops de productos y vendedores.
* **RF40:** El sistema debe generar reportes ejecutivos consolidados en formato PDF que incluyan KPIs gerenciales, Top Clientes con porcentaje de ventas (`%`), sección desglosada de **Ventas a Clientes por Mes** con total facturado mensual y porcentaje de contribución individual, y tabla de efectividad comercial.

---

## 3. Requisitos No Funcionales (RNF)

* **RNF01:** Todas las transacciones y consultas a la base de datos deben ejecutarse mediante PDO con sentencias preparadas y parámetros enlazados para prevenir ataques de inyección SQL.
* **RNF02:** Todas las solicitudes que modifiquen el estado del sistema deben validar obligatoriamente un token de seguridad contra ataques de falsificación de petición en sitios cruzados (CSRF).
* **RNF03:** La interfaz de usuario debe estar estructurada mediante hojas de estilo CSS modulares organizadas por componentes sin incrustar estilos inline en las vistas.
* **RNF04:** El sistema debe ser compatible para su ejecución en entornos web con PHP 8.2 y servidores de base de datos MySQL 8 o MariaDB, permitiendo despliegues continuos sin pérdida de información ni sobreescritura de datos persistentes montados en volúmenes Docker.
* **RNF05:** El código fuente debe contar con pruebas unitarias automatizadas para validar la lógica de cálculos comerciales, consecutivos e integridad de seguridad.
* **RNF06:** La interfaz debe ser adaptable y visualmente consistente para diferentes resoluciones de pantalla en computadores de escritorio y dispositivos móviles.
* **RNF07:** El subsistema de generación de PDF debe procesar catálogos extensos (más de 180 productos con imágenes) manteniendo el consumo de memoria dentro del umbral operativo (`memory_limit = 256M`), empleando miniaturas ligeras JPEG (72% de compresión) generadas una sola vez y reutilizadas desde disco.
* **RNF08:** Compatibilidad estricta con PHP 8.2+, evitando el uso de propiedades dinámicas no declaradas en controladores y servicios para mantener limpios los logs de advertencias y errores del servidor.
