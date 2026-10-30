# 📚 Manual de Usuario — Sistema Impobiomedical

**Versión del sistema**: v3.6.3 (Edición Comercial, Proveedores y Operativa)  
**Autor y Titular**: Santiago Lizcano  
**Público objetivo**: Asesores comerciales y administradores de Impobiomedical

---

## 📋 Tabla de Contenidos

1. [Acceso al Sistema](#1-acceso-al-sistema)
2. [Dashboard Principal](#2-dashboard-principal)
3. [Nueva Cotización (Flujo en 2 Pasos)](#3-nueva-cotización-flujo-en-2-pasos)
4. [Consultar Cotizaciones y Revisiones](#4-consultar-cotizaciones-y-revisiones)
5. [Órdenes de Compra (P.O.)](#5-órdenes-de-compra-po)
6. [Gestión de Clientes](#6-gestión-de-clientes)
7. [Gestión de Proveedores](#7-gestión-de-proveedores)
8. [Catálogo de Productos (Eliminación Solo Admin)](#8-catálogo-de-productos-eliminación-solo-admin)
9. [Gestión de Usuarios (Solo Admin)](#9-gestión-de-usuarios-solo-admin)
10. [Estadísticas y Reportes (Solo Admin)](#10-estadísticas-y-reportes-solo-admin)
11. [Botón de Ayuda Rápida](#11-botón-de-ayuda-rápida)
12. [Preguntas Frecuentes](#12-preguntas-frecuentes)

---

## 1. Acceso al Sistema

### Iniciar sesión
1. Abrir el navegador y dirigirse a la URL del sistema.
2. Ingresar su **número de documento** y **contraseña** (o su documento como contraseña inicial).
3. Hacer clic en **"Ingresar al Sistema"**.
4. *(Opcional)*: Use el ícono 👁️ para mostrar u ocultar su contraseña digitada.

### Sesión expirada
Si el sistema permanece **inactivo por más de 1 hora**, la sesión se cerrará automáticamente por seguridad.

---

### Roles del Sistema y Niveles de Acceso
El sistema cuenta con **3 roles de usuario** claramente definidos:
1. **Administrador (`admin`):**
   * **Alcance:** Control total y sin restricciones de la plataforma.
   * **Permisos exclusivos:** Gestión de usuarios/contraseñas, estadísticas ejecutivas gerenciales, y potestad exclusiva de eliminación física o *Soft Delete* en **Clientes**, **Proveedores** y **Productos**.
   * **Visibilidad:** Global (ve y puede gestionar/eliminar cotizaciones y órdenes de todos los asesores).
2. **Encargado de Compras (`compras`):**
   * **Alcance:** Gestión operativa comercial y de abastecimiento de la empresa.
   * **Permisos:** Acceso completo a cotizaciones, órdenes de compra, directorios de clientes, proveedores y catálogo de productos (creación y edición).
   * **Visibilidad y Eliminación:** *Visibilidad global* (ve las cotizaciones y órdenes de compra emitidas por todos los asesores) y **cuenta con permisos para eliminar cotizaciones y órdenes de compra** en cualquier estado. Está restringido de los módulos de Gestión de Usuarios y Estadísticas Gerenciales.
3. **Asesor Comercial (`usuario`):**
   * **Alcance:** Asesor comercial estándar.
   * **Permisos:** Crear cotizaciones, emitir órdenes de compra, registrar clientes y consultar catálogos.
   * **Aislamiento de Datos:** Estricto aislamiento por propietario (`usuario_id`). Solo visualiza, ajusta o genera versiones de sus propias cotizaciones y órdenes. **No tiene permisos para eliminar cotizaciones, órdenes ni catálogos maestros**.

---

## 2. Dashboard Principal

### 1. Banner de Bienvenida y Accesos Rápidos
Presenta el saludo personalizado con el nombre del usuario conectado, su rol y botones de acceso directo para agilizar la jornada laboral:
* **Nueva Cotización:** Permite iniciar de inmediato la formulación de una nueva propuesta comercial.
* **Órdenes de Compra:** Lleva directamente al listado y gestión de órdenes de compra (P.O.).

### 2. Tarjetas de Indicadores Clave (KPI)
Contadores actualizados en tiempo real según el rol del usuario conectado:

| Tarjeta | Administrador (`admin`) | Encargado de Compras (`compras`) | Asesor Comercial (`usuario`) |
|---|---|---|---|
| **Cotizaciones Totales** | Todas las registradas en el sistema | Todas las registradas en el sistema | Solo las creadas por el asesor |
| **Cotizaciones este Mes** | Conteo consolidado del mes | Conteo consolidado del mes | Consecutivo mensual propio |
| **Clientes Registrados** | Total entidades de salud | Total entidades de salud | Total entidades de salud |
| **Productos en Catálogo** | Total catálogo médico | Total catálogo médico | Total catálogo médico |

### 3. Listas de Actividad Reciente
En la parte inferior del panel se disponen dos bloques informativos de consulta rápida:
* **Últimos Productos del Catálogo:** Muestra los 5 productos médicos ingresados recientemente con su fotografía, código, nombre y categoría, incluyendo el enlace directo **«Ver catálogo →»** para consultar la lista completa.
* **Últimas Cotizaciones:** Muestra las 5 cotizaciones más recientes con su número, cliente, asesor y fecha de emisión, con el enlace **«Ver todas →»** para acceder a la consulta detallada. *(Para los asesores comerciales, esta lista muestra únicamente sus propias cotizaciones recientes)*.

---

## 3. Nueva Cotización (Flujo en 2 Pasos)

### Paso 1: Agregar Productos (Ítems)
* **Buscar del Catálogo:** Digite el nombre del producto en el buscador en vivo y seleccione la opción deseada para autocompletar nombre, código, marca, modelo, descripción técnica, imagen y categoría.
* **Ingreso Manual:** Puede registrar productos o servicios médicos personalizados completando los campos obligatorios: **Nombre del Producto**, **Marca**, **Modelo**, cantidad y descripción.
* **Calculadora de Ganancias Dinámica:** Permite ingresar el precio base del proveedor y calcular utilidad, flete, calibración y estampillas. El valor resultante se establece como el precio unitario del producto para la cotización.
* **Búsqueda Predictiva de Proveedor:** En el ítem puede buscar el proveedor por Nombre o NIT; el sistema presentará sugerencias limpias con opción de selección directa.
* **Agregar:** Presione **"Agregar a Cotización"** para almacenar temporalmente el ítem. Los borradores y productos agregados quedan preservados inteligentemente.

### Paso 2: Datos del Cliente y Finalización
1. Haga clic en **"Continuar → Datos Cliente y PDF"**.
2. **Seleccionar Cliente:** Busque por NIT o Nombre para autocompletar automáticamente ciudad, departamento, dirección, contacto y correo (o ingrese los datos manualmente si es un cliente nuevo que aún no está registrado).
3. **Condiciones Comerciales:** Ingrese forma de pago (Contado, 30 días, etc.) y días de validez.
4. **Finalizar:** Clic en **"Finalizar Cotización"**. El sistema calcula el consecutivo mensual de forma continua (`01`, `02`, `03`...) según el código del asesor y abre el visor PDF.

---

## 4. Consultar Cotizaciones y Revisiones

* **Pestañas (Tabs) de Estado:** Navegue entre *Cotizaciones Pendientes*, *Cotizaciones Concluidas*, *Cotizaciones Descartadas* y *Todas las Cotizaciones*.
* **Filtro Avanzado por Rango de Fechas:** Ingrese fecha **Desde** y **Hasta** para acotar la consulta. Los tags numéricos en cada pestaña se actualizan de forma reactiva reflejando exactamente el número de cotizaciones comprendidas en ese intervalo. Para volver al histórico general, use el botón de limpiar filtros (✖).
* **Estados Comerciales en Vivo:** Al cambiar de *Pendiente* a *Concluida* o *Descartada*, se registra y muestra la fecha y hora del cambio (`d/m/Y H:i`). En cotizaciones históricas donde dicha fecha no existía, se preserva el estado *"Sin cambio"*.
* **Control Logístico de Estado de Entrega:** Cada cotización incluye un menú desplegable en vivo para monitoreo de despachos:
  * 🟡 **Pendiente:** Estado inicial ("Por despachar").
  * 🔵 **En Tránsito:** Producto despachado ("En camino").
  * 🟢 **Entregado:** Registra automáticamente la fecha y hora de entrega y calcula los días transcurridos hasta la entrega a partir de la fecha de cierre (o desde su creación si no hubo cambio previo).
* **Ver PDF:** Abre el PDF formal para el cliente en un visor emergente interactivo con opción de descarga de PDF.
* **Descargar Excel:** Botón verde `[ 📊 Excel ]` para descargar de inmediato la cotización completa en formato tabular `Excel (.xlsx)` estructurado (sin imágenes para máxima velocidad incluso con catálogos extensos).
* **Hoja de Respaldo:** Consulta interna confidencial con los costos de proveedor y márgenes para auditoría.
* **Modificar Cotización (Ajustar vs. Nueva Versión / Revisión):** Al hacer clic en ✏️ **Modificar**, el sistema despliega una ventana modal interactiva con dos caminos de edición según la necesidad comercial:
  * **🛠️ Ajustar Cotización (Mismo número):**
    * **Propósito:** Corregir errores tipográficos, ajustar precios, cambiar cantidades, reemplazar ítems o actualizar datos del cliente.
    * **Comportamiento:** Modifica directamente la cotización actual **preservando su número de cotización original (ej: `EB 01`)**. No crea registros duplicados, no incrementa el consecutivo mensual de cotizaciones y actualiza tanto el PDF del cliente como la Hoja de Respaldo interna.
    * **Guardado Dinámico y Permanencia:** Al presionar **«Guardar Ajuste»**, los cambios se guardan de inmediato en la cotización oficial, el sistema regenera un nuevo clon sincronizado y **permaneces en la misma pantalla de edición** con una alerta verde de confirmación (*«Cotización actualizada con éxito. Puedes verificar los cambios en Consultar Cotizaciones.»*). Puedes seguir haciendo cambios y guardando avances tantas veces como requieras.
    * **Cómo Salir:** Cuando hayas terminado o si decides no guardar cambios adicionales, pulsa el botón **«Cancelar Ajuste»** o **Ir a Otro Modulo**; esto cerrará el clon de trabajo de forma segura.
    * **Cuándo usarlo:** Cuando se trata de la misma negociación y se cometió un error o el cliente solicitó un pequeño cambio previo al cierre sin requerir histórico.
  * **📑 Nueva Versión / Revisión (Clon con sufijo `_01`, `_02`):**
    * **Propósito:** Generar una propuesta comercial evolucionada manteniendo el historial de la versión anterior para trazabilidad y auditoría.
    * **Comportamiento:** Clona la cotización conservando la original **completamente intacta** en el listado y genera una versión derivada con sufijo secuencial (ejemplo: si la original es `EB 01`, la nueva versión será `EB 01_01`, y la siguiente `EB 01_02`).
    * **Restricción de seguridad:** Si una cotización ya es una revisión (contiene `_`), la opción de "Nueva Versión" se deshabilita para evitar cadenas infinitas de versiones anidadas; en ese caso se debe modificar la original o usar la opción *Ajustar*.
* **Emitir Orden:** Botón directo para pasar los ítems cotizados a una Orden de Compra formal (habilitado únicamente para cotizaciones en estado pendiente).
* **Eliminar Cotización:** Disponible para usuarios con rol `admin` o `compras` independientemente del estado de la cotización.

---

## 5. Órdenes de Compra (P.O.)

* **Emisión:** Desde consultar cotizaciones, haga clic en 🛒 **Orden** en propuestas pendientes. Seleccione únicamente los ítems a comprar al proveedor y complete los datos bancarios, tributarios y de contacto (celular y correo electrónico, los cuales se autocompletan predictivamente desde el directorio oficial si el proveedor ya existe). Si la cotización tiene varios proveedores, el sistema detecta y sincroniza automáticamente al proveedor del ítem que marques (o puedes filtrar por proveedor con los botones superiores).
* **Saldo a Favor:** Si la empresa cuenta con un anticipo o saldo a favor con el proveedor, puede ingresarlo en el campo opcional **"Saldo a Favor ($)"**. El sistema lo deducirá automáticamente del valor neto en tiempo real tanto en el resumen de pantalla como en el PDF oficial de la orden (`SALDO A FAVOR: - $X`), asegurando que el total a pagar nunca sea negativo.
* **Sincronización Automática de Proveedor:** Al emitir o ajustar una orden, cualquier cambio o dato complementario en su celular, correo, banco o datos fiscales actualiza automáticamente la ficha maestra del proveedor en la base de datos para futuras operaciones.
* **Órdenes de Mostrador (Directas):** Permite emitir compras a proveedores sin necesidad de una cotización previa pulsando **"+ Nueva Orden Mostrador"**. Cuenta con buscador predictivo conectado al catálogo oficial para añadir productos homologados, búsqueda dual interactiva por Razón Social o NIT, campos para celular, correo y saldo a favor, y auto-registra automáticamente al proveedor en el directorio central si aún no existe.
* **Ajustar Orden de Compra:** En la tabla de consulta de órdenes, pulse el botón 🛠️ **Ajustar** en la fila de la orden correspondiente. El sistema reabrirá la orden permitiendo modificar ítems, cantidades, datos tributarios, flete, retenciones, saldo a favor o descuentos, **conservando intacto el mismo consecutivo P.O.** tras guardar.
* **Cancelación de Ajuste:** Si decide no realizar modificaciones, el botón **"Cancelar Ajuste"** restablece la orden original sin aplicar ningún cambio.
* **Visor y Descarga PDF:** Al guardar o ajustar una orden, el sistema lo llevará a la tabla de consulta de órdenes, donde podrá previsualizar en cualquier momento el documento oficial en un visor modal interactivo para descargarlo en formato PDF.
* **Gestión y Clasificación:** Controle órdenes pendientes y completadas con contadores en tiempo real. Cada orden clasifica cronológicamente al proveedor como `🟡 Nuevo` si corresponde a su primera compra emitida o `🟢 Registrado` a partir de su segunda orden.
* **Selección y Exportación Multipágina:** Casillas de selección múltiple con persistencia automática a través de la paginación (puede seleccionar órdenes de la página 1, navegar a la página 2 y continuar marcando). Barra superior interactiva con contador consolidado, botón para limpiar selección y exportación masiva a **PDF** o **Excel (.xls)**.

---

## 6. Gestión de Clientes

Directorio centralizado de instituciones de salud:
* **Búsqueda en Vivo:** Filtrado predictivo por nombre, NIT o municipio con botón de limpieza instantánea `[X]`.
* **Creación y Edición:** Registro con validación de NIT único para evitar duplicados institucionales.
* **Uso en Cotizaciones:** La base de clientes alimenta la búsqueda en vivo al cotizar para cargar automáticamente todos los datos, permitiendo también digitar un cliente nuevo directamente en la cotización.
* **Eliminación Inteligente y Reactivación (Exclusivo Administrador):**
  * Si el cliente tiene cotizaciones asociadas, el sistema aplica una **desactivación preventiva**: el cliente queda en estado *inactivo* y deja de aparecer en nuevas búsquedas, pero se resguarda íntegramente todo el historial comercial previo.
  * Si el cliente no cuenta con ninguna cotización asociada (0 registros), el sistema permite su **eliminación definitiva** de la base de datos.
  * **Botón Reactivar:** Para cualquier cliente que haya sido desactivado, se presenta un botón verde en la tabla que permite devolverlo al estado activo de forma inmediata en un solo clic.

---

## 7. Gestión de Proveedores

Directorio centralizado de proveedores comerciales y técnicos:
* **Consulta y Búsqueda en Vivo:** Localice rápidamente proveedores por NIT, Nombre o Banco sin necesidad de presionar Enter y botón de limpieza rápida `[X]`.
* **Nuevo Proveedor:** Modal para registrar NIT, Razón Social, Tipo de Contribuyente, datos bancarios (Banco, Tipo de Cuenta y Número) y canales de contacto directo (Celular / Teléfono y Correo Electrónico).
* **Edición Rápida y Sincronización:** Actualice información comercial, de contacto o bancaria desde el botón de lápiz o directamente al emitir/ajustar órdenes de compra.
* **Uso en Cotizaciones y Órdenes:** Permite asociar el proveedor al cotizar y abastece automáticamente sus datos bancarios y de contacto al emitir órdenes de compra. Si en una orden se ingresa un proveedor nuevo o se corrigen sus datos, el sistema auto-registra o sincroniza su ficha maestra en este directorio en tiempo real.
* **Eliminación Inteligente y Reactivación (Exclusivo Administrador):**
  * Si el proveedor posee cotizaciones u órdenes de compra vinculadas, el sistema ejecuta una **desactivación**: se oculta de nuevas compras pero protege todas las órdenes y cotizaciones históricas asociadas.
  * Si el proveedor no tiene transacciones asociadas, se habilita su **eliminación física completa**.
  * **Botón Reactivar:** Los proveedores inactivos muestran el botón verde para reincorporarlos al directorio activo al instante.
* **Normalización de NIT:** El sistema limpia automáticamente puntos y espacios al escribir, manteniendo intacto el formato con guion de verificación (ej: `900535843-3`).

---

## 8. Catálogo de Productos (Eliminación Solo Admin)

Directorio y catálogo central de equipamiento y dispositivos médicos:
* **Visualización en Tarjetas y Filtros:** Muestra los productos en cuadrícula con su foto, título, código, marca, modelo y distintivo de IVA. Permite filtrar instantáneamente por botones de categoría que indican la cantidad de productos disponibles (ej: *Todos*, *Equipo Médico*), o buscar en vivo por nombre/código con botón de limpieza rápida `[X]`.
* **Creación y Edición Modal:** Botón `[ + Nuevo Producto ]` y botón de lápiz `[ ✏️ ]` en cada tarjeta para registrar o modificar datos comerciales, descripción técnica y cargar fotografías médicas (formatos JPG, PNG, WebP con tamaño máximo de 8MB).
* **Uso en Cotizaciones y Órdenes:** El catálogo abastece la búsqueda rápida al crear cotizaciones (autocompletando foto, marca y especificaciones) y es la fuente oficial y obligatoria para emitir Órdenes Directas de mostrador.
* **Exportación PDF (3 Modalidades):**
  * **Catálogo Completo:** Botón superior `[ PDF ]` cuando se visualizan todos los productos.
  * **Por Categoría:** Al seleccionar una categoría, el botón se adapta automáticamente a `[ PDF Categoría ]` para descargar solo esa línea.
  * **Selección Manual:** Casilla circular en la esquina superior de cada tarjeta para elegir productos específicos, activando una barra flotante inferior con la cantidad seleccionada y el botón de exportación puntual.
* **Eliminación Inteligente y Reactivación (Exclusivo Administrador):**
  * Si el producto se encuentra presente en cotizaciones u órdenes históricas, el sistema impide su borrado físico y aplica una **desactivación**: deja de aparecer en nuevas búsquedas pero protege el historial anterior.
  * Si el producto no tiene transacciones asociadas (0 registros), se permite su **eliminación física definitiva**.
  * **Botón Reactivar:** Los productos inactivos muestran el botón verde para devolverlos al catálogo activo de forma inmediata.

---

## 9. Gestión Administrativa y de Usuarios (Solo Admin)

* **Visualización en Tarjetas y Búsqueda:** Muestra a los usuarios en cuadrícula con su avatar, estado (*Activo/Inactivo*), nombre, cargo, rol, código de cotización, correo y teléfono. Incluye buscador rápido por nombre, cargo o rol con botón de limpieza `[X]`.
* **Creación y Edición Modal:** Botón `[ + Nuevo Usuario ]` y botón `[ ✏️ ]` para registrar o modificar datos personales, cargo, código único de cotización (ej: `EB`, `SL`) y rol (*Administrador*, *Encargado de Compras*, *Asesor*).
* **Contraseñas y Accesos:** Asignación inicial de contraseña (por defecto el número de documento si se deja vacía). El Administrador cuenta con dos mecanismos rápidos para restablecer la clave al documento del usuario: el botón de acceso directo con ícono de llave dorada en la tarjeta del usuario, y el enlace interactivo *Restablecer al documento* dentro del formulario de edición. Al iniciar sesión con su documento, el sistema sugiere actualizar la contraseña.
* **Políticas Centralizadas de Soft Delete y Protección de Integridad:**
  * **Clientes, Proveedores y Productos:** La eliminación es potestad exclusiva del Administrador y opera mediante *Soft Delete* (inactivación lógica). Esto garantiza que ninguna cotización, hoja de respaldo u orden de compra histórica pierda integridad, descripciones ni cálculos contables.
  * **Usuarios y Asesores:** La eliminación se realiza mediante un modal inteligente interactivo: si el usuario ya cuenta con cotizaciones finalizadas vinculadas, el sistema despliega la advertencia de integridad comercial con el botón *Desactivar Usuario*, pasando su estado a **Inactivo** y suspendiendo su acceso pero salvaguardando la trazabilidad de sus ventas. Si el usuario tiene 0 cotizaciones asociadas, se presenta el modal de advertencia crítica con el botón *Sí, eliminar* para borrar el registro de forma definitiva. Cualquier usuario inactivo puede ser reincorporado inmediatamente pulsando el botón verde *Reactivar*.

---

## 10. Estadísticas y Reportes (Solo Admin)

* **Filtro de Fechas y Tarjetas KPI:** Barra superior con selectores de fecha **Desde** y **Hasta** (con botón de limpiar) que actualiza en tiempo real los gráficos y los 4 indicadores clave: Monto Cotizado, Monto Vendido (ventas reales concluidas), Cotizaciones Totales y Órdenes de Compra emitidas.
* **Gráficos Analíticos Interactivos (Chart.js):**
  * **Evolución Mensual:** Barras comparativas de cotizaciones totales vs. concluidas, con menú desplegable para filtrar el rendimiento de un asesor específico o de todo el equipo.
  * **Top 5 Productos Cotizados:** Gráfico circular (dona) con los dispositivos médicos más demandados.
  * **Top 5 Clientes y Top 5 Vendedores:** Gráficos de barras con las entidades con mayor monto facturado y los asesores con más órdenes cerradas.
  * **Ventas por Cliente por Mes ($):** Gráfico interactivo con selector mensual para auditar cuánto facturó cada institución de salud en cada mes o en el acumulado.

* **Exportación de Informe Ejecutivo en PDF:** Documento formal con logos corporativos, KPIs, Top Clientes con porcentaje de compras (`%`), desglose de **Ventas a Clientes por Mes** y tabla de evolución mensual con tasa de efectividad comercial.

---

## 11. Botón de Ayuda Rápida

En la barra superior de todas las pantallas del sistema se encuentra el botón **`[ ? Ayuda ]`**, el cual despliega este manual en un modal con pestañas adaptado automáticamente a los permisos del usuario activo.

---

## 12. Preguntas Frecuentes

### ¿La hoja de respaldo la ve el cliente?
**No.** Es un documento interno confidencial que detalla los costos de proveedor y utilidades.

### ¿Se pueden mezclar proveedores en una misma orden de compra?
**No.** Cada orden de compra (P.O.) se genera para un único proveedor para garantizar el orden contable.

### ¿Qué significa la numeración `EB 01_01`?
- `EB` → Código del asesor comercial.
- `01` → Consecutivo mensual de la cotización.
- `_01` → Número de revisión o actualización de la oferta.

### ¿Cuál es el límite de tamaño para subir fotos de productos?
El sistema admite imágenes de hasta **8 MB** en formatos JPG, PNG y WebP. Si seleccionas una imagen que exceda este tamaño o tenga un formato incompatible, el formulario te advertirá de inmediato antes de enviarlo e incluirá un acceso para optimizarla.

### ¿Qué ocurre si un usuario olvida su contraseña?
El Administrador puede hacer clic en el botón de la llave dorada en la tarjeta del usuario (o ingresar a Editar y presionar "Restablecer al documento"). La clave quedará configurada de inmediato como su propio número de documento, permitiéndole ingresar nuevamente y configurar una clave personal.

### ¿Qué debo hacer si aparece una alerta roja de error?
Cada alerta roja en pantalla incluye un **título claro** con la causa del inconveniente y un **cuadro amarillo con la solución paso a paso** (por ejemplo: recargar con F5 si expiró el tiempo de sesión, buscar si un NIT ya existe, o verificar campos requeridos). Sigue las instrucciones del cuadro amarillo para completar la acción.
