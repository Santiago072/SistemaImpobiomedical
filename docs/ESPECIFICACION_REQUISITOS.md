# 📋 Especificación de Requisitos y Alcance Funcional — Sistema Impobiomedical

**Versión del Sistema:** v3.5.4  
**Fecha:** Octubre 2026  
**Tecnología:** PHP 8.2 (PDO, MVC, Arquitectura Modular) · MariaDB / MySQL 8.0 · Vanilla CSS Modular (`css/components/`) · DomPDF · PHPUnit 10

Este documento formaliza los requisitos funcionales (RF), requisitos no funcionales (RNF), control de acceso por roles y reglas de negocio del **Sistema Impobiomedical**.

---

## 1. Control de Acceso y Visibilidad por Roles

El sistema cuenta con tres roles claramente estructurados:

* **Administrador (`admin`):** Acceso total al sistema. Dispone del menú de **Administración** (Gestión de Usuarios, Catálogo de Productos, Directorio de Clientes y Directorio de Proveedores con potestad exclusiva para su eliminación/desactivación), menú de **Cotizaciones** (Nueva Cotización, Consultar, Órdenes de Compra y Estadísticas/Reportes), potestad para eliminar cualquier cotización u orden en cualquier estado comercial, y ajuste/modificación de cualquier cotización u orden de compra.
* **Encargado de Compras (`compras`):** Acceso enfocado a la gestión de aprovisionamiento y compras. Puede visualizar la totalidad de cotizaciones de todos los usuarios, eliminar cotizaciones en cualquier estado comercial, ajustar/modificar cotizaciones globales, gestionar, cambiar estados, ajustar y eliminar órdenes de compra, y acceder con permisos de creación y edición a los directorios de **Proveedores**, **Clientes** y **Catálogo de Productos** (sin permisos de eliminación).
* **Usuario / Asesor Comercial (`usuario`):** Acceso enfocado a su operación comercial. Dispone del menú **Cotizaciones** con los submódulos de **Nueva Cotización**, **Consultar** (sus propias cotizaciones con indicadores visuales de estado), **Órdenes de Compra** (con potestad para ajustar órdenes de su autoría), y acceso de consulta, creación y edición en el **Catálogo de Productos**, **Directorio de Clientes** y **Directorio de Proveedores** (sin permisos de eliminación o desactivación física). Únicamente puede ajustar o modificar cotizaciones de su propia autoría. No tiene acceso al módulo de gestión de usuarios ni a estadísticas generales.

---

## 2. Requisitos Funcionales (RF)

### 🔐 Autenticación y Seguridad de Acceso
* **RF01:** El sistema debe permitir el inicio de sesión mediante el número de documento y la contraseña asignada (o el mismo documento si es su contraseña inicial).
* **RF02:** El sistema debe validar el rol del usuario autenticado (`admin`, `compras`, `usuario`) para dar acceso únicamente a los módulos autorizados según sus permisos.
* **RF03:** El sistema debe sugerir al usuario cambiar su contraseña cuando ingrese por primera vez con su número de documento, permitiéndole omitir el cambio para esa sesión.
* **RF04:** El sistema debe validar que toda nueva contraseña tenga al menos 6 caracteres, coincida con su confirmación y sea distinta al número de documento.
* **RF05:** El sistema debe bloquear temporalmente los intentos continuos de inicio de sesión fallidos para proteger las cuentas.
* **RF06:** El sistema debe permitir cerrar la sesión de forma segura, cerrando el acceso en el navegador.

### 📊 Panel de Control (Dashboard)
* **RF07:** El sistema debe mostrar un resumen de indicadores clave: total de cotizaciones, cotizaciones del mes, clientes registrados, productos activos y los registros recientes.
* **RF08:** El sistema debe mostrar a cada asesor comercial únicamente sus propias métricas y cotizaciones en el dashboard, mientras que los administradores y encargados de compras visualizan los consolidados generales.
* **RF09:** El sistema debe presentar accesos rápidos a las funciones principales adaptados al rol del usuario conectado.

