# HNG Country & Currency Exchange API

A RESTful API that fetches country data from external APIs, stores it in MySQL, and provides CRUD operations with exchange rates and estimated GDP calculations.

## 🎯 Features

- Fetch country data from [restcountries.com](https://restcountries.com)
- Get exchange rates from [open.er-api.com](https://open.er-api.com)
- Calculate estimated GDP with random multiplier
- CRUD operations for countries
- Filtering and sorting by region, currency, GDP, population
- Generate summary images with top 5 countries by GDP
- Full Docker support
- Comprehensive test coverage

## 📋 Requirements

- PHP 8.3+
- Composer
- MySQL 8.0+
- Docker & Docker Compose (for containerized deployment)

## 🚀 Quick Start with Docker

### 1. Clone the Repository

```bash
git clone <repository-url>
cd hng-country-api
```

### 2. Start Docker Containers

```bash
docker-compose up -d --build
```

### 3. Run Migrations

```bash
docker exec -it hng_country_app php artisan migrate
```

### 4. Generate Application Key

```bash
docker exec -it hng_country_app php artisan key:generate
```

The API will be available at `http://localhost:8000`

## 🔧 Local Development Setup

### 1. Install Dependencies

```bash
composer install
```

### 2. Environment Configuration

Copy `.env.example` to `.env` (already done):

```bash
php artisan key:generate
```

Configure your database settings in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hng
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Start Development Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

## 📡 API Endpoints

### 1. Refresh Countries Data

Fetch fresh data from external APIs and update/insert countries in database.

**Endpoint:** `POST /api/countries/refresh`

**Response:**
```json
{
  "total_countries": 250,
  "last_refreshed_at": "2025-01-25T12:00:00Z"
}
```

**Example:**
```bash
curl -X POST http://localhost:8000/api/countries/refresh
```

### 2. Get All Countries

Retrieve all countries with optional filters and sorting.

**Endpoint:** `GET /api/countries`

**Query Parameters:**
- `region` - Filter by region (e.g., Africa, Europe)
- `currency` - Filter by currency code (e.g., NGN, USD)
- `sort` - Sort by `gdp_desc`, `gdp_asc`, `population_desc`, `population_asc`

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Nigeria",
      "capital": "Abuja",
      "region": "Africa",
      "population": 206139589,
      "currency_code": "NGN",
      "exchange_rate": 1600.23,
      "estimated_gdp": 25767448125.2,
      "flag_url": "https://flagcdn.com/ng.svg",
      "last_refreshed_at": "2025-01-25T12:00:00Z"
    }
  ],
  "count": 2,
  "filters_applied": {
    "region": "Africa",
    "currency": "NGN",
    "sort": "gdp_desc"
  }
}
```

**Examples:**
```bash
# Get all countries
curl http://localhost:8000/api/countries

# Filter by region
curl "http://localhost:8000/api/countries?region=Africa"

# Filter by currency
curl "http://localhost:8000/api/countries?currency=NGN"

# Sort by GDP descending
curl "http://localhost:8000/api/countries?sort=gdp_desc"

