# 📚 Manual de Usuario — Sistema Impobiomedical

**Versión del sistema**: v2.0.0 (Edición Comercial)  
**Autor y Titular**: Santiago Lizcano  
**Público objetivo**: Asesores comerciales y administradores de Impobiomedical

---

## 📋 Tabla de Contenidos

1. [Acceso al Sistema](#1-acceso-al-sistema)
2. [Dashboard Principal](#2-dashboard-principal)
3. [Nueva Cotización](#3-nueva-cotización)
4. [Consultar Cotizaciones y Revisiones](#4-consultar-cotizaciones-y-revisiones)
5. [Órdenes de Compra (P.O.)](#5-órdenes-de-compra-po)
6. [Gestión de Clientes](#6-gestión-de-clientes)
7. [Gestión de Productos (Catálogo)](#7-gestión-de-productos-catálogo)
8. [Gestión de Usuarios (Solo Admin)](#8-gestión-de-usuarios-solo-admin)
9. [Seguridad y Cierre de Sesión](#9-seguridad-y-cierre-de-sesión)
10. [Preguntas Frecuentes](#10-preguntas-frecuentes)

---

## 1. Acceso al Sistema

### Iniciar sesión
1. Dirigirse a la URL del sistema en el navegador.
2. Ingresar su **código/documento** y **contraseña**.
3. Hacer clic en **"Ingresar al Sistema"**.
4. *(Opcional)*: Use el botón con icono de ojo 👁️ para verificar visualmente su contraseña escrita.

### Sesión expirada por inactividad
Si el sistema permanece **inactivo por más de 1 hora**, la sesión se cerrará automáticamente por seguridad. Al volver a interactuar, el sistema redirigirá a la pantalla de login con un aviso.

---

## 2. Dashboard Principal

El panel de bienvenida resume los indicadores operativos según el rol del usuario:
* **Cotizaciones Totales:** Administrador visualiza todas las del sistema; los asesores visualizan las propias.
* **Productos en Catálogo:** Acceso rápido al catálogo médico.
* **Clientes Registrados:** Directorio de entidades de salud.
* **Accesos Directos:** Botones de un clic para crear cotizaciones, consultar órdenes o gestionar clientes.

---

## 3. Nueva Cotización

### Paso 1: Agregar Productos (Ítems)
* **Opción A (Buscador en Vivo):** Escriba el nombre en *"Buscar producto..."* y seleccione una sugerencia para autocompletar la descripción, foto y datos del catálogo.
* **Opción B (Ingreso Manual):** Complete título, descripción, código del proveedor y marca.
* **Calculadora Comercial Dinámica:**
  1. Ingrese el **Precio Base Proveedor ($)**.
  2. Configure el **% de Utilidad**, **Flete ($)**, **Calibración ($)** y **Estampillas ($)**.
  3. El sistema calcula en tiempo real el valor unitario final para el cliente y el valor de respaldo interno.
* **Agregar:** Clic en *"Agregar a Cotización"*. Los ítems se acumulan en la tabla temporal.

### Paso 2: Datos del Cliente y Finalización
1. Clic en *"Completar Datos del Cliente"*.
2. Busque un cliente existente por nombre/NIT o ingrese los datos de una nueva entidad.
3. Configure condiciones de pago (Contado, 30 días, etc.) y validez de la oferta.
4. Clic en **"Generar Cotización"**: El sistema asigna un consecutivo automático (Ej: `EB 01`) y abre el visor PDF.

---

## 4. Consultar Cotizaciones y Revisiones

* **Filtros:** Búsqueda combinada por Fecha, Cliente o Número de Cotización.
* **Acciones:**
  * 👁️ **Ver PDF:** Visualiza o descarga el PDF oficial del cliente.
  * 📋 **Respaldo:** Hoja interna confidencial con los costos de proveedor y márgenes calculados.
  * 🛒 **Orden:** Inicia la generación de una Orden de Compra (P.O.) a partir de la cotización.
  * 🔄 **Modificar / Crear Revisión:** Genera una nueva versión de la cotización original (Ej: `EB 01_01`) sin alterar la original.
  * 🗑️ **Eliminar:** *(Solo Administrador con confirmación y token CSRF)*.

---

## 5. Órdenes de Compra (P.O.)

1. En una cotización aprobada, haga clic en 🛒 **Orden**.
2. **Seleccionar Ítems:** Marque los productos que se solicitarán al proveedor seleccionado.
3. **Datos de Despacho y Bancarios:** Complete NIT, tipo de contribuyente, banco, tipo y número de cuenta, IVA y retenciones.
4. **Generar:** El sistema crea la P.O. y genera el documento PDF formal para enviar al proveedor.

---

## 6. Gestión de Clientes

* **Listado:** Tabla con buscador por nombre, NIT, departamento o municipio.
* **Crear / Editar:** Modal rápido con validación de NIT único.
* **Eliminar:** *(Solo Administrador)*.

---

## 7. Gestión de Productos (Catálogo)

* **Administración:** Creación y edición con carga de imagen médica, estado (Activo/Inactivo), clasificación por categoría e IVA (19% o No aplica).
* **Exportación:** Botón de descarga de catálogo completo en formato PDF.

---

## 8. Gestión de Usuarios (Solo Admin)

* **Crear Asesor:** Asignación de código único (2 a 3 letras, ej: `EB`), documento, nombre, rol y contraseña.
* **Campos Opcionales:** Correo electrónico y teléfono son opcionales.
* **Reset de Clave:** Botón administrativo que restablece la contraseña al número de documento del usuario.
* **Visualizador de Contraseña:** Botón interactivo de ojo 👁️ en los modales para confirmar la clave digitada.

---

## 9. Seguridad y Cierre de Sesión

* **Cierre Manual:** Desde el botón *"Cerrar Sesión"* en el menú lateral o la barra superior.
* **Protección de Navegación:** El sistema impide volver atrás en el navegador una vez cerrada la sesión.
* **Protección Anti-Doble Envío:** Los formularios se bloquean automáticamente al primer clic para evitar duplicados.

---

## 10. Preguntas Frecuentes

* **¿La hoja de respaldo la ve el cliente?**  
  *No.* La hoja de respaldo es de uso estrictamente interno para control de costos y márgenes de ganancia.
* **¿Se pueden mezclar proveedores en una misma orden de compra?**  
  *No.* Cada P.O. debe corresponder a un único proveedor para mantener la trazabilidad contable.
* **¿Qué ocurre si se elimina un producto del catálogo?**  
  *Las cotizaciones históricas no se alteran.* Los ítems ya guardados conservan su información intacta.
