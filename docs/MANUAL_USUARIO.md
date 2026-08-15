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
7. [Gestión de Productos (Catálogo)](#7-gestión-de-productos-solo-admin)
8. [Gestión de Usuarios (Solo Admin)](#8-gestión-de-usuarios-solo-admin)
9. [Cerrar Sesión](#9-cerrar-sesión)
10. [Preguntas Frecuentes](#10-preguntas-frecuentes)

---

## 1. Acceso al Sistema

### Iniciar sesión
1. Abrir el navegador y dirigirse a la URL del sistema.
2. Ingresar su **código/documento** y **contraseña**.
3. Hacer clic en **"Ingresar al Sistema"**.
4. *(Opcional)*: Use el ícono 👁️ para mostrar u ocultar su contraseña digitada.

### Sesión expirada
Si el sistema permanece **inactivo por más de 1 hora**, la sesión se cerrará automáticamente. Al regresar, verá el mensaje:
> *"Tu sesión ha expirado por inactividad. Por favor, inicia sesión nuevamente."*

Simplemente ingrese sus credenciales nuevamente para continuar.

---

## 2. Dashboard Principal

Al iniciar sesión llegará al **Panel Principal**, que muestra:

### Tarjetas KPI
| Tarjeta | Administrador | Asesor / Usuario |
|---|---|---|
| **Cotizaciones Totales** | Todas las registradas en el sistema | Solo las creadas por el asesor |
| **Cotizaciones este Mes** | Sí (conteo mensual consolidado) | Sí (consecutivo mensual propio) |
| **Productos en Catálogo** | Total catálogo médico | Total catálogo médico |
| **Clientes Registrados** | Total entidades de salud | Total entidades de salud |

### Accesos Rápidos
Botones de acceso directo a los módulos más usados:
- 📝 **Nueva Cotización**
- 🔍 **Consultar Cotizaciones**
- 🏢 **Clientes**
- 📦 **Catálogo** *(solo admin)*
- 👥 **Usuarios** *(solo admin)*

---

## 3. Nueva Cotización

El flujo de cotización se divide en **dos pasos**:

### Paso 1: Agregar Productos (Ítems)

#### Opción A: Buscar un producto del catálogo
1. En el campo **"Buscar producto..."**, escriba el nombre o código del producto.
2. Seleccione el producto de la lista desplegable de sugerencias.
3. El formulario se autocompletará con la descripción técnica, IVA y precio base.
4. Ajuste la cantidad y los parámetros comerciales.

#### Opción B: Ingresar un producto manualmente
1. Complete los campos requeridos:
   - **Título**: Nombre del producto o equipo médico.
   - **Descripción**: Especificaciones técnicas.
   - **Precio Proveedor**: Costo base del proveedor (dato interno confidencial).
   - **Cantidad**: Unidades a cotizar.
   - **IVA**: Sí (19%) o No (Exento 0%).
   - **Tiempo de entrega**: Ej: "Inmediata" o "2 a 3 semanas".
   - **Proveedor / Código Proveedor**.

#### Calculadora Dinámica de Ganancias
Complete la sección de márgenes:
* 🟢 **Utilidad:** Ganancia sobre el costo del proveedor.
* 🟠 **Flete:** Costos logísticos y de transporte.
* 🔵 **Calibración:** Costo de calibración metrológica o instalación.
* 🟣 **Estampillas:** Impuestos territoriales o estampillas aplicables.

Para cada etapa puede agregar:
- **`+$`** (Suma fija): Agrega un monto fijo en pesos.
- **`+%`** (Porcentaje): Calcula el porcentaje sobre el costo.
- **`÷`** (División): Divide entre un factor de margen.

#### Guardar Ítem
1. Clic en **"Agregar a Cotización"**.
2. El ítem aparecerá en la tabla inferior temporal. Puede editar ✏️ o eliminar 🗑 cualquier ítem antes de finalizar.

---

### Paso 2: Datos del Cliente y Finalización

1. Clic en **"Completar Datos del Cliente"**.
2. **Buscar o Crear Cliente:** Ingrese el NIT o Nombre para autocompletar. El sistema rellenará automáticamente **Departamento**, **Ciudad/Municipio**, teléfono, contacto y correo.
3. **Condiciones Comerciales:** Seleccione forma de pago (Contado, 30 días, etc.) y validez de la oferta (30 días).
4. **Generar PDF:** Clic en **"Generar Cotización"**. Se asigna automáticamente el número consecutivo (`EB 01`, `EB 02`, etc.) y se abre el visor PDF.

---

## 4. Consultar Cotizaciones y Revisiones

### Filtros de búsqueda
- **Fecha:** Rango o fecha exacta.
- **Cliente:** Nombre o NIT de la entidad.
- **N° Cotización:** Búsqueda exacta (Ej: `EB 01`).
- **Estado Comercial:** Filtrar por 🟡 *Pendientes*, 🟢 *Concluidas* o 🔴 *Descartadas*.

### Gestión de Estados Comerciales
- **Para Administradores:** Cada fila dispone de un selector desplegable interactivo que permite alternar entre **Pendiente**, **Concluida** y **Descartada**. El cambio se guarda instantáneamente mediante AJAX sin recargar la página.
- **Para Asesores:** Se visualiza una insignia informativa con el color y estado actual de la negociación.

### Acciones disponibles
- 👁️ **Ver PDF:** Abre el PDF oficial del cliente en un modal interactivo.
- 📋 **Respaldo:** Hoja de respaldo interna con costos de proveedor y desglose de utilidades.
- 🛒 **Orden:** Convierte los ítems cotizados en una Orden de Compra formal.
- 🔄 **Modificar / Revisión:** Genera una nueva versión de la cotización original (Ej: `EB 01_01`), manteniendo el histórico intacto.
- 🗑️ **Eliminar:** *(Solo Administrador con verificación CSRF)*.

---

## 5. Órdenes de Compra (P.O.)

1. En **Consultar Cotizaciones**, haga clic en 🛒 **Orden**.
2. **Seleccionar Ítems:** Marque únicamente los productos correspondientes al proveedor a solicitar.
3. **Datos del Proveedor:** Ingrese NIT, condiciones de pago, datos bancarios de consignación, IVA y retenciones.
4. **Generar Orden:** El sistema emite la Orden de Compra y genera el documento PDF formal.

---

## 6. Gestión de Clientes

- **Listado y Búsqueda:** Búsqueda en vivo por nombre, NIT, departamento o municipio.
- **Crear / Editar:** Modal rápido con validación de NIT único.
- **Eliminar:** *(Solo Administrador)*.

---

## 7. Gestión de Productos *(solo admin)*

- **Catálogo:** Creación y edición con foto médica sanitizada, categoría, código e IVA.
- **Exportación:** Descarga de catálogo completo en PDF.
- **Eliminación Segura:** Eliminar un producto del catálogo no afecta cotizaciones históricas ya creadas.

---

## 8. Gestión de Usuarios *(solo admin)*

- **Crear Usuario:** Asignación de código de asesor (2 letras, ej: `EB`), documento, nombre, rol y contraseña.
- **Campos Opcionales:** Correo y teléfono son opcionales.
- **Resetear Contraseña:** Restablece con 1 clic la clave al número de documento del usuario.
- **Ver/Ocultar Clave:** Botón interactivo 👁️ para confirmar la clave escrita.

---

## 9. Cerrar Sesión

- **Cierre Manual:** Desde el menú lateral o la barra superior.
- **Cierre Automático:** Tras 1 hora de inactividad por seguridad.
- **Prevención de Back:** El sistema impide volver atrás en el navegador una vez cerrada la sesión.

---

## 10. Preguntas Frecuentes

### ¿La hoja de respaldo la ve el cliente?
**No.** Es un documento de control interno confidencial que detalla los costos de proveedor y márgenes de ganancia.

### ¿Se pueden mezclar proveedores en una misma orden de compra?
**No.** Cada orden de compra (P.O.) se genera para un único proveedor para garantizar el orden contable.

### ¿Qué significa la numeración `EB 01_01`?
- `EB` → Código del asesor comercial.
- `01` → Consecutivo mensual de la cotización.
- `_01` → Número de revisión o actualización de la oferta.
