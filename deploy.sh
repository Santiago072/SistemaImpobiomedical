#!/bin/bash
# Script de despliegue automático — Sistema Impobiomedical

set -e   # Detener en cualquier error

echo "========================================"
echo "  Despliegue Impobiomedical"
echo "========================================"

# ── Resolver contraseña de BD ─────────────────────────────────────────────────
# Prioridad: variable de entorno del sistema > config/.env
if [ -n "$DB_PASS" ]; then
    DB_PASS_LOCAL="$DB_PASS"
else
    DB_PASS_LOCAL=$(grep '^DB_PASS=' config/.env 2>/dev/null | cut -d '=' -f2- | tr -d '\r' || true)
fi

# 1. Ajustar permisos
echo ""
echo "[1/5] Ajustando permisos locales..."
sudo chown -R $USER:$USER .

# 2. Obtener los últimos cambios de GitHub
echo ""
echo "[2/5] Obteniendo cambios de GitHub..."
git fetch origin

# 3. Forzar sincronización exacta con main
echo ""
echo "[3/5] Sincronizando con la rama main..."
git reset --hard origin/main

# 4. Reconstruir y levantar contenedores
echo ""
echo "[4/5] Reconstruyendo y levantando contenedores Docker..."
docker compose up -d --build

# 5. Ejecutar migraciones SQL
echo ""
echo "[5/5] Ejecutando migraciones de base de datos..."

if [ -z "$DB_PASS_LOCAL" ]; then
    echo "  ⚠️  DB_PASS no definido. Para próximos deploys ejecuta:"
    echo "     echo \"export DB_PASS='tu_contraseña'\" >> ~/.bashrc && source ~/.bashrc"
    echo "  Migración saltada — ejecuta manualmente si es necesario:"
    echo "     docker exec -i impobiomedical_db mariadb -u impo_user -p'TU_PASS' sistema_impobiomedical < migraciones.sql"
else
    echo "  Esperando que la base de datos esté lista..."
    for i in $(seq 1 15); do
        if docker exec impobiomedical_db mariadb-admin ping -u impo_user -p"${DB_PASS_LOCAL}" --silent 2>/dev/null; then
            echo "  Base de datos lista."
            break
        fi
        sleep 2
    done


    docker exec -i impobiomedical_db mariadb \
        -u impo_user \
        -p"${DB_PASS_LOCAL}" \
        sistema_impobiomedical \
        < migraciones.sql \
        && echo "  ✅ migraciones.sql aplicado." \
        || echo "  ⚠️  Error en migraciones.sql."
fi

echo ""
echo "========================================"
echo "✅ Despliegue completado exitosamente."
echo "========================================"