### 📝 Cotizaciones y Calculadora Comercial
* **RF10:** El sistema debe permitir elaborar cotizaciones seleccionando productos del catálogo o ingresando productos de manera manual si es un producto nuevo.
* **RF11:** El sistema debe calcular el precio de venta unitario a partir del costo del proveedor, sumando el porcentaje de utilidad, flete, calibración y estampillas.
* **RF12:** El sistema debe permitir editar y ajustar los productos agregados a la lista de la cotización antes de finalizarla.
* **RF13:** El sistema debe generar automáticamente un consecutivo mensual para cada cotización (`01`, `02`, `03`...) precedido por las iniciales del asesor comercial.
* **RF14:** El sistema debe permitir modificar una cotización finalizada bajo dos modalidades:
  * **Ajustar Cotización:** Corrige precios o datos manteniendo el mismo número original.
  * **Nueva Revisión:** Genera una copia con sufijo de versión (`_01`, `_02`...) para conservar el histórico de la negociación original.
* **RF15:** El sistema debe permitir ajustar o modificar cotizaciones únicamente a sus autores originales, a excepción de los roles `admin` y `compras` que pueden gestionar cualquier cotización.
* **RF16:** El sistema debe permitir gestionar el **Estado Comercial** de las cotizaciones (*Pendiente*, *Concluida*, *Descartada*) en tiempo real, registrando la fecha y hora del cambio.
* **RF17:** El sistema debe permitir actualizar el **Estado de Entrega** de las cotizaciones (*Por despachar*, *En camino*, *Entregado*), calculando el tiempo transcurrido en días al confirmarse la entrega.
* **RF18:** El sistema debe generar el documento PDF oficial de la cotización con formato corporativo para ser presentado al cliente.
* **RF19:** El sistema debe permitir registrar la fecha de cotización, los días de validez y las condiciones de pago para incluirlas en el documento oficial.
* **RF20:** El sistema debe disponer de una **Hoja de Respaldo** interna que muestre los costos reales del proveedor, utilidades y márgenes de ganancia de cada producto cotizado.
* **RF21:** El sistema debe permitir exportar las cotizaciones a formato Excel de manera estructurada y rápida.
* **RF22:** El sistema debe permitir a los roles `admin` y `compras` consultar y eliminar cotizaciones de cualquier asesor comercial.
* **RF23:** El sistema debe permitir buscar cotizaciones por número de cotización, nombre del cliente, asesor comercial o rango de fechas, actualizando los contadores de cada estado.

### 📦 Órdenes de Compra (P.O.)
* **RF24:** El sistema debe permitir generar órdenes de compra a proveedores a partir de cotizaciones en estado pendiente o mediante la creación de Órdenes Directas de mostrador.
* **RF25:** El sistema debe bloquear la creación de órdenes de compra sobre cotizaciones que se encuentren concluidas o descartadas.
* **RF26:** El sistema debe clasificar las órdenes de compra en pendientes y completadas con contadores en tiempo real.
* **RF27:** El sistema debe permitir a los roles `admin` y `compras` actualizar el estado de las órdenes de compra y eliminarlas cuando sea necesario.
* **RF28:** El sistema debe permitir la selección individual y masiva de órdenes de compra para su exportación consolidada a PDF y Excel.
* **RF29:** El sistema debe incluir un visor interactivo para previsualizar, imprimir y descargar las órdenes de compra en formato PDF.
* **RF30:** El sistema debe permitir ajustar directamente cualquier orden de compra conservando su número consecutivo P.O. original.
* **RF31:** El sistema debe restringir el ajuste de órdenes de compra a su autor o a roles con privilegio superior (`admin` y `compras`).
* **RF32:** El sistema debe agrupar los ítems de la orden de compra por proveedor, asegurando que cada orden pertenezca a un único proveedor.
* **RF33:** En las Órdenes Directas de mostrador, el sistema debe exigir que los productos se seleccionen del catálogo oficial para garantizar la integridad de los datos.

