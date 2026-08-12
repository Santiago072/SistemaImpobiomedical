# 📖 Documentación Técnica del Sistema Impobiomedical

Documento de referencia técnica para arquitectos de software, administradores de sistemas y desarrolladores.

---

## 1. Estructura del Código Fuente

```
SistemaImpobiomedical/
├── index.php                         # Front Controller y Router principal
├── composer.json / composer.lock     # Gestión de dependencias y autoloader
├── phpunit.xml                       # Configuración de pruebas automatizadas
├── LICENSE                           # Licencia de uso comercial y transferencia
├── README.md                         # Portada principal con índice de documentación
├── CHANGELOG.md                      # Historial de versiones y cambios
├── MANUAL_USUARIO.md                 # Manual de usuario en raíz
├── deploy.sh                         # Script Bash para despliegue automatizado en VPS
├── Dockerfile / docker-compose.yml   # Contenedores Docker de producción
├── .github/workflows/ci.yml          # Pipeline de Integración Continua (GitHub Actions)
├── app/
│   ├── contracts/                    # Interfaces y contratos de servicios
│   ├── controllers/                  # Controladores MVC (SRP)
│   │   ├── AuthController.php
│   │   ├── ClienteController.php
│   │   ├── CotizacionController.php
│   │   ├── EstadisticaController.php
│   │   ├── OrdenCompraController.php
│   │   ├── PanelController.php
│   │   ├── ProductoController.php
│   │   └── UsuarioController.php
│   ├── models/                       # Capa de datos con PDO y sentencias preparadas
│   │   ├── ClienteModel.php
│   │   ├── CotizacionModel.php
│   │   ├── EstadisticaModel.php
│   │   ├── OrdenCompraModel.php
│   │   ├── ProductoModel.php
│   │   └── UsuarioModel.php
│   ├── services/                     # Lógica de negocio desacoplada
│   │   ├── CotizacionItemService.php
│   │   ├── FileUploadService.php
│   │   └── FinalizarCotizacionService.php
│   └── views/                        # Vistas organizadas por módulo y layouts
│       ├── auth/
│       ├── clientes/
│       ├── cotizaciones/
│       ├── layout/                   # header.php, topbar.php, menu.php, footer.php
│       ├── ordenes/
│       ├── panel/
│       ├── productos/
│       └── usuarios/
├── config/
│   ├── .env.example                  # Plantilla de variables de entorno
│   ├── conexion.php                  # Conexión Singleton PDO
│   ├── EnvLoader.php                 # Parser liviano de archivos .env
│   └── seguridad.php                 # Funciones de seguridad (CSRF, XSS, rate limiting)
├── css/
│   ├── estilos.css                   # Punto de entrada maestro con @imports
│   └── modules/                      # 7 submódulos CSS independientes
├── docs/                             # Documentación técnica completa y manuales
├── tests/                            # Suite de pruebas unitarias (PHPUnit)
│   ├── bootstrap.php
│   └── Unit/
└── uploads/                          # Almacenamiento de imágenes de productos médicos
```

---

## 2. Mapa de Rutas y Endpoints

El sistema opera bajo el patrón **Front Controller** donde todas las solicitudes ingresan por `index.php`:

| Módulo (`?module=`) | Acción (`&action=`) | Método HTTP | Controlador | Propósito |
|---|---|:---:|---|---|
| *(vacío)* | `login` / *(default)* | GET / POST | `AuthController` | Inicio de sesión |
| *(vacío)* | `logout` | GET | `AuthController` | Cierre de sesión y destrucción |
| `panel` | `index` | GET | `PanelController` | Dashboard principal |
| `cotizaciones` | `crear` | GET / POST | `CotizacionController` | Cotizador dinámico e ítems |
| `cotizaciones` | `finalizar` | GET / POST | `CotizacionController` | Datos de cliente y consecutivo |
| `cotizaciones` | `consultar` | GET / POST | `CotizacionController` | Listado y filtros de cotizaciones |
| `cotizaciones` | `generar_pdf` | GET | `CotizacionController` | Render de PDF comercial |
| `cotizaciones` | `modificar` | GET | `CotizacionController` | Clonación para nueva versión/revisión |
| `cotizaciones` | `eliminar` | GET (CSRF) | `CotizacionController` | Eliminación de cotización *(Admin)* |
| `ordenes` | `consultar` | GET / POST | `OrdenCompraController` | Listado de Órdenes de Compra |
| `ordenes` | `seleccionar_items` | GET | `OrdenCompraController` | Selector de ítems por proveedor |
| `ordenes` | `crear` | POST | `OrdenCompraController` | Generación de P.O. formal |
| `productos` | `lista` | GET | `ProductoController` | Catálogo de productos |
| `productos` | `crear` / `editar` | POST | `ProductoController` | Gestión de productos *(Admin)* |
| `productos` | `eliminar` | GET (CSRF) | `ProductoController` | Borrado de producto *(Admin)* |
| `clientes` | `lista` | GET | `ClienteController` | Directorio de clientes |
| `clientes` | `crear` / `editar` | POST | `ClienteController` | Gestión de entidades |
| `usuarios` | `lista` | GET | `UsuarioController` | Directorio de asesores *(Admin)* |
| `usuarios` | `reset_password` | GET (CSRF) | `UsuarioController` | Reset de contraseña a documento |

---

## 3. Modelo de Base de Datos y Persistencia

* **Motor:** InnoDB (soporte nativo de transacciones ACID y llaves foráneas).
* **Codificación:** `utf8mb4_unicode_ci` (soporte completo de caracteres internacionales y acentos).
* **Gestión de Errores:** `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`. Todos los errores SQL son capturados y enviados al log de PHP en producción (`logs/php_errors.log`), evitando la exposición de detalles internos al usuario.
