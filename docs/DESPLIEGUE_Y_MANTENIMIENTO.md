# 🚀 Guía de Despliegue y Mantenimiento — Sistema Impobiomedical

Esta guía detalla los pasos para desplegar, configurar y mantener en producción el **Sistema Impobiomedical** en un servidor VPS (Ubuntu Linux / Debian) o mediante Docker.

---

## 1. Despliegue en Servidor VPS (Linux Ubuntu 22.04 / 24.04 LTS)

### Requisitos Previos en el Servidor
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-fileinfo php8.2-xml php8.2-curl composer git mysql-server nginx
```

### Pasos de Instalación
1. **Clonar el proyecto en el directorio web:**
   ```bash
   cd /var/www/
   git clone https://github.com/Santiago072/SistemaImpobiomedical.git sistema
   cd sistema
   ```

2. **Instalar dependencias de Composer (Solo Producción):**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Configurar el archivo `.env`:**
   ```bash
   cp .env.example config/.env
   nano config/.env
   ```
   Configura las variables reales de producción:
   ```ini
   DB_HOST=127.0.0.1
   DB_NAME=sistema_impobiomedical
   DB_USER=usuario_prod
   DB_PASS=ContrasenaSegura_2026!
   APP_BASE=/
   COOKIE_SECURE=1
   SESSION_LIFETIME=3600
   ```

4. **Permisos y Propiedad de Directorios:**
   Es indispensable otorgar permisos de escritura a `www-data` para las carpetas de almacenamiento y sesiones:
   ```bash
   sudo chown -R www-data:www-data /var/www/sistema/
   sudo chmod -R 755 /var/www/sistema/
   sudo chmod -R 775 /var/www/sistema/uploads /var/www/sistema/logs /var/www/sistema/sessions
   ```

5. **Automatización de Despliegues (`deploy.sh`):**
   Para actualizar el servidor en cada cambio, simplemente ejecuta:
   ```bash
   bash deploy.sh
   ```

---

## 2. Despliegue con Docker y Docker Compose

El proyecto incluye `Dockerfile` y `docker-compose.yml` listos para producción:

1. **Construir y levantar los contenedores:**
   ```bash
   docker compose up -d --build
   ```

2. **Verificar estado de los contenedores:**
   ```bash
   docker compose ps
   ```

3. **Ver logs en tiempo real:**
   ```bash
   docker compose logs -f
   ```

---

## 3. Política de Respaldos (Backups Automáticos)

### Respaldo de Base de Datos MySQL
Crea un script de backup diario mediante `crontab`:

```bash
#!/bin/bash
# /root/scripts/backup_db.sh
FECHA=$(date +"%Y-%m-%d_%H%M")
BACKUP_DIR="/backups/mysql"
mkdir -p $BACKUP_DIR

mysqldump -u usuario_prod -p'ContrasenaSegura_2026!' sistema_impobiomedical | gzip > "$BACKUP_DIR/impobiomedical_$FECHA.sql.gz"

# Mantener solo los últimos 30 días de backups
find $BACKUP_DIR -type f -name "*.sql.gz" -mtime +30 -exec rm {} \;
```

Programar en cron (`crontab -e`):
```cron
# Ejecutar todos los días a las 2:00 AM
0 2 * * * /root/scripts/backup_db.sh
```

### Respaldo de Archivos Multimedia (`uploads/`)
```bash
# Respaldo semanal de imágenes de productos
tar -czvf /backups/uploads_$(date +"%Y%m%d").tar.gz /var/www/sistema/uploads/
```

---

## 4. Certificado SSL / HTTPS (Let's Encrypt)

Para producción con dominio comercial, activa SSL gratuito con Certbot:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d tu-dominio.com -d www.tu-dominio.com
```