### 🩺 Catálogo de Productos Médicos
* **RF34:** El sistema debe permitir a los usuarios autorizados consultar, registrar y editar productos médicos con su foto, código, título, categoría, IVA y estado; reservando la eliminación física exclusivamente al Administrador.
* **RF35:** El sistema debe permitir buscar productos en el catálogo de manera instantánea mientras se escribe el nombre o código.
* **RF36:** El sistema debe validar que los archivos de imagen subidos al catálogo cumplan con formatos válidos (JPG, PNG, WebP).
* **RF37:** El sistema debe permitir exportar el catálogo de productos a PDF, ya sea completo, por categoría o mediante la selección manual de productos específicos.

### 🏢 Directorio de Clientes
* **RF38:** El sistema debe permitir consultar, registrar y editar clientes con sus datos de contacto y tributarios (NIT, ubicación, teléfono, correo), reservando la eliminación exclusivamente al Administrador.
* **RF39:** El sistema debe permitir buscar clientes para cargar automáticamente su información en la cotización, o permitir digitarlos manualmente si es un cliente nuevo.

### 👥 Gestión de Usuarios (Solo Administrador)
* **RF40:** El sistema debe permitir registrar y administrar las cuentas de usuario, asignando sus datos personales, código único de asesor y su rol en el sistema.
* **RF41:** El sistema debe permitir restablecer la contraseña de cualquier usuario asignándole temporalmente su número de documento.

### 📈 Estadísticas y Reportes (Solo Administrador)
* **RF42:** El sistema debe consolidar indicadores comerciales financieros: monto total cotizado, ventas reales por órdenes concluidas, número de cotizaciones y órdenes emitidas.
* **RF43:** El sistema debe mostrar gráficos de rendimiento: clientes con mayores compras, histórico de ventas mensuales y comparativa de efectividad por asesor.
* **RF44:** El sistema debe permitir exportar los reportes analíticos consolidados a formato PDF con diseño institucional.

### 🚚 Directorio de Proveedores
* **RF45:** El sistema debe centralizar la información de los proveedores (NIT, Razón Social, datos bancarios y tributarios), permitiendo a los usuarios consultar, crear y editar, y reservando la eliminación al Administrador.
* **RF46:** El sistema debe permitir buscar proveedores por NIT o Nombre en tiempo real mientras el usuario escribe.
* **RF47:** El sistema debe permitir seleccionar un proveedor registrado para cargar sus datos comerciales y bancarios al cotizar o generar órdenes de compra, o ingresarlos manualmente si es requerido.
* **RF48:** El sistema debe clasificar automáticamente a los proveedores en las órdenes como `Nuevo` (primera compra) o `Registrado` (compras anteriores).
* **RF49:** El sistema debe registrar automáticamente a un proveedor en el directorio general cuando se emite una orden de compra con datos no existentes previamente.

### 🎨 Usabilidad y Experiencia de Usuario
* **RF50:** El sistema debe señalar visualmente los campos obligatorios de los formularios con un asterisco en color rojo.
* **RF51:** El sistema debe incluir un botón de ayuda rápida (`[ ? Ayuda ]`) en la barra superior para consultar el manual operativo según los permisos del usuario activo.
* **RF52:** El sistema debe presentar mensajes de alerta claros ante errores o validaciones, explicando la causa y sugiriendo la acción correcta para continuar.

---

## 3. Requisitos No Funcionales (RNF)

