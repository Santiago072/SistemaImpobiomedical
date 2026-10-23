<?php
/**
 * Topbar reutilizable — botón de menú + mensaje de bienvenida + etiqueta de rol + botón modo.
 * Variables esperadas:
 *   $pageHeading  string  — título h1 de la sección (opcional)
 *   $usuario      array   — datos del usuario (nombre, rol)
 *   $esDashboard  bool    — indica si es el dashboard principal
 */
$pageHeading = $pageHeading ?? '';
$usuario = $usuario ?? null;
$esDashboard = $esDashboard ?? false;
?>
<?php
$meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
$fechaHoy = date('d') . ' de ' . $meses[(int)date('n') - 1] . ' de ' . date('Y');
$tituloModulo = !empty($pageHeading) ? $pageHeading : 'Sistema Comercial & Equipamiento Biomédico';
$nombreUsuario = $usuario['nombre'] ?? ($_SESSION['usuario_nombre'] ?? 'Asesor Comercial');
$rolUsuario = $usuario['rol'] ?? ($_SESSION['rol'] ?? 'usuario');
$codigoUsuario = $usuario['codigo'] ?? ($_SESSION['usuario_codigo'] ?? '');
?>
<header class="cabecera-superior">
    <!-- Lado Izquierdo: Botón Menú + Título con Pulso Vivo + Información Corporativa -->
    <div class="topbar-left-zone">
        <button class="boton-menu-ocultar" id="btnMenu" aria-label="Alternar Menú Lateral">
            <i class="bi bi-list"></i>
        </button>

        <div class="topbar-title-block">
            <div class="topbar-main-title">
                <span class="topbar-pulse-dot"></span>
                <span><?= htmlspecialchars($tituloModulo) ?></span>
            </div>
            <div class="topbar-subtitle-info">
                <i class="bi bi-calendar3 icon-calendar-sub"></i>
                <span><?= $fechaHoy ?> &bull; <strong class="text-corp-brand">Control Comercial &bull; NIT 900.535.843-3</strong></span>
            </div>
        </div>
    </div>

    <!-- Lado Derecho: Estado Online + Ayuda + Perfil + Salir -->
    <div class="topbar-right-zone">

        <!-- Botón de Ayuda / Manual -->
        <button type="button" class="btn-help-topbar" onclick="abrirManualUsuario()" title="Ver Manual y Guía de Uso del Sistema">
            <i class="bi bi-question-circle-fill"></i>
            <span>Ayuda</span>
        </button>

        <div class="topbar-divider-v"></div>

        <!-- Usuario y Botón Salir -->
        <div class="topbar-user-section">
            <div class="topbar-user-text">
                <span class="topbar-user-fullname"><?= htmlspecialchars($nombreUsuario) ?></span>
                <span class="topbar-user-badge">
                    @<?= htmlspecialchars(strtolower($rolUsuario)) ?> <?= !empty($codigoUsuario) ? '('.htmlspecialchars($codigoUsuario).')' : '' ?>
                </span>
            </div>

            <a href="<?= $basePath ?>?action=logout" class="btn-topbar-logout" title="Cerrar Sesión">
                <i class="bi bi-power"></i>
                <span>Salir</span>
            </a>
        </div>
    </div>
</header>

