# 🚀 Manual de Despliegue en VPS y Mantenimiento — Sistema Impobiomedical

Guía completa para instalar, configurar y mantener en producción el **Sistema Impobiomedical** en un servidor VPS (Ubuntu Linux / Nginx / Apache / Docker).

---

## 1. Despliegue en Servidor VPS (Ubuntu 22.04 / 24.04 LTS)

### Instalación de Paquetes
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-fileinfo php8.2-xml php8.2-curl composer git mysql-server nginx
```

### Clonación y Configuración
```bash
cd /var/www/
git clone https://github.com/Santiago072/SistemaImpobiomedical.git sistema
cd sistema
composer install --no-dev --optimize-autoloader
cp .env.example config/.env
```

### Configurar `.env` de Producción
```ini
DB_HOST=127.0.0.1
DB_NAME=sistema_impobiomedical
DB_USER=usuario_prod
DB_PASS=TuContrasenaSegura_2026!
APP_BASE=/
COOKIE_SECURE=1
SESSION_LIFETIME=3600
```

### Permisos de Archivos
```bash
sudo chown -R www-data:www-data /var/www/sistema/
sudo chmod -R 755 /var/www/sistema/
sudo chmod -R 775 /var/www/sistema/uploads /var/www/sistema/logs /var/www/sistema/sessions
```

### Actualización Automatizada (`deploy.sh`)
```bash
cd /var/www/sistema
bash deploy.sh
```

---

## 2. Despliegue con Docker

```bash
docker compose up -d --build
```

---

## 3. Backups Automáticos (MySQL & Archivos)

```cron
# Ejecutar backup diario a las 2:00 AM
0 2 * * * mysqldump -u usuario_prod -p'Contrasena' sistema_impobiomedical | gzip > /backups/mysql/impobiomedical_$(date +\%F).sql.gz
```