* **RNF01:** Todas las transacciones y consultas a la base de datos deben ejecutarse mediante sentencias preparadas (PDO) para garantizar la seguridad contra inyecciones SQL.
* **RNF02:** Las solicitudes que modifiquen información deben protegerse mediante tokens de seguridad (CSRF).
* **RNF03:** La interfaz de usuario debe estar estructurada mediante estilos modulares organizados por componentes para facilitar su mantenimiento.
* **RNF04:** El sistema debe operar de forma estable en servidores web con PHP 8.2 y motores MySQL 8.0 o MariaDB.
* **RNF05:** El código fuente debe contar con pruebas unitarias automatizadas para verificar la precisión de los cálculos comerciales y la seguridad del sistema.
* **RNF06:** La generación de documentos PDF debe procesar catálogos extensos manteniendo un consumo eficiente de memoria mediante imágenes optimizadas.
* **RNF07:** El sistema debe aplicar protección contra sobrecarga de peticiones e intentos de fuerza bruta mediante control de frecuencia (Rate Limiting).

---

## 4. Historias de Usuario (HU)

A continuación se detallan las Historias de Usuario organizadas según la barra de **Navegación Principal** del sistema y sus módulos transversales, vinculadas a sus respectivos Requisitos Funcionales y con sus Criterios de Aceptación (CA):

### 🔐 Transversal: Acceso al Sistema

#### HU01 - Inicio de Sesión y Control de Acceso (Módulo: Autenticación)
* **Como** usuario del sistema (`admin`, `compras`, `usuario`),
* **Quiero** autenticarme ingresando mi número de documento y contraseña,
* **Para** acceder de forma segura a las funciones y módulos autorizados para mi rol.
* **Requisitos cubiertos:** `RF01`, `RF02`, `RF05`, `RF06`
* **Criterios de Aceptación:**
  * **CA01.1:** Dado un número de documento y contraseña válidos, el sistema inicia la sesión y redirige al Dashboard correspondiente al rol.
  * **CA01.2:** Si las credenciales son incorrectas, el sistema muestra un mensaje de error sin revelar si el dato fallido fue el usuario o la clave.
  * **CA01.3:** Tras múltiples intentos fallidos consecutivos, el sistema bloquea temporalmente el acceso desde la IP solicitante por medio de Rate Limiting.
  * **CA01.4:** Al presionar "Cerrar Sesión", la sesión del servidor se destruye y se redirige a la vista de login.

#### HU02 - Sugerencia y Validación de Contraseña Inicial (Módulo: Autenticación)
* **Como** usuario que inicia sesión por primera vez con su número de documento como contraseña,
* **Quiero** recibir una notificación para actualizar mi clave personal o poder omitirla en dicha sesión,
* **Para** resguardar la confidencialidad de mi cuenta y mis operaciones comerciales.
* **Requisitos cubiertos:** `RF03`, `RF04`
* **Criterios de Aceptación:**
  * **CA02.1:** Si la contraseña actual coincide con el documento de identidad, el sistema muestra un modal/banner sugiriendo el cambio de contraseña con opción de omitir por esa sesión.
  * **CA02.2:** La nueva contraseña debe tener mínimo 6 caracteres, coincidir con su confirmación y ser distinta al documento de identidad.

---

### 1. 🎛️ Módulo: Dashboard

#### HU03 - Visualización del Panel de Control Personalizado (Módulo: Dashboard)
* **Como** usuario autenticado en la plataforma,
* **Quiero** ver un panel inicial con contadores de gestión y accesos rápidos según mi rol,
* **Para** obtener un resumen inmediato de la operación y acceder ágilmente a mis tareas prioritarias.
* **Requisitos cubiertos:** `RF07`, `RF08`, `RF09`
* **Criterios de Aceptación:**
  * **CA03.1:** Para rol `usuario`, las tarjetas de métricas (total cotizaciones, cotizaciones del mes) y la tabla de actividad reciente muestran exclusivamente las operaciones de su autoría.
  * **CA03.2:** Para roles `admin` y `compras`, las métricas consolidan la información global de toda la empresa.
  * **CA03.3:** El panel muestra accesos directos únicamente a los módulos a los que el rol activo tiene autorización.

---

### 2. ➕ Módulo: Nueva Cotización

