# 🚀 MondolShopBD — Production Deployment & Server Runbook

> **Target Platform**: Ubuntu 22.04 / 24.04 LTS or AlmaLinux 9 (JoypurHost VPS / Cloud Server)  
> **Stack**: PHP 8.2/8.3-FPM + MySQL 8.0 / MariaDB 10.11 + Redis 7 + Nginx / OpenLiteSpeed  

---

## ১. সার্ভার প্রস্তুতি ও রিকোয়ারমেন্টস (Server Prerequisites)

```bash
# সিস্টেম প্যাকেজ আপডেট
sudo apt update && sudo apt upgrade -y

# প্রয়োজনীয় প্যাকেজ ও PHP 8.2 ইনস্টলেশন
sudo apt install -y nginx redis-server supervisor git curl unzip zip \
    php8.2-fpm php8.2-mysql php8.2-redis php8.2-curl php8.2-gd php8.2-mbstring \
    php8.2-xml php8.2-zip php8.2-bcmath php8.2-intl php8.2-cli

# Composer ইনস্টলেশন
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

---

## ২. প্রজেক্ট সেটআপ ও পারমিশন (Project Setup & Permissions)

```bash
# রিপোজিটরি ক্লোন
sudo git clone https://github.com/maccpro/mondolshopbd.git /var/www/mondolshopbd
cd /var/www/mondolshopbd

# এনভায়রনমেন্ট কনফিগ
sudo cp .env.example .env

# প্রোডাকশন ডিপেন্ডেন্সি ইনস্টল (Optimized)
sudo composer install --no-dev --optimize-autoloader --prefer-dist

# পারমিশন সেট করা
sudo chown -R www-data:www-data /var/www/mondolshopbd
sudo chmod -R 775 /var/www/mondolshopbd/storage /var/www/mondolshopbd/bootstrap/cache
```

---

## ৩. গুরুত্বপূর্ণ `.env` কনফিগারেশন

```ini
APP_NAME=MondolShopBD
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://mondolshopbd.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mondolshop_prod
DB_USERNAME=mondol_user
DB_PASSWORD=your_secure_password_here

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1

# Storage Link & Optimization
FILESYSTEM_DISK=public
```

---

## ৪. ডাটাবেজ মাইগ্রেশন ও অপ্টিমাইজেশন কমান্ড

```bash
# এপ্লিকেশন কী জেনারেট
php artisan key:generate

# মাইগ্রেশন রান
php artisan migrate --force

# স্টোরেজ সিমলিংক
php artisan storage:link

# প্রোডাকশন ক্যাশ অপ্টিমাইজেশন
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## ৫. Nginx ও Supervisor কনফিগারেশন

```bash
# Nginx কনফিগ কপি ও লিঙ্ক
sudo cp docs/deployment/NGINX-CONFIG.conf /etc/nginx/sites-available/mondolshopbd.conf
sudo ln -s /etc/nginx/sites-available/mondolshopbd.conf /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# Supervisor কনফিগ কপি ও চালু করা
sudo cp docs/deployment/SUPERVISOR-WORKERS.conf /etc/supervisor/conf.d/mondolshopbd-workers.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

---

## ৬. ক্রনজব (Crontab) শিডিউলার

```bash
# crontab এন্ট্রি ওপেন করুন
sudo crontab -e -u www-data

# নিচের লাইনটি যোগ করুন:
* * * * * cd /var/www/mondolshopbd && php artisan schedule:run >> /dev/null 2>&1
```

---

## ৭. কুরিয়ার ওয়েবহুক URL কনফিগারেশন

কুরিয়ার পোর্টালে (Steadfast, Pathao, RedX) নিচের ওয়েবহুক ইউআরএলসমূহ কনফিগার করুন:
- **Steadfast Webhook URL**: `https://mondolshopbd.com/api/v1/webhooks/courier/steadfast`
- **Pathao Webhook URL**: `https://mondolshopbd.com/api/v1/webhooks/courier/pathao`
- **RedX Webhook URL**: `https://mondolshopbd.com/api/v1/webhooks/courier/redx`

---

## ৮. সিকিউর ব্যাকআপ পলিসি (Automated Daily Backup)

```bash
#!/bin/bash
# /root/backup_mondolshop.sh
BACKUP_DIR="/root/backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
mkdir -p $BACKUP_DIR

# Database dump
mysqldump -u mondol_user -p'your_password' mondolshop_prod | gzip > $BACKUP_DIR/db_$TIMESTAMP.sql.gz

# Delete backups older than 7 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +7 -exec rm {} \;
```
