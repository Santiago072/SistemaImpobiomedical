# 📚 Manual de Usuario — Sistema Impobiomedical

**Versión del sistema**: v1.1.0  
**Última actualización**: Julio 2026  
**Público objetivo**: Asesores comerciales y administradores de Impobiomedical

---

## 📋 Tabla de Contenidos

1. [Acceso al Sistema](#1-acceso-al-sistema)
2. [Dashboard Principal](#2-dashboard-principal)
3. [Nueva Cotización](#3-nueva-cotización)
4. [Consultar Cotizaciones](#4-consultar-cotizaciones)
5. [Órdenes de Compra](#5-órdenes-de-compra)
6. [Gestión de Clientes](#6-gestión-de-clientes)
7. [Gestión de Productos](#7-gestión-de-productos-solo-admin)
8. [Gestión de Usuarios](#8-gestión-de-usuarios-solo-admin)
9. [Cerrar Sesión](#9-cerrar-sesión)
10. [Preguntas Frecuentes](#10-preguntas-frecuentes)

---

## 1. Acceso al Sistema

### Iniciar sesión

1. Abrir el navegador y dirigirse a la URL del sistema.
2. Ingresar su **correo electrónico** y **contraseña**.
3. Hacer clic en **"Ingresar al Sistema"**.

> 💡 Use el ícono 👁 para mostrar/ocultar su contraseña.

### Sesión expirada

Si el sistema permanece **inactivo por más de 1 hora**, la sesión se cerrará automáticamente. Al regresar, verá el mensaje:

> *"Tu sesión ha expirado por inactividad. Por favor, inicia sesión nuevamente."*

Simplemente ingrese sus credenciales nuevamente para continuar.

---

## 2. Dashboard Principal

Al iniciar sesión llegará al **Panel Principal**, que muestra:

### Tarjetas KPI

| Tarjeta | Administrador | Usuario |
|---------|--------------|---------|
| Cotizaciones Totales | Todas las del sistema | Solo las propias |
| Cotizaciones este Mes | Sí | No disponible |

### Accesos Rápidos

Botones de acceso directo a los módulos más usados:
- 📝 **Nueva Cotización**
- 🔍 **Consultar Cotizaciones**
- 🏢 **Clientes**
- 📦 **Catálogo** *(solo admin)*
- 👥 **Usuarios** *(solo admin)*

---

## 3. Nueva Cotización

Este es el módulo principal del sistema. Se divide en **dos pasos**.

### Paso 1: Agregar Productos (Ítems)

#### Opción A: Buscar un producto del catálogo

1. En el campo **"Buscar producto..."**, escriba el nombre del producto.
2. Aparecerá una lista de sugerencias. Haga clic en el producto deseado.
3. El formulario se completará automáticamente con los datos del catálogo.
4. Ajuste los campos si es necesario (precio proveedor, cantidad, tiempo de entrega, etc.).

#### Opción B: Ingresar un producto manualmente

1. Complete los campos del formulario directamente:
   - **Título**: Nombre del producto/equipo
   - **Descripción**: Descripción técnica
   - **Precio Proveedor**: Costo del proveedor (campo interno)
   - **Cantidad**: Unidades a cotizar
   - **IVA**: Sí o No
   - **Tiempo de entrega**: Ej: "4-6 semanas"
   - **Categoría**: Clasificación del producto
   - **Código Producto / Código Proveedor**
   - **Proveedor**: Nombre del proveedor

> 💡 Si escribe un producto manualmente y no existe en el catálogo, se guardará automáticamente para uso futuro.

#### Calculadora de Márgenes de Ganancia

Después de ingresar los datos del producto, complete la sección de **márgenes de ganancia**:

| Etapa | Color | Descripción |
|-------|-------|-------------|
| **Utilidad** | 🟢 Verde | Ganancia sobre el precio del proveedor |
| **Flete** | 🟠 Naranja | Costo de transporte |
| **Calibración** | 🔵 Azul | Costo de calibración/instalación |
| **Estampillas** | 🟣 Morado | Impuestos o estampillas |

Para cada etapa puede agregar **una o varias operaciones**:
- **`+$`** (Suma fija): Agrega un monto en pesos. Ej: `+$ 50,000`
- **`+%`** (Porcentaje): Calcula un % sobre el precio del proveedor. Ej: `+15%`
- **`÷`** (División): Divide el precio entre un factor. Ej: `÷ 0.85`

> 💡 El precio final para el cliente se calcula automáticamente aplicando todas las operaciones en orden.

#### Agregar el ítem a la cotización

1. Haga clic en **"Agregar a Cotización"**.
2. El ítem aparecerá en la **tabla inferior** (lista temporal).
3. Desde la tabla puede **editar** ✏️ o **eliminar** 🗑 cualquier ítem.
4. Repita el proceso para agregar más productos.

---

### Paso 2: Datos del Cliente

Una vez agregados todos los productos, haga clic en **"Completar Datos del Cliente"**.

#### Opción A: Buscar cliente existente

1. En el campo **"Buscar cliente..."**, escriba el nombre o NIT.
2. Seleccione el cliente de la lista de sugerencias.
3. El formulario se completará automáticamente.

#### Opción B: Ingresar cliente manualmente

Complete los campos:
- **Nombre / Entidad**: Nombre del cliente o empresa
- **NIT / CC**: Identificación tributaria
- **Dirección**
- **Teléfono**
- **Correo**
- **Nombre del Contacto**: Persona de contacto
- **Ciudad**

> 💡 Si el cliente no existe en el sistema, se guardará automáticamente al generar la cotización.

#### Condiciones de la Cotización

- **Días de Validez**: Por defecto 30 días
- **Condiciones de Pago**: Ej: "CONTADO", "30 días", etc.
- **Observaciones**: Información adicional para el cliente

#### Generar la Cotización

Haga clic en **"Generar PDF"** para:
1. Finalizar la cotización (se asigna número automático: `EB01`, `EB02`, etc.)
2. Visualizar el PDF del cliente en pantalla
3. Descargarlo como archivo

O haga clic en **"Volver a Ítems"** para agregar más productos.

---

## 4. Consultar Cotizaciones

### Filtros de búsqueda

Use uno o varios filtros para encontrar cotizaciones:

| Filtro | Descripción |
|--------|-------------|
| **Fecha** | Buscar por fecha de creación |
| **Cliente** | Buscar por nombre del cliente |
| **N° Cotización** | Buscar por número exacto (Ej: `EB01`) |

1. Complete el/los filtros deseados.
2. Haga clic en **"Buscar"**.
3. La tabla mostrará los resultados.
4. Para limpiar la búsqueda, haga clic en la **X** junto al filtro.

> 🔐 **Control de acceso**: Los usuarios solo ven sus propias cotizaciones. Los administradores ven todas.

### Acciones disponibles por cotización

| Botón | Acción |
|-------|--------|
| 👁 **Ver PDF** | Abre el PDF de la cotización en un modal. Puede descargarlo. |
| 📋 **Respaldo** | Muestra la hoja interna con los proveedores y márgenes de ganancia. |
| 🛒 **Orden** | Crea una orden de compra a partir de la cotización. |
| 🗑 **Eliminar** | Elimina la cotización permanentemente *(solo admin)*. |

### Ver PDF de Cotización

El PDF incluye:
- Logo e información de la empresa
- Datos del cliente
- Tabla de productos (Ítem, Descripción, Cant., Precio, % IVA, T/IVA)
- Totales y condiciones
- Datos del asesor

### Ver Hoja de Respaldo

Documento interno (no para el cliente) que muestra:
- Precio del proveedor por ítem
- Desglose de cada etapa de márgenes (Utilidad, Flete, Calibración, Estampillas)
- Código de proveedor y nombre del proveedor
- Valor final con IVA

---

## 5. Órdenes de Compra

### Crear una Orden de Compra

1. Desde **Consultar Cotizaciones**, haga clic en 🛒 **Orden** en la cotización deseada.
2. Aparecerá la pantalla de **selección de ítems**:
   - Los ítems están agrupados por proveedor.
   - Marque los ítems que **sí desea pedir** al proveedor.
3. Complete los datos del proveedor:
   - NIT del proveedor
   - Tipo de contribuyente
   - Condiciones de pago
   - % IVA y % Retención
4. Haga clic en **"Generar Orden"**.
5. El PDF de la orden se genera automáticamente.

> ⚠️ No se puede mezclar ítems de proveedores diferentes en una misma orden.

### Consultar Órdenes de Compra

Vaya a **Módulo → Órdenes de Compra** desde el menú lateral.

**Filtros disponibles**:
- N° P.O. (Purchase Order)
- Nombre del proveedor
- N° de Cotización
- Fecha

**Acciones por orden**:
- 👁 **Ver**: Modal con la información completa de la orden
- ⬇️ **Descargar PDF**: Descarga el documento de la orden
- 🗑 **Eliminar**: *(solo admin)*

---

## 6. Gestión de Clientes

### Ver lista de clientes

Vaya al módulo **Clientes** desde el menú lateral. Verá una tabla con todos los clientes registrados.

**Búsqueda**: Use el campo de búsqueda para filtrar por nombre, NIT o municipio.

### Crear un cliente

1. Haga clic en **"+ Nuevo Cliente"**.
2. Complete el formulario en el modal:
   - Nombre / Entidad *(requerido)*
   - NIT / CC *(requerido — debe ser único)*
   - Departamento *(requerido)*
   - Municipio *(requerido)*
   - Dirección *(requerido)*
   - Nombre del Contacto *(requerido)*
   - Teléfono *(requerido)*
   - Correo electrónico *(opcional)*
3. Haga clic en **"Guardar"**.

> ⚠️ Si el NIT ya existe, el sistema mostrará un error de duplicado.

### Editar un cliente

1. En la tabla de clientes, haga clic en ✏️ **Editar**.
2. Modifique los datos en el formulario.
3. Haga clic en **"Guardar Cambios"**.

### Eliminar un cliente *(solo admin)*

1. Haga clic en 🗑 **Eliminar** en la fila del cliente.
2. Confirme la acción en el diálogo de confirmación.

---

## 7. Gestión de Productos *(solo admin)*

### Ver catálogo de productos

Vaya a **Catálogo** desde el menú lateral o los accesos rápidos.

**Filtros disponibles**:
- Búsqueda por título
- Filtro por categoría

### Crear un producto

1. Haga clic en **"+ Nuevo Producto"**.
2. Complete el formulario:
   - Título *(requerido — debe ser único)*
   - Descripción *(requerido)*
   - Precio base *(requerido)*
   - IVA: Sí / No
   - % IVA *(por defecto 19%)*
   - Categoría
   - Código Producto
   - Foto *(opcional — max. 5MB, formatos: JPG, PNG, WEBP)*
   - Estado: Activo / Inactivo
3. Haga clic en **"Guardar"**.

### Editar un producto

1. Haga clic en ✏️ **Editar** en el producto.
2. Modifique los campos necesarios.
3. Para cambiar la foto, seleccione una nueva imagen.
4. Haga clic en **"Guardar Cambios"**.

### Eliminar un producto *(solo admin)*

1. Haga clic en 🗑 **Eliminar**.
2. Confirme la acción.

> ⚠️ Eliminar un producto del catálogo no afecta las cotizaciones existentes que lo contienen (los ítems ya guardados se mantienen).

---

## 8. Gestión de Usuarios *(solo admin)*

### Ver lista de usuarios

Vaya a **Usuarios** desde el menú lateral.

**Búsqueda**: Por nombre, código o correo electrónico.

### Crear un usuario

1. Haga clic en **"+ Nuevo Usuario"**.
2. Complete el formulario:
   - **Código** *(único — Ej: EB, JD, MA)*: Se usa en el número de cotización
   - **Documento** *(único)*: Se usará como contraseña inicial al resetear
   - **Nombre completo** *(requerido)*
   - **Correo electrónico** *(único)*
   - **Contraseña** *(mínimo 8 caracteres)*
   - **Teléfono** *(opcional)*
   - **Cargo** *(opcional)*
   - **Rol**: Administrador / Usuario
   - **Estado**: Activo / Inactivo
3. Haga clic en **"Guardar"**.

### Editar un usuario

1. Haga clic en ✏️ **Editar**.
2. Modifique los datos necesarios.
3. Si no desea cambiar la contraseña, deje el campo vacío.
4. Haga clic en **"Guardar Cambios"**.

### Resetear contraseña

Si un usuario olvidó su contraseña:

1. Haga clic en 🔑 **Resetear Contraseña** en la fila del usuario.
2. La contraseña se restablecerá automáticamente al **número de documento** del usuario.
3. Informe al usuario para que la cambie en su próximo acceso.

### Cambiar estado de usuario

Use el botón de **Editar** para cambiar el estado de `activo` a `inactivo` y viceversa.

> ⚠️ No se puede eliminar el último administrador del sistema.

---

## 9. Cerrar Sesión

### Cierre manual

Haga clic en su nombre de usuario en el menú lateral y seleccione **"Cerrar Sesión"**, o use el botón de cierre de sesión en la barra superior.

### Cierre automático por inactividad

El sistema cerrará automáticamente su sesión después de **1 hora de inactividad**. Al regresar verá el mensaje:

> *"Tu sesión ha expirado por inactividad."*

Ingrese sus credenciales para volver a acceder.

---

## 10. Preguntas Frecuentes

### ¿Por qué no puedo ver las cotizaciones de otros usuarios?

Los usuarios solo pueden ver sus propias cotizaciones. Solo el **administrador** tiene acceso a todas.

### ¿Qué pasa si agrego un producto manualmente que ya existe en el catálogo?

El sistema verifica si ya existe un producto con el mismo nombre. Si existe y usted lo escribe manualmente, se actualizará la información del catálogo con los nuevos datos ingresados y se usará ese registro existente.

### ¿Puedo editar una cotización después de generada?

Las cotizaciones finalizadas no se pueden editar directamente. Para correcciones, comuníquese con el administrador.

### ¿Qué significa el número de cotización como `EB01`?

- `EB` → Código del usuario que la creó
- `01` → Consecutivo mensual del usuario (se reinicia cada mes)

### ¿La hoja de respaldo la ve el cliente?

**No**. La hoja de respaldo es un documento **interno** que muestra los proveedores y márgenes de ganancia. El cliente solo ve el PDF de cotización.

### ¿Puedo crear una orden de compra con productos de varios proveedores?

**No**. Cada orden de compra es por un único proveedor. Si tiene ítems de varios proveedores, deberá generar una orden por proveedor.

### ¿Qué pasa si el sistema muestra un error al agregar un ítem?

Tome nota del error y comuníquelo al administrador del sistema con:
- La cotización en la que ocurrió
- Los datos del producto que intentaba agregar
- La hora aproximada del error

### ¿Puedo recuperar una cotización eliminada?

**No**. La eliminación es permanente. Solo los administradores pueden eliminar cotizaciones.

### ¿Cómo sé si un producto tiene IVA?

En el catálogo y en el formulario de cotización, cada producto indica si aplica IVA (Sí/No) y el porcentaje correspondiente (generalmente 19%).

---

## 📞 Soporte

Para soporte técnico o reporte de errores, contacte al administrador del sistema.

**Impobiomedical — Soluciones y Servicios de Tecnología Biomédica**
