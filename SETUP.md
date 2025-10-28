# Quick Setup Guide

## ✅ What's Been Created

Your complete Country API with:
- ✅ Database migration with all required fields
- ✅ Country model with proper casting
- ✅ CountryService with complete business logic
- ✅ CountryController with all 6 endpoints
- ✅ Docker configuration (Dockerfile, docker-compose.yml, nginx config)
- ✅ Comprehensive tests
- ✅ Complete README with API documentation

## 🚀 Deployment Options

### Option 1: Docker (Recommended)

Since you're on Windows and PHP path might have issues, Docker is your best bet:

```bash
# 1. Install Docker Desktop for Windows from docker.com/products/docker-desktop

# 2. Once Docker is installed, open PowerShell and run:
cd C:\Users\ESTHER\hng-country-api

# 3. Start all services
docker-compose up -d --build

# 4. Generate application key
docker exec -it hng_country_app php artisan key:generate

# 5. Run migrations
docker exec -it hng_country_app php artisan migrate

# 6. Access the API at http://localhost:8000
```

### Option 2: Use Laragon (Easiest for Windows)

1. Download Laragon from laragon.org
2. Install it
3. Copy your project to `C:\laragon\www\hng-country-api`
4. Open Laragon, click "Start All"
5. In terminal, run:
```bash
cd C:\laragon\www\hng-country-api
composer install
php artisan key:generate
php artisan migrate
```
6. Access at http://hng-country-api.test

### Option 3: Use WAMP/XAMPP

Similar to Laragon but more manual setup.

## 📝 Quick Test

Once running, test the API:

```bash
# Refresh countries
curl -X POST http://localhost:8000/api/countries/refresh

# Get all countries
curl http://localhost:8000/api/countries

# Get status
curl http://localhost:8000/api/status
```

## 📁 Project Structure Summary

```
hng-country-api/
├── app/
│   ├── Http/Controllers/CountryController.php  ← All endpoints
│   ├── Models/Country.php                      ← Database model
│   └── Services/CountryService.php             ← Business logic
├── database/
│   ├── migrations/2025_01_01_000000_create_countries_table.php
│   └── factories/CountryFactory.php
├── routes/api.php                              ← API routes
├── tests/Feature/CountryApiTest.php           ← Tests
├── Dockerfile
├── docker-compose.yml
└── README.md                                   ← Full documentation
```

## 🎯 All Endpoints

1. `POST /api/countries/refresh` - Fetch and cache countries
2. `GET /api/countries` - List all (with filters: ?region=Africa&currency=NGN&sort=gdp_desc)
3. `GET /api/countries/{name}` - Get one by name
4. `DELETE /api/countries/{name}` - Delete by name
5. `GET /api/status` - Total count + last refresh time
6. `GET /api/countries/image` - Summary image

## 🧪 Run Tests

```bash
# With Docker
docker exec -it hng_country_app php artisan test

# With Laragon/Local
php artisan test
```

## ⚠️ Important Notes

- **Database**: Uses MySQL (configured in docker-compose.yml and .env)
- **Autoload Issues**: If you get vendor/autoload.php errors, use Docker or reinstall vendor with `composer install`
- **GD Extension**: Required for image generation. Already configured in Dockerfile
- **External APIs**: Uses http timeouts (10s). Returns 503 if unavailable.

## 🔧 Troubleshooting

### "vendor/autoload.php not found"
```bash
composer install --ignore-platform-reqs
```

### "Could not find driver" (PDO)
Enable pdo_mysql extension in php.ini (already done in Docker)

### "Summary image not found"
Run `/api/countries/refresh` first to generate the image

### Docker container won't start
Check if ports 8000, 9000, 33060 are already in use:
```bash
netstat -ano | findstr :8000
```

## 📞 Next Steps

1. **Choose your deployment method** (Docker recommended)
2. **Start the server**
3. **Run migrations**
4. **Test the API** using curl or Postman
5. **Check the README.md** for complete API documentation

Need help? Check the README.md for detailed API documentation!

