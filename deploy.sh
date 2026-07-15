#!/bin/bash
# Script de despliegue automático — Sistema Impobiomedical

set -e   # Detener en cualquier error

echo "========================================"
echo "  Despliegue Impobiomedical"
echo "========================================"

# 1. Ajustar permisos para evitar conflictos con archivos creados por Docker
echo ""
echo "[1/5] Ajustando permisos locales..."
sudo chown -R $USER:$USER .

# Leer DB_PASS ANTES del reset (git reset --hard sobreescribe config/.env)
DB_PASS_LOCAL=$(grep '^DB_PASS=' config/.env 2>/dev/null | cut -d '=' -f2- | tr -d '\r')
# Fallback: variable de entorno del sistema si config/.env no tiene la contraseña
if [ -z "$DB_PASS_LOCAL" ] && [ -n "$DB_PASS" ]; then
    DB_PASS_LOCAL="$DB_PASS"
fi

# 2. Obtener los últimos cambios de GitHub
echo ""
echo "[2/5] Obteniendo cambios de GitHub..."
git fetch origin

# 3. Forzar sincronización exacta con main
echo ""
echo "[3/5] Sincronizando con la rama main..."
git reset --hard origin/main

# Restaurar config/.env con la contraseña leída antes del reset
if [ -n "$DB_PASS_LOCAL" ]; then
    sed -i "s|^DB_PASS=.*|DB_PASS=${DB_PASS_LOCAL}|" config/.env 2>/dev/null || true
fi

# 4. Reconstruir y levantar contenedores
echo ""
echo "[4/5] Reconstruyendo y levantando contenedores Docker..."
docker compose up -d --build

# 5. Ejecutar migraciones SQL pendientes (CREATE TABLE IF NOT EXISTS — seguro correrlo siempre)
echo ""
echo "[5/5] Ejecutando migraciones de base de datos..."

if [ -z "$DB_PASS_LOCAL" ]; then
    echo "  ⚠️  No se encontró DB_PASS. Define la variable de entorno:"
    echo "     export DB_PASS='tu_contraseña' && bash deploy.sh"
    echo "  O ejecuta manualmente:"
    echo "     docker exec -i impobiomedical_db mariadb -u impo_user -p'TU_PASS' sistema_impobiomedical < migraciones.sql"
else
    # Esperar a que MariaDB esté lista (máx 30 segundos)
    echo "  Esperando que la base de datos esté lista..."
    for i in $(seq 1 15); do
        if docker exec impobiomedical_db mariadb-admin ping -u impo_user -p"${DB_PASS_LOCAL}" --silent 2>/dev/null; then
            echo "  Base de datos lista."
            break
        fi
        sleep 2
    done

    # Ejecutar el SQL principal
    docker exec -i impobiomedical_db mariadb \
        -u impo_user \
        -p"${DB_PASS_LOCAL}" \
        sistema_impobiomedical \
        < ordenes_compra_bd.sql \
        && echo "  ✅ Migración ordenes_compra aplicada." \
        || echo "  ⚠️  Error en la migración — revisa los logs."

    # Ejecutar migraciones ALTER TABLE pendientes
    docker exec -i impobiomedical_db mariadb \
        -u impo_user \
        -p"${DB_PASS_LOCAL}" \
        sistema_impobiomedical \
        < migraciones.sql \
        && echo "  ✅ Migraciones ALTER TABLE aplicadas." \
        || echo "  ⚠️  Error en migraciones ALTER — revisa los logs."
fi

echo ""
echo "========================================"
echo "✅ Despliegue completado exitosamente."
echo "========================================"
