# RepDoc Network

An operating system for Computer Shops, Digital Service Centers, Document Professionals, and Remote Operators in Bangladesh.

## Requirements

- PHP 8.1 or higher
- MySQL 8.0 or higher
- Composer
- Node.js 18+ (for Tailwind CSS)
- Apache with mod_rewrite (or Nginx)
- Required PHP extensions: `pdo_mysql`, `mbstring`, `json`, `gd`, `fileinfo`, `xml`

## Installation

### 1. Clone and Setup

```bash
git clone <repository-url> repdoc
cd repdoc
```

### 2. Environment Configuration

```bash
cp .env.example .env
```

Edit `.env` with your database credentials and app settings:

```env
APP_NAME=RepDocNetwork
APP_URL=http://localhost:8000
APP_ENV=development
APP_DEBUG=true

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=repdoc
DB_USER=root
DB_PASS=your_password
```

### 3. Install Dependencies

```bash
composer install
npm install
```

### 4. Build Assets

```bash
npm run build
```

### 5. Database Setup

Create the database:

```bash
mysql -u root -p -e "CREATE DATABASE repdoc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Run migrations:

```bash
php migrations/migrate.php
```

To run migrations with seed data:

```bash
php migrations/migrate.php --seed
```

To reset and re-run all migrations:

```bash
php migrations/migrate.php --fresh
```

### 6. Development Server

Use the PHP built-in server for local development:

```bash
php -S localhost:8000 -t /path/to/repdoc
```

Or use the automation script:

```bash
python3 /root/repdoc_start.py
```

Access the app at **http://localhost:8000**

#### Production — Apache

Ensure `.htaccess` is enabled in your Apache configuration:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/repdoc

    <Directory /path/to/repdoc>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Production — Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/repdoc;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }
}
```

### 7. Development

Watch Tailwind CSS changes:

```bash
npm run watch
```

## Directory Structure

```
/
├── index.php                 # Front controller (entry point)
├── .htaccess                 # URL rewriting
├── config/                   # Application configuration
├── core/                     # Framework kernel (MVC)
│   ├── Router.php            # Request router
│   ├── Controller.php        # Base controller
│   ├── Model.php             # Base model with PDO + tenant scoping
│   ├── View.php              # Template renderer
│   ├── Middleware.php        # Auth, CSRF, permission middleware
│   ├── Database.php          # PDO singleton
│   ├── Session.php           # Session handler
│   ├── AuthContext.php       # Current user/tenant context
│   ├── TenantManager.php     # Multi-tenant resolution
│   ├── CSRF.php              # CSRF protection
│   ├── Security.php          # Password/input/file security
│   └── Validator.php         # Input validation
├── controllers/              # Request handlers
├── models/                   # Data layer
├── views/                    # PHP templates
│   ├── layouts/              # Layout templates
│   ├── components/           # Reusable partials
│   └── ...
├── assets/                   # Frontend assets
│   ├── css/                  # Tailwind CSS source
│   └── js/                   # JavaScript modules
├── storage/                  # File storage
│   ├── cache/
│   └── logs/
├── migrations/               # Database migration SQL files
├── seeds/                    # Sample data SQL files
├── cron/                     # Cron job scripts
└── vendor/                   # Composer dependencies
```

## Architecture

### MVC Pattern

- **Controllers** handle HTTP requests, validate input, call models, render views
- **Models** handle database queries with built-in tenant isolation
- **Views** are plain PHP templates with extracted variables

### Multi-Tenancy

- Each `shop` is a tenant
- All queries are scoped by `shop_id` via the base `Model` class
- Users can belong to multiple shops with different roles
- Tenant resolution via subdomain or session

### Security

- Passwords hashed with bcrypt (cost 12)
- CSRF protection on all state-changing requests
- PDO prepared statements prevent SQL injection
- Output escaped with `htmlspecialchars()`
- Tenant isolation prevents cross-shop data access
- Immutable audit logs for all sensitive operations

## Commands

```bash
# Run migrations
php migrations/migrate.php

# Run migrations with seed data
php migrations/migrate.php --seed

# Reset database
php migrations/migrate.php --fresh

# Rollback last migration batch
php migrations/migrate.php --rollback

# Build frontend assets
npm run build

# Watch for CSS changes
npm run watch
```

## License

Proprietary — All rights reserved.
