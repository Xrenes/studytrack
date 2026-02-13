# 🚀 StudyTrack Production Deployment Guide

Complete guide for deploying StudyTrack to production servers.

---

## 📋 Pre-Deployment Checklist

- [ ] PHP 7.4+ installed on server
- [ ] MySQL 5.7+ or PlanetScale account
- [ ] Apache/Nginx web server
- [ ] SSL certificate obtained
- [ ] Domain name configured
- [ ] Git installed (for deployment)

---

## 🎯 Deployment Options

### **Option 1: PlanetScale (Recommended)**
✅ Free 5GB database  
✅ Automatic backups  
✅ Zero downtime migrations  
✅ Global CDN  

### **Option 2: Traditional Hosting**
- Shared hosting (cPanel/Plesk)
- VPS (DigitalOcean, Linode, AWS EC2)
- Dedicated server

---

## 📦 Step 1: Setup Database

### Using PlanetScale (Cloud)

```bash
# 1. Create PlanetScale account
https://planetscale.com

# 2. Create new database
Name: studytrack
Region: Choose closest to your users

# 3. Get connection string
pscale connect studytrack main --port 3309

# 4 Copy credentials
Host: your-db.us-east-1.psdb.cloud
Database: studytrack
Username: generated_username
Password: generated_password
```

### Using Traditional MySQL

```bash
# 1. Create database
mysql -u root -p
CREATE DATABASE studytrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'studytrack'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON studytrack.* TO 'studytrack'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 🔧 Step  2: Configure Environment

```bash
# 1. Copy environment template
cp .env.example .env

# 2. Edit .env with your production values
nano .env
```

Edit `.env`:
```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DATABASE_HOST=your-database-host
DATABASE_NAME=studytrack
DATABASE_USERNAME=your-username
DATABASE_PASSWORD=your-secure-password
```

---

## 📤 Step 3: Upload Files

### Using Git (Recommended)

```bash
# On your server
cd /var/www/html  # or your web root
git clone https://github.com/yourusername/studytrack.git
cd studytrack

# Set proper permissions
chmod 755 -R .
chmod 644 .env
```

### Using FTP/SFTP

1. Upload all files EXCEPT:
   - `.git/` folder
   - `node_modules/` (if any)
   - Local `.env` file

2. Upload `.env.example` and rename to `.env`
3. Edit `.env` with production values

---

## 🗄️ Step 4: Run Database Migration

```bash
# SSH into your server
cd /path/to/studytrack

# Run migration script
php database/migrate.php

# Expected output:
# ✓ Tables created successfully
# ✓ Migrated 4 users
# ✓ Migrated 2 sections
# ✓ Migrated 6 section memberships
# ✓ Migrated 12 events
#
# Test Credentials:
# student@diu.edu.bd / student123
# teacher@diu.edu.bd / teacher123
```

**⚠️ IMPORTANT:** Save the test credentials from migration output!

---

## 🌐 Step 5: Configure Web Server

### Apache (.htaccess already configured)

```apache
# Verify mod_rewrite is enabled
sudo a2enmod rewrite
sudo systemctl restart apache2

# Set DocumentRoot to your app folder
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/html/studytrack
    
    <Directory /var/www/html/studytrack>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/studytrack-error.log
    CustomLog ${APACHE_LOG_DIR}/studytrack-access.log combined
</VirtualHost>
```

### Nginx

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/html/studytrack;
    index index.php;

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # API routing
    location /api/ {
        try_files $uri $uri/ =404;
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;

    # Block sensitive files
    location ~ /\.(env|git|htaccess) {
        deny all;
    }

    location ~ /config/ {
        deny all;
    }
}
```

---

## 🔒 Step 6: Setup SSL Certificate

### Using Let's Encrypt (Free)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache  # For Apache
# OR
sudo apt install certbot python3-certbot-nginx   # For Nginx

# Get certificate
sudo certbot --apache -d yourdomain.com          # For Apache
# OR
sudo certbot --nginx -d yourdomain.com           # For Nginx

# Auto-renewal (cron job)
sudo certbot renew --dry-run
```

### Update .htaccess for HTTPS

Uncomment these lines in `.htaccess`:
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 🧪 Step 7: Production Testing

### Test Database Connection

```bash
php -r "require 'config/Database.php'; echo getDbConnection() ? '✓ Connected' : '✗ Failed';"
```

### Test Login

1. Visit: `https://yourdomain.com`
2. Login with: `student@diu.edu.bd` / `student123`
3. Create test event
4. Check database:
   ```bash
   mysql -u studytrack -p studytrack
   SELECT * FROM events ORDER BY id DESC LIMIT 1;
   ```

### Test API Endpoints

```bash
# Get user stats
curl https://yourdomain.com/api/user/stats.php \
  --cookie "PHPSESSID=your_session_id"

# List events
curl https://yourdomain.com/api/events/list.php?filter=upcoming \
  --cookie "PHPSESSID=your_session_id"
```

---

## 📊 Step 8: Enable Production Monitoring

### Setup Error Logging

