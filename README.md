# WorkAround Setup Guide

-   Project Setup
-   Supervisor (for queue management)
-   Laravel Passport (for API authentication)
-   SMTP (for email setup)
-   Stripe (for payment processing)

---

## Project Setup

### 1. Clone the Repository

```sh
  git clone https://github.com/your-repo.git
  cd your-repo
  chmod -R 777 .
```

### 2. Install Dependencies

```sh
  composer install
```

### 3. Copy Environment File

```sh
  cp .env.example .env
```

### 4. Generate Application Key

```sh
  php artisan key:generate
```

### 5. Configure Database in `.env`

```sh
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=your_database_name
  DB_USERNAME=your_db_user
  DB_PASSWORD=your_db_password
```

### 6. SMTP Email Setup `.env`

```sh
  MAIL_MAILER=smtp
  MAIL_HOST=smtp.your-email-provider.com
  MAIL_PORT=587
  MAIL_USERNAME=your-email@example.com
  MAIL_PASSWORD=your-email-password
  MAIL_ENCRYPTION=tls
  MAIL_FROM_ADDRESS=your-email@example.com
  MAIL_FROM_NAME="Your App Name"
```

### 7. STRIPE Configure `.env`

```sh
  STRIPE_KEY=your-stripe-public-key
  STRIPE_SECRET=your-stripe-secret-key
```

### 8. Run Migrations & Seeders & Install Passport Keys & Cache clear

```sh
  php artisan migrate --seed
  php artisan storage:link
  php artisan passport:install
  php artisan passport:keys
  php artisan passport:client --personal
  php artisan optimize
```

### 9. Serve the Application

```sh
  php artisan serve
```

---

## Queue Setup with Supervisor

Supervisor is used to manage Laravel queues and ensure they run continuously.

### 1. Install Supervisor (Linux)

```sh
  sudo apt update
  sudo apt install supervisor
```

### 2. Create Supervisor Configuration File

Run the following command:

```sh
  sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

Add the following content:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-your-project/artisan queue:work --tries=3
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stderr_logfile=/var/log/laravel-worker.err.log
stdout_logfile=/var/log/laravel-worker.out.log
```

Replace `/path-to-your-project/` with your actual Laravel project path.

### 3. Reload Supervisor & Start Worker

```sh
  sudo supervisorctl reread
  sudo supervisorctl update
  sudo supervisorctl start laravel-worker:*
```

To check status:

```sh
  sudo supervisorctl status
```
