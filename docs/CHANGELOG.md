# Historial de Versiones (Changelog) — Sistema Impobiomedical

Todas las actualizaciones, mejoras arquitectónicas, parches de seguridad y versiones del sistema se documentan en este archivo.

---

## [v2.0.0] - 2026-08-11
### Añadido
- **Suite de Pruebas Automatizadas (PHPUnit 10)**: 11 tests unitarios con 36 aserciones que validan cálculos comerciales, IVA, consecutivos, hashing y seguridad.
- **Pipeline de Integración Continua (CI)**: Configuración en GitHub Actions (`.github/workflows/ci.yml`) para chequeo de sintaxis PHP y tests en cada commit.
- **Licencia Comercial Propietaria (`LICENSE`)**: Cláusulas formales de uso comercial, protección intelectual y facultades explícitas para mantenimiento interno por parte del comprador o desarrolladores designados.
- **Modularización de Estilos CSS (`css/modules/`)**: Arquitectura SMACSS/ITCSS en 7 submódulos (`variables.css`, `base.css`, `layout.css`, `components.css`, `forms.css`, `auth.css`, `responsive.css`).
- **Seguridad HTTP & CSRF Universal**: Cabeceras de seguridad (`X-Frame-Options`, `nosniff`, `HSTS`, `CSP`) y validación estricta de tokens CSRF en todas las acciones de modificación y eliminación.
- **Toggle de Contraseñas (Ver/Ocultar)**: Icono de ojo interactivo 👁️ en formularios de login y gestión de usuarios.
- **Campos Flexibles en Usuarios**: Correo y teléfono opcionales al crear y editar usuarios.

### Modificado
- **Migración a PDO**: Capa de datos completamente migrada de `mysqli` a `PDO` con sentencias preparadas y parámetros nombrados, eliminando vulnerabilidades de inyección SQL.

---

## [v1.2.1] - 2026-07-17
### Corregido
- **Bug en inserción de ítems**: Corrección de tipos de parámetros en la inserción de ítems de cotización.
- **Manejo de errores**: Lanzamiento de excepciones con feedback visible en la interfaz ante fallos de persistencia.

---

## [v1.1.0] - 2026-07-16
### Añadido
- **Calculadora Dinámica de Ganancias**: Captura de múltiples operaciones por etapa (Utilidad, Flete, Calibración, Estampillas) almacenadas en estructura JSON (`calc_ops`).
- **Hoja de Respaldo Interna**: Desglose con identificación de proveedores y costos reales.

---

## [v1.0.0] - 2026-07-09
### Estable
- Despliegue inicial en producción mediante contenedores Docker y proxy inverso Nginx.
- Catálogo de productos médicos, directorio de entidades de salud y generación de cotizaciones en PDF con DomPDF.
