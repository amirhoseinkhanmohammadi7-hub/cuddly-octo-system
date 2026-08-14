# Sleep Quality Assessment Application - Installation Guide

## Project Name: sleep 2

A comprehensive Laravel-based sleep quality assessment application with API-driven architecture.

---

## Prerequisites

Before installing this application, ensure you have the following installed:

- **PHP** >= 8.2
- **Composer** (Latest version)
- **SQLite** or **MySQL** database
- **Git** (optional, for version control)

---

## Installation Steps

### 1. Navigate to the Project Directory

```bash
cd /workspace/sleep
```

### 2. Install PHP Dependencies

If not already installed during project creation:

```bash
composer install
```

### 3. Environment Configuration

#### Copy the Environment File

```bash
cp .env.example .env
```

#### Generate Application Key

This is a critical security step. Run:

```bash
php artisan key:generate
```

You should see output like:
```
INFO  Application key set successfully.
```

### 4. Database Setup

#### Create SQLite Database (Default)

```bash
touch database/database.sqlite
```

#### Or Configure MySQL

Edit `.env` file and update these lines:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sleep_app
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run Database Migrations

Execute all database migrations to create necessary tables:

```bash
php artisan migrate
```

This will create:
- `users` table
- `personal_access_tokens` table (for API authentication)
- `sleep_entries` table (for sleep data)
- `cache` and `jobs` tables

### 6. Seed the Database (Optional)

To populate the database with sample data for testing:

```bash
php artisan db:seed --class=SleepEntrySeeder
```

This creates:
- A test user (email: test@example.com, password: password)
- 30 sample sleep entries

---

## Running the Application

### Start the Development Server

```bash
php artisan serve
```

The application will be available at: `http://localhost:8000`

### Access the API Endpoints

All API routes are prefixed with `/api`:

- **Base URL**: `http://localhost:8000/api`

---

## API Documentation

### Authentication Endpoints

#### Register New User
```bash
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "securepassword123",
    "password_confirmation": "securepassword123"
}
```

#### Login
```bash
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "securepassword123"
}
```

Response includes an access token for authenticated requests.

#### Logout
```bash
POST /api/logout
Authorization: Bearer {token}
```

#### Get Current User
```bash
GET /api/user
Authorization: Bearer {token}
```

---

### Sleep Entry Endpoints

All endpoints below require authentication via Bearer token.

#### List Sleep Entries
```bash
GET /api/sleep-entries
Authorization: Bearer {token}

# Optional query parameters:
# ?start_date=2024-01-01&end_date=2024-12-31
```

#### Create Sleep Entry
```bash
POST /api/sleep-entries
Authorization: Bearer {token}
Content-Type: application/json

{
    "sleep_date": "2024-01-15",
    "bedtime": "22:30",
    "wake_time": "06:30",
    "sleep_duration_minutes": 480,
    "time_to_fall_asleep_minutes": 15,
    "night_awakenings": 1,
    "sleep_quality_score": 8,
    "notes": "Felt well rested",
    "factors": ["exercise", "no_caffeine"]
}
```

#### Get Single Sleep Entry
```bash
GET /api/sleep-entries/{id}
Authorization: Bearer {token}
```

#### Update Sleep Entry
```bash
PUT /api/sleep-entries/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "sleep_quality_score": 9,
    "notes": "Updated notes"
}
```

#### Delete Sleep Entry
```bash
DELETE /api/sleep-entries/{id}
Authorization: Bearer {token}
```

#### Get Sleep Statistics
```bash
GET /api/sleep-statistics
Authorization: Bearer {token}

# Optional query parameters:
# ?start_date=2024-01-01&end_date=2024-12-31
```

Response includes:
- Average sleep duration
- Average quality score
- Total entries
- Sleep efficiency percentage
- Trend (improving/declining/stable)

---

## Testing with cURL

### Example: Register and Login

```bash
# Register
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password",
    "password_confirmation": "password"
  }'

# Login (save the token from response)
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password"
  }'

# Use token to access protected routes
curl -X GET http://localhost:8000/api/sleep-entries \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## Features

### Core Features
- **User Authentication**: Secure registration and login with Laravel Sanctum
- **Sleep Tracking**: Record bedtime, wake time, and sleep duration
- **Quality Scoring**: Rate sleep quality on a 1-10 scale
- **Factors Tracking**: Log factors affecting sleep (caffeine, exercise, stress, etc.)
- **Sleep Statistics**: View average sleep metrics and trends
- **Sleep Efficiency Calculation**: Automatic calculation of sleep efficiency percentage

### Technical Features
- RESTful API architecture
- Token-based authentication
- Data validation and sanitization
- Pagination for large datasets
- Date range filtering
- SQLite database (easy setup, no configuration needed)
- Factory and seeder for testing

---

## Troubleshooting

### Common Issues

#### 1. Application Key Missing
```bash
php artisan key:generate
```

#### 2. Database Migration Errors
```bash
# Reset and run migrations
php artisan migrate:fresh
php artisan db:seed --class=SleepEntrySeeder
```

#### 3. Permission Issues
```bash
# Ensure storage directory is writable
chmod -R 775 storage bootstrap/cache
```

#### 4. Composer Dependencies
```bash
# Clear composer cache and reinstall
composer clear-cache
composer install
```

---

## Security Notes

1. **Never commit `.env` file** to version control
2. **Change default passwords** in production
3. **Use HTTPS** in production environments
4. **Regularly update dependencies** (`composer update`)
5. **Set appropriate file permissions** on production servers

---

## Additional Commands

```bash
# Clear application cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# List all routes
php artisan route:list

# Run tests
php artisan test

# Enter Tinker (interactive shell)
php artisan tinker
```

---

## Support

For issues or questions:
1. Check Laravel documentation: https://laravel.com/docs
2. Review API endpoint definitions in `routes/api.php`
3. Examine controller logic in `app/Http/Controllers/`

---

## License

This project is open-sourced software licensed under the MIT license.
