# 🏥 Sistema Impobiomedical — Gestión de Cotizaciones

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql)](https://www.mysql.com/)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=flat-square&logo=docker)](https://www.docker.com/)
[![Versión](https://img.shields.io/badge/Versión-v1.1.0-10757e?style=flat-square)](CHANGELOG.md)

Sistema web MVC de gestión comercial para **Impobiomedical — Soluciones y Servicios de Tecnología Biomédica**. Permite administrar cotizaciones, clientes, productos, órdenes de compra y usuarios con control de acceso por roles.

---

## 📋 Tabla de Contenidos

- [Descripción General](#-descripción-general)
- [Tecnologías](#-tecnologías)
- [Arquitectura](#-arquitectura)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Base de Datos](#-base-de-datos)
- [Requisitos del Sistema](#-requisitos-del-sistema)
- [Instalación Local (XAMPP)](#-instalación-local-xampp)
- [Instalación en Producción (Docker)](#-instalación-en-producción-docker--vps)
- [Variables de Entorno](#-variables-de-entorno)
- [Módulos del Sistema](#-módulos-del-sistema)
- [Seguridad](#-seguridad)
- [Despliegue](#-despliegue)

---

## 📖 Descripción General

Aplicación web que gestiona el ciclo comercial completo: creación de cotizaciones con cálculo de márgenes de ganancia, generación de PDFs profesionales para clientes, hojas de respaldo internas y órdenes de compra por proveedor.

**Roles disponibles:**
- **Administrador (`admin`)**: Acceso total — ve todos los datos, puede eliminar registros.
- **Usuario (`usuario`)**: Crea cotizaciones y gestiona clientes, pero solo ve sus propios registros.

---

## 🛠 Tecnologías

- **Backend**: PHP 8.x (MVC sin framework)
- **Base de Datos**: MySQL 8.0 / MariaDB
- **PDF**: [DomPDF](https://github.com/dompdf/dompdf) vía Composer
- **Frontend**: HTML5, CSS3 Vanilla, Bootstrap Icons, Google Fonts (Outfit)
- **Contenedor**: Docker + Docker Compose
- **Servidor Web**: Nginx (proxy reverso en VPS)
- **Deploy**: Script Bash (`deploy.sh`)

---

## 🏛 Arquitectura

Patrón **MVC** con Front Controller en `index.php`:

```
Petición HTTP → index.php (Router)
    ├── AuthController      → Login / Logout / Sesión
    ├── PanelController     → Dashboard KPIs
    ├── UsuarioController   → CRUD Usuarios (admin)
    ├── ProductoController  → CRUD Catálogo
    ├── CotizacionController→ Cotizaciones + PDF + Respaldo
    ├── OrdenCompraController → Órdenes de Compra
    └── ClienteController   → CRUD Clientes
          │
     *Model.php (Queries preparadas MySQLi)
          │
     *View .php (HTML + CSS inline)
```

**Principios aplicados**: SRP (un controlador por módulo), OCP (`$rutasMap` extensible sin cambiar el router), DRY (layout reutilizable).

---

## 📁 Estructura del Proyecto

```
SistemaImpobiomedical/
├── app/
│   ├── controllers/        ← Lógica de negocio
│   ├── models/             ← Acceso a datos (MySQLi preparado)
│   └── views/
│       ├── auth/           ← Login
│       ├── clientes/       ← Gestión clientes
│       ├── cotizaciones/   ← Crear, Consultar, PDF, Respaldo
│       ├── layout/         ← Header, Menú, Topbar, Paginación
│       ├── ordenes/        ← Órdenes de compra
│       ├── panel/          ← Dashboard
│       ├── productos/      ← Catálogo
│       └── usuarios/       ← Gestión usuarios
├── config/
│   ├── .env                ← Variables de entorno (NO subir al repo)
│   ├── conexion.php        ← Conexión MySQLi
│   ├── EnvLoader.php       ← Carga .env
│   └── seguridad.php       ← Sesiones, CSRF, Rate Limiting
├── css/estilos.css         ← Estilos globales
├── logo/                   ← Logos de la empresa
├── public/js/script.js     ← Scripts globales
├── uploads/                ← Fotos de productos (gitignored)
├── logs/                   ← Errores PHP (gitignored)
├── sessions/               ← Sesiones PHP (gitignored)
├── vendor/                 ← Composer (gitignored)
├── BD.txt                  ← Script SQL completo
├── CHANGELOG.md
├── AUDITORIA_SISTEMA.md
├── MANUAL_USUARIO.md
├── Dockerfile
├── docker-compose.yml
├── deploy.sh               ← Script despliegue VPS
└── index.php               ← Front Controller
```

---

## 🗄 Base de Datos

**BD**: `sistema_impobiomedical` | **Charset**: `utf8mb4_unicode_ci`

| Tabla | Descripción |
|-------|-------------|
| `usuarios` | Usuarios con roles (admin/usuario) |
| `clientes` | Clientes/entidades con datos de contacto |
| `productos` | Catálogo de productos con foto y categoría |
| `cotizaciones` | Cabecera con snapshot cliente + asesor |
| `cotizacion_items` | Ítems con márgenes almacenados en JSON (`calc_ops`) |

Script completo en [`BD.txt`](BD.txt). Incluye índices, constraints y usuario admin inicial.

---

## 💻 Requisitos del Sistema

**Local (XAMPP)**:
- PHP 8.x con extensiones: `mysqli`, `mbstring`, `gd`, `fileinfo`, `json`
- MySQL 8.0 / MariaDB 10.6+
- Apache con `mod_rewrite` habilitado
- Composer 2.x

**Producción (VPS)**:
- Docker Engine 24+, Docker Compose v2
- Nginx como proxy reverso
- SSL recomendado (Let's Encrypt)

---

## 🖥 Instalación Local (XAMPP)

```bash
# 1. Clonar repositorio
git clone https://github.com/Santiago072/SistemaImpobiomedical.git
cd SistemaImpobiomedical

# 2. Instalar dependencias PHP
composer install

# 3. Configurar entorno
cp .env.example config/.env
# Editar config/.env con tus datos de BD

# 4. Crear base de datos (en phpMyAdmin o MySQL CLI)
# SOURCE /ruta/al/BD.txt

# 5. Crear carpetas necesarias
mkdir uploads logs sessions
```

**Acceso**: `http://localhost/SistemaImpobiomedical/`

**Credenciales iniciales**:
- Email: `admin@impobiomedical.com`
- Contraseña: `Admin2026*` *(cambiar inmediatamente)*

---

## 🐳 Instalación en Producción (Docker + VPS)

```bash
# 1. Clonar en el VPS
git clone https://github.com/Santiago072/SistemaImpobiomedical.git /var/www/SistemaImpobiomedical
cd /var/www/SistemaImpobiomedical

# 2. Crear archivo .env
cp .env.example config/.env
# Editar con datos reales de producción

# 3. Levantar contenedores
docker compose up -d --build

# 4. Inicializar BD (primera vez)
docker compose exec db mysql -u root -p sistema_impobiomedical < BD.txt

# 5. Verificar estado
docker compose ps && docker compose logs app
```

---

## ⚙ Variables de Entorno

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| `DB_HOST` | Host de la BD | `localhost` / `db` |
| `DB_PORT` | Puerto MySQL | `3306` |
| `DB_NAME` | Nombre de la BD | `sistema_impobiomedical` |
| `DB_USER` | Usuario MySQL | `root` |
| `DB_PASS` | Contraseña MySQL | *(vacío en local)* |
| `APP_BASE` | URL base | `/SistemaImpobiomedical/` |
| `SESSION_LIFETIME` | Inactividad en segundos | `3600` |

---

## 📦 Módulos del Sistema

| Módulo | Ruta | Roles |
|--------|------|-------|
| Dashboard | `?module=panel` | Todos |
| Gestión Usuarios | `?module=usuarios` | Solo admin |
| Catálogo Productos | `?module=productos` | Todos (eliminar: admin) |
| Nueva Cotización | `?module=cotizaciones` | Todos |
| Consultar Cotizaciones | `?module=cotizaciones&action=consultar` | Todos (eliminar: admin) |
| Órdenes de Compra | `?module=ordenes` | Todos (eliminar: admin) |
| Gestión Clientes | `?module=clientes` | Todos (eliminar: admin) |

### Nueva Cotización — Flujo

1. **Paso 1 – Ítems**: Buscar producto AJAX → auto-completar formulario *o* ingresar manualmente. Calcular márgenes con calculadora JSON (Utilidad, Flete, Calibración, Estampillas). Agregar a lista temporal.
2. **Paso 2 – Cliente**: Buscar cliente AJAX → auto-completar *o* ingresar manualmente. Completar condiciones de pago y observaciones.
3. **Generación**: Número automático `CODIGO_USUARIOMM-NN`. Se puede Ver PDF o Volver a ítems.

### Consultar Cotizaciones — Acciones por registro

| Acción | Descripción | Rol |
|--------|-------------|-----|
| 👁 Ver PDF | Modal con PDF descargable para el cliente | Todos |
| 📋 Respaldo | Hoja interna con proveedores y márgenes | Todos |
| 🛒 Orden | Crear orden de compra seleccionando ítems | Todos |
| 🗑 Eliminar | Eliminar cotización permanentemente | Solo admin |

---

## 🔐 Seguridad

| Medida | Implementación |
|--------|----------------|
| Autenticación | `password_verify()` bcrypt |
| Autorización | Validación de rol en cada acción sensible |
| CSRF | Token por sesión, verificado en todos los POSTs |
| Rate Limiting | Por acción y por IP |
| SQL Injection | Queries preparadas `bind_param()` |
| XSS | `htmlspecialchars()` en toda salida |
| Sesiones | `httponly`, `use_only_cookies`, `SameSite=Strict` |
| Timeout | `LAST_ACTIVITY` verificado en cada request |
| File Upload | Validación MIME, extensión, tamaño (max 5MB) |
| Errores | Solo al log `/logs/php_errors.log` |

---

## 🚀 Despliegue

```bash
# Desde el VPS (o con acceso SSH)
bash deploy.sh
```

El script ejecuta: `git pull` → rebuild Docker si hay cambios → reinicio del contenedor → verificación de servicios.

> ⚠️ El archivo `config/.env` **no se sube al repositorio**. Mantenerlo manualmente en el VPS.

### Migraciones de BD

```bash
# Ejecutar ALTERs del archivo de migraciones
docker compose exec db mysql -u root -p sistema_impobiomedical < migraciones.sql
```

---

## 🗂 Documentación Adicional

- [`CHANGELOG.md`](CHANGELOG.md) — Historial de versiones
- [`MANUAL_USUARIO.md`](MANUAL_USUARIO.md) — Guía de uso para usuarios finales
- [`AUDITORIA_SISTEMA.md`](AUDITORIA_SISTEMA.md) — Estado de requisitos del sistema
- [`BD.txt`](BD.txt) — Script SQL completo
- [`FIX_DUPLICADOS.md`](FIX_DUPLICADOS.md) — Documentación técnica de validación de duplicados

---

**Empresa**: Impobiomedical — Soluciones y Servicios de Tecnología Biomédica  
**Repositorio**: [Santiago072/SistemaImpobiomedical](https://github.com/Santiago072/SistemaImpobiomedical)  
**Licencia**: Privado — Todos los derechos reservados

> *"Tecnología Biomédica de confianza"*
