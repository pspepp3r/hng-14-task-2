# Backend Wizards - Stage 1: Multi-API Integration API

A production-ready RESTful API built with PHP using SOLID principles and modern OOP practices. This API integrates with three external services (Genderize, Agify, Nationalize) to classify and persist profile data.

## Features

- **Multi-API Integration**: Seamlessly integrates with Genderize, Agify, and Nationalize APIs
- **Data Persistence**: Stores classified profiles in MySQL database
- **Idempotency**: Prevents duplicate entries - same name returns existing profile
- **Advanced Filtering**: Filter profiles by gender, country, or age group
- **CORS Support**: Full CORS headers for cross-origin requests
- **UUID v7**: Modern UUID generation for profile IDs
- **RFC 3339 Timestamps**: All timestamps in UTC ISO 8601 format
- **SOLID Principles**: Clean, maintainable, and testable code
- **Comprehensive Error Handling**: Proper HTTP status codes and error messages

## Architecture

```example
src/
├── Bootstrap.php                 # Environment configuration
├── Database/
│   ├── Connection.php            # PDO singleton (Singleton pattern)
│   └── Migration.php             # Database schema management
├── Models/
│   └── Profile.php               # Profile entity
├── Repositories/
│   ├── ProfileRepositoryInterface.php  # Repository interface (Dependency Inversion)
│   └── ProfileRepository.php           # Data access implementation
├── External/
│   ├── ExternalApiClientInterface.php  # External API interface
│   ├── HttpClient.php                  # HTTP utility
│   └── Clients/
│       ├── GenderizeClient.php         # Genderize API adapter
│       ├── AgifyClient.php             # Agify API adapter
│       └── NationalizeClient.php       # Nationalize API adapter
├── Services/
│   ├── AgeGroupClassifier.php    # Age classification logic
│   └── ProfileService.php        # Business logic orchestration
├── Http/
│   ├── Response.php              # Response formatting
│   ├── Router.php                # URL routing
│   ├── Controllers/
│   │   └── ProfileController.php # Request handling
│   └── Middleware/
│       └── CorsMiddleware.php    # CORS headers
└── public/
    └── index.php                 # Application entry point
```

## Installation & Setup

### Prerequisites

- PHP 8.1+
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Git

### Steps

- **Clone the repository**

```bash
git clone <repository-url>
cd hng-14-task-1
```

- **Install dependencies**

```bash
composer install
```

- **Set up environment variables**

```bash
cp .env.example .env
# Edit .env with your database credentials
```

- **Create database**

```bash
mysql -u root -p -e "CREATE DATABASE profiles_db;"
```

- **Run migrations**

```bash
composer migrate
```

- **Start development server**

```bash
composer run start
```

The API will be available at `http://localhost:8000/api`

## API Documentation

### 1. Create Profile

**Endpoint:** `POST /api/profiles`

**Request Body:**

```json
{
 "name": "ella"
}
```

**Success Response (201 Created):**

```json
{
 "status": "success",
 "data": [
  {
   "id": "b3f9c1e2-7d4a-4c91-9c2a-1f0a8e5b6d12",
   "name": "ella",
   "gender": "female",
   "gender_probability": 0.99,
   "sample_size": 1234,
   "age": 46,
   "age_group": "adult",
   "country_id": "DRC",
   "country_probability": 0.85,
   "created_at": "2026-04-01T12:00:00Z"
  }
 ]
}
```

**Duplicate Response (200):**

```json
{
  "status": "success",
  "message": "Profile already exists",
  "data": [
    {
      "id": "...",
      "name": "ella",
      ...
    }
  ]
}
```

**Error Responses:**

- `400 Bad Request`: Missing or empty name
- `422 Unprocessable Entity`: Invalid type
- `502 Bad Gateway`: External API failed

### 2. Get Single Profile

**Endpoint:** `GET /api/profiles/{id}`

**Success Response (200):**

```json
{
 "status": "success",
 "data": [
  {
   "id": "b3f9c1e2-7d4a-4c91-9c2a-1f0a8e5b6d12",
   "name": "emmanuel",
   "gender": "male",
   "gender_probability": 0.99,
   "sample_size": 1234,
   "age": 25,
   "age_group": "adult",
   "country_id": "NG",
   "country_probability": 0.85,
   "created_at": "2026-04-01T12:00:00Z"
  }
 ]
}
```

**Error Response (404):**

```json
{
 "status": "error",
 "message": "Profile not found"
}
```

### 3. Get All Profiles

**Endpoint:** `GET /api/profiles`

**Query Parameters (all optional, case-insensitive):**

- `gender`: Filter by gender (male, female)
- `country_id`: Filter by country ISO code
- `age_group`: Filter by age group (child, teenager, adult, senior)

**Example:** `/api/profiles?gender=male&country_id=NG&age_group=adult`

**Success Response (200):**

```json
{
 "status": "success",
 "count": 2,
 "data": [
  {
   "id": "id-1",
   "name": "emmanuel",
   "gender": "male",
   "age": 25,
   "age_group": "adult",
   "country_id": "NG"
  },
  {
   "id": "id-2",
   "name": "sarah",
   "gender": "female",
   "age": 28,
   "age_group": "adult",
   "country_id": "US"
  }
 ]
}
```

### 4. Delete Profile

**Endpoint:** `DELETE /api/profiles/{id}`

**Success Response (204 No Content)**:

- Empty response body

**Error Response (404):**

```json
{
 "status": "error",
 "message": "Profile not found"
}
```
