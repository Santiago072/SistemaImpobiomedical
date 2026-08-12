# 🏥 Sistema Impobiomedical — Gestión de Cotizaciones y Catálogo Médico

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql)](https://www.mysql.com/)
[![PHPUnit](https://img.shields.io/badge/Tests-PHPUnit%2010-37b24d?style=flat-square&logo=php)](phpunit.xml)
[![CI](https://img.shields.io/badge/CI-GitHub%20Actions-2088FF?style=flat-square&logo=githubactions)](.github/workflows/ci.yml)
[![Licencia](https://img.shields.io/badge/Licencia-Comercial%20Propietaria-e03c3c?style=flat-square)](LICENSE)
[![Versión](https://img.shields.io/badge/Versión-v2.0.0-10757e?style=flat-square)](CHANGELOG.md)

Sistema web MVC de gestión comercial y médica para **Impobiomedical — Soluciones y Servicios de Tecnología Biomédica**. Administra el ciclo completo de cotizaciones dinámicas con calculadora de ganancias, catálogo de productos médicos, directorio de entidades/clientes, órdenes de compra por proveedor (P.O.), reportes estadísticos y control de acceso por roles.

---

## 📚 Documentación Técnica y Manuales

Toda la documentación especializada y diagramas se encuentran centralizados en el directorio [`docs/`](docs/):

* 🏗️ **[Arquitectura y Componentes](docs/ARQUITECTURA_Y_COMPONENTES.md):** Diagramas C4/Mermaid, capas de persistencia PDO, matriz SOLID y flujos de datos.
* 🤝 **[Guía para Desarrolladores y Colaboradores](docs/CONTRIBUTING.md):** Estándares PSR-12, convenciones de commit, tipado y ejecución de pruebas.
* 🚀 **[Guía de Despliegue y Mantenimiento](docs/DESPLIEGUE_Y_MANTENIMIENTO.md):** Despliegue en VPS (Ubuntu/Nginx/Apache), Docker, políticas de backup y certificados SSL.
* 📋 **[Especificación de Requisitos](docs/ESPECIFICACION_REQUISITOS.md):** Requisitos funcionales (RF) y no funcionales (RNF) módulo por módulo.
* 📖 **[Manual de Usuario](docs/MANUAL_DE_USUARIO.md):** Guía operativa detallada para administradores y asesores comerciales.

---

## 🛠 Stack Tecnológico

* **Backend:** PHP 8.1+ / 8.2 (Arquitectura MVC sin framework pesado).
* **Persistencia:** `PDO` (PHP Data Objects) con sentencias preparadas y parámetros nombrados.
* **Base de Datos:** MySQL 8.0+ / MariaDB 10.4+.
* **Pruebas Automatizadas:** [PHPUnit 10](https://phpunit.de/) con suite unitaria y de cálculo comercial.
* **Integración Continua:** GitHub Actions (`.github/workflows/ci.yml`).
* **Generación de PDFs:** [DomPDF](https://github.com/dompdf/dompdf) integrado vía Composer.
* **Frontend:** HTML5 semántico, CSS3 Vanilla modularizado (SMACSS/ITCSS en 7 submódulos), Bootstrap Icons y Google Fonts (Inter / Outfit).
* **Contenedores:** Docker y Docker Compose con persistencia de volúmenes.

---

## 🏛 Arquitectura del Sistema

```
index.php (Front Controller & Router con Security Headers)
 ├── AuthController          → Inicio de sesión, logout y control de sesiones
 ├── PanelController         → Dashboard con KPIs e indicadores
 ├── CotizacionController    → Flujo de cotizaciones, calculadora de ganancias y revisiones
 │    ├── CotizacionItemService      → Cálculo dinámico de utilidades, fletes, calibración e IVA
 │    └── FinalizarCotizacionService → Asignación de consecutivos (EB 01) y cierre
 ├── OrdenCompraController   → Generación de órdenes de compra (P.O.) por proveedor
 ├── ProductoController      → Catálogo médico y subida segura de imágenes (FileUploadService)
 ├── ClienteController       → Directorio de entidades de salud y búsqueda AJAX
 ├── UsuarioController       → Gestión de asesores, roles y reseteo de contraseñas
 └── EstadisticaController   → Métricas de venta y reportes consolidados en PDF
```

---

## ⚡ Instalación y Puesta en Marcha

### 1. Clonar el repositorio
```bash
git clone https://github.com/Santiago072/SistemaImpobiomedical.git
cd SistemaImpobiomedical
```

### 2. Instalar dependencias
```bash
composer install
```

### 3. Configurar variables de entorno
Copia `.env.example` a `config/.env` y ajusta tus credenciales locales:
```ini
DB_HOST=localhost
DB_NAME=sistema_impobiomedical
DB_USER=root
DB_PASS=
APP_BASE=/SistemaImpobiomedical/
SESSION_LIFETIME=3600
COOKIE_SECURE=0
```

### 4. Importar base de datos
Importa el esquema y datos iniciales desde `BD.txt` en MySQL / phpMyAdmin.

### 5. Ejecutar la suite de pruebas unitarias
```bash
composer test
```

---

## 🔒 Controles de Seguridad Implementados

1. **Protección SQLi:** 100% de las consultas a través de `PDO` con parámetros preparados.
2. **Protección CSRF:** Tokens criptográficos verificados en cada formulario y petición AJAX de eliminación.
3. **Protección XSS:** Escape universal de salidas HTML con `htmlspecialchars`.
4. **Cabeceras HTTP:** `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `HSTS`, `CSP` y `Permissions-Policy`.
5. **Rate Limiting:** Control de frecuencia en peticiones sensibles contra ataques de fuerza bruta.
6. **Protección Anti-Doble Envío:** Desactivación interactiva de botones submit durante el procesamiento.

---

## 📄 Licencia

Este software se distribuye bajo una **Licencia Comercial Propietaria**. Todos los derechos reservados © 2026 Santiago Lizcano. Consulta el archivo [LICENSE](LICENSE) para conocer los términos y restricciones legales.