#### HU04 - Elaboración de Cotizaciones y Calculadora Comercial (Módulo: Nueva Cotización)
* **Como** asesor comercial,
* **Quiero** seleccionar productos del catálogo o agregarlos manualmente si son nuevos, ingresando costo de proveedor, flete, utilidad, calibración y estampillas,
* **Para** calcular automáticamente los precios de venta unitarios y generar una propuesta comercial con su respectivo consecutivo mensual.
* **Requisitos cubiertos:** `RF10`, `RF11`, `RF12`, `RF13`, `RF19`
* **Criterios de Aceptación:**
  * **CA04.1:** El usuario puede buscar productos del catálogo o escribir manualmente los datos de un producto no registrado.
  * **CA04.2:** El sistema calcula el valor unitario sumando al costo del proveedor el margen de utilidad configurado y los recargos operacionales (flete, calibración, estampillas).
  * **CA04.3:** El usuario puede editar o eliminar ítems de la lista antes de guardar la cotización.
  * **CA04.4:** Al guardar, se genera el consecutivo mensual con las iniciales del asesor (ej. `EB-10-01`) y se registran los días de validez y condición de pago.

#### HU05 - Generación de Documento PDF Oficial y Hoja de Respaldo (Módulo: Nueva Cotización)
* **Como** asesor comercial,
* **Quiero** descargar el PDF corporativo para el cliente y consultar la Hoja de Respaldo interna,
* **Para** remitir una oferta formal impecable y contar con la auditoría de costos y márgenes de ganancia.
* **Requisitos cubiertos:** `RF18`, `RF20`, `RF21`
* **Criterios de Aceptación:**
  * **CA05.1:** El PDF oficial para el cliente contiene la marca institucional, datos del cliente, condiciones comerciales y precios finales sin revelar costos de proveedor ni márgenes internos.
  * **CA05.2:** La Hoja de Respaldo (disponible en vista/impresión interna) desglosa el costo base del proveedor, utilidades proyectadas y costo logístico de cada ítem.
  * **CA05.3:** El sistema permite exportar el detalle de la cotización a formato Excel de manera estructurada.

---

### 3. 🔍 Módulo: Consultar Cotizaciones

#### HU06 - Ajuste de Precios y Creación de Revisiones (Módulo: Consultar Cotizaciones)
* **Como** asesor comercial (o usuario `admin`/`compras`),
* **Quiero** modificar una cotización existente mediante un ajuste directo o mediante la creación de una nueva revisión,
* **Para** corregir precios sobre la misma propuesta o conservar el histórico de versiones de una negociación en curso.
* **Requisitos cubiertos:** `RF14`, `RF15`
* **Criterios de Aceptación:**
  * **CA06.1:** Bajo la opción "Ajustar", el sistema permite modificar ítems y precios manteniendo exactamente el mismo número consecutivo original.
  * **CA06.2:** Bajo la opción "Nueva Revisión", el sistema crea una copia de la cotización agregando el sufijo de versión (`_01`, `_02`...).
  * **CA06.3:** Un asesor comercial solo puede ajustar o crear revisiones sobre cotizaciones de su autoría; los roles `admin` y `compras` pueden hacerlo sobre cualquier cotización.

#### HU07 - Trazabilidad de Estados Comerciales y Logística de Entrega (Módulo: Consultar Cotizaciones)
* **Como** usuario del sistema,
* **Quiero** actualizar el estado comercial (*Pendiente*, *Concluida*, *Descartada*) y el estado de entrega (*Por despachar*, *En camino*, *Entregado*),
* **Para** dar seguimiento al embudo comercial y medir el tiempo real en días transcurrido hasta la entrega efectiva.
* **Requisitos cubiertos:** `RF16`, `RF17`
* **Criterios de Aceptación:**
  * **CA07.1:** El cambio de estado comercial se actualiza en tiempo real registrando fecha y hora del cambio.
  * **CA07.2:** Al cambiar el estado de entrega a *Entregado*, el sistema calcula y almacena la diferencia en días desde la fecha de emisión de la cotización.

