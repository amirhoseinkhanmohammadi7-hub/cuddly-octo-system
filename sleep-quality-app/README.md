# Sleep Quality Assessment Application

A comprehensive Laravel-based sleep quality assessment application that helps users track, analyze, and improve their sleep patterns.

## Features

### 1. Sleep Entry Tracking
- Record bedtime and wake time
- Track sleep duration automatically
- Log time to fall asleep (sleep latency)
- Record night awakenings
- Add personal sleep notes

### 2. Comprehensive Sleep Assessment (PSQI-Based)
- **Pittsburgh Sleep Quality Index (PSQI)** components:
  - Subjective sleep quality (0-3 scale)
  - Sleep latency score (0-3 scale)
  - Sleep duration score (0-3 scale)
  - Sleep efficiency score (0-3 scale)
  - Sleep disturbances score (0-3 scale)
  - Sleep medication score (0-3 scale)
  - Daytime dysfunction score (0-3 scale)
- Total PSQI score calculation (0-21 scale)
- Automatic sleep quality categorization (Good/Poor Sleeper)

### 3. Additional Health Metrics
- Overall sleep rating (1-10 scale)
- Energy levels (morning/evening)
- Sleep aid usage tracking
- Caffeine consumption tracking
- Alcohol consumption tracking
- Exercise minutes
- Stress level monitoring

### 4. Dashboard & Analytics
- Sleep statistics (average, min, max duration)
- PSQI score trends
- Weekly sleep pattern visualization
- Sleep quality distribution
- Recent entries overview
- Customizable date range filtering

### 5. Personalized Recommendations
- AI-powered sleep improvement suggestions
- Based on PSQI component scores
- Lifestyle factor analysis
- Actionable insights

## Technical Stack

- **Framework**: Laravel 12.x
- **Database**: SQLite (default), supports MySQL/PostgreSQL
- **Authentication**: Laravel built-in auth (ready for Sanctum/API)
- **API**: RESTful JSON API

## Installation

```bash
cd sleep-quality-app

# Install dependencies (already done)
composer install

# Run migrations (already done)
php artisan migrate:fresh --seed

# Start the development server
php artisan serve
```

## API Endpoints

### Authentication Required
All endpoints below require authentication.

### Dashboard
```
GET /dashboard?days=30
```
Returns comprehensive dashboard statistics including:
- Sleep statistics
- Assessment statistics
- Recent entries
- Weekly trend data
- Quality distribution

### Sleep Entries
```
GET    /sleep-entries                    # List all entries
POST   /sleep-entries                    # Create new entry
GET    /sleep-entries/{id}               # Get specific entry
PUT    /sleep-entries/{id}               # Update entry
DELETE /sleep-entries/{id}               # Delete entry
```

**Request Body for POST/PUT:**
```json
{
  "sleep_date": "2024-01-15",
  "bedtime": "23:00",
  "wake_time": "07:00",
  "sleep_duration_minutes": 480,
  "time_to_fall_asleep_minutes": 15,
  "night_awakenings": 1,
  "sleep_notes": "Felt rested"
}
```

### Sleep Assessments
```
GET    /sleep-assessments                # List all assessments
POST   /sleep-assessments                # Create new assessment
GET    /sleep-assessments/{id}           # Get specific assessment
PUT    /sleep-assessments/{id}           # Update assessment
DELETE /sleep-assessments/{id}           # Delete assessment
```

**Request Body for POST/PUT:**
```json
{
  "assessment_date": "2024-01-16",
  "subjective_sleep_quality": 1,
  "sleep_latency_score": 1,
  "sleep_duration_score": 0,
  "sleep_efficiency_score": 1,
  "sleep_disturbances_score": 0,
  "sleep_medication_score": 0,
  "daytime_dysfunction_score": 1,
  "overall_sleep_rating": 7,
  "energy_level_morning": 8,
  "energy_level_evening": 6,
  "used_sleep_aids": false,
  "consumed_caffeine": true,
  "consumed_alcohol": false,
  "exercise_minutes": 45,
  "stress_level": 4,
  "additional_notes": "Good night overall"
}
```

## Database Schema

### Users Table
- Standard Laravel user fields

### Sleep Entries Table
- id
- user_id (foreign key)
- sleep_date
- bedtime
- wake_time
- sleep_duration_minutes
- time_to_fall_asleep_minutes
- night_awakenings
- sleep_notes

### Sleep Assessments Table
- id
- user_id (foreign key)
- sleep_entry_id (foreign key, nullable)
- assessment_date
- PSQI component scores (7 fields)
- total_psqi_score
- sleep_quality_category
- Additional metrics (rating, energy, lifestyle factors)
- additional_notes

## Models

### SleepEntry
- Relationships: belongsTo User, hasOne Assessment
- Accessor: sleep_quality_label
- Method: calculateSleepDuration()

### SleepAssessment
- Relationships: belongsTo User, belongsTo SleepEntry
- Accessors: sleep_quality_category, analysis
- Method: calculateTotalPsqiScore(), getRecommendations()

## PSQI Scoring

The Pittsburgh Sleep Quality Index is a validated instrument for measuring sleep quality:
- **Score Range**: 0-21
- **Good Sleeper**: ≤ 5
- **Poor Sleeper**: > 5

Each component is scored 0-3, with higher scores indicating worse sleep quality.

## Security

- CSRF protection enabled
- Input validation on all endpoints
- Authorization checks (users can only access their own data)
- SQL injection prevention via Eloquent ORM

## Testing

```bash
php artisan test
```

## License

MIT License

## Support

For issues and feature requests, please create an issue in the repository.