<!-- ── MODAL MANUAL DE USUARIO INTERACTIVO (Adaptativo según Rol) ── -->
<?php $userRolActual = $_SESSION['rol'] ?? 'usuario'; ?>
<div id="modalManualUsuario" class="modal-manual-overlay" onclick="if(event.target === this) cerrarManualUsuario()">
    <div class="modal-manual-card">
        <!-- Header del Modal -->
        <div class="modal-manual-header">
            <div class="modal-manual-title-box">
                <div class="modal-manual-icon">
                    <i class="bi bi-journal-bookmark-fill"></i>
                </div>
                <div>
                    <h2 class="modal-manual-title">Manual de Usuario &bull; Impobiomedical</h2>
                    <p class="modal-manual-subtitle">
                        Guía de funcionamiento &mdash; 
                        <?php if ($userRolActual === 'admin'): ?>
                        <span class="badge-manual-rol badge-rol-admin">
                            <i class="bi bi-shield-check"></i> Vista Administrador (Completa)
                        </span>
                        <?php elseif ($userRolActual === 'compras'): ?>
                        <span class="badge-manual-rol" style="background:#e0f2fe; color:#0369a1; border-color:#bae6fd;">
                            <i class="bi bi-cart-check"></i> Vista Encargado de Compras
                        </span>
                        <?php else: ?>
                        <span class="badge-manual-rol badge-rol-user">
                            <i class="bi bi-person-badge"></i> Vista Asesor Comercial
                        </span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <button type="button" class="btn-manual-close" onclick="cerrarManualUsuario()" title="Cerrar">&times;</button>
        </div>

        <!-- Navegación por pestañas (Tabs) -->
        <div class="modal-manual-tabs">
            <button type="button" class="tab-btn active" onclick="cambiarTabManual('tab-cotizaciones', this)">
                <i class="bi bi-file-earmark-plus-fill"></i> 1. Nueva Cotización
            </button>
            <button type="button" class="tab-btn" onclick="cambiarTabManual('tab-consultar', this)">
                <i class="bi bi-search"></i> 2. Consultar y Modificar
            </button>
            <button type="button" class="tab-btn" onclick="cambiarTabManual('tab-ordenes', this)">
                <i class="bi bi-cart-check-fill"></i> 3. Órdenes de Compra
            </button>
            <button type="button" class="tab-btn" onclick="cambiarTabManual('tab-clientes', this)">
                <i class="bi bi-box-seam"></i> 4. Catálogos Comerciales
            </button>
            <?php if ($userRolActual === 'admin'): ?>
            <button type="button" class="tab-btn tab-btn-admin" onclick="cambiarTabManual('tab-admin', this)">
                <i class="bi bi-gear-fill"></i> 5. Gestión Administrativa
            </button>
            <button type="button" class="tab-btn tab-btn-admin" onclick="cambiarTabManual('tab-stats', this)">
                <i class="bi bi-bar-chart-fill"></i> 6. Estadísticas y Reportes
            </button>
            <?php endif; ?>
        </div>

        <!-- Contenido de las Pestañas -->
        <div class="modal-manual-body">
            
            <!-- TAB 1: Nueva Cotización -->
            <div id="tab-cotizaciones" class="tab-content active">
                <div class="manual-section">
                    <h3 class="manual-h3"><i class="bi bi-1-circle-fill text-primary"></i> Flujo para Crear una Cotización</h3>
                    <p class="manual-p">El módulo de cotizaciones te permite armar propuestas comerciales rápidas en 2 sencillos pasos:</p>
                    
                    <div class="manual-step-grid">
                        <div class="manual-step-card">
                            <div class="step-badge">Paso 1</div>
                            <h4>Agregar Productos / Ítems</h4>
                            <ul>
                                <li><strong>Buscar del Catálogo:</strong> Usa el buscador superior para autocompletar nombre, descripción técnica, imagen y categoría del producto.</li>
                                <li><strong>Ingreso Manual:</strong> Puedes escribir un producto nuevo o personalizado si no está en el catálogo.</li>
                                <li><strong>Calculadora de Ganancias:</strong> Permite ingresar el precio base del proveedor y calcular utilidad, flete, calibración y estampillas. El valor resultante se asigna como precio unitario.</li>
                                <li>Haz clic en <strong>"Agregar a Cotización"</strong> para sumar el ítem a la lista temporal.</li>
                            </ul>
                        </div>

                        <div class="manual-step-card">
                            <div class="step-badge">Paso 2</div>
                            <h4>Datos del Cliente y PDF Final</h4>
                            <ul>
                                <li>Haz clic en <strong>"Continuar → Datos Cliente y PDF"</strong>.</li>
                                <li>Busca el cliente registrado o ingresa uno nuevo (NIT, Nombre, Ciudad, Contacto).</li>
                                <li>Define la <strong>Forma de Pago</strong> (Contado, Crédito 30 días, etc.) y los <strong>Días de Validez</strong>.</li>
                                <li>Presiona <strong>"Finalizar Cotización"</strong>: el sistema asigna el consecutivo oficial (ej: <code>EB 01</code>) y genera el PDF listo para descargar o enviar.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: Consultar y Modificar -->
            <div id="tab-consultar" class="tab-content">
                <div class="manual-section">
                    <h3 class="manual-h3"><i class="bi bi-2-circle-fill text-primary"></i> Consulta, Estados, Revisiones y Ajustes</h3>
                    <p class="manual-p">En el módulo <strong>Consultar Cotizaciones</strong> puedes buscar, exportar y dar seguimiento a todas las ofertas comerciales:</p>
                    
                    <div class="manual-features-list">
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-search"></i></div>
                            <div class="feature-text">
                                <strong>Filtros Avanzados y Pestañas:</strong> Busca por rango de fechas (Desde - Hasta), cliente, NIT o consecutivo. Pestañas dedicadas con contadores reactivos en vivo: <em>Pendientes</em>, <em>Concluidas</em>, <em>Descartadas</em> y <em>Todas</em>.
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-file-earmark-pdf-fill text-danger"></i></div>
                            <div class="feature-text">
                                <strong>Ver PDF:</strong> Abre el documento formal de la cotización presentado al cliente en un visor emergente interactivo con opción de descarga directa.
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-file-earmark-excel-fill text-success"></i></div>
                            <div class="feature-text">
                                <strong>Exportar a Excel:</strong> Descarga la propuesta comercial completa en hoja de cálculo estructurada <code>.xlsx</code> de forma ágil y liviana para análisis de datos o envíos tabulares.
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-shield-lock-fill text-warning"></i></div>
                            <div class="feature-text">
                                <strong>Hoja de Respaldo:</strong> Documento confidencial interno con costos de proveedor, calibración, estampillas y márgenes de ganancia para auditoría interna y compras.
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-pencil-square text-primary"></i></div>
                            <div class="feature-text">
                                <strong>Modificar Cotización (2 Opciones Disponibles):</strong>
                                <ul style="margin: 6px 0 0 16px; padding: 0; font-size: 0.9em;">
                                    <li><strong>🛠️ Ajustar Cotización (Mismo Número):</strong> Permite corregir productos, cantidades, precios o datos del cliente <em>manteniendo intacto el mismo consecutivo</em> (ej: <code>EB 01</code>), sin generar cotizaciones duplicadas ni alterar el consecutivo mensual.</li>
                                    <li><strong>📑 Nueva Versión / Revisión:</strong> Clona la cotización generando una versión derivada (ej: <code>EB 01_01</code>, <code>EB 01_02</code>), preservando la cotización original inalterada para trazabilidad y auditoría comercial.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-truck text-info"></i></div>
                            <div class="feature-text">
                                <strong>Control de Estado de Entrega:</strong> Permite gestionar la logística de cada cotización en tiempo real mediante un menú desplegable interactivo: 🟡 <em>Pendiente (Por despachar)</em>, 🔵 <em>En Tránsito (En camino)</em> y 🟢 <em>Entregado (con registro de fecha y cómputo de días)</em>.
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-tags-fill"></i></div>
                            <div class="feature-text">
                                <strong>Estados Comerciales:</strong> Clasifica las propuestas en 🟡 <em>Pendiente</em>, 🟢 <em>Concluida (Ganada)</em> o 🔴 <em>Descartada</em>, registrando automáticamente la fecha y hora exacta del cambio de estado.
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-shield-lock-fill text-danger"></i></div>
                            <div class="feature-text">
                                <strong>Permisos por Rol (Visibilidad y Eliminación):</strong>
                                <ul style="margin: 6px 0 0 16px; padding: 0; font-size: 0.9em;">
                                    <li><strong>Asesor Comercial:</strong> Visualiza, ajusta y genera versiones exclusivamente de sus propias cotizaciones. No tiene permisos de eliminación.</li>
                                    <li><strong>Admin y Encargado de Compras:</strong> Tienen <em>visibilidad global</em> (ven las cotizaciones de todos los asesores) y cuentan con el botón <i class="bi bi-trash3-fill text-danger"></i> para eliminar cotizaciones en cualquier estado comercial.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: Órdenes de Compra -->
            <div id="tab-ordenes" class="tab-content">
                <div class="manual-section">
                    <h3 class="manual-h3"><i class="bi bi-3-circle-fill text-primary"></i> Emisión y Control de Órdenes de Compra (P.O.)</h3>
                    <p class="manual-p">Convierte los productos cotizados en órdenes formales para los proveedores:</p>
                    
                    <div class="manual-step-grid">
                        <div class="manual-step-card">
                            <div class="step-badge">Generar Orden</div>
                            <h4>Desde Consultar Cotizaciones</h4>
                            <ul>
                                <li>En las cotizaciones con estado <strong>Pendiente</strong>, haz clic en el botón <strong>"Orden"</strong>.</li>
                                <li>Selecciona únicamente los ítems que se van a pedir a ese proveedor específico.</li>
                                <li>Completa los datos del proveedor (NIT, condiciones de pago, datos bancarios, retenciones).</li>
                                <li>Se genera automáticamente el documento de Orden de Compra oficial.</li>
                            </ul>
                        </div>

                        <div class="manual-step-card">
                            <div class="step-badge">Gestión y Exportación</div>
                            <h4>Módulo Órdenes de Compra</h4>
                            <ul>
                                <li>Visualiza órdenes separadas en pestañas <strong>Pendientes</strong> y <strong>Completadas</strong>.</li>
                                <li><strong>Visibilidad Global y Eliminación:</strong> Los roles <strong>Admin</strong> y <strong>Compras</strong> ven las órdenes emitidas por todos los usuarios y pueden eliminarlas o cambiar su estado; los asesores solo gestionan las suyas.</li>
                                <li><strong>Exportación Masiva:</strong> Selecciona varias órdenes con las casillas de verificación para exportar en bloque a <strong>PDF</strong> o descargar a <strong>Excel</strong> estructurado.</li>
                                <li>Permite control ágil de despacho y recepción de mercancía médica.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 4: Catálogos Comerciales (Clientes, Proveedores y Productos) -->
            <div id="tab-clientes" class="tab-content">
                <div class="manual-section">
                    <h3 class="manual-h3"><i class="bi bi-4-circle-fill text-primary"></i> Catálogos Comerciales: Clientes, Proveedores y Productos</h3>
                    <p class="manual-p">Consulta y gestión de los directorios maestros de salud, proveedores oficiales y catálogo de equipamiento:</p>
                    
                    <div class="manual-step-grid">
                        <div class="manual-step-card">
                            <div class="step-badge">Clientes e Instituciones</div>
                            <h4>Directorio de Salud</h4>
                            <ul>
                                <li><strong>Búsqueda Reactiva en Tiempo Real:</strong> Localiza al instante hospitales, clínicas o EPS por NIT, razón social o municipio, con botón <code>[X]</code> para limpiar búsqueda al instante.</li>
                                <li><strong>Validación de NIT Único:</strong> Previene registros duplicados en el sistema.</li>
                                <li><strong>Autocompletado Comercial:</strong> Al seleccionar un cliente en el Paso 2 de una cotización, sus datos tributarios y de despacho se completan automáticamente.</li>
                            </ul>
                        </div>

                        <div class="manual-step-card">
                            <div class="step-badge">Proveedores Comerciales</div>
                            <h4>Directorio de Proveedores</h4>
                            <ul>
                                <li><strong>Búsqueda Predictiva en Vivo:</strong> Filtrado dinámico por NIT o Razón Social sin recargar página.</li>
                                <li><strong>Datos Tributarios y Bancarios:</strong> Registra NIT, Razón Social, banco, tipo y número de cuenta para compras.</li>
                                <li><strong>Sincronización con Órdenes:</strong> Al seleccionar un proveedor en cotizaciones u órdenes, sus datos bancarios y comerciales se cargan de inmediato.</li>
                            </ul>
                        </div>

                        <div class="manual-step-card" style="grid-column: 1 / -1;">
                            <div class="step-badge step-badge-teal">Catálogo de Productos</div>
                            <h4>Equipamiento y Dispositivos Médicos</h4>
                            <ul>
                                <li><strong>Creación y Edición Abierta:</strong> Todos los usuarios (asesores y admin) pueden consultar, registrar y editar productos con fotos médicas sanitizadas, categorías, código institucional, marca, modelo y porcentaje de IVA.</li>
                                <li><strong>Exportación PDF:</strong> Descarga el catálogo completo, por categoría o mediante la barra flotante de selección múltiple interactiva.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($userRolActual === 'admin'): ?>
            <!-- TAB 5: Gestión Administrativa (Solo Admin) -->
            <div id="tab-admin" class="tab-content">
                <div class="manual-section">
                    <h3 class="manual-h3"><i class="bi bi-shield-fill-check text-success"></i> Panel de Control y Administración</h3>
                    <p class="manual-p">Funciones y privilegios reservados exclusivamente para el rol de Administrador:</p>
                    
                    <div class="manual-step-grid">
                        <div class="manual-step-card">
                            <div class="step-badge step-badge-teal">Usuarios y Asesores</div>
                            <h4>Gestión de Accesos</h4>
                            <ul>
                                <li><strong>Creación de Usuarios:</strong> Asigna nombre, rol y el <strong>Código de Cotización</strong> único de 2 letras (ej: <code>EB</code>, <code>SL</code>).</li>
                                <li><strong>Contraseña Inicial:</strong> Al crear un usuario, su contraseña por defecto es su <strong>número de documento</strong>.</li>
                                <li><strong>Primer Ingreso:</strong> El sistema sugiere al asesor cambiar su contraseña personal al ingresar por primera vez.</li>
                                <li><strong>Restablecimiento Rápido:</strong> Si un asesor olvida su clave, el administrador la restablece al documento original con un solo clic.</li>
                            </ul>
                        </div>

                        <div class="manual-step-card">
                            <div class="step-badge step-badge-teal">Eliminación y Seguridad</div>
                            <h4>Soft Delete Exclusivo (Admin)</h4>
                            <ul>
                                <li><strong>Clientes, Proveedores y Productos:</strong> Solo el Administrador puede eliminarlos o desactivarlos. Se aplica <em>Soft Delete</em> (inactivación lógica en base de datos), ocultándolos de nuevas búsquedas y formularios sin borrar su historial ni romper cotizaciones u órdenes emitidas.</li>
                                <li><strong>Asesores:</strong> Si un usuario tiene cotizaciones u órdenes asociadas, el sistema protege la integridad referencial y bloquea el borrado destructivo, recomendando cambiar su estado a <em>Inactivo</em>.</li>
                                <li><strong>Protección de Datos:</strong> Ningún asesor comercial puede eliminar registros maestros; esto garantiza la trazabilidad comercial y auditoría de la empresa.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 6: Estadísticas y Reportes (Solo Admin) -->
            <div id="tab-stats" class="tab-content">
                <div class="manual-section">
                    <h3 class="manual-h3"><i class="bi bi-graph-up-arrow text-primary"></i> Estadísticas y Reportes Comerciales</h3>
                    <p class="manual-p">Métricas ejecutivas y análisis de rendimiento para la toma de decisiones:</p>
                    
                    <div class="manual-step-grid">
                        <div class="manual-step-card">
                            <div class="step-badge">Métricas Clave</div>
                            <h4>Evolución y Rendimiento</h4>
                            <ul>
                                <li><strong>Gráficos Mensuales:</strong> Seguimiento visual de la evolución y volumen de cotizaciones emitidas durante el año.</li>
                                <li><strong>Efectividad Comercial:</strong> Comparativa visual entre cotizaciones totales vs. cotizaciones concluidas (ganadas).</li>
                                <li><strong>Desempeño por Asesor:</strong> Tabla comparativa de actividad y volumen por cada usuario comercial.</li>
                            </ul>
                        </div>

                        <div class="manual-step-card">
                            <div class="step-badge">Exportación Ejecutiva</div>
                            <h4>Informes Gerenciales</h4>
                            <ul>
                                <li><strong>Reporte en PDF:</strong> Generación instantánea del informe gerencial consolidado con gráficos estadísticos, tablas de totales y resumen ejecutivo para reuniones comerciales.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Footer del Modal -->
        <div class="modal-manual-footer">
            <span class="manual-footer-text">
                <i class="bi bi-info-circle"></i> Sistema de Gestión Comercial &bull; Impobiomedical
            </span>
            <button type="button" class="btn-mod-primary" onclick="cerrarManualUsuario()">
                <i class="bi bi-check-lg"></i> Entendido
            </button>
        </div>
    </div>
