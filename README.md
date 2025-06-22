# Laravel Application Setup

This guide will help you set up and run the Laravel application locally.

## Prerequisites

- PHP >= 8.1
- Composer
- MySQL

## Installation Steps

### 1. Install Composer Dependencies

Install the required PHP packages using Composer:

```bash
composer install
```

### 2. Environment Configuration

Copy the example environment file and configure your settings:

```bash
cp .env.example .env
```

Edit the `.env` file to configure:
- Database connection details
- Mail server settings
- Other application-specific variables

### 3. Generate Application Key

Generate the Laravel application encryption key:

```bash
php artisan key:generate
```

### 4. Create Database

Create the database for your application:

```bash
php artisan db:create
```

### 5. Run Database Migrations

Set up the database tables by running migrations:

```bash
php artisan migrate
```

### 6. Seed the Database

Populate the database with initial data:

```bash
php artisan db:seed
```

### 7. Start the Development Server

Launch the Laravel development server:

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

### 8. Run Background Queue Worker

For processing queued jobs, start the queue worker in a separate terminal:

```bash
php artisan queue:work
```

### Additional Configuration(Optional)

### Cache Configuration

For better performance in production, cache your configuration:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Troubleshooting

- Ensure all required PHP extensions are installed
- Check database connection settings in `.env`
- Verify file permissions for `storage` and `bootstrap/cache` directories
- Make sure the database exists before running migrations