# 🤝 Guía para Desarrolladores y Colaboradores — Sistema Impobiomedical

¡Bienvenido! Este documento proporciona las directrices, estándares de ingeniería y procedimientos necesarios para trabajar en el código base de **Sistema Impobiomedical**.

---

## 1. Requisitos del Entorno de Desarrollo

Para trabajar en este proyecto localmente se requiere:
* **PHP:** Versión `8.1` o superior (Recomendado `8.2`+).
  * Extensiones requeridas: `pdo`, `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`.
* **Base de Datos:** MySQL `8.0`+ o MariaDB `10.4`+.
* **Gestor de Paquetes:** Composer `2.2`+.
* **Servidor Web:** Apache (con `mod_rewrite` habilitado) o Nginx / Docker.

---

## 2. Instalación y Configuración Local

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/Santiago072/SistemaImpobiomedical.git
   cd SistemaImpobiomedical
   ```

2. **Instalar dependencias de Composer:**
   ```bash
   composer install
   ```

3. **Configurar el entorno (`config/.env`):**
   * Copia `.env.example` a `config/.env`:
     ```bash
     cp .env.example config/.env
     ```
   * Ajusta las credenciales de tu base de datos local y URL base:
     ```ini
     DB_HOST=localhost
     DB_NAME=sistema_impobiomedical
     DB_USER=root
     DB_PASS=
     APP_BASE=/SistemaImpobiomedical/
     ```

4. **Importar la Base de Datos:**
   * Importa el archivo `BD.txt` en tu gestor de base de datos MySQL / phpMyAdmin.

---

## 3. Estándares de Código y Buenas Prácticas

* **PSR-12:** Todo el código PHP debe seguir el estándar de estilo PSR-12.
* **Tipado Estricto:** Utiliza declaraciones de tipo explícitas en parámetros y retornos (`string`, `int`, `array`, `void`, `\PDO`).
* **Seguridad Obligatoria:**
  * **Cero Consultas Crudas:** Siempre usa `PDO::prepare()` con parámetros nombrados `:param`.
  * **Protección CSRF:** Toda acción que cree, edite o elimine datos debe verificar `verificar_token_csrf()`.
  * **Protección XSS:** Escapa siempre las variables en las vistas mediante `htmlspecialchars()`.
  * **Control de Acceso:** Protege endpoints con `verificar_autenticacion()` y `verificar_admin()`.

---

## 4. Ejecución de Pruebas Automatizadas

Antes de realizar cualquier commit o pull request, **es obligatorio** que toda la suite de pruebas unitarias pase al 100%:

```bash
# Ejecutar suite de pruebas con PHPUnit
composer test
```

### Agregar Nuevas Pruebas
* Las pruebas se ubican en el directorio `tests/Unit/`.
* Si creas un nuevo servicio o método con reglas de negocio, crea su respectivo archivo `Tests\Unit\...Test.php` extendiendo `PHPUnit\Framework\TestCase`.

---

## 5. Convención de Commits

Utilizamos el estándar de **Conventional Commits**:

| Prefijo | Propósito | Ejemplo |
|---|---|---|
| `feat:` | Nueva funcionalidad | `feat(cotizaciones): agregar descuento global por item` |
| `fix:` | Corrección de un error | `fix(auth): corregir redireccion tras expiracion de sesion` |
| `refactor:` | Refactorización de código sin cambio funcional | `refactor(model): optimizar consulta de listado de productos` |
| `security:` | Mejoras o parches de seguridad | `security(csrf): agregar token a eliminacion de clientes` |
| `test:` | Adición o ajuste de pruebas | `test(calculos): agregar caso de prueba para fletes negativos` |
| `docs:` | Actualización de documentación | `docs: actualizar guia de despliegue en VPS` |

---

## 6. Integración Continua (CI)

El repositorio cuenta con un pipeline automatizado en **GitHub Actions** (`.github/workflows/ci.yml`) que verifica en cada `push` o `pull request`:
1. La sintaxis de todos los archivos PHP (`php -l`).
2. La consistencia de `composer.lock`.
3. La ejecución limpia de todas las pruebas unitarias de PHPUnit.

---

Para dudas sobre la arquitectura, consulta [ARQUITECTURA_Y_COMPONENTES.md](file:///c:/xampp/htdocs/SistemaImpobiomedical/docs/ARQUITECTURA_Y_COMPONENTES.md).