Edit `config/config.php`:
```php
// Production error handling
if (getenv('APP_ENV') === 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);  // Don't show errors to users
    ini_set('log_errors', 1);      // Log errors to file
    ini_set('error_log', BASE_PATH . '/logs/php-errors.log');
}
```

Create logs directory:
```bash
mkdir logs
chmod 755 logs
touch logs/php-errors.log
chmod 644 logs/php-errors.log
```

### Setup Database Monitoring

```sql
-- Check table sizes
SELECT 
    table_name,
    round(((data_length + index_length) / 1024 / 1024), 2) as size_mb
FROM information_schema.TABLES
WHERE table_schema = 'studytrack';

-- Check query performance
SHOW FULL PROCESSLIST;
```

---

## 🔐 Step 9: Security Hardening

### File Permissions

```bash
# Protect sensitive files
chmod 644 .env
chmod 644 config/*.php
chmod 644 database/*.sql
chmod 600 logs/*.log

# Prevent migration from running again
chmod 000 database/migrate.php  # Or delete it
```

### Remove Development Files

```bash
rm -f test-api.html
rm -f DATABASE_SETUP_NEEDED.txt
rm -rf .git  # If not using git for updates
```

### Setup Backup Cron Job

```bash
crontab -e

# Add this line (daily backup at 2 AM)
0 2 * * * /usr/bin/mysqldump -u studytrack -pyour_password studytrack > /backups/studytrack-$(date +\%Y\%m\%d).sql
```

---

## 🚦 Step 10: Go Live

### Pre-Launch Checklist

- [ ] Database migrated successfully
- [ ] SSL certificate installed
- [ ] Error logging enabled
- [ ] Test accounts working
- [ ] API endpoints responding
- [ ] Static assets loading (CSS/JS)
- [ ] Forms submitting correctly
- [ ] Email notifications working (if implemented)
- [ ] Backup cron job configured
- [ ] DNS pointing to server
- [ ] `.env` configured correctly

### Launch Commands

```bash
# Clear any caches
php -r "opcache_reset();"

# Restart web server
sudo systemctl restart apache2  # or nginx

# Monitor logs
tail -f logs/php-errors.log
tail -f /var/log/apache2/studytrack-error.log
```

### Post-Launch Monitoring

```bash
# Check server resources
htop

# Check database connections
mysql -u studytrack -p studytrack
SHOW PROCESSLIST;

# Check disk usage
df -h

# Check error logs
tail -n 50 logs/php-errors.log
```

---

## 🐛 Troubleshooting

### Issue: "Database connection failed"

```bash
# Check database is running
sudo systemctl status mysql

# Test connection manually
mysql -h your-database-host -u your-username -p

# Check .env file exists and is readable
ls -la .env
cat .env  # Verify credentials
```

### Issue: "500 Internal Server Error"

```bash
# Check Apache error log
sudo tail -f /var/log/apache2/error.log

# Check PHP error log
tail -f logs/php-errors.log

# Verify file permissions
ls -la config/
```

### Issue: "API returns 404"

```bash
# Verify .htaccess is being read
sudo cat /var/www/html/studytrack/.htaccess

# Enable mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2

# Check AllowOverride is set to All in Apache config
```

### Issue: "Sessions not persisting"

```bash
# Check session directory permissions
ls -la /var/lib/php/sessions

# Check PHP session configuration
php -i | grep session

# Verify session.cookie_secure matches HTTPS usage
```

---

## 📈 Performance Optimization

### Enable OPcache

```ini
# /etc/php/8.1/apache2/php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### Enable Gzip Compression

Already configured in `.htaccess`:
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css text/javascript application/javascript application/json
</IfModule>
```

### Setup CDN (Optional)

1. Cloudflare (Free):
   - Add your domain to Cloudflare
   - Update nameservers
   - Enable "Auto Minify" for CSS/JS
   - Enable "Brotli" compression

---

## 📞 Support & Maintenance

### Regular Maintenance Tasks

**Daily:**
- Check error logs
- Monitor server resources

**Weekly:**
- Review database size
- Check backup integrity
- Update dependencies

**Monthly:**
- Review security patches
- Update PHP/MySQL versions
- Clean old logs

### Updating Production

```bash
# Pull latest changes
cd /var/www/html/studytrack
git pull origin main

# Run any new migrations (if any)
php database/migrate.php

# Clear caches
php -r "opcache_reset();"

# Restart web server
sudo systemctl restart apache2
```

---

## 🎉 You're Live!

Your StudyTrack application is now running in production!

**Default Test Accounts:**
- Student: `student@diu.edu.bd` / `student123`
- Teacher: `teacher@diu.edu.bd` / `teacher123`

**Next Steps:**
1. Change default passwords
2. Create real user accounts
3. Remove test accounts from database
4. Setup regular backups
5. Monitor error logs daily

**Need Help?**
- Check logs: `logs/php-errors.log`
- Database issues: Verify `.env` credentials
- Web server issues: Check Apache/Nginx error logs

---

**Production URL:** https://yourdomain.com  
**Deployed:** February 14, 2026  
**Version:** 1.0.0
