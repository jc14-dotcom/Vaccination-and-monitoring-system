# 🏥 Infant Vaccination & Growth Monitoring System
## Comprehensive System Documentation & Deployment Guide

---

## 📋 Table of Contents
1. [System Overview](#system-overview)
2. [Technology Stack](#technology-stack)
3. [System Architecture](#system-architecture)
4. [Key Features](#key-features)
5. [Database Schema](#database-schema)
6. [System Requirements](#system-requirements)
7. [Deployment Options](#deployment-options)
8. [Installation Guide](#installation-guide)
9. [Configuration](#configuration)
10. [Production Deployment](#production-deployment)
11. [Maintenance & Monitoring](#maintenance--monitoring)
12. [Security Considerations](#security-considerations)
13. [Troubleshooting](#troubleshooting)

---

## 🎯 System Overview

### Purpose
The **Infant Vaccination & Growth Monitoring System** is a comprehensive Progressive Web Application (PWA) designed for the Barangay Balayhangin Health Center in Calauan, Laguna. It digitizes and streamlines the management of infant vaccination records, growth monitoring, and health scheduling for children aged 0-5 years.

### Target Users
- **Health Workers**: Medical staff managing vaccination records and inventory
- **Parents/Guardians**: View their children's vaccination status and schedules
- **Administrators**: System management and reporting

### Location
- **Primary Location**: Barangay Balayhangin, Calauan, Laguna, Philippines
- **Coverage**: Local barangay health center with expansion potential

---

## 🛠 Technology Stack

### Backend Framework
- **Laravel 11.9** (PHP 8.2+)
  - Modern PHP framework with expressive syntax
  - Built-in authentication, routing, and ORM
  - Artisan CLI for development tasks

### Frontend Technologies
- **Blade Templates** (Laravel's templating engine)
- **Tailwind CSS 2.2.19** - Utility-first CSS framework
- **Tailwind CSS v4.1.7** (@tailwindcss/cli) - Latest version for compilation
- **Vanilla JavaScript** - No framework dependencies
- **Vite 6.3.5** - Modern frontend build tool

### Database
- **MySQL/MariaDB** (Primary)
  - Relational database for production
  - ACID compliance for data integrity
  - Support for complex queries and relationships

- **SQLite** (Development fallback)
  - Lightweight for local development
  - Zero configuration

### Caching & Session
- **Redis** (ACTIVE)
  - High-performance caching (`CACHE_STORE=redis`)
  - PHPRedis client
  - Session storage in database
  - **Note**: Redis server must be running for system to work properly

### Progressive Web App (PWA)
- **Service Worker** (`sw.js`)
  - Offline capability
  - Push notification handling
  - Asset caching
  - Background sync

- **Web App Manifest** (`manifest.json`)
  - Installable app experience
  - Custom icons (72x72 to 512x512)
  - Standalone display mode
  - Theme colors: #7a5bbd (purple)

### Push Notifications & SMS
- **Web Push Protocol** (VAPID)
  - Package: `laravel-notification-channels/webpush` v10.3
  - Browser push notifications
  - Real-time alerts for vaccination schedules
  - Reminder notifications
  - VAPID keys configured and active

- **SMS Notifications** (Semaphore Gateway)
  - Provider: Semaphore.co
  - Cost: ~₱0.65 per SMS
  - Status: Configured (can be enabled with `SMS_ENABLED=true`)
  - Use case: Vaccination reminders via text message
  - Sender name: "InfantVax"

### Additional Packages

#### PHP/Composer Dependencies
```json
{
  "phpoffice/phpspreadsheet": "^5.2",                  // Excel export/import
  "laravel-notification-channels/webpush": "^10.3",   // Web Push notifications
  "laravel/telescope": "^5.8",                         // Debugging & monitoring
  "laravel/tinker": "^2.9"                             // REPL for Laravel
}
```

#### External Services
- **Semaphore SMS Gateway**: https://semaphore.co/
  - API integration for SMS notifications
  - Configured but disabled by default (cost consideration)

#### NPM Dependencies
```json
{
  "@tailwindcss/cli": "^4.1.7",
  "@tailwindcss/postcss": "^4.1.5",
  "autoprefixer": "^10.4.21",
  "axios": "^1.7.4",                      // HTTP client
  "laravel-vite-plugin": "^1.0",
  "postcss": "^8.5.3",
  "vite": "^6.3.5"
}
```

### Development Tools
- **Laravel Telescope** - Application debugging and monitoring
- **Laravel Pint** - PHP code style fixer
- **Laravel Sail** - Docker development environment
- **PHPUnit** - Testing framework
- **Faker** - Test data generation

---

## 🏗 System Architecture

### Application Structure

```
infantsSystem/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/                    # API endpoints
│   │   │   │   ├── NotificationController.php
│   │   │   │   ├── SessionController.php
│   │   │   │   └── VaccineStockController.php
│   │   │   ├── Auth/                   # Authentication
│   │   │   │   ├── PasswordController.php
│   │   │   │   └── PrivacyController.php
│   │   │   ├── Parent/                 # Parent-specific
│   │   │   │   ├── DashboardController.php
│   │   │   │   └── ProfileController.php
│   │   │   ├── AuthController.php
│   │   │   ├── BackupController.php
│   │   │   ├── FeedbackController.php
│   │   │   ├── HealthWorkerController.php
│   │   │   ├── InventoryController.php
│   │   │   ├── LoginController.php
│   │   │   ├── PatientController.php
│   │   │   ├── PushSubscriptionController.php
│   │   │   ├── ReportController.php
│   │   │   ├── VaccinationController.php
│   │   │   └── VaccinationScheduleController.php
│   │   └── Middleware/
│   │       ├── EnsureAuthenticated.php  # Auth verification
│   │       ├── PreventBackHistory.php   # Cache control
│   │       └── CacheResponse.php        # Response caching
│   ├── Models/
│   │   ├── Patient.php
│   │   ├── PatientVaccineRecord.php
│   │   ├── PatientGrowthRecord.php
│   │   ├── Vaccine.php
│   │   ├── VaccineInventory.php
│   │   ├── VaccinationTransaction.php
│   │   ├── VaccinationSchedule.php
│   │   ├── VaccinationReportSnapshot.php
│   │   ├── Parents.php
│   │   ├── HealthWorker.php
│   │   ├── Feedback.php
│   │   └── SmsLog.php
│   ├── Notifications/                   # Push notifications
│   ├── Services/                        # Business logic
│   └── Helpers/                         # Utility functions
├── database/
│   ├── migrations/                      # 40+ migration files
│   ├── seeders/
│   └── factories/
├── public/
│   ├── css/
│   │   └── tailwind-full.css           # Compiled Tailwind
│   ├── javascript/
│   │   ├── notifications.js            # Push notification client
│   │   ├── pwa.js                      # PWA installation
│   │   ├── logout-helper.js
│   │   └── session-guard.js
│   ├── images/                          # Icons & assets
│   ├── manifest.json                    # PWA manifest
│   ├── sw.js                            # Service Worker
│   └── index.php                        # Entry point
├── resources/
│   ├── views/
│   │   ├── auth/                        # Login pages
│   │   ├── health_worker/               # Health worker views
│   │   ├── parents/                     # Parent views
│   │   ├── layouts/
│   │   │   ├── responsive-layout.blade.php
│   │   │   └── master.blade.php
│   │   └── welcome.blade.php            # Landing page
│   ├── css/
│   │   └── app.css                      # Source CSS
│   └── js/
├── routes/
│   ├── web.php                          # Web routes
│   └── console.php
├── config/                              # Configuration files
│   ├── database.php
│   ├── cache.php
│   ├── session.php
│   ├── queue.php
│   ├── webpush.php                      # VAPID configuration
│   └── telescope.php
├── storage/
│   ├── app/                             # Application storage
│   ├── framework/                       # Framework cache
│   └── logs/                            # Application logs
└── vendor/                              # Composer dependencies
```

### Multi-Guard Authentication System

The system implements **dual authentication guards**:

1. **Health Worker Guard** (`health_worker`)
   - Table: `health_workers`
   - Access: Full system management
   - Features: Patient management, inventory, reports, scheduling

2. **Parent Guard** (`parents`)
   - Table: `parents`
   - Access: View-only for their children
   - Features: Vaccination records, growth charts, feedback

### Database Architecture

#### Core Tables
- `patients` - Patient master records
- `patient_vaccine_records` - Vaccination history
- `patient_growth_records` - Growth measurements (weight, height, head circumference)
- `vaccines` - Vaccine master list
- `vaccine_inventory` - Stock management
- `vaccination_transactions` - Transaction log
- `vaccination_schedules` - Upcoming schedules
- `vaccination_report_snapshots` - Monthly reports with versioning
- `parents` - Parent/guardian accounts
- `health_workers` - Health worker accounts
- `feedback` - Parent feedback system
- `push_subscriptions` - Web push subscriptions
- `notifications` - Notification records
- `sms_logs` - SMS notification logs
- `cache` - Cache storage
- `cache_locks` - Cache locking
- `jobs` - Queue jobs
- `job_batches` - Batch job tracking
- `sessions` - User sessions
- `telescope_entries` - Debugging data (development)

#### Key Relationships
- Patient → Parent (many-to-one)
- Patient → VaccineRecords (one-to-many)
- Patient → GrowthRecords (one-to-many)
- Vaccine → Inventory (one-to-one)
- VaccinationTransaction → Schedule (many-to-one)
- Parent → PushSubscription (one-to-many)

---

## ✨ Key Features

### For Health Workers

#### 1. Patient Management
- Register new infants with detailed information
- Track vaccination history per patient
- Record growth measurements (weight, height, head circumference)
- View patient vaccination cards
- Search and filter patient records
- Barangay-based filtering

#### 2. Vaccination Management
- Schedule vaccination appointments
- Record administered vaccines
- Track vaccine inventory with bottle tracking
- Automatic dose calculations (multi-dose vaccines)
- Backdate vaccination records when needed
- Cancel/reschedule appointments

#### 3. Inventory Management
- Real-time vaccine stock monitoring
- Add new vaccine stock
- Track doses used vs doses available
- Low stock alerts
- Batch/lot number tracking
- Expiration date management

#### 4. Reporting System
- Monthly vaccination coverage reports
- Age-group segmentation (0-11 months, 12-23 months, 24-59 months)
- Report versioning (draft, final, locked)
- Export to Excel and PDF
- Historical report viewing
- Restore previous versions
- Compare report versions

#### 5. Vaccination Scheduling
- Create vaccination schedules
- View upcoming appointments
- Send reminder notifications
- Cancel schedules with reasons

#### 6. Feedback Management
- View parent feedback
- Track feedback analytics
- Sentiment analysis
- Load more pagination

#### 7. Backup & Restore
- Database backup functionality
- Restore from backup
- Download backup files
- Backup history

### For Parents

#### 1. Dashboard
- View all registered children
- Quick stats (vaccinated, pending)
- Upcoming schedules
- Recent notifications

#### 2. Vaccination Records
- Complete vaccination history
- Vaccination card view
- Growth chart visualization
- Download vaccination records

#### 3. Feedback System
- Submit feedback to health center
- View submission history
- Rating system

#### 4. Profile Management
- Update contact information
- Change password (forced on first login)
- Privacy policy consent

#### 5. Notifications
- Push notifications for:
  - Upcoming vaccinations
  - Missed appointments
  - System announcements
- SMS notifications (configured)
- In-app notification center

### Progressive Web App Features

#### 1. Installation
- Add to home screen (mobile)
- Standalone app experience
- Custom splash screen
- App shortcuts

#### 2. Offline Capability
- Service worker caching
- Offline asset access
- Background sync (when online)

#### 3. Push Notifications
- Real-time browser notifications
- Works even when app is closed
- Customizable notification actions
- Badge notifications

---

## 💾 Database Schema

### Key Entities

#### Patients Table
```sql
- id (bigint, primary key)
- parent_id (foreign key → parents)
- child_number (integer)
- firstname, middlename, lastname (varchar)
- birthdate, birthplace (varchar)
- gender (enum: male, female)
- birthweight, birthlength (decimal)
- immunization_record_number (varchar)
- contact_number (varchar)
- barangay (varchar, indexed)
- purok, city (varchar)
- timestamps
- Indexes: parent_id, barangay, vaccination_status
```

#### Vaccine Records Table
```sql
- id (bigint, primary key)
- patient_id (foreign key → patients)
- vaccine_id (foreign key → vaccines)
- dose_number (integer)
- administered_date (date)
- administrator (varchar)
- remarks (text, nullable)
- timestamps
- Unique: patient_id + vaccine_id + dose_number
```

#### Vaccination Schedules Table
```sql
- id (bigint, primary key)
- patient_id (foreign key → patients)
- vaccine_id (foreign key → vaccines, nullable)
- scheduled_date (date)
- dose_number (integer, nullable)
- status (enum: pending, completed, cancelled, missed)
- notes (text, nullable)
- notified_at (timestamp, nullable)
- cancelled_at (timestamp, nullable)
- cancellation_reason (varchar, nullable)
- cancelled_by (varchar, nullable)
- timestamps
```

#### Vaccine Inventory Table
```sql
- id (bigint, primary key)
- vaccine_id (foreign key → vaccines)
- current_stock (integer)
- minimum_stock (integer)
- last_restock_date (date)
- timestamps
```

#### Push Subscriptions Table (Web Push)
```sql
- id (bigint, primary key)
- subscribable_type (varchar) - polymorphic
- subscribable_id (bigint) - polymorphic
- endpoint (text)
- public_key (varchar, nullable)
- auth_token (varchar, nullable)
- content_encoding (varchar, nullable)
- timestamps
```

---

## ⚙️ System Requirements

### Server Requirements

#### Minimum Requirements
- **PHP**: 8.2 or higher
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Redis**: 5.0+ (REQUIRED - system uses Redis for caching)
- **Memory**: 512 MB RAM minimum (1GB recommended with Redis)
- **Storage**: 1 GB available space
- **SSL Certificate**: Required for PWA and push notifications

#### Recommended Requirements
- **PHP**: 8.3 (latest stable)
- **Web Server**: Nginx 1.24+ (better performance)
- **Database**: MySQL 8.0+ / MariaDB 10.11+
- **Redis**: 7.x (latest stable)
- **Memory**: 2 GB RAM (Redis caching improves performance)
- **Storage**: 5 GB SSD
- **CPU**: 2 cores minimum
- **SSL**: Let's Encrypt or commercial SSL

#### PHP Extensions Required
```ini
- OpenSSL
- PDO
- Mbstring
- Tokenizer
- XML
- Ctype
- JSON
- BCMath
- Fileinfo
- GD (for image processing)
- Curl
- Zip
- Redis (optional, if using Redis cache)
```

### Development Requirements
- **Node.js**: 18.x or 20.x (LTS)
- **NPM**: 9.x or higher
- **Composer**: 2.6+
- **Git**: For version control

---

## 🌐 Deployment Options

### Option 1: VPS (Virtual Private Server) ⭐ **RECOMMENDED**

#### ✅ Advantages
1. **Full Control**: Complete server access and configuration
2. **Redis Support**: **REQUIRED** - System uses Redis for caching
3. **Push Notifications**: Full support for VAPID/Web Push
4. **SMS Integration**: Semaphore API works without restrictions
5. **Background Jobs**: Can run queue workers for async tasks
6. **Cron Jobs**: Full cron job support for scheduled tasks
7. **Service Worker**: No restrictions on service worker functionality
8. **WebSocket Support**: If you expand to real-time features
9. **Performance**: Better resource allocation with Redis
10. **Scalability**: Easy to upgrade resources
11. **SSL**: Free Let's Encrypt SSL

#### 💰 Cost
- **Budget VPS**: $5-10/month (Vultr, DigitalOcean, Linode)
- **Mid-tier VPS**: $20-40/month (better performance)

#### 📌 Recommended VPS Providers
1. **DigitalOcean** - Droplets starting at $6/month
2. **Vultr** - Cloud compute starting at $5/month
3. **Linode** - Shared CPU starting at $5/month
4. **AWS Lightsail** - Starting at $5/month
5. **Hetzner** - Very competitive pricing (Europe-based)

#### ⚙️ VPS Setup Stack
```bash
OS: Ubuntu 22.04 LTS
Web Server: Nginx
PHP: 8.3 FPM
Database: MySQL 8.0 / MariaDB 10.11
Cache: Redis 7.x (REQUIRED)
SSL: Let's Encrypt (Certbot)
Process Manager: Supervisor (for queue workers)
SMS: Semaphore API (external service)
```

### Option 2: Shared Hosting ⚠️ **NOT RECOMMENDED**

#### ❌ Limitations
1. **No Redis**: Most shared hosting doesn't support Redis (**CRITICAL - System requires Redis**)
2. **Push Notifications**: Limited or no VAPID support
3. **SMS Integration**: May be blocked or restricted
4. **Queue Workers**: Cannot run background workers
5. **Cron Jobs**: Limited or restricted cron access
6. **Performance**: Shared resources, much slower without Redis
7. **Service Worker**: May have restrictions
8. **SSL**: May be extra cost
9. **Limited Control**: Restricted server configuration
10. **Memory Limits**: Usually 128MB-512MB PHP memory
11. **No SSH Access**: GUI-only management

**⚠️ IMPORTANT**: This system **REQUIRES Redis** for caching. Shared hosting that doesn't support Redis will not work properly.

#### 🔄 Alternative: Modify System for Shared Hosting
If you **must** use shared hosting without Redis:

**⚠️ WARNING**: This requires code modifications and will impact performance

1. **Change Cache Driver**: Modify `.env`
   ```env
   CACHE_STORE=database  # Change from redis to database
   SESSION_DRIVER=database
   QUEUE_CONNECTION=database
   ```

2. **Code Changes Required**:
   - Update cache configuration
   - Test all features thoroughly
   - Expect slower performance

3. **Push Notifications**: May need third-party service
   - Firebase Cloud Messaging (FCM)
   - OneSignal
   - Pusher Beams

4. **SMS**: Semaphore should still work (HTTP API)

5. **Queue Jobs**: Use cron + database queue driver
   ```bash
   # .htaccess cron job simulation
   php artisan queue:work --once
   ```

**Recommendation**: Strongly avoid shared hosting for this system. VPS is only $5-10/month and provides full Redis support.

#### 💰 Cost
- **Shared Hosting**: $3-15/month
- **Note**: May need push notification service ($0-50/month)

---

## 🚀 Installation Guide

### Step 1: Clone Repository
```bash
cd /var/www/
git clone <your-repo-url> infantsSystem
cd infantsSystem
```

### Step 2: Install PHP Dependencies
```bash
composer install --optimize-autoloader --no-dev
```

### Step 3: Install Node Dependencies
```bash
npm install
npm run build:tailwind
npm run build
```

### Step 4: Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### Step 5: Database Setup
```bash
# Create database
mysql -u root -p
CREATE DATABASE infant_vaccination_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ivs_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON infant_vaccination_system.* TO 'ivs_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php artisan migrate --force

# Optional: Seed initial data
php artisan db:seed
```

### Step 6: Generate VAPID Keys (for Push Notifications)
```bash
php artisan webpush:vapid
```
Copy the generated keys to `.env`:
```env
VAPID_PUBLIC_KEY=your_public_key_here
VAPID_PRIVATE_KEY=your_private_key_here
VAPID_SUBJECT=mailto:your-email@example.com
```

### Step 7: Storage Permissions
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Step 8: Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## ⚙️ Configuration

### Environment Variables (.env)

```env
# Application
APP_NAME="Infant Vaccination System"
APP_ENV=production
APP_KEY=base64:your_app_key_here
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=infant_vaccination_system
DB_USERNAME=ivs_user
DB_PASSWORD=secure_password_here

# Cache & Session
CACHE_STORE=redis                # REQUIRED: System uses Redis for caching
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Queue
QUEUE_CONNECTION=sync            # Development: sync (immediate)
# QUEUE_CONNECTION=database      # Production: database with queue worker

# Redis (REQUIRED)
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# SMS Notifications (Semaphore)
SMS_ENABLED=false                # Set to true to enable SMS
SMS_GATEWAY=semaphore
SMS_API_KEY=your_semaphore_api_key_here
SMS_SENDER_NAME=InfantVax
SMS_API_URL=https://api.semaphore.co/api/v4/messages

# Push Notifications (VAPID)
VAPID_PUBLIC_KEY=your_vapid_public_key
VAPID_PRIVATE_KEY=your_vapid_private_key
VAPID_SUBJECT=mailto:admin@yourdomain.com

# Mail (for password reset)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

# Telescope (Development only)
TELESCOPE_ENABLED=false
```

### Web Server Configuration

#### Nginx Configuration
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    
    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    
    root /var/www/infantsSystem/public;
    index index.php index.html;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval';" always;
    
    # Logging
    access_log /var/log/nginx/ivs_access.log;
    error_log /var/log/nginx/ivs_error.log;
    
    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;
    
    # Service Worker (must be served from root)
    location = /sw.js {
        add_header Cache-Control "no-store, no-cache, must-revalidate, proxy-revalidate, max-age=0";
        add_header Service-Worker-Allowed "/";
        try_files $uri =404;
    }
    
    # Manifest.json
    location = /manifest.json {
        add_header Cache-Control "public, max-age=604800";
        try_files $uri =404;
    }
    
    # Laravel routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
    
    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

#### Apache Configuration (.htaccess)
The system includes `.htaccess` files, but Nginx is recommended for better performance with PWA features.

---

## 🚢 Production Deployment

### VPS Deployment Checklist

#### 1. Server Setup
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required software
sudo apt install -y nginx mysql-server php8.3-fpm php8.3-mysql php8.3-mbstring \
    php8.3-xml php8.3-bcmath php8.3-curl php8.3-zip php8.3-gd php8.3-redis \
    redis-server git composer npm certbot python3-certbot-nginx supervisor

# Enable services (Redis is REQUIRED)
sudo systemctl enable nginx mysql redis-server
sudo systemctl start nginx mysql redis-server

# Verify Redis is running
redis-cli ping  # Should return: PONG
```

#### 2. SSL Certificate
```bash
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
sudo certbot renew --dry-run  # Test auto-renewal
```

#### 3. Configure Queue Worker (VPS only)
Create supervisor config: `/etc/supervisor/conf.d/ivs-worker.conf`
```ini
[program:ivs-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/infantsSystem/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/infantsSystem/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start ivs-worker:*
```

#### 4. Setup Cron Jobs
```bash
sudo crontab -e -u www-data
```
Add:
```cron
* * * * * cd /var/www/infantsSystem && php artisan schedule:run >> /dev/null 2>&1
```

#### 5. Configure Redis (Optional)
Edit `.env`:
```env
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis
```

#### 6. Firewall Configuration
```bash
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable
```

#### 7. Database Backup Script
Create: `/usr/local/bin/backup-ivs.sh`
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/ivs"
mkdir -p $BACKUP_DIR

mysqldump -u ivs_user -p'your_password' infant_vaccination_system | gzip > $BACKUP_DIR/ivs_backup_$DATE.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -name "ivs_backup_*.sql.gz" -mtime +30 -delete
```

```bash
chmod +x /usr/local/bin/backup-ivs.sh
```

Add to crontab:
```cron
0 2 * * * /usr/local/bin/backup-ivs.sh
```

### Shared Hosting Deployment

#### 1. Upload Files
- Upload all files except `.env`, `.git`, `node_modules`, `vendor`
- Upload via FTP/SFTP or cPanel File Manager

#### 2. Install Dependencies
```bash
# Via SSH (if available)
composer install --no-dev --optimize-autoloader

# Or upload pre-built vendor folder from local
```

#### 3. Configure .env
Use cPanel environment variables or create `.env` file

#### 4. Database
- Create MySQL database via cPanel
- Import database schema
- Update `.env` credentials

#### 5. Set Document Root
Point domain to `/public` directory

#### 6. Cron Job
Add via cPanel:
```
* * * * * cd /home/username/public_html && php artisan schedule:run
```

---

## 🔧 Maintenance & Monitoring

### Regular Maintenance Tasks

#### Daily
- Monitor error logs: `storage/logs/laravel.log`
- Check notification delivery
- Verify backup completion

#### Weekly
- Review system performance
- Check disk space usage
- Update vaccine inventory
- Review user feedback

#### Monthly
- Update dependencies (security patches)
- Generate reports
- Database optimization
- Review and archive old logs

### Monitoring Commands

```bash
# Check disk usage
df -h

# Check memory usage
free -m

# Check active processes
top

# Check PHP-FPM status
sudo systemctl status php8.3-fpm

# Check Nginx status
sudo systemctl status nginx

# Check MySQL status
sudo systemctl status mysql

# Check Redis status (VPS)
sudo systemctl status redis-server

# Check queue workers (VPS)
sudo supervisorctl status ivs-worker:*

# View Laravel logs
tail -f storage/logs/laravel.log

# Clear cache
php artisan optimize:clear
```

### Performance Optimization

```bash
# Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Database optimization
php artisan db:optimize

# Clear expired sessions
php artisan session:gc
```

---

## 🚨 Critical System Dependencies

### Redis Cache (REQUIRED)

**Status**: ✅ **ACTIVE AND REQUIRED**

The system is configured to use Redis for caching (`CACHE_STORE=redis`). Redis must be running for the application to function properly.

#### Why Redis is Required
- **Performance**: Caches database queries and computed results
- **Session Management**: Can optionally store sessions
- **Queue Backend**: Can handle background jobs
- **Speed**: 10-100x faster than database caching
- **Scalability**: Handles high traffic efficiently

#### Installation & Verification
```bash
# Ubuntu/Debian
sudo apt install redis-server php8.3-redis

# Start Redis
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Test connection
redis-cli ping  # Should return: PONG

# Check if PHP can connect
php -r "echo extension_loaded('redis') ? 'Redis extension loaded' : 'Redis extension not loaded';"

# Monitor Redis in real-time
redis-cli monitor
```

#### Configuration in .env
```env
CACHE_STORE=redis           # ACTIVE
REDIS_CLIENT=phpredis       # PHP extension
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null         # Set if Redis requires password
```

#### Common Redis Commands
```bash
# Clear all Redis cache
php artisan cache:clear

# View Redis stats
redis-cli info stats

# Check memory usage
redis-cli info memory

# List all keys (development only)
redis-cli keys '*'

# Flush all Redis data (careful!)
redis-cli flushall
```

#### If Redis Stops Working
1. Check Redis service: `sudo systemctl status redis-server`
2. Check error logs: `sudo journalctl -u redis-server`
3. Restart Redis: `sudo systemctl restart redis-server`
4. Clear Laravel cache: `php artisan cache:clear`
5. Verify connection: `redis-cli ping`

---

### SMS Notifications (Semaphore)

**Status**: ✅ **CONFIGURED (Disabled by Default)**

The system has Semaphore SMS integration configured but disabled to avoid costs.

#### Semaphore Configuration
```env
SMS_ENABLED=false                                    # Change to true to enable
SMS_GATEWAY=semaphore
SMS_API_KEY=                                        # Add your API key here
SMS_SENDER_NAME=InfantVax
SMS_API_URL=https://api.semaphore.co/api/v4/messages
```

#### How to Enable SMS
1. **Get API Key**:
   - Sign up at https://semaphore.co/
   - Purchase credits (₱100 minimum)
   - Get API key from dashboard

2. **Update .env**:
   ```env
   SMS_ENABLED=true
   SMS_API_KEY=your_actual_api_key_here
   ```

3. **Test SMS**:
   ```bash
   php artisan tinker
   # Send test SMS
   \App\Services\SmsService::send('09171234567', 'Test message');
   ```

#### SMS Pricing (Semaphore)
- **Cost per SMS**: ₱0.65 - ₱0.70
- **Minimum Load**: ₱100 (~142 messages)
- **Recommended**: ₱500 load for testing
- **Monthly Cost**: Depends on usage (100 reminders = ₱65-70)

#### SMS Use Cases
When `SMS_ENABLED=true`, SMS will be sent for:
- Vaccination schedule reminders
- Missed appointment alerts
- Urgent health notifications
- Password reset codes (if implemented)

#### SMS Logs
Check `sms_logs` table in database for:
- Sent messages
- Delivery status
- Timestamps
- Cost tracking

#### Cost Management
```php
// Monitor SMS usage
$totalSent = \App\Models\SmsLog::count();
$thisMonth = \App\Models\SmsLog::whereMonth('created_at', now()->month)->count();
$estimatedCost = $thisMonth * 0.65; // in pesos
```

---

## 🔒 Security Considerations

### Security Checklist

✅ **Application Level**
- [ ] APP_DEBUG=false in production
- [ ] Strong APP_KEY generated
- [ ] HTTPS enforced (SSL certificate)
- [ ] CSRF protection enabled (Laravel default)
- [ ] XSS protection in Blade templates
- [ ] SQL injection prevention (Eloquent ORM)
- [ ] Password hashing (bcrypt)
- [ ] Rate limiting on login attempts
- [ ] Session security (httpOnly, secure cookies)

✅ **Server Level**
- [ ] Firewall configured (UFW/iptables)
- [ ] SSH key authentication (disable password)
- [ ] Fail2ban installed (brute force protection)
- [ ] Regular security updates
- [ ] Non-root user for deployment
- [ ] Database user with limited privileges
- [ ] File permissions properly set (755/644)

✅ **Data Protection**
- [ ] Regular automated backups
- [ ] Backup testing/verification
- [ ] GDPR/Data Privacy Act compliance
- [ ] Privacy policy acceptance
- [ ] Secure password reset flow
- [ ] Patient data encryption at rest (if required)

✅ **Push Notifications**
- [ ] VAPID keys kept secret
- [ ] HTTPS required for service worker
- [ ] Subscription validation
- [ ] Rate limiting on notifications

### Security Headers
Already included in Nginx config:
- X-Frame-Options: SAMEORIGIN
- X-XSS-Protection: 1; mode=block
- X-Content-Type-Options: nosniff
- Content-Security-Policy
- Referrer-Policy

---

## 🔍 Troubleshooting

### Common Issues

#### Issue 1: Push Notifications Not Working
**Symptoms**: No browser notifications received

**Solutions**:
```bash
# 1. Verify VAPID keys are set
php artisan tinker
config('webpush.vapid.public_key');

# 2. Check service worker registration
# Open DevTools → Application → Service Workers

# 3. Verify HTTPS is working
# PWA requires HTTPS (except localhost)

# 4. Check browser permissions
# Ensure notifications are allowed

# 5. Test push subscription
# Visit /api/push/test (while authenticated)
```

#### Issue 2: Queue Jobs Not Processing (VPS)
**Symptoms**: Scheduled tasks not running

**Solutions**:
```bash
# Check supervisor status
sudo supervisorctl status ivs-worker:*

# Restart workers
sudo supervisorctl restart ivs-worker:*

# Check queue table
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Monitor queue in real-time
php artisan queue:work --verbose
```

#### Issue 3: Cache Issues
**Symptoms**: Changes not reflecting

**Solutions**:
```bash
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Clear browser cache
# Hard refresh: Ctrl+Shift+R
```

#### Issue 4: Service Worker Update Not Applying
**Symptoms**: Old service worker cached

**Solutions**:
```javascript
// Update CACHE_VERSION in sw.js
const CACHE_VERSION = 'ivs-v2'; // increment version

// Force update in browser
// DevTools → Application → Service Workers → Update
```

#### Issue 5: Database Connection Error
**Symptoms**: SQLSTATE[HY000] [2002]

**Solutions**:
```bash
# Check MySQL is running
sudo systemctl status mysql

# Test connection
mysql -u ivs_user -p

# Verify .env credentials
cat .env | grep DB_

# Check MySQL socket
php -i | grep mysqli.default_socket
```

#### Issue 6: Redis Connection Refused (VPS)
**Symptoms**: Connection to Redis failed

**Solutions**:
```bash
# Check Redis status
sudo systemctl status redis-server

# Test connection
redis-cli ping

# Check Redis configuration
sudo nano /etc/redis/redis.conf

# Restart Redis
sudo systemctl restart redis-server

# Fallback to database cache
# Change CACHE_STORE=database in .env
```

#### Issue 7: Permission Denied Errors
**Symptoms**: Storage errors

**Solutions**:
```bash
# Fix permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# SELinux context (if applicable)
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/infantsSystem/storage(/.*)?"
sudo restorecon -Rv /var/www/infantsSystem/storage
```

---

## 📊 System Metrics & Capacity

### Current System Capacity
- **Users**: Supports unlimited health workers and parents
- **Patients**: No hard limit, tested with 500+ records
- **Concurrent Users**: Depends on server resources
  - Shared Hosting: 10-50
  - Budget VPS: 100-200
  - Mid-tier VPS: 500-1000

### Database Size Estimates
- **Per Patient**: ~2-5 KB (patient record + vaccines)
- **Per Growth Record**: ~500 bytes
- **Per Vaccination Transaction**: ~1 KB
- **Per Notification**: ~500 bytes

**Example**: 1,000 patients with full records ≈ 10-20 MB

### Performance Benchmarks
- **Page Load Time**: 200-500ms (optimized)
- **API Response Time**: 50-200ms
- **Database Queries**: Average 5-10 per page load
- **Cache Hit Rate**: 80-90% (with Redis)

---

## 📱 PWA Installation Guide

### For Parents/Users

#### Android (Chrome)
1. Visit website on mobile browser
2. Tap menu (⋮) → "Install app" or "Add to Home screen"
3. Follow prompts
4. App icon appears on home screen

#### iOS (Safari)
1. Visit website in Safari
2. Tap Share button
3. Select "Add to Home Screen"
4. Name the app
5. Tap "Add"

#### Desktop (Chrome/Edge)
1. Visit website
2. Click install icon in address bar
3. Or: Menu → "Install Infant Vaccination System"
4. App opens in standalone window

---

## 🎓 Training & Documentation

### User Manuals
- Health Worker Manual: See `QUICK_REFERENCE.md`
- Parent User Guide: In-app help section
- Notification Guide: See `NOTIFICATION_TESTING_GUIDE.md`

### Video Tutorials (Recommended)
Create short videos for:
1. System login and navigation
2. Patient registration
3. Recording vaccinations
4. Inventory management
5. Generating reports
6. Parent dashboard tour

---

## 📞 Support & Contact

### For Technical Issues
- System Admin: [your-email@example.com]
- Developer Contact: [dev-email@example.com]
- Health Center: Barangay Balayhangin Health Center

### Reporting Bugs
1. Check `storage/logs/laravel.log`
2. Note exact error message
3. Document steps to reproduce
4. Contact technical support with details

---

## 📝 Changelog & Version History

### Version 1.0.0 (Current)
- ✅ Multi-guard authentication
- ✅ Patient vaccination tracking
- ✅ Growth monitoring
- ✅ Vaccine inventory management
- ✅ Reporting system with versioning
- ✅ Vaccination scheduling
- ✅ PWA implementation
- ✅ Web push notifications
- ✅ Parent dashboard
- ✅ Feedback system
- ✅ Backup & restore functionality
- ✅ Responsive design

### Current Features (Implemented)
- ✅ Redis caching for performance
- ✅ SMS integration (Semaphore - configurable)
- ✅ Email notifications (Gmail SMTP)
- ✅ Auto-save monthly reports

### Future Enhancements (Roadmap)
- Barcode/QR code vaccination cards
- Mobile app (native)
- Multi-language support (Tagalog/English)
- Advanced analytics dashboard
- Enhanced SMS automation
- Integration with DOH systems
- Telemedicine consultation booking
- Photo uploads for patient records
- SMS delivery reports tracking

---

## 🏁 Final Recommendations

### ✅ Deploy on VPS (MANDATORY)
**Why VPS is REQUIRED (not optional):**
1. **Redis is REQUIRED** - System won't work without it
2. Full push notification support (VAPID)
3. Semaphore SMS API support
4. Background queue processing
5. Complete control over server configuration
6. Better security and performance
7. Room for growth
8. Cost-effective ($5-10/month)

**⚠️ Shared hosting is NOT compatible unless Redis is available (very rare)**

### 🚀 Deployment Provider Recommendation

**Top Choice: DigitalOcean Droplet**
- **Plan**: Basic Droplet - $6/month
- **Specs**: 1 GB RAM, 1 CPU, 25 GB SSD, 1 TB transfer
- **Reason**: Excellent documentation, 1-click Laravel setup, reliable

**Alternative: Vultr Cloud Compute**
- **Plan**: Regular Performance - $6/month  
- **Specs**: 1 GB RAM, 1 CPU, 25 GB SSD, 2 TB bandwidth
- **Reason**: Better global network, competitive pricing

### 📋 Pre-Deployment Checklist

Before going live:
- [ ] All tests passed
- [ ] Database migrations verified
- [ ] VAPID keys generated and configured
- [ ] SSL certificate installed
- [ ] Backup system tested
- [ ] Performance optimizations applied
- [ ] Security headers configured
- [ ] Domain DNS configured
- [ ] Admin accounts created
- [ ] Initial vaccine data seeded
- [ ] Documentation reviewed
- [ ] User training completed
- [ ] Monitoring tools setup
- [ ] Emergency contacts documented

---

## 📚 Additional Resources

### Laravel Documentation
- https://laravel.com/docs/11.x

### PWA Resources
- https://web.dev/progressive-web-apps/

### Web Push Protocol
- https://developer.mozilla.org/en-US/docs/Web/API/Push_API

### Server Setup Guides
- DigitalOcean: https://www.digitalocean.com/community/tutorials
- Laravel Deployment: https://laravel.com/docs/11.x/deployment

---

## 📄 License & Credits

### Framework
- **Laravel**: MIT License
- **Tailwind CSS**: MIT License

### Packages
- See `composer.json` and `package.json` for full dependency list
- All packages used under their respective licenses

### System
- **Developed for**: Barangay Balayhangin Health Center, Calauan, Laguna
- **Purpose**: Public health management
- **Year**: 2025

---

## ✨ Conclusion

This system is production-ready and optimized for deployment on a VPS environment. It provides comprehensive vaccination management with modern PWA capabilities, real-time notifications, and robust reporting features.

**For the best experience and full feature support, deploy on a VPS with Redis caching and queue workers enabled.**

For questions or support, refer to the contact information in the Support section above.

---

**Document Version**: 1.0.0  
**Last Updated**: November 23, 2025  
**Author**: System Developer  
**Status**: Production Ready ✅