#### HU08 - Búsqueda Avanzada y Eliminación Controlada de Cotizaciones (Módulo: Consultar Cotizaciones)
* **Como** usuario de la plataforma,
* **Quiero** filtrar cotizaciones por número, cliente, asesor o rango de fechas, y permitir su eliminación si tengo perfil directivo,
* **Para** consultar rápidamente el historial comercial y depurar registros erróneos o duplicados.
* **Requisitos cubiertos:** `RF22`, `RF23`
* **Criterios de Aceptación:**
  * **CA08.1:** El sistema filtra las cotizaciones en tiempo real actualizando los contadores por estado (*Pendientes*, *Concluidas*, *Descartadas*).
  * **CA08.2:** El botón y acción de eliminación de cotizaciones está habilitado exclusivamente para los roles `admin` y `compras`.

---

### 4. 🛒 Módulo: Órdenes de Compra

#### HU09 - Emisión de Órdenes de Compra Agrupadas por Proveedor desde Cotización (Módulo: Órdenes de Compra)
* **Como** asesor comercial o encargado de compras,
* **Quiero** generar órdenes de compra oficiales a partir de una cotización en estado pendiente,
* **Para** tramitar la compra de los insumos asegurando que cada orden contenga únicamente los ítems de un mismo proveedor.
* **Requisitos cubiertos:** `RF24`, `RF25`, `RF32`
* **Criterios de Aceptación:**
  * **CA09.1:** El sistema solo permite emitir órdenes de compra sobre cotizaciones cuyo estado comercial sea *Pendiente*.
  * **CA09.2:** Si la cotización está *Concluida* o *Descartada*, el sistema bloquea y no muestra la opción de generar orden de compra.
  * **CA09.3:** Al emitir la compra, el sistema agrupa los productos cotizados por proveedor, generando una orden independiente por cada proveedor involucrado.

#### HU10 - Creación de Órdenes Directas con Productos de Catálogo (Módulo: Órdenes de Compra)
* **Como** encargado de compras o asesor comercial,
* **Quiero** elaborar una orden de compra directa de mostrador seleccionando productos del catálogo general,
* **Para** reabastecer stock o realizar compras a proveedores sin necesidad de haber emitido una cotización previa.
* **Requisitos cubiertos:** `RF24`, `RF33`
* **Criterios de Aceptación:**
  * **CA10.1:** En la creación de órdenes directas, el sistema exige que los ítems agregados provengan del catálogo oficial de productos para asegurar la consistencia del catálogo.
  * **CA10.2:** La orden directa genera su consecutivo oficial de orden de compra (P.O.) y se asocia al proveedor seleccionado.

#### HU11 - Visor PDF, Exportación Individual/Masiva en PDF/Excel y Ajuste de Órdenes (Módulo: Órdenes de Compra)
* **Como** usuario autorizado (`admin`, `compras` o autor),
* **Quiero** previsualizar las órdenes en un visor PDF interactivo, exportarlas de forma individual o masiva a PDF y Excel, y realizar ajustes directos conservando el consecutivo original,
* **Para** enviar la orden formal al proveedor, alimentar reportes externos y corregir ítems sin alterar el número de control.
* **Requisitos cubiertos:** `RF28`, `RF29`, `RF30`, `RF31`
* **Criterios de Aceptación:**
  * **CA11.1:** El visor PDF interactivo permite ver, imprimir y descargar la orden de compra con el formato institucional de Impobiomedical.
  * **CA11.2:** El sistema permite seleccionar una o múltiples órdenes para su descarga consolidada en archivo PDF o exportación a Excel.
  * **CA11.3:** La función "Ajustar Orden" permite modificar cantidades y precios conservando el mismo identificador P.O. original, restringida al autor de la orden o a roles `admin`/`compras`.

