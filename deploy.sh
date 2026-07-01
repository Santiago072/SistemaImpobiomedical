#!/bin/bash
# Script de despliegue automático — Sistema Impobiomedical

echo "Iniciando despliegue Impobiomedical..."

# 1. Ajustar permisos para evitar conflictos con archivos creados por Docker
echo "[1/4] Ajustando permisos locales..."
sudo chown -R $USER:$USER .

# 2. Obtener los últimos cambios de GitHub
echo "[2/4] Obteniendo cambios de GitHub..."
git fetch origin

# 3. Forzar sincronización exacta con main
echo "[3/4] Sincronizando con la rama main..."
git reset --hard origin/main

# 4. Reconstruir y levantar contenedores
echo "[4/4] Reconstruyendo y levantando contenedores Docker..."
docker compose up -d --build

echo ""
echo "✅ Despliegue Impobiomedical completado exitosamente."
echo "   Disponible en puerto: 8894"
