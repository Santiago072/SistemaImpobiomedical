# 📋 Especificación de Requisitos y Alcance Funcional — Sistema Impobiomedical

**Versión del Sistema:** v2.3.0 (Edición Comercial)  
**Tecnología:** PHP 8.2 (PDO, MVC, Arquitectura Modular) · MariaDB / MySQL 8.0 · Vanilla CSS (SMACSS/ITCSS en `css/modules/`) · DomPDF · PHPUnit 10

Este documento formaliza los requisitos funcionales (RF), requisitos no funcionales (RNF), reglas de negocio y modelo de datos del **Sistema Impobiomedical**.

---

## 1. Módulos y Requisitos Funcionales (RF)

### 🔐 RF01 — Autenticación, Sesiones y Seguridad de Contraseñas
* **RF01.1:** Inicio de sesión mediante Código/Documento y Contraseña cifrada con `bcrypt`.
* **RF01.2:** Control de roles estricto: **Administrador** (acceso total a configuración, usuarios y métricas globales) y **Usuario/Asesor** (gestión de sus propias cotizaciones y consulta de catálogo/clientes).
* **RF01.3 (Detección de Contraseña Inicial y Modal Sugerido):** Detección automática al iniciar sesión cuando la clave del usuario coincide con su número de documento, desplegando un modal interactivo para invitarlo a personalizar su contraseña (con botón de omisión para esa sesión).
* **RF01.4:** Validación de contraseña personalizada (mínimo 6 caracteres, coincidencia de confirmación y prohibición de reutilizar el número de documento).
* **RF01.5:** Visibilidad interactiva de contraseñas (botón de ojo 👁️ para alternar entre texto claro y oculto).
* **RF01.6:** Bloqueo ante ataques de fuerza bruta mediante Rate Limiting en memoria de sesión por IP y endpoint.
* **RF01.7:** Cierre de sesión seguro con invalidación total de cookies y almacenamiento de sesión.

### 📊 RF02 — Panel de Control (Dashboard)
* **RF02.1:** Indicadores clave de rendimiento (KPIs): Total de cotizaciones, productos registrados, clientes y cotizaciones del mes (filtrados por asesor para usuarios y consolidados para administradores).
* **RF02.2:** Accesos directos rápidos a las funciones operativas más frecuentes (*Nueva Cotización*, *Consultar*, *Órdenes de Compra*, *Clientes*, *Catálogo*, *Usuarios*, *Estadísticas*).
* **RF02.3:** Saludo personalizado con insignia destacada del código del asesor comercial (`EB-XX`).

### 📝 RF03 — Cotizador Dinámico y Calculadora Comercial
* **RF03.1:** Creación de cotizaciones con buscador interactivo en vivo de productos del catálogo médico.
* **RF03.2 (Calculadora Dinámica de Ganancias Multietapa):** Cálculo automatizado paso a paso:
  1. Precio base proveedor ($).
  2. Etapa 1: Porcentaje de utilidad o sumas sobre el costo base (`utilidad`).
  3. Etapa 2: Costos de flete y logística (`flete`).
  4. Etapa 3: Calibración metrológica o instalación técnica (`calibracion`).
  5. Etapa 4: Estampillas e impuestos territoriales (`estampillas`).
  6. Cálculo del precio unitario final antes de IVA y sincronización del valor de venta sugerido al cliente.
* **RF03.3 (Edición de Ítems en 2 Columnas):** Formulario de edición con distribución balanceada:
  - **Columna izquierda:** Datos del producto (título, categoría, código, descripción, cantidad, IVA, imagen y Precio Unitario Final).
  - **Columna derecha:** Información de proveedor, código de proveedor, panel de Calculadora Dinámica de Ganancias y resumen de Valor Final con IVA reactivo en tiempo real.
* **RF03.4 (Persistencia de Ítems Temporales):** Mantenimiento continuo del borrador en curso y sus ítems temporales mientras el usuario navega entre módulos sin pérdida de datos.
* **RF03.5:** Tratamiento tributario de IVA (19% discriminado o exento 0%).
* **RF03.6:** Numeración consecutiva mensual inteligente con prefijo del asesor (Ej: `EB 01`, `EB 02`).
* **RF03.7 (Versionamiento y Revisiones):** Modificación de cotizaciones existentes generando revisiones numeradas (Ej: `EB 01_01`), protegiendo el historial comercial.
* **RF03.8 (Ciclo y Estados Comerciales):** Clasificación del estado de la negociación (`pendiente` 🟡, `concluida` 🟢, `descartada` 🔴) mediante selectores tipo *pill badge* interactivos vía AJAX para administradores.
* **RF03.9:** Generación de PDF profesional con diseño corporativo listo para impresión y envío al cliente, además de Hoja de Respaldo interna confidencial.