#### HU12 - Seguimiento de Estados y Eliminación de Órdenes (Módulo: Órdenes de Compra)
* **Como** usuario con rol `admin` o `compras`,
* **Quiero** gestionar el estado de las órdenes (Pendiente / Completada) con contadores visuales y eliminar órdenes canceladas,
* **Para** mantener actualizado el control del aprovisionamiento logístico.
* **Requisitos cubiertos:** `RF26`, `RF27`
* **Criterios de Aceptación:**
  * **CA12.1:** Los contadores de la vista clasifican y cuantifican en tiempo real las órdenes pendientes y completadas.
  * **CA12.2:** Únicamente los usuarios con rol `admin` o `compras` pueden alternar el estado de la orden o eliminarla físicamente.

---

### 5. 🏢 Módulo: Directorio Clientes

#### HU13 - Gestión y Búsqueda de Clientes (Módulo: Directorio Clientes)
* **Como** asesor comercial o usuario autorizado,
* **Quiero** consultar, registrar y editar clientes, o buscarlos por nombre/NIT al cotizar para cargar sus datos (o digitarlos manualmente),
* **Para** agilizar la formulación de propuestas comerciales asegurando que solo el Administrador pueda eliminarlos.
* **Requisitos cubiertos:** `RF38`, `RF39`
* **Criterios de Aceptación:**
  * **CA13.1:** Cualquier usuario autenticado puede crear y modificar información de clientes (NIT, nombre, contacto, ubicación, correo).
  * **CA13.2:** En el formulario de cotización, la búsqueda predictiva carga automáticamente los datos del cliente al seleccionarlo, permitiendo también el ingreso manual si es un cliente nuevo.
  * **CA13.3:** El botón y endpoint de eliminación de clientes está restringido exclusivamente al rol `admin`.

---

### 6. 🚚 Módulo: Proveedores

#### HU14 - Búsqueda en Tiempo Real, Carga y Detección Automática de Proveedores (Módulo: Proveedores)
* **Como** usuario autorizado (Asesor o Compras),
* **Quiero** buscar proveedores por NIT o Nombre en tiempo real, cargar sus datos comerciales y bancarios (o ingresarlos manualmente), y que el sistema auto-clasifique y auto-registre nuevos proveedores desde órdenes de compra,
* **Para** agilizar el aprovisionamiento de insumos y mantener centralizado el directorio comercial protegiendo su eliminación.
* **Requisitos cubiertos:** `RF45`, `RF46`, `RF47`, `RF48`, `RF49`
* **Criterios de Aceptación:**
  * **CA14.1:** La búsqueda de proveedores por NIT o Razón Social responde en tiempo real mientras el usuario digita.
  * **CA14.2:** El sistema permite seleccionar un proveedor para autocompletar sus datos bancarios y comerciales en cotizaciones u órdenes, o digitarlos manualmente si no está en la base.
  * **CA14.3:** Al procesar una orden de compra, el sistema clasifica al proveedor como `Nuevo` (primera compra) o `Registrado` (compras previas) y lo registra en el directorio general si no existía.
  * **CA14.4:** La eliminación física o desactivación de proveedores está reservada exclusivamente al rol `admin`.

---

### 7. 📦 Módulo: Catálogo Productos

#### HU15 - Consulta, Registro y Búsqueda Instantánea de Productos (Módulo: Catálogo Productos)
* **Como** usuario del sistema,
* **Quiero** consultar la lista de productos médicos, registrarlos con su fotografía e información comercial, y buscarlos en tiempo real por código o nombre,
* **Para** disponer de una base de productos estandarizada y agilizar el armado de cotizaciones.
* **Requisitos cubiertos:** `RF34`, `RF35`, `RF36`
* **Criterios de Aceptación:**
  * **CA15.1:** El catálogo permite el filtrado predictivo instantáneo por código o descripción.
  * **CA15.2:** Al subir imágenes para productos médicos, el sistema valida que los formatos sean válidos (JPG, PNG, WebP).
  * **CA15.3:** Los usuarios `usuario` y `compras` pueden crear y editar productos médicos del catálogo.

