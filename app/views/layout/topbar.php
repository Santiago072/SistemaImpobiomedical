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
<div class="cabecera-superior">
    <button class="boton-menu-ocultar" id="btnMenu">
        <i class="fas fa-bars"></i> Ocultar Menú
    </button>
    <div class="cabecera-bienvenida flex-1 pl-16">
        <?php if ($esDashboard && $usuario): 
            $codigoMostrar = !empty($usuario['codigo']) ? $usuario['codigo'] : ($_SESSION['usuario_codigo'] ?? '');
        ?>
        <h3 class="topbar-welcome-title">
            <span>¡Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?>!</span>
            <?php if (!empty($codigoMostrar)): ?>
            <span class="badge-codigo">
                <?= htmlspecialchars($codigoMostrar) ?>
            </span>
            <?php endif; ?>
        </h3>
        <?php endif; ?>
        <?php if ($pageHeading): ?>
        <span class="page-heading">
            <?= htmlspecialchars($pageHeading) ?>
        </span>
        <?php endif; ?>
    </div>
    <div class="header-actions-wrap align-center gap-16">
        <!-- Botón de Ayuda / Manual de Usuario -->
        <button type="button" class="btn-help-topbar" onclick="abrirManualUsuario()" title="Ver Manual y Guía de Uso del Sistema">
            <i class="bi bi-question-circle-fill"></i>
            <span>Ayuda</span>
        </button>

        <?php if ($esDashboard && $usuario): ?>
            <?php if ($usuario['rol'] === 'admin'): ?>
            <span class="rol-admin">
                <i class="bi bi-shield-check"></i> Administrador
            </span>
            <?php else: ?>
            <span class="rol-usuario">
                <i class="bi bi-person"></i> Usuario
            </span>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

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
                        <span class="badge-manual-rol <?= $userRolActual === 'admin' ? 'badge-rol-admin' : 'badge-rol-user' ?>">
                            <i class="bi <?= $userRolActual === 'admin' ? 'bi-shield-check' : 'bi-person-badge' ?>"></i>
                            <?= $userRolActual === 'admin' ? 'Vista Administrador (Completa)' : 'Vista Asesor Comercial' ?>
                        </span>
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
                <i class="bi bi-building"></i> 4. Clientes
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
                    <h3 class="manual-h3"><i class="bi bi-2-circle-fill text-primary"></i> Consulta, Estados y Revisiones</h3>
                    <p class="manual-p">En el módulo <strong>Consultar Cotizaciones</strong> puedes buscar y dar seguimiento a todas las ofertas comerciales:</p>
                    
                    <div class="manual-features-list">
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-search"></i></div>
                            <div class="feature-text">
                                <strong>Filtros Avanzados:</strong> Busca por rango de fechas, nombre o NIT del cliente, número de cotización o estado comercial.
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-file-earmark-pdf-fill text-danger"></i></div>
                            <div class="feature-text">
                                <strong>Ver PDF:</strong> Abre el documento formal de la cotización presentado al cliente.
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-shield-lock-fill text-warning"></i></div>
                            <div class="feature-text">
                                <strong>Hoja de Respaldo:</strong> Documento confidencial interno con costos de proveedor y márgenes para auditoría interna.
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-pencil-square text-success"></i></div>
                            <div class="feature-text">
                                <strong>Modificar / Crear Revisión:</strong> Permite editar una cotización existente creando una versión derivada (ej: <code>EB 01_01</code>) sin sobreescribir ni perder el historial original.
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-tags-fill"></i></div>
                            <div class="feature-text">
                                <strong>Estados Comerciales:</strong> Clasifica las propuestas en 🟡 <em>Pendiente</em>, 🟢 <em>Concluida (Ganada)</em> o 🔴 <em>Descartada</em>.
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
                                <li><strong>Exportación Masiva:</strong> Selecciona varias órdenes con las casillas de verificación para exportar en bloque a <strong>PDF</strong> o descargar a <strong>Excel</strong> estructurado.</li>
                                <li>Permite control ágil de despacho y recepción de mercancía médica.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 4: Clientes -->
            <div id="tab-clientes" class="tab-content">
                <div class="manual-section">
                    <h3 class="manual-h3"><i class="bi bi-4-circle-fill text-primary"></i> Directorio de Clientes e Instituciones de Salud</h3>
                    <p class="manual-p">Gestiona y consulta el catálogo centralizado de hospitales, clínicas, EPS y entidades médicas:</p>
                    
                    <div class="manual-step-grid">
                        <div class="manual-step-card">
                            <div class="step-badge">Búsqueda y Filtros</div>
                            <h4>Consulta en Tiempo Real</h4>
                            <ul>
                                <li><strong>Filtrado Inmediato:</strong> Encuentra entidades al instante buscando por NIT, razón social, departamento o municipio.</li>
                                <li><strong>Validación de NIT Único:</strong> Evita duplicados en el registro institucional.</li>
                            </ul>
                        </div>

                        <div class="manual-step-card">
                            <div class="step-badge">Registro y Cotizaciones</div>
                            <h4>Autocompletado Comercial</h4>
                            <ul>
                                <li><strong>Crear / Actualizar:</strong> Registra la persona de contacto, teléfono y correo electrónico.</li>
                                <li><strong>Llenado Ágil:</strong> Al seleccionar un cliente en el paso final de una cotización, todos sus datos tributarios y geográficos se rellenan solos.</li>
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
                    <p class="manual-p">Funciones exclusivas para el rol de Administrador:</p>
                    
                    <div class="manual-step-grid">
                        <div class="manual-step-card">
                            <div class="step-badge step-badge-teal">Usuarios y Claves</div>
                            <h4>Gestión de Asesores</h4>
                            <ul>
                                <li><strong>Creación de Usuarios:</strong> Asigna el nombre, rol y el <strong>Código de Cotización</strong> (2 letras, ej: <code>EB</code>, <code>SL</code>).</li>
                                <li><strong>Contraseña Inicial:</strong> Al crear un usuario, su contraseña inicial por defecto es su <strong>número de documento</strong>.</li>
                                <li><strong>Primer Ingreso:</strong> Cuando el usuario inicia sesión por primera vez con su documento, el sistema le sugiere actualizar su contraseña personal.</li>
                                <li><strong>Restablecimiento:</strong> Si un asesor olvida su clave, el administrador puede restablecerla a su documento original con un solo clic.</li>
                            </ul>
                        </div>

                        <div class="manual-step-card">
                            <div class="step-badge step-badge-teal">Productos</div>
                            <h4>Catálogo Central</h4>
                            <ul>
                                <li><strong>Crear y Editar:</strong> Organiza productos con fotos médicas, categorías, código institucional, porcentaje de IVA y precios base.</li>
                                <li><strong>Exportación:</strong> Descarga el catálogo completo de productos en PDF con diseño estructurado.</li>
                                <li><strong>Seguridad de Datos:</strong> Eliminar o modificar un producto del catálogo no afecta las cotizaciones históricas ya emitidas.</li>
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
</script>