### 📦 RF04 — Gestión de Órdenes de Compra (P.O. - Purchase Orders)
* **RF04.1:** Conversión de cotizaciones en estado `pendiente` en Órdenes de Compra a proveedores (bloqueo automático para cotizaciones concluidas o descartadas).
* **RF04.2:** Selección granular de ítems asociados a un mismo proveedor.
* **RF04.3:** Formulario de datos de despacho, condiciones de pago, datos bancarios de consignación, IVA y retenciones en la fuente.
* **RF04.4 (Gestión de Estados y Pestañas):** Clasificación de órdenes en pestañas 🟡 **Órdenes Pendientes** y 🟢 **Órdenes Completadas** con selector *pill badge* reactivo vía AJAX para administradores.
* **RF04.5 (Selección y Exportación Granular):** Checkboxes individuales y casilla maestra para exportación selectiva a PDF y archivo Excel (`.xls`) con sumatoria consolidada.
* **RF04.6 (Visor y Descarga):** Botón `👁️ Ver P.O.` con modal interactivo para visualización e impresión/descarga directa del documento.

### 🩺 RF05 — Catálogo de Productos Médicos
* **RF05.1:** Cuadrícula moderna de productos en tarjetas (`.prod-grid` / `.prod-card`) con título, código, etiquetas de IVA y estado.
* **RF05.2 (Buscador Dinámico en Tiempo Real):** Filtrado instantáneo en vivo mientras el usuario escribe sin recargar la página.
* **RF05.3:** Registro y edición con subida y sanitización segura de imágenes (validación de extensiones y tipos MIME reales).
* **RF05.4:** Exportación de catálogo consolidado en PDF con fotografías médicas miniatura de alta resolución y optimización de memoria.

### 🏢 RF06 — Directorio de Clientes y Entidades
* **RF06.1:** Registro de entidades de salud, hospitales, clínicas y médicos particulares con NIT, departamento, municipio/ciudad, contacto, teléfono, correo y dirección.
* **RF06.2:** Autocompletado rápido de clientes y departamentos en el flujo de finalización de cotizaciones.

### 👥 RF07 — Administración de Usuarios (Solo Admin)
* **RF07.1:** Diseño moderno de tarjetas de usuarios (`.usr-card`) con avatar, rol, código, correo, teléfono y acciones.
* **RF07.2:** Creación y edición de asesores comerciales con asignación de código único (`codigo_asesor`).
* **RF07.3:** Campos de correo y teléfono opcionales.
* **RF07.4:** Restablecimiento administrativo de contraseñas con un solo clic (asigna por defecto el documento del usuario).

### 📈 RF08 — Reportes y Estadísticas
* **RF08.1:** Filtros avanzados por rango de fechas, asesor y cliente.
* **RF08.2 (Gráfico Evolutivo Comparativo):** Visualización interactiva en Chart.js de la relación mensual entre **Cotizaciones Totales** vs. **Cotizaciones Concluidas**.
* **RF08.3:** Exportación consolidada de reportes en PDF con diseño formal, KPIs, tops y evolución mensual sincronizada.

---

## 2. Requisitos No Funcionales (RNF)

* **RNF01 (Seguridad y Resiliencia):** Todas las consultas SQL implementadas con PDO y parámetros preparados contra inyecciones SQL.
* **RNF02 (Integridad de Peticiones):** Validación universal de tokens CSRF en todas las operaciones POST y DELETE.
* **RNF03 (Arquitectura CSS Modular y Limpia):** Estilos organizados en `css/modules/` (SMACSS/ITCSS) sin código inline en las vistas PHP.
* **RNF04 (Compatibilidad y Portabilidad):** Arquitectura ejecutable en servidores LAMP/LEMP tradicionales o contenedores Docker con script `deploy.sh` sin pérdida de datos ni destrucción de volúmenes.
* **RNF05 (Calidad de Código):** Cobertura de pruebas unitarias automatizadas con PHPUnit y pipeline de CI en GitHub Actions.
* **RNF06 (Diseño Responsivo y Experiencia de Usuario):** Interfaz adaptada para escritorio y pantallas móviles con tipografía Inter, transiciones suaves y componentes accesibles.