#### HU16 - Modalidades de Exportación a PDF y Restricción de Eliminación (Módulo: Catálogo Productos)
* **Como** usuario comercial (o Administrador),
* **Quiero** exportar el catálogo a PDF (completo, por categoría o por selección manual de productos) y salvaguardar los registros restringiendo la eliminación al Administrador,
* **Para** compartir portafolios comerciales personalizados con los clientes y evitar borrados accidentales.
* **Requisitos cubiertos:** `RF34`, `RF37`
* **Criterios de Aceptación:**
  * **CA16.1:** El sistema ofrece tres opciones de exportación a PDF: catálogo completo, filtrado por una categoría seleccionada, o únicamente los productos seleccionados mediante casillas de verificación.
  * **CA16.2:** La acción de eliminación física de productos valida estrictamente el rol `admin`, bloqueando cualquier intento por parte de otros roles.

---

### 8. 👥 Módulo: Gestión Usuarios

#### HU17 - Administración de Cuentas y Restablecimiento de Credenciales (Módulo: Gestión Usuarios)
* **Como** Administrador del sistema,
* **Quiero** crear usuarios, asignar su rol (`admin`, `compras`, `usuario`) y prefijo de asesor, y restablecer contraseñas asignando temporalmente el documento de identidad,
* **Para** gestionar el acceso de los empleados a la plataforma y proporcionar soporte técnico ante olvido de claves.
* **Requisitos cubiertos:** `RF40`, `RF41`
* **Criterios de Aceptación:**
  * **CA17.1:** El acceso al módulo está bloqueado para roles distintos a `admin`.
  * **CA17.2:** Cada usuario creado cuenta con nombres, documento, rol y código de asesor (usado como prefijo en consecutivos de cotización).
  * **CA17.3:** La opción de restablecimiento asigna el documento del usuario como su contraseña temporal y activa la sugerencia de cambio de clave en su próximo inicio de sesión.

---

### 9. 📊 Módulo: Estadísticas

#### HU18 - Indicadores Analíticos y Gráficos de Rendimiento Comercial (Módulo: Estadísticas)
* **Como** Administrador del sistema,
* **Quiero** visualizar indicadores financieros (total cotizado vs. ventas reales en órdenes), gráficos de clientes y rendimiento por asesor, y exportar reportes consolidados a PDF,
* **Para** evaluar la efectividad comercial del equipo y tomar decisiones directivas respaldadas en datos.
* **Requisitos cubiertos:** `RF42`, `RF43`, `RF44`
* **Criterios de Aceptación:**
  * **CA18.1:** El módulo calcula y compara el valor total cotizado frente a las ventas concluidas reales.
  * **CA18.2:** Se presentan gráficos analíticos con los clientes con mayor facturación, evolución de ventas y efectividad por asesor comercial.
  * **CA18.3:** El sistema genera un reporte ejecutivo en formato PDF con diseño institucional.

---

### 🎨 Transversal: Usabilidad y Soporte

#### HU19 - Campos Obligatorios, Ayuda Contextual y Notificaciones (Módulo: Usabilidad y Soporte)
* **Como** usuario del sistema,
* **Quiero** identificar fácilmente los campos requeridos con asterisco rojo, disponer de un botón de ayuda con el manual operativo y recibir alertas claras,
* **Para** operar la plataforma con fluidez, sin ambigüedades y conociendo de inmediato cómo resolver cualquier error de validación.
* **Requisitos cubiertos:** `RF50`, `RF51`, `RF52`
* **Criterios de Aceptación:**
  * **CA19.1:** Todos los formularios marcan los campos mandatorios con un asterisco de color rojo visible.
  * **CA19.2:** El botón `[ ? Ayuda ]` en la barra superior despliega el manual de usuario correspondiente a las funciones del rol activo.
  * **CA19.3:** Las validaciones de negocio presentan alertas informativas que indican con precisión la causa y la acción correctiva.

