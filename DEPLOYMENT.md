# Panduan Deployment - Rikkes Berkala

## Domain: https://biddokkespoldajabar.net

---

## Prasyarat VPS

### 1. Install PHP 8.2 dan Extensions
```bash
sudo apt update && sudo apt upgrade -y

sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring \
    php8.2-xml php8.2-bcmath php8.2-zip php8.2-gd php8.2-curl \
    php8.2-cli php8.2-intl php8.2-fileinfo php8.2-dom php8.2-ctype \
    php8.2-tokenizer php8.2-opcache
```

### 2. Install Nginx
```bash
sudo apt install -y nginx
```

### 3. Install MySQL
```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

### 4. Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 5. Install Node.js (untuk build assets)
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

---

## Langkah Deployment

### 1. Clone/Upload Project
```bash
cd /var/www
sudo git clone <repository-url> biddokkespoldajabar.net
# ATAU upload manual via SFTP

cd biddokkespoldajabar.net
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache
```

### 2. Setup Environment
```bash
# Copy template production
cp .env.production .env

# Generate app key
php artisan key:generate

# Edit .env dan sesuaikan:
# - DB_DATABASE, DB_USERNAME, DB_PASSWORD
# - MAIL settings
nano .env
```

### 3. Install Dependencies
```bash
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

### 4. Setup Database
```bash
# Buat database di MySQL
mysql -u root -p
> CREATE DATABASE rikkes_berkala CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
> CREATE USER 'rikkes_user'@'localhost' IDENTIFIED BY 'YOUR_SECURE_PASSWORD';
> GRANT ALL PRIVILEGES ON rikkes_berkala.* TO 'rikkes_user'@'localhost';
> FLUSH PRIVILEGES;
> EXIT;

# Jalankan migration
php artisan migrate --force
```

### 5. Storage Link
```bash
php artisan storage:link
```

### 6. Cache Optimization
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## Konfigurasi Nginx

### Buat file: `/etc/nginx/sites-available/biddokkespoldajabar.net`

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name biddokkespoldajabar.net www.biddokkespoldajabar.net;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name biddokkespoldajabar.net www.biddokkespoldajabar.net;

    root /var/www/biddokkespoldajabar.net/public;
    index index.php;

    # SSL Certificate (gunakan Certbot)
    ssl_certificate /etc/letsencrypt/live/biddokkespoldajabar.net/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/biddokkespoldajabar.net/privkey.pem;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/json application/xml;

    # Max Upload Size (untuk Excel/PDF)
    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### Aktifkan Site
```bash
sudo ln -s /etc/nginx/sites-available/biddokkespoldajabar.net /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## SSL Certificate (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d biddokkespoldajabar.net -d www.biddokkespoldajabar.net

# Auto-renew
sudo certbot renew --dry-run
```

---

## Queue Worker (untuk WhatsApp)

### Buat Supervisor Config: `/etc/supervisor/conf.d/rikkes-worker.conf`

```ini
[program:rikkes-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/biddokkespoldajabar.net/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/biddokkespoldajabar.net/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start rikkes-worker:*
```

---

## Cron Job (Laravel Scheduler)

```bash
sudo crontab -e

# Tambahkan:
* * * * * cd /var/www/biddokkespoldajabar.net && php artisan schedule:run >> /dev/null 2>&1
```

---

## Troubleshooting

### Permission Issues
```bash
sudo chown -R www-data:www-data /var/www/biddokkespoldajabar.net
sudo chmod -R 755 /var/www/biddokkespoldajabar.net
sudo chmod -R 775 /var/www/biddokkespoldajabar.net/storage
sudo chmod -R 775 /var/www/biddokkespoldajabar.net/bootstrap/cache
```

### Clear All Cache
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Check Logs
```bash
tail -f /var/www/biddokkespoldajabar.net/storage/logs/laravel.log
sudo tail -f /var/log/nginx/error.log
```

---

## Update Deployment

```bash
cd /var/www/biddokkespoldajabar.net

# Pull changes
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Run migrations
php artisan migrate --force

# Clear & rebuild cache
php artisan optimize:clear
php artisan optimize

# Restart queue workers
sudo supervisorctl restart rikkes-worker:*
```

---

## Checklist Deployment

- [ ] PHP 8.2 dengan semua extensions terinstall
- [ ] Nginx dikonfigurasi dengan benar
- [ ] SSL Certificate aktif (HTTPS)
- [ ] Database MySQL dibuat dan dikonfigurasi
- [ ] .env sudah disesuaikan dengan production
- [ ] `php artisan key:generate` sudah dijalankan
- [ ] `composer install --no-dev` berhasil
- [ ] `npm run build` berhasil
- [ ] `php artisan migrate --force` berhasil
- [ ] `php artisan storage:link` berhasil
- [ ] Queue worker (Supervisor) berjalan
- [ ] Cron job untuk scheduler aktif
- [ ] File permissions sudah benar (www-data)
