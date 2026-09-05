# 📚 Manual de Usuario — Sistema Impobiomedical

**Versión del sistema**: v2.4.0 (Edición Comercial y Operativa)  
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
7. [Gestión de Productos (Solo Admin)](#7-gestión-de-productos-solo-admin)
8. [Gestión de Usuarios (Solo Admin)](#8-gestión-de-usuarios-solo-admin)
9. [Estadísticas y Reportes (Solo Admin)](#9-estadísticas-y-reportes-solo-admin)
10. [Botón de Ayuda Rápida](#10-botón-de-ayuda-rápida)
11. [Preguntas Frecuentes](#11-preguntas-frecuentes)

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
* **Modificar / Revisión:** Genera una nueva versión (ej: `EB 01_01`) sin sobreescribir la cotización original.
* **Emitir Orden:** Botón directo para pasar los ítems cotizados a una Orden de Compra formal.

---

## 5. Órdenes de Compra (P.O.)

* **Emisión:** Desde consultar cotizaciones, haga clic en 🛒 **Orden** en propuestas pendientes. Seleccione únicamente los ítems a comprar al proveedor y complete los datos bancarios y tributarios.
* **Gestión:** Controle órdenes pendientes y completadas con contadores en tiempo real.
* **Exportación:** Casillas de selección múltiple para exportar consolidado en **PDF** o descargar a **Excel (.xls)** con tablas de pago estructuradas.

---

## 6. Gestión de Clientes

Directorio centralizado de instituciones de salud:
* Búsqueda en vivo por nombre, NIT o municipio.
* Creación y edición con validación de NIT único.

---

## 7. Gestión de Productos (Solo Admin)

* Catálogo con fotos sanitizadas, categorías, códigos y porcentajes de IVA.
* Exportación completa del catálogo a PDF.
* Las eliminaciones no afectan cotizaciones históricas ya emitidas.

---

## 8. Gestión de Usuarios (Solo Admin)

* Alta de asesores con su **Código de Cotización** (2 letras, ej: `EB`, `SL`).
* Reset de contraseñas rápido al número de documento en un solo clic.

---

## 9. Estadísticas y Reportes (Solo Admin)

* **Indicadores Financieros y Operativos:** Monto cotizado consolidado, monto real facturado por órdenes concluidas, total de cotizaciones y órdenes emitidas.
* **Top Clientes y Ventas Mensuales:** Identificación de clientes líderes por facturación y visor interactivo de ventas mensuales por cliente con filtro por mes y acumulado general.
* **Métricas de Productos y Asesores:** Productos más cotizados y comparativas de efectividad por asesor comercial.
* **Exportación de Informe Ejecutivo en PDF:** Documento formal con logos corporativos, KPIs, Top Clientes con porcentaje de compras (`%`), desglose de **Ventas a Clientes por Mes** y tabla de evolución mensual con tasa de efectividad comercial.

---

## 10. Botón de Ayuda Rápida

En la barra superior de todas las pantallas del sistema se encuentra el botón **`[ ? Ayuda ]`**, el cual despliega este manual en un modal con pestañas adaptado automáticamente a los permisos del usuario activo.

---

## 11. Preguntas Frecuentes

### ¿La hoja de respaldo la ve el cliente?
**No.** Es un documento interno confidencial que detalla los costos de proveedor y utilidades.

### ¿Se pueden mezclar proveedores en una misma orden de compra?
**No.** Cada orden de compra (P.O.) se genera para un único proveedor para garantizar el orden contable.

### ¿Qué significa la numeración `EB 01_01`?
- `EB` → Código del asesor comercial.
- `01` → Consecutivo mensual de la cotización.
- `_01` → Número de revisión o actualización de la oferta.
