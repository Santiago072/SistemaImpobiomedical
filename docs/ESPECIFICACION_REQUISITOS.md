# 📋 Especificación de Requisitos y Alcance Funcional — Sistema Impobiomedical

Este documento formaliza los requisitos funcionales (RF), requisitos no funcionales (RNF), reglas de negocio y modelo de datos del **Sistema Impobiomedical**.

---

## 1. Módulos y Requisitos Funcionales (RF)

### 🔐 RF01 — Autenticación y Control de Acceso
* **RF01.1:** Inicio de sesión mediante Código/Documento y Contraseña cifrada con `bcrypt`.
* **RF01.2:** Control de roles: **Administrador** (acceso total a configuración, usuarios y métricas globales) y **Usuario/Asesor** (gestión de sus propias cotizaciones y catálogo médico).
* **RF01.3:** Bloqueo ante ataques de fuerza bruta mediante Rate Limiting en memoria de sesión.
* **RF01.4:** Cierre de sesión seguro con invalidación total de cookies y almacenamiento de sesión.

### 📊 RF02 — Panel de Control (Dashboard)
* **RF02.1:** Indicadores clave de rendimiento (KPIs): Total de cotizaciones, productos registrados, clientes y cotizaciones del mes.
* **RF02.2:** Accesos directos rápidos a las funciones operativas más frecuentes.

### 📝 RF03 — Cotizador Dinámico y Calculadora Comercial
* **RF03.1:** Creación de cotizaciones con buscador interactivo en vivo de productos del catálogo.
* **RF03.2 (Calculadora Dinámica de Ganancias):** Cálculo automatizado paso a paso:
  1. Precio base proveedor ($).
  2. Porcentaje de utilidad sobre el costo (%).
  3. Flete ($).
  4. Calibración ($).
  5. Estampillas ($).
  6. Cálculo del precio unitario final antes de IVA.
* **RF03.3:** Tratamiento tributario de IVA (19% discriminado o exento 0%).
* **RF03.4:** Numeración consecutiva mensual inteligente con prefijo del asesor (Ej: `EB 01`, `EB 02`).
* **RF03.5 (Versionamiento y Revisiones):** Posibilidad de modificar cotizaciones originales generando una revisión numerada (Ej: `EB 01_01`), protegiendo el historial comercial.
* **RF03.6 (Ciclo y Estados Comerciales):** Clasificación del estado de la negociación (`pendiente`, `concluida`, `descartada`) editable en tiempo real vía AJAX por administradores y filtrable en búsquedas.
* **RF03.7:** Generación de PDF profesional con diseño corporativo listo para impresión y envío por correo.

### 📦 RF04 — Gestión de Órdenes de Compra (P.O. - Purchase Orders)
* **RF04.1:** Conversión de cotizaciones aprobadas en Órdenes de Compra a proveedores.
* **RF04.2:** Selección granular de ítems asociados a un mismo proveedor.
* **RF04.3:** Formulario de datos de despacho, condiciones de pago, datos bancarios y retenciones.
* **RF04.4:** Generación de reporte PDF formal de la Orden de Compra para el proveedor.

### 🩺 RF05 — Catálogo de Productos Médicos
* **RF05.1:** Registro y edición de productos con título, código, descripción, categoría médica, IVA aplicable y foto.
* **RF05.2:** Subida y sanitización segura de imágenes (validación de extensiones y tipos MIME reales).
* **RF05.3:** Exportación de catálogo consolidado en PDF con renderizado de fotografías médicas miniatura de alta resolución y optimización de memoria.
* **RF05.4:** Paginación optimizada y filtros de búsqueda por nombre o categoría.

### 🏢 RF06 — Directorio de Clientes y Entidades
* **RF06.1:** Registro de entidades de salud, hospitales, clínicas y médicos particulares con NIT, departamento, municipio/ciudad, contacto, teléfono, correo y dirección.
* **RF06.2:** Autocompletado rápido de clientes y departamentos en el flujo de finalización de cotizaciones.

### 👥 RF07 — Administración de Usuarios (Solo Admin)
* **RF07.1:** Creación y edición de asesores comerciales con asignación de código único (`codigo_asesor`).
* **RF07.2:** Campos de correo y teléfono opcionales.
* **RF07.3:** Restablecimiento administrativo de contraseñas con un solo clic (por defecto asigna el documento del usuario).
* **RF07.4:** Toggle interactivo para ver/ocultar contraseñas.

### 📈 RF08 — Reportes y Estadísticas
* **RF08.1:** Filtros avanzados por rango de fechas, asesor y cliente.
* **RF08.2:** Exportación consolidada de reportes en PDF con diseño formal.

---

## 2. Requisitos No Funcionales (RNF)

* **RNF01 (Seguridad y Resiliencia):** Todas las consultas SQL implementadas con PDO y parámetros preparados.
* **RNF02 (Integridad de Peticiones):** Validación universal de tokens CSRF en todas las operaciones POST y DELETE.
* **RNF03 (Compatibilidad y Portabilidad):** Arquitectura ejecutable en servidores LAMP/LEMP tradicionales o contenedores Docker.
* **RNF04 (Calidad de Código):** Cobertura de pruebas unitarias automatizadas con PHPUnit y pipeline de CI en GitHub Actions.
* **RNF05 (Diseño Responsivo):** Interfaz adaptada para escritorio, tablets y dispositivos móviles con tipografía Inter y paleta médica corporativa.