</div>

<style>
/* ── Estilos del Botón de Ayuda en Topbar ── */
.btn-help-topbar {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(13, 148, 136, 0.1);
    color: #0f766e;
    border: 1.5px solid rgba(13, 148, 136, 0.3);
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}
.btn-help-topbar:hover {
    background: #0f766e;
    color: #ffffff;
    border-color: #0f766e;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(15, 118, 110, 0.2);
}
.btn-help-topbar i {
    font-size: 0.95rem;
}

/* ── Estilos del Modal del Manual ── */
.modal-manual-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    padding: 20px;
}
.modal-manual-card {
    background: #ffffff;
    width: 100%;
    max-width: 900px;
    max-height: 88vh;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: manualPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes manualPop {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}
.modal-manual-header {
    padding: 18px 24px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.modal-manual-title-box {
    display: flex;
    align-items: center;
    gap: 14px;
}
.modal-manual-icon {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #0d9488, #0f766e);
    color: #ffffff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    box-shadow: 0 4px 10px rgba(13, 148, 136, 0.3);
}
.modal-manual-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    line-height: 1.2;
}
.modal-manual-subtitle {
    font-size: 0.8rem;
    color: #64748b;
    margin: 2px 0 0 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.badge-manual-rol {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.72rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.badge-rol-admin {
    background: #f0fdf4;
    color: #15803d;
    border: 1px solid #bbf7d0;
}
.badge-rol-user {
    background: #f0f9ff;
    color: #0369a1;
    border: 1px solid #bae6fd;
}
.btn-manual-close {
    background: transparent;
    border: none;
    font-size: 1.8rem;
    color: #94a3b8;
    cursor: pointer;
    line-height: 1;
    transition: color 0.15s;
}
.btn-manual-close:hover {
    color: #0f172a;
}

/* Tabs */
.modal-manual-tabs {
    display: flex;
    background: #f1f5f9;
    padding: 8px 16px 0 16px;
    border-bottom: 1px solid #e2e8f0;
    overflow-x: auto;
    gap: 6px;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}
.modal-manual-tabs::-webkit-scrollbar {
    height: 5px;
}
.modal-manual-tabs::-webkit-scrollbar-track {
    background: transparent;
}
.modal-manual-tabs::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.modal-manual-tabs::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
.tab-btn {
    padding: 9px 14px;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    font-size: 0.82rem;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
    flex-shrink: 0;
}
.tab-btn:hover {
    color: #0f172a;
    background: rgba(255,255,255,0.6);
}
.tab-btn.active {
    color: #0f766e;
    background: #ffffff;
    border-bottom-color: #0f766e;
    font-weight: 700;
}
.tab-btn-admin {
    color: #475569;
}
.tab-btn-admin.active {
    color: #0f766e;
}

/* Body & Content */
.modal-manual-body {
    padding: 24px;
    overflow-y: auto;
    flex: 1;
}
.tab-content {
    display: none;
}
.tab-content.active {
    display: block;
    animation: fadeInTab 0.2s ease;
}
@keyframes fadeInTab {
    from { opacity: 0; transform: translateY(4px); }
    to   { opacity: 1; transform: translateY(0); }
}
.manual-h3 {
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.manual-p {
    font-size: 0.88rem;
    color: #475569;
    margin-bottom: 18px;
    line-height: 1.5;
}

/* Steps Grid */
.manual-step-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-top: 14px;
}
@media (max-width: 768px) {
    .manual-step-grid { grid-template-columns: 1fr; }
}
.manual-step-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 18px;
    position: relative;
}
.step-badge {
    display: inline-block;
    background: #0d9488;
    color: #ffffff;
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 2px 8px;
    border-radius: 6px;
    margin-bottom: 8px;
}
.step-badge-teal {
    background: #0f766e;
}
.manual-step-card h4 {
    font-size: 0.92rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
}
.manual-step-card ul {
    margin: 0;
    padding-left: 18px;
    font-size: 0.82rem;
    color: #334155;
    line-height: 1.5;
}
.manual-step-card li {
    margin-bottom: 6px;
}

/* Features List */
.manual-features-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.feature-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 12px 16px;
    border-radius: 10px;
}
.feature-icon {
    font-size: 1.1rem;
    color: #0d9488;
    margin-top: 1px;
}
.feature-text {
    font-size: 0.84rem;
    color: #334155;
    line-height: 1.45;
}

/* Footer */
.modal-manual-footer {
    padding: 14px 24px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.manual-footer-text {
    font-size: 0.78rem;
    color: #64748b;
    font-weight: 500;
}
</style>

<script>
function abrirManualUsuario() {
    const modal = document.getElementById('modalManualUsuario');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function cerrarManualUsuario() {
    const modal = document.getElementById('modalManualUsuario');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function cambiarTabManual(tabId, btn) {
    document.querySelectorAll('.modal-manual-tabs .tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.modal-manual-body .tab-content').forEach(c => c.classList.remove('active'));
    
    if (btn) btn.classList.add('active');
    const target = document.getElementById(tabId);
    if (target) target.classList.add('active');
}

// Cerrar con Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cerrarManualUsuario();
});

// Helper global para descartar toast
function cerrarToastNotificacion(btn) {
    const toast = btn.closest('.toast-top-right');
    if (toast) {
        toast.classList.add('toast-hide');
        setTimeout(() => toast.remove(), 350);
    }
}

// Auto-desvanecer toasts después de 5 segundos
document.addEventListener('DOMContentLoaded', function() {
    const toasts = document.querySelectorAll('.toast-top-right');
    toasts.forEach(toast => {
        setTimeout(() => {
            if (toast && !toast.classList.contains('toast-hide')) {
                toast.classList.add('toast-hide');
                setTimeout(() => toast.remove(), 350);
            }
        }, 5000);
    });
});
</script>

<?php
// Renderizado de Toast Superior Derecho si hay mensaje de éxito en sesión o variable
$toastTexto = '';
if (!empty($_SESSION['flash_exito'])) {
    $toastTexto = $_SESSION['flash_exito'];
    unset($_SESSION['flash_exito']);
} elseif (!empty($_SESSION['flash_success'])) {
    $toastTexto = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
} elseif (!empty($mensajeExito)) {
    $toastTexto = $mensajeExito;
}
?>
<?php if (!empty($toastTexto)): ?>
<div class="toast-top-right" role="alert" aria-live="polite">
    <div class="toast-icon-wrap">
        <i class="bi bi-check2-circle"></i>
    </div>
    <div class="toast-body-wrap">
        <h4 class="toast-title">Operación Exitosa</h4>
        <p class="toast-desc"><?= htmlspecialchars($toastTexto) ?></p>
    </div>
    <button type="button" class="toast-close-btn" onclick="cerrarToastNotificacion(this)" title="Cerrar notificación">
        <i class="bi bi-x-lg"></i>
    </button>
</div>
<?php endif; ?>
