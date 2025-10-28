<?php
/**
 * Quick Start Script - Shows what's in the project
 */

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║         HNG Country & Currency Exchange API                 ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "📦 Project Structure:\n\n";
echo "✅ Database Migration: database/migrations/2025_01_01_000000_create_countries_table.php\n";
echo "✅ Country Model: app/Models/Country.php\n";
echo "✅ Country Service: app/Services/CountryService.php\n";
echo "✅ Country Controller: app/Http/Controllers/CountryController.php\n";
echo "✅ API Routes: routes/api.php\n";
echo "✅ Docker Setup: Dockerfile, docker-compose.yml\n";
echo "✅ Tests: tests/Feature/CountryApiTest.php\n";
echo "✅ Documentation: README.md, SETUP.md, PROJECT_SUMMARY.md\n\n";

echo "🚀 To start the app:\n\n";
echo "Option 1 - Docker (Recommended):\n";
echo "  1. Install Docker Desktop from docker.com\n";
echo "  2. Run: docker-compose up -d --build\n";
echo "  3. Run: docker exec -it hng_country_app php artisan key:generate\n";
echo "  4. Run: docker exec -it hng_country_app php artisan migrate\n";
echo "  5. Access: http://localhost:8000\n\n";

echo "Option 2 - Local with Composer:\n";
echo "  1. Run: composer install\n";
echo "  2. Run: php artisan key:generate\n";
echo "  3. Run: php artisan migrate\n";
echo "  4. Run: php artisan serve\n";
echo "  5. Access: http://localhost:8000\n\n";

echo "📡 API Endpoints:\n\n";
echo "  POST   /api/countries/refresh      - Fetch and cache countries\n";
echo "  GET    /api/countries              - List all countries\n";
echo "  GET    /api/countries/{name}       - Get one country\n";
echo "  DELETE /api/countries/{name}       - Delete country\n";
echo "  GET    /api/status                 - Get total count & timestamp\n";
echo "  GET    /api/countries/image        - Get summary image\n\n";

echo "📝 Quick Test:\n";
echo "  curl -X POST http://localhost:8000/api/countries/refresh\n";
echo "  curl http://localhost:8000/api/countries\n";
echo "  curl http://localhost:8000/api/status\n\n";

echo "📚 Documentation:\n";
echo "  - README.md          Full API documentation\n";
echo "  - SETUP.md           Deployment guide\n";
echo "  - PROJECT_SUMMARY.md Implementation details\n\n";

echo "🎉 Everything is ready! Choose your deployment method above.\n\n";

