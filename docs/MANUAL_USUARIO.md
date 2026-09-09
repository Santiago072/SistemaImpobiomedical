# 📚 Manual de Usuario — Sistema Impobiomedical

**Versión del sistema**: v3.0.0 (Edición Comercial, Proveedores y Operativa)  
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
8. [Gestión de Productos (Solo Admin)](#8-gestión-de-productos-solo-admin)
9. [Gestión de Usuarios (Solo Admin)](#9-gestión-de-usuarios-solo-admin)
10. [Estadísticas y Reportes (Solo Admin)](#10-estadísticas-y-reportes-solo-admin)
11. [Botón de Ayuda Rápida](#11-botón-de-ayuda-rápida)
12. [Preguntas Frecuentes](#12-preguntas-frecuentes)

---

## 1. Acceso al Sistema

### Iniciar sesión
1. Abrir el navegador y dirigirse a la URL del sistema.
2. Ingresar su **código/documento** y **contraseña**.
3. Hacer clic en **"Ingresar al Sistema"**.
4. *(Opcional)*: Use el ícono 👁️ para mostrar u ocultar su contraseña digitada.

### Sesión expirada
Si el sistema permanece **inactivo por más de 1 hora**, la sesión se cerrará automáticamente por seguridad.

---

## 2. Dashboard Principal

Al iniciar sesión llegará al **Panel Principal**, que muestra:

### Tarjetas KPI
| Tarjeta | Administrador | Asesor / Usuario |
|---|---|---|
| **Cotizaciones Totales** | Todas las registradas en el sistema | Solo las creadas por el asesor |
| **Cotizaciones este Mes** | Conteo consolidado del mes | Consecutivo mensual propio |
| **Clientes Registrados** | Total entidades de salud | Total entidades de salud |
| **Productos en Catálogo** | Total catálogo médico | Total catálogo médico |

---

## 3. Nueva Cotización (Flujo en 2 Pasos)

### Paso 1: Agregar Productos (Ítems)
* **Buscar del Catálogo:** Digite el nombre del producto en el buscador en vivo y seleccione la opción deseada para autocompletar título, descripción técnica, imagen y categoría (incluyendo *Servicio Calibración*).
* **Ingreso Manual:** Puede registrar productos o servicios médicos personalizados directamente completando los campos requeridos y seleccionando su categoría.
* **Calculadora de Ganancias Dinámica:** Permite ingresar el precio base del proveedor y calcular utilidad, flete, calibración y estampillas. El valor resultante se establece como el precio unitario del producto para la cotización.
* **Agregar:** Presione **"Agregar a Cotización"** para almacenar temporalmente el ítem. Los borradores y productos agregados quedan preservados inteligentemente.

### Paso 2: Datos del Cliente y Finalización
1. Haga clic en **"Continuar → Datos Cliente y PDF"**.
2. **Seleccionar Cliente:** Busque por NIT o Nombre para autocompletar automáticamente ciudad, departamento, dirección, contacto y correo.
3. **Condiciones Comerciales:** Ingrese forma de pago (Contado, 30 días, etc.) y días de validez.
4. **Finalizar:** Clic en **"Finalizar Cotización"**. El sistema calcula el consecutivo mensual de forma continua (`01`, `02`, `03`...) según el código del asesor y abre el visor PDF.

---

## 4. Consultar Cotizaciones y Revisiones

* **Filtros de Búsqueda:** Filtre por fecha, cliente, número de cotización o estado comercial (🟡 *Pendiente*, 🟢 *Concluida*, 🔴 *Descartada*).
* **Ver PDF:** Abre el PDF formal para el cliente en un visor emergente interactivo con opción de descarga de PDF.
* **Descargar Excel:** Botón verde `[ 📊 Excel ]` para descargar la cotización en una hoja de cálculo estructurada de forma inmediata (sin imágenes para máxima velocidad con muchos productos).
* **Hoja de Respaldo:** Consulta interna confidencial con los costos de proveedor y márgenes para auditoría.
* **Modificar (Ajustar vs. Nueva Versión):** Al hacer clic en ✏️ **Modificar**, se abre un diálogo que permite escoger:
  * **Ajustar Cotización (Mismo número):** Permite corregir productos, precios o condiciones directamente sobre la cotización original conservando su mismo número (ej: `EB01`), sin crear registros duplicados ni aumentar el consecutivo mensual.
  * **Nueva Versión / Revisión:** Deja la original intacta y crea una cotización derivada (`_01`, `_02`...) para auditoría de cambios comerciales.
* **Emitir Orden:** Botón directo para pasar los ítems cotizados a una Orden de Compra formal.
* **Eliminar Cotización:** Disponible para usuarios con rol `admin` o `compras` independientemente del estado de la cotización.

---

## 5. Órdenes de Compra (P.O.)

* **Emisión:** Desde consultar cotizaciones, haga clic en 🛒 **Orden** en propuestas pendientes. Seleccione únicamente los ítems a comprar al proveedor y complete los datos bancarios y tributarios (se autocompletan predictivamente desde el directorio oficial si el proveedor ya existe).
* **Gestión y Clasificación:** Controle órdenes pendientes y completadas con contadores en tiempo real. Cada orden clasifica cronológicamente al proveedor como `🟡 Nuevo` si corresponde a su primera compra emitida o `🟢 Registrado` a partir de su segunda orden.
* **Selección y Exportación Multipágina:** Casillas de selección múltiple con persistencia automática a través de la paginación (puede seleccionar órdenes de la página 1, navegar a la página 2 y continuar marcando). Barra superior interactiva con contador consolidado, botón para limpiar selección y exportación masiva a **PDF** o **Excel (.xls)**.

---

## 6. Gestión de Clientes

Directorio centralizado de instituciones de salud:
* Búsqueda en vivo por nombre, NIT o municipio.
* Creación y edición con validación de NIT único.

---

## 7. Gestión de Proveedores

Directorio centralizado de proveedores comerciales y técnicos:
* **Consulta y Búsqueda en Vivo:** Localice rápidamente proveedores por NIT o Nombre sin necesidad de presionar Enter (búsqueda automática con debounce).
* **Nuevo Proveedor:** Modal para registrar NIT, Razón Social, Tipo de Contribuyente y datos bancarios (Banco, Tipo de Cuenta y Número).
* **Edición Rápida:** Actualice información comercial o bancaria desde el botón de lápiz.
* **Desactivación Segura (Solo Admin):** Los administradores pueden desactivar proveedores obsoletos. Los proveedores desactivados no aparecerán en nuevas cotizaciones u órdenes.
* **Normalización de NIT:** El sistema limpia automáticamente puntos y espacios al escribir, manteniendo intacto el formato con guion de verificación (ej: `900535843-3`).

---

## 8. Gestión de Productos (Solo Admin)

* Catálogo con fotos sanitizadas, categorías, códigos y porcentajes de IVA.
* Exportación completa del catálogo a PDF.
* Las eliminaciones no afectan cotizaciones históricas ya emitidas.

---

## 9. Gestión de Usuarios (Solo Admin)

* Alta de asesores con su **Código de Cotización** (2 letras, ej: `EB`, `SL`).
* Reset de contraseñas rápido al número de documento en un solo clic.

---

## 10. Estadísticas y Reportes (Solo Admin)

* **Indicadores Financieros y Operativos:** Monto cotizado consolidado, monto real facturado por órdenes concluidas, total de cotizaciones y órdenes emitidas.
* **Top Clientes y Ventas Mensuales:** Identificación de clientes líderes por facturación y visor interactivo de ventas mensuales por cliente con filtro por mes y acumulado general.
* **Métricas de Productos y Asesores:** Productos más cotizados y comparativas de efectividad por asesor comercial.
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
