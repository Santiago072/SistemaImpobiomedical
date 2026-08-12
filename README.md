# 🏥 Sistema Impobiomedical — Gestión de Cotizaciones y Catálogo Médico

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql)](https://www.mysql.com/)
[![PHPUnit](https://img.shields.io/badge/Tests-PHPUnit%2010-37b24d?style=flat-square&logo=php)](phpunit.xml)
[![CI](https://img.shields.io/badge/CI-GitHub%20Actions-2088FF?style=flat-square&logo=githubactions)](.github/workflows/ci.yml)
[![Licencia](https://img.shields.io/badge/Licencia-Comercial%20Propietaria-e03c3c?style=flat-square)](LICENSE)
[![Versión](https://img.shields.io/badge/Versión-v2.0.0-10757e?style=flat-square)](CHANGELOG.md)

Bienvenido al **Sistema Impobiomedical**. Es una solución web integral de gestión comercial y médica desarrollada para **Impobiomedical — Soluciones y Servicios de Tecnología Biomédica**. Administra el ciclo comercial completo: creación de cotizaciones dinámicas con calculadora de márgenes de ganancia, generación de PDFs oficiales para clientes, hojas internas de respaldo de proveedores, órdenes de compra por proveedor (P.O.), catálogo de productos con fotos sanitizadas y reportes estadísticos avanzados.

---

| Documento | Descripción |
|-----------|-------------|
| 👤 [Manual de Usuario](MANUAL_USUARIO.md) | Guía de uso de la aplicación para usuarios finales y asesores comerciales |
| 📜 [Registro de Cambios](CHANGELOG.md) | Historial de versiones y modificaciones del sistema (v2.0.0) |
| 📋 [Plan de Implementación](docs/PLAN_DE_IMPLEMENTACION.md) | Fases del proyecto, stack tecnológico y arquitectura empresarial |
| 📖 [Documentación Técnica](docs/documentacion-tecnica.md) | Arquitectura de carpetas, endpoints, persistencia PDO y seguridad |
| 📋 [Especificación de Requisitos](docs/ESPECIFICACION_REQUISITOS.md) | Requisitos funcionales (RF), RNF, calculadora comercial y modelo de datos |
| 🚀 [Manual de Despliegue VPS](docs/DESPLIEGUE_VPS.md) | Guía paso a paso para instalar y actualizar en el VPS con Docker y Nginx |
| 🏗️ [Arquitectura y Componentes](docs/ARQUITECTURA_Y_COMPONENTES.md) | Diagramas Mermaid: componentes MVC, flujo comercial y capas de seguridad |
| 🤝 [Guía para Colaboradores](docs/CONTRIBUTING.md) | Configuración local, uso de PHPUnit, convenciones de commits y checklist de PR |
| ⚖️ [Licencia Comercial](LICENSE) | Términos legales de propiedad intelectual, uso comercial y mantenimiento |

---

## 🛠️ Tecnologías Utilizadas

* **Backend:** PHP 8.1+ / 8.2 (Arquitectura MVC sin framework pesado).
* **Persistencia:** `PDO` (PHP Data Objects) con sentencias preparadas y parámetros nombrados.
* **Base de Datos:** MySQL 8.0+ / MariaDB 10.4+.
* **Pruebas Automatizadas:** [PHPUnit 10](https://phpunit.de/) con suite unitaria y de cálculo comercial.
* **Integración Continua:** GitHub Actions (`.github/workflows/ci.yml`).
* **Generación de PDFs:** [DomPDF](https://github.com/dompdf/dompdf) integrado vía Composer.
* **Frontend:** HTML5 semántico, CSS3 Vanilla modularizado (SMACSS/ITCSS en 7 submódulos), Bootstrap Icons y Google Fonts (Inter / Outfit).
* **Contenedores:** Docker y Docker Compose con persistencia de volúmenes.

---

## 🏛️ Arquitectura del Sistema

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

Este software se distribuye bajo una **Licencia Comercial Propietaria**. Todos los derechos reservados © 2026 Santiago Lizcano. Consulta el archivo [LICENSE](LICENSE) para conocer los términos de uso, explotación comercial y facultades de mantenimiento interno.