# Combined filters
curl "http://localhost:8000/api/countries?region=Africa&currency=NGN&sort=gdp_desc"
```

### 3. Get Country by Name

**Endpoint:** `GET /api/countries/{name}`

**Response:**
```json
{
  "id": 1,
  "name": "Nigeria",
  "capital": "Abuja",
  "region": "Africa",
  "population": 206139589,
  "currency_code": "NGN",
  "exchange_rate": 1600.23,
  "estimated_gdp": 25767448125.2,
  "flag_url": "https://flagcdn.com/ng.svg",
  "last_refreshed_at": "2025-01-25T12:00:00Z"
}
```

**Example:**
```bash
curl http://localhost:8000/api/countries/Nigeria
```

### 4. Delete Country

**Endpoint:** `DELETE /api/countries/{name}`

**Response:** `204 No Content`

**Example:**
```bash
curl -X DELETE http://localhost:8000/api/countries/Nigeria
```

### 5. Get Status

**Endpoint:** `GET /api/status`

**Response:**
```json
{
  "total_countries": 250,
  "last_refreshed_at": "2025-01-25T12:00:00Z"
}
```

**Example:**
```bash
curl http://localhost:8000/api/status
```

### 6. Get Summary Image

**Endpoint:** `GET /api/countries/image`

**Response:** PNG image with summary statistics

**Example:**
```bash
curl http://localhost:8000/api/countries/image -o summary.png
```

## 🧪 Testing

Run the test suite:

```bash
php artisan test
```

Or with Docker:

```bash
docker exec -it hng_country_app php artisan test
```

## 📁 Project Structure

```
hng-country-api/
├── app/
│   ├── Http/Controllers/CountryController.php
│   ├── Models/Country.php
│   └── Services/CountryService.php
├── database/
│   ├── factories/CountryFactory.php
│   └── migrations/2025_01_01_000000_create_countries_table.php
├── routes/
│   └── api.php
├── tests/
│   └── Feature/CountryApiTest.php
├── Dockerfile
├── docker-compose.yml
└── nginx/conf.d/default.conf
```

## 🎨 How It Works

### Refresh Process

1. Fetches countries from `restcountries.com`
2. Fetches exchange rates from `open.er-api.com`
3. For each country:
   - Extracts first currency code
   - Matches with exchange rates
   - Calculates `estimated_gdp = population × random(1000-2000) ÷ exchange_rate`
   - Upserts to database (updates if exists, inserts if new)
4. Generates summary image with top 5 countries by GDP
5. Returns total count and timestamp

### Edge Cases Handled

- ✅ Countries with no currencies → set `estimated_gdp = 0`
- ✅ Currency not found in exchange rates → set rates and GDP to `null`
- ✅ Missing population or name → skip with logging
- ✅ External API failures → return 503 without modifying database
- ✅ Case-insensitive country name matching
- ✅ Multiple currencies → use first currency only

## 🔒 Error Handling

All endpoints return consistent JSON error responses:

- **400 Bad Request:**
```json
{
  "error": "Validation failed",
  "details": {
    "currency_code": "is required"
  }
}
```

- **404 Not Found:**
```json
{
  "error": "Country not found"
}
```

- **500 Internal Server Error:**
```json
{
  "error": "Internal server error"
}
```

- **503 Service Unavailable:**
```json
{
  "error": "External data source unavailable",
  "details": "Could not fetch data from restcountries.com"
}
```

## 🚀 Deployment

### Docker Production Deployment

1. Build and start containers:
```bash
docker-compose up -d --build
```

2. Run migrations:
```bash
docker exec -it hng_country_app php artisan migrate --force
```

3. Set production environment:
```bash
docker exec -it hng_country_app php artisan config:cache
docker exec -it hng_country_app php artisan route:cache
```

### Traditional Deployment (AWS/Railway)

1. Clone and install:
```bash
git clone <repository-url>
cd hng-country-api
composer install --optimize-autoloader --no-dev
```

2. Configure environment:
```bash
cp .env.example .env
# Edit .env with production settings
php artisan key:generate
```

3. Run migrations:
```bash
php artisan migrate --force
```

4. Start queue workers (if using):
```bash
php artisan queue:work
```

## 📝 Database Schema

```sql
CREATE TABLE countries (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    name_normalized VARCHAR(255) UNIQUE NOT NULL,
    capital VARCHAR(255) NULL,
    region VARCHAR(100) NULL,
    population BIGINT NOT NULL,
    currency_code VARCHAR(10) NULL,
    exchange_rate DOUBLE NULL,
    estimated_gdp DOUBLE NULL,
    flag_url TEXT NULL,
    last_refreshed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## 📄 License

MIT License

## 👤 Author

Built for HNG Stage 2 Backend Task
