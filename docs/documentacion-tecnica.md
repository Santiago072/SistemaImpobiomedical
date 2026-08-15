# 📘 Documentación Técnica — Sistema Impobiomedical

**Versión:** 2.1.0  
**Fecha:** Agosto 2026  
**Tecnología:** PHP 8.2 (PDO, MVC, Arquitectura Modular) · MariaDB / MySQL 8.0 · Vanilla CSS (SMACSS/ITCSS) · DomPDF · PHPUnit 10

---

## 📑 Tabla de Contenidos

1. [Resumen del Sistema](#1-resumen-del-sistema)
2. [Arquitectura General y Patrones de Diseño](#2-arquitectura-general-y-patrones-de-diseño)
3. [Estructura Completa de Directorios](#3-estructura-completa-de-directorios)
4. [Esquema de Base de Datos y Modelo de Datos](#4-esquema-de-base-de-datos-y-modelo-de-datos)
5. [Módulos del Sistema y Controladores](#5-módulos-del-sistema-y-controladores)
6. [Flujo Comercial Completo](#6-flujo-comercial-completo)
7. [Algoritmo de la Calculadora Dinámica de Ganancias](#7-algoritmo-de-la-calculadora-dinámica-de-ganancias)
8. [Seguridad y Protección de Capas](#8-seguridad-y-protección-de-capas)
9. [Generación de Documentos PDF](#9-generación-de-documentos-pdf)
10. [Variables de Sesión y Control de Estado](#10-variables-de-sesión-y-control-de-estado)
11. [Suite de Pruebas Automatizadas (PHPUnit)](#11-suite-de-pruebas-automatizadas-phpunit)

---

## 1. Resumen del Sistema

El **Sistema Impobiomedical** es una plataforma web especializada para la gestión comercial y técnica de equipos, insumos y servicios biomédicos para **Impobiomedical — Soluciones y Servicios de Tecnología Biomédica**. 

Centraliza el ciclo comercial completo:
- Creación de cotizaciones dinámicas con cálculo automatizado de márgenes de utilidad, fletes, calibración metrológica e impuestos.
- Emisión de documentos PDF oficiales para entidades de salud (hospitales, clínicas, laboratorios y médicos independientes).
- Control de estados comerciales (`pendiente`, `concluida`, `descartada`) con actualización reactiva en tiempo real vía AJAX.
- Generación de órdenes de compra (P.O. - Purchase Orders) por proveedor seleccionado.
- Hojas internas de respaldo confidencial de costos de proveedores.
- Directorio de entidades de salud y catálogo médico con almacenamiento sanitizado de fotografías.
- Panel analítico con indicadores clave de rendimiento (KPIs) y reportes ejecutivos.

---

## 2. Arquitectura General y Patrones de Diseño

El sistema está estructurado bajo el patrón **Modelo-Vista-Controlador (MVC)** con aplicación estricta de **Principios SOLID**:

```text
Cliente (Navegador) ──► index.php?module=...&action=... (Front Controller)
                               │
            ┌──────────────────┼──────────────────┐
            ▼                  ▼                  ▼
     [Seguridad HTTP]   [Control de Sesión]  [Validación CSRF & Rate Limit]
     (Security Headers)  (verificar_auth)     (Tokens & Prevención Fuerza Bruta)
                               │
                               ▼
                    [Controladores / Controllers]
         (Coordina peticiones, valida entradas y delega lógica)
                               │
            ┌──────────────────┴──────────────────┐
            ▼                                     ▼
   [Servicios de Negocio]                 [Modelos PDO / Datos]
   - FinalizarCotizacionService          - CotizacionModel (Locks & Transacciones)
   - CotizacionItemService               - OrdenCompraModel
   - FileUploadService (MIME safe)       - ProductoModel, ClienteModel
   - DomPDF Engine                       - UsuarioModel, EstadisticaModel
            │                                     │
            ▼                                     ▼
    [Vistas HTML / UI]                    [Base de Datos MySQL / MariaDB]
```

### Patrón de Enrutamiento Frontal:
| Módulo | Acción | Ruta | Propósito |
|---|---|---|---|
| Autenticación | Login | `/?module=` | Pantalla de inicio de sesión segura |
| Panel | Dashboard | `/?module=panel` | KPIs y accesos directos rápidos |
| Cotizaciones | Crear | `/?module=cotizaciones&action=crear&nueva=1` | Cotizador dinámico y calculadora |
| Cotizaciones | Finalizar | `/?module=cotizaciones&action=finalizar` | Cierre de cotización y datos de cliente |
| Cotizaciones | Consultar | `/?module=cotizaciones&action=consultar` | Tabla de cotizaciones y cambio de estado AJAX |
| Cotizaciones | PDF | `/?module=cotizaciones&action=generar_pdf&ver=EB01` | Visor / descarga de PDF oficial |
| Cotizaciones | Respaldo | `/?module=cotizaciones&action=ver_respaldo&numero=EB01` | Hoja confidencial de costos de proveedores |
| Órdenes | Consultar | `/?module=ordenes&action=consultar` | Listado de órdenes de compra emitidas |
| Órdenes | Crear P.O. | `/?module=ordenes&action=seleccionar_items&cotizacion=EB01` | Generar orden para un proveedor |
| Productos | Listar | `/?module=productos` | Catálogo médico y subida de imágenes |
| Clientes | Listar | `/?module=clientes` | Directorio de entidades de salud |
| Usuarios | Listar | `/?module=usuarios` | Gestión de cuentas de asesores y roles |
| Estadísticas | Reportes | `/?module=estadisticas` | Métricas y gráficos consolidados |

---

## 3. Estructura Completa de Directorios

```text
SistemaImpobiomedical/
├── app/
│   ├── controllers/            # Controladores del sistema (MVC)
│   │   ├── AuthController.php
│   │   ├── ClienteController.php
│   │   ├── CotizacionController.php
│   │   ├── EstadisticaController.php
│   │   ├── OrdenCompraController.php
│   │   ├── PanelController.php
│   │   ├── ProductoController.php
│   │   └── UsuarioController.php
│   │
│   ├── models/                 # Modelos con persistencia PDO y sentencias preparadas
│   │   ├── ClienteModel.php
│   │   ├── CotizacionModel.php
│   │   ├── EstadisticaModel.php
│   │   ├── OrdenCompraModel.php
│   │   ├── ProductoModel.php
│   │   └── UsuarioModel.php
│   │
│   ├── services/               # Lógica de negocio y utilidades desacopladas
│   │   ├── CotizacionItemService.php
│   │   ├── FileUploadService.php
│   │   └── FinalizarCotizacionService.php
│   │
│   └── views/                  # Vistas modulares (HTML + PHP puro)
│       ├── auth/               # Login y cambio de credenciales
│       ├── clientes/           # Gestión y modales de clientes
│       ├── cotizaciones/       # Cotizador, finalizar, consultar, respaldo y PDF
│       ├── estadisticas/       # Métricas y reporte consolidado
│       ├── layout/             # Header, menú lateral, topbar, paginación y footer
│       ├── ordenes/            # Consultar y generar órdenes de compra
│       ├── panel/              # Dashboard principal
│       ├── productos/          # Catálogo médico y exportación PDF con imágenes
│       └── usuarios/           # Gestión de asesores y restablecimiento de claves
│
├── config/
│   ├── .env.example            # Plantilla de variables de entorno
│   ├── EnvLoader.php           # Cargador seguro de configuración .env
│   ├── conexion.php            # Singleton PDO con manejo seguro de excepciones
│   └── seguridad.php           # Biblioteca central de funciones de seguridad
│
├── css/
│   ├── estilos.css             # Entry point de la arquitectura CSS
│   └── modules/                # 7 submódulos SMACSS/ITCSS
│       ├── auth.css            # Estilos de login
│       ├── base.css            # Reset, tipografías y canvas
│       ├── components.css      # Botones, tablas, badges y modales
│       ├── forms.css           # Formularios, inputs y selects
│       ├── layout.css          # Sidebar, topbar y grillas principales
│       ├── responsive.css      # Media queries adaptables
│       └── variables.css       # Tokens, paleta de colores y transiciones
│
├── docs/                       # Documentación técnica y funcional exhaustiva
│   ├── ARQUITECTURA_Y_COMPONENTES.md
│   ├── CHANGELOG.md
│   ├── CONTRIBUTING.md
│   ├── ESPECIFICACION_REQUISITOS.md
│   ├── MANUAL_USUARIO.md
│   ├── PLAN_DE_IMPLEMENTACION.md
│   └── documentacion-tecnica.md
│
├── public/
│   └── js/
│       └── script.js           # Toggle de menú, confirmaciones y anti-doble envío
│
├── tests/
│   └── Unit/                   # Suite de pruebas unitarias PHPUnit 10
│       ├── CalculosComercialesTest.php
│       └── SeguridadTest.php
│
├── uploads/                    # Almacenamiento persistente de imágenes sanitizadas
├── .github/workflows/ci.yml    # Pipeline de Integración Continua (CI)
├── BD.txt                      # Script SQL DDL de la base de datos
├── Dockerfile                  # Imagen multi-stage PHP 8.2
├── docker-compose.yml          # Orquestación de servicios y volúmenes
└── deploy.sh                   # Script automatizado de actualización sin caída
```

---

## 4. Esquema de Base de Datos y Modelo de Datos

**Motor:** MySQL 8.0+ / MariaDB 10.4+  
**Collation:** `utf8mb4_unicode_ci`  
**Storage Engine:** `InnoDB` (Soporte transaccional y claves foráneas)

```mermaid
erDiagram
    usuarios ||--o{ cotizaciones : crea
    usuarios ||--o{ ordenes_compra : emite
    clientes ||--o{ cotizaciones : recibe
    cotizaciones ||--|{ cotizacion_items : contiene
    cotizaciones ||--o{ ordenes_compra : origina
    ordenes_compra ||--|{ orden_compra_items : contiene
    productos ||--o{ cotizacion_items : provee

    usuarios {
        int id PK
        string codigo UK
        string documento UK
        string nombre
        string correo
        string telefono
        string cargo
        string password
        string rol
        string estado
    }

    clientes {
        int id PK
        string nombre
        string nit UK
        string departamento
        string municipio
        string direccion
        string nombre_contacto
        string telefono
        string correo
        string estado
    }

    productos {
        int id PK
        string codigo_producto
        string titulo
        string foto
        text descripcion
        decimal precio
        string categoria
        string iva
        string estado
    }

    cotizaciones {
        int id PK
        string numero_cotizacion
        int usuario_id FK
        string usuario_codigo
        int cliente_id FK
        string cliente_nombre
        string cliente_nit
        string cliente_departamento
        string cliente_ciudad
        string cliente_direccion
        string cliente_telefono
        string cliente_correo
        string cliente_contacto
        date fecha_creacion
        int dias_validez
        date fecha_validez
        string condiciones_pago
        text observaciones
        string estado
        string estado_comercial
    }

    cotizacion_items {
        int id PK
        int cotizacion_id FK
        int producto_id FK
        string titulo
        string foto
        text descripcion
        int cantidad
        decimal precio
        decimal precio_proveedor
        decimal porcentaje_utilidad
        decimal flete
        decimal calibracion
        decimal estampillas
        string iva
        decimal porcentaje_iva
        string tiempo_entrega
        string categoria
        string codigo_producto
        string proveedor
        string codigo_proveedor
        string calc_ops
    }

    ordenes_compra {
        int id PK
        string numero_orden UK
        int cotizacion_id FK
        string numero_cotizacion
        int usuario_id FK
        string proveedor_nombre
        string proveedor_nit
        string condiciones_pago
        string tiempo_entrega
        date fecha_emision
        decimal subtotal
        decimal iva_total
        decimal retencion_fuente
        decimal total
    }

    orden_compra_items {
        int id PK
        int orden_compra_id FK
        int cotizacion_item_id FK
        string codigo_producto
        string descripcion
        int cantidad
        decimal precio_unitario
        decimal subtotal
        decimal valor_iva
        decimal total
    }
```

---

## 5. Módulos del Sistema y Controladores

### 5.1 Autenticación (`AuthController.php`)
- **Login Seguro:** Valida documento y contraseña contra `password_verify()`.
- **Regeneración de ID de Sesión:** Ejecuta `regenerar_sesion()` tras login exitoso para prevenir ataques de Session Fixation.
- **Sugerencia de Cambio de Contraseña:** Detecta si el usuario ingresó con su documento como clave y activa un modal no invasivo para actualizarla.
- **Protección Fuerza Bruta:** Rate Limiting estricto de máximo 5 intentos fallidos cada 5 minutos.

### 5.2 Dashboard Principal (`PanelController.php`)
- **Métricas:** Conteo dinámico de cotizaciones totales, cotizaciones del mes (por asesor o global para admin), clientes activos y catálogo de productos.
- **Accesos Directos:** Botones de acción rápida adaptados por rol.

### 5.3 Cotizador y Negociaciones (`CotizacionController.php`)
- **Manejo de Borrador Activo:** Sesión `$_SESSION['cotizacion_id']` para construir la cotización paso a paso sin perder datos.
- **Revisiones Numéricas:** Si se modifica una cotización existente, el sistema genera sufijos automáticos (`_01`, `_02`) conservando el historial.
- **Actualización de Estado Comercial:** Endpoint `cambiar_estado` protegido con `verificar_admin()`, token CSRF y rate limit con respuesta JSON asíncrona.

### 5.4 Catálogo de Productos (`ProductoController.php`)
- **Carga de Fotografías:** Integrado con `FileUploadService` para validación de tipo MIME real (`finfo`), generación de nombres únicos (`bin2hex(random_bytes(16))`) y control de tamaño.
- **Exportación PDF:** Genera catálogo estructurado en dos columnas con miniaturas e imágenes optimizadas.

### 5.5 Órdenes de Compra (`OrdenCompraController.php`)
- **Aislamiento por Proveedor:** Agrupa los ítems cotizados y permite generar la Orden de Compra (P.O.) únicamente con los productos del proveedor seleccionado.
- **Cálculo de Retenciones:** Aplica retención en la fuente y discriminación de IVA para contabilidad.

### 5.6 Directorio de Clientes (`ClienteController.php`)
- **Autocompletado en Vivo:** Endpoint AJAX para búsqueda instantánea por NIT o Razón Social.
- **Ubicación Geográfica:** Almacena departamento y municipio de cada entidad de salud.

### 5.7 Gestión de Usuarios (`UsuarioController.php`)
- **Control de Asesores:** Asigna código de cotización de 2 a 3 letras único por asesor (ej. `EB`).
- **Reseteo Administrativo:** Restablece contraseñas al número de documento con un solo clic.

---

## 6. Flujo Comercial Completo

El siguiente diagrama resume el ciclo de vida de una negociación desde la creación de la oferta hasta la compra a proveedores y su seguimiento:

```mermaid
flowchart TD
    Inicio(["🏁 Inicio: Asesor Comercial"]) --> Paso1["1️⃣ Seleccionar o Crear Ítem\n(Catálogo médico o ingreso manual)"]
    Paso1 --> Paso2["2️⃣ Calculadora de Ganancias\n(Costo proveedor + Utilidad + Flete + Calibración)"]
    Paso2 --> Paso3{"¿Agregar más productos?"}
    Paso3 -- Sí --> Paso1
    Paso3 -- No --> Paso4["3️⃣ Completar Datos del Cliente\n(Autocompletar NIT, Departamento y Ciudad)"]
    Paso4 --> Paso5["4️⃣ Finalizar Cotización\n(Asigna consecutivo automático: EB 01)"]
    Paso5 --> Paso6["📄 Generar PDF Oficial\n(Documento para el cliente con DomPDF)"]
    Paso5 --> Paso7["📋 Hoja de Respaldo\n(Costos reales confidenciales y desglose)"]
    
    Paso5 --> Estado{"5️⃣ Seguimiento Comercial\n(Administrador)"}
    Estado -- 🟡 En negociación --> Pendiente["🟡 Estado: Pendiente"]
    Estado -- 🔴 Cliente desiste --> Descartada["🔴 Estado: Descartada"]
    Estado -- 🟢 Oferta Aprobada --> Concluida["🟢 Estado: Concluida"]

    Concluida --> Orden["6️⃣ Generar Orden de Compra (P.O.)\n(Seleccionar ítems por proveedor y retenciones)"]
    Orden --> Fin(["📦 Emisión de Orden PDF al Proveedor"])

    style Inicio fill:#10757e,stroke:#0d5c63,color:#fff
    style Fin fill:#10757e,stroke:#0d5c63,color:#fff
    style Concluida fill:#16a34a,stroke:#15803d,color:#fff
    style Pendiente fill:#ca8a04,stroke:#a16207,color:#fff
    style Descartada fill:#dc2626,stroke:#b91c1c,color:#fff
    style Paso2 fill:#f8fafc,stroke:#cbd5e1,color:#1e293b
    style Paso4 fill:#f8fafc,stroke:#cbd5e1,color:#1e293b
    style Paso6 fill:#e0f2fe,stroke:#38bdf8,color:#0369a1
    style Paso7 fill:#fef3c7,stroke:#f59e0b,color:#92400e
    style Orden fill:#dcfce7,stroke:#22c55e,color:#166534
```

---

## 7. Algoritmo de la Calculadora Dinámica de Ganancias

La calculadora comercial (`CotizacionItemService.php`) evalúa en orden estricto las 4 etapas de sobrecosto y margen:

$$\text{Costo Base} = \text{Precio Proveedor}$$

1. **Etapa 1: Utilidad ($\%$, $\$$, $\div$)**
   - Si porcentaje: $\text{Sub}_1 = \text{Costo Base} \times (1 + \frac{\text{utilidad}}{100})$
   - Si factor divisor: $\text{Sub}_1 = \frac{\text{Costo Base}}{1 - \frac{\text{utilidad}}{100}}$
2. **Etapa 2: Flete Logístico**
   - $\text{Sub}_2 = \text{Sub}_1 + \text{Flete}$
3. **Etapa 3: Calibración Metrológica**
   - $\text{Sub}_3 = \text{Sub}_2 + \text{Calibración}$
4. **Etapa 4: Estampillas e Impuestos Territoriales**
   - $\text{Precio Unitario} = \text{Sub}_3 + \text{Estampillas}$
5. **Cálculo Tributario de IVA**
   - Si aplica IVA (19%): $\text{Total Unitario} = \text{Precio Unitario} \times 1.19$
   - Si es exento (0%): $\text{Total Unitario} = \text{Precio Unitario}$

Todas las operaciones adicionales intermedias quedan serializadas en la columna JSON `calc_ops` para trazabilidad exacta en la hoja de respaldo.

---

## 8. Seguridad y Protección de Capas

| Vector de Ataque | Mecanismo de Defensa Implementado | Ubicación en Código |
|---|---|---|
| **Inyección SQL** | Sentencias preparadas PDO con parámetros obligatorios `:param`. | Todos los archivos en `app/models/` |
| **Cross-Site Scripting (XSS)** | Escape estricto con `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` y sanitización. | `config/seguridad.php` y vistas |
| **CSRF (Cross-Site Request Forgery)** | Tokens criptográficos aleatorios `random_bytes(32)` validados con `hash_equals()` en todas las peticiones POST/DELETE. | `config/seguridad.php` |
| **Clickjacking** | Cabecera HTTP `X-Frame-Options: SAMEORIGIN`. | `index.php` |
| **MIME-Sniffing** | Cabecera HTTP `X-Content-Type-Options: nosniff`. | `index.php` |
| **Content Security Policy (CSP)** | Directiva estricta restringiendo scripts y estilos únicamente al origen y CDNs autorizados. | `index.php` |
| **Ataques de Fuerza Bruta** | Rate limiting en memoria con ventana de tiempo fija (`verificar_rate_limit()`). | `config/seguridad.php` |
| **Falsificación de Archivos** | Verificación de extensión en lista blanca y validación de cabecera binaria MIME (`finfo_open`). | `app/services/FileUploadService.php` |
| **Session Hijacking** | Cookies con flags `HttpOnly`, `SameSite=Strict`, `Secure` y expiración por inactividad a 3600s. | `config/seguridad.php` |

---

## 9. Generación de Documentos PDF

El sistema utiliza la biblioteca **DomPDF** optimizada para el entorno de producción:

1. **Cotización Oficial para Clientes (`app/views/cotizaciones/generar_pdf.php`)**:
   - Encabezado institucional con logo corporativo de Impobiomedical.
   - Datos completos de la entidad y ciudad.
   - Tabla de productos cotizados con especificaciones técnicas, precios unitarios, subtotales, IVA discriminado y condiciones comerciales.
2. **Hoja de Respaldo de Proveedores (`app/views/cotizaciones/respaldo.php`)**:
   - Documento interno confidencial con costos de proveedor, desglose de márgenes por ítem y utilidad bruta proyectada.
3. **Orden de Compra (`app/views/ordenes/`)**:
   - Documento P.O. formal con datos bancarios del proveedor y desglose tributario.
4. **Catálogo Médico con Imágenes (`app/views/productos/pdf.php`)**:
   - Renderizado optimizado de fotografías médicas con reglas `page-break-inside: avoid`, `@ini_set('memory_limit', '256M')` y timeout extendido a 120s.

---

## 10. Variables de Sesión y Control de Estado

| Variable | Tipo | Propósito |
|---|---|---|
| `$_SESSION['usuario_id']` | `int` | ID del usuario autenticado en la base de datos |
| `$_SESSION['usuario_nombre']` | `string` | Nombre completo del usuario |
| `$_SESSION['usuario_codigo']` | `string` | Código de asesor (ej. `EB`, `HM`) |
| `$_SESSION['usuario_cargo']` | `string` | Cargo del usuario |
| `$_SESSION['rol']` | `string` | Nivel de permisos (`admin` o `usuario`) |
| `$_SESSION['csrf_token']` | `string` | Token de seguridad activo |
| `$_SESSION['cotizacion_id']` | `int` | ID de la cotización en borrador en construcción |
| `$_SESSION['cotizacion_revision_de']` | `string` | Número base de cotización que se está modificando |
| `$_SESSION['LAST_ACTIVITY']` | `int` | Timestamp de última interacción para expiración automática |

---

## 11. Suite de Pruebas Automatizadas (PHPUnit)

El proyecto cuenta con una suite de pruebas unitarias configurada en `phpunit.xml`:

```bash
# Ejecutar todas las pruebas unitarias
./vendor/bin/phpunit
```

### Cobertura de Pruebas:
- **`CalculosComercialesTest.php`**: Valida cálculos de margen de utilidad, adición de fletes, calibración, estampillas, discriminación de IVA (19% y 0%) y generación de sufijos de revisión (`EB 01_01`).
- **`SeguridadTest.php`**: Valida generación y rotación de tokens CSRF, sanitización contra inyecciones XSS, cifrado `bcrypt` y validación de extensiones de archivos.
