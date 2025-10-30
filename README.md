# Country Currency & Exchange API

A RESTful API that fetches country data from external APIs, stores it in a database, and provides CRUD operations with currency exchange rate calculations.

## 🚀 Features

- **External API Integration**: Fetches country data from [REST Countries API](https://restcountries.com/v2/all) and exchange rates from [Open Exchange Rates API](https://open.er-api.com/v6/latest/USD)
- **GDP Estimation**: Calculates estimated GDP using population × random(1000-2000) ÷ exchange_rate
- **CRUD Operations**: Full Create, Read, Update, Delete operations for countries
- **Filtering & Sorting**: Support for region, currency, and GDP/population sorting
- **Image Generation**: Automatically generates summary images with top countries by GDP
- **Comprehensive Error Handling**: Consistent JSON error responses
- **Database Caching**: Efficient bulk upsert operations for performance

## 📋 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/countries/refresh` | Fetch all countries and exchange rates, then cache them |
| `GET` | `/api/countries` | Get all countries (supports filters: `?region=Africa`, `?currency=NGN`, `?sort=gdp_desc`) |
| `GET` | `/api/countries/{name}` | Get one country by name |
| `DELETE` | `/api/countries/{name}` | Delete a country record |
| `GET` | `/api/status` | Show total countries and last refresh timestamp |
| `GET` | `/api/countries/image` | Serve summary image |

## 🛠️ Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Database**: MySQL (with Aiven cloud hosting)
- **Image Processing**: GD Library
- **Testing**: PHPUnit with comprehensive test suite
- **Deployment**: Docker + Koyeb

## 📦 Installation & Setup

### Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL database
- Docker (for deployment)

### Local Development

1. **Clone the repository**
```bash
   git clone <your-repo-url>
cd hng-country-api
```

2. **Install dependencies**
```bash
composer install
```

3. **Environment setup**
```bash
   cp .env.example .env
php artisan key:generate
```

4. **Configure database**
   Update `.env` with your database credentials:
```env
DB_CONNECTION=mysql
   DB_HOST=your-mysql-host
DB_PORT=3306
   DB_DATABASE=your-database
   DB_USERNAME=your-username
   DB_PASSWORD=your-password
   ```

5. **Run migrations**
```bash
php artisan migrate
```

6. **Start the development server**
```bash
php artisan serve
```

7. **Visit the API**
   - API Base URL: `http://localhost:8000/api`
   - Welcome Page: `http://localhost:8000`

### Testing

Run the comprehensive test suite:

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

## 🌐 Deployment on Koyeb

### 1. Prepare for Deployment

Ensure your `Dockerfile` and `docker-compose.yml` are properly configured.

### 2. Deploy to Koyeb

1. **Create a Koyeb account** and connect your GitHub repository
2. **Create a new app** and select your repository
3. **Configure build settings**:
   - Build Method: Dockerfile
   - Dockerfile Path: `./Dockerfile`

4. **Set environment variables**:
   ```
   DB_CONNECTION=mysql
   DB_HOST=your-aiven-mysql-host
   DB_PORT=22962
   DB_DATABASE=defaultdb
   DB_USERNAME=avnadmin
   DB_PASSWORD=your-aiven-password
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=your-generated-app-key
   ```

5. **Deploy** and wait for the build to complete

6. **Run migrations** (via Koyeb console or SSH):
```bash
   php artisan migrate --force
   php artisan storage:link
   ```

### 3. Post-Deployment

- **Test your endpoints** using the deployed URL
- **Refresh countries data**: `POST /countries/refresh`
- **Verify all endpoints** are working correctly

## 📊 API Usage Examples

### Refresh Countries Data
```bash
curl -X POST https://your-app.koyeb.app/api/countries/refresh
```

### Get All Countries
```bash
curl https://your-app.koyeb.app/api/countries
```

### Filter by Region
```bash
curl https://your-app.koyeb.app/api/countries?region=Africa
```

### Sort by GDP (Descending)
```bash
curl https://your-app.koyeb.app/api/countries?sort=gdp_desc
```

### Get Specific Country
```bash
curl https://your-app.koyeb.app/api/countries/Nigeria
```

### Get API Status
```bash
curl https://your-app.koyeb.app/api/status
```

## 🧪 Testing Strategy

The project includes comprehensive tests covering:

- **External API Integration**: Mocked HTTP responses for reliable testing
- **Database Operations**: Bulk upsert, filtering, sorting
- **Error Handling**: 400, 404, 500, 503 responses
- **Currency Logic**: Multiple currencies, missing currencies, missing exchange rates
- **GDP Calculation**: Random multiplier validation
- **Image Generation**: Summary image creation and serving
- **Validation**: Query parameter validation

## 🔧 Configuration

### Environment Variables

| Variable | Description | Required |
|----------|-------------|----------|
| `DB_CONNECTION` | Database driver (mysql) | Yes |
| `DB_HOST` | Database host | Yes |
| `DB_PORT` | Database port | Yes |
| `DB_DATABASE` | Database name | Yes |
| `DB_USERNAME` | Database username | Yes |
| `DB_PASSWORD` | Database password | Yes |
| `APP_ENV` | Application environment | Yes |
| `APP_DEBUG` | Debug mode | Yes |
| `APP_KEY` | Application encryption key | Yes |

### External APIs

- **Countries API**: `https://restcountries.com/v2/all?fields=name,capital,region,population,flag,currencies`
- **Exchange Rates API**: `https://open.er-api.com/v6/latest/USD`

## 📈 Performance Optimizations

- **Bulk Upsert**: Uses Laravel's `upsert()` method for efficient database operations
- **Chunked Processing**: Processes countries in chunks of 500 to prevent memory issues
- **Caching**: Database caching for frequently accessed data
- **Image Optimization**: Efficient GD library usage for summary images

## 🐛 Error Handling

The API returns consistent JSON error responses:

```json
{
  "error": "Error message",
  "details": "Additional details (optional)"
}
```

### Error Codes

- `400` - Bad Request (validation errors)
- `404` - Not Found (country not found, image not found)
- `500` - Internal Server Error
- `503` - Service Unavailable (external API failures)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

## 📄 License

This project is licensed under the MIT License.

## 🎯 HNG Task Compliance

This implementation fully complies with the HNG Backend Task requirements:

- ✅ External API integration (REST Countries + Exchange Rates)
- ✅ MySQL database with proper schema
- ✅ All required endpoints implemented
- ✅ Proper error handling and validation
- ✅ GDP calculation with random multiplier
- ✅ Image generation and serving
- ✅ Comprehensive test coverage
- ✅ Docker deployment ready
- ✅ Clear documentation and setup instructions

## 📞 Support

For questions or issues, please open an issue in the GitHub repository or contact the development team.