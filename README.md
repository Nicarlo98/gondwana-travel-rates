# Rates API Assessment

A complete full-stack application for querying accommodation rates with PHP backend and React frontend.

## 📁 Project Structure

```
rates-api-assessment/
├── backend/                    # PHP API server
│   ├── src/
│   │   ├── api/
│   │   │   ├── rates.php      # Main API endpoint
│   │   │   └── test.php       # CORS test endpoint
│   │   ├── Services/
│   │   │   └── RatesService.php # Business logic
│   │   ├── Utils/
│   │   │   └── Validator.php   # Input validation
│   │   └── index.php          # Backend status endpoint
│   ├── tests/                  # Unit tests
│   │   └── RatesServiceTest.php
│   ├── composer.json           # PHP dependencies
│   ├── phpunit.xml            # PHPUnit configuration
│   └── .htaccess              # Apache config
├── frontend/                   # React application
│   ├── src/
│   │   ├── components/
│   │   │   ├── Form.jsx       # Input form component
│   │   │   └── Result.jsx     # Results display component
│   │   ├── App.jsx            # Main app component
│   │   ├── main.jsx           # Entry point
│   │   └── index.css          # Global styles with Tailwind
│   ├── package.json           # Node dependencies
│   ├── tailwind.config.js     # Tailwind configuration
│   ├── postcss.config.js      # PostCSS configuration
│   ├── vite.config.js         # Vite configuration
│   └── index.html             # HTML template
├── .env.example               # Environment variables template
├── .env                       # Environment variables (ignored by git)
├── .gitignore                 # Git ignore rules
├── start-dev.ps1              # PowerShell startup script
├── start-dev.bat              # Batch startup script
└── README.md                  # This file
```

## 🚀 Quick Start

### Local Development

1. **Clone the repository**

   ```bash
   git clone <your-repository-url>
   cd rates-api-assessment
   ```

2. **Setup environment**

   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

3. **Install Dependencies**

   ```bash
   # Backend dependencies
   cd backend
   composer install

   # Frontend dependencies
   cd ../frontend
   npm install
   ```

4. **Start Development Servers**

   **Option A: Use startup scripts (Windows)**

   ```bash
   # PowerShell
   .\start-dev.ps1

   # Command Prompt
   start-dev.bat
   ```

   **Option B: Manual startup**

   ```bash
   # Terminal 1: Backend
   cd backend
   php -S localhost:8000 -t src/

   # Terminal 2: Frontend
   cd frontend
   npm run dev
   ```

5. **Access the application**

   - Frontend: http://localhost:5173
   - Backend API: http://localhost:8000/api/rates.php
   - Backend Status: http://localhost:8000

6. **Test the API (Optional)**
   ```bash
   # PowerShell (if you have the test script)
   .\test-api.ps1
   ```

## 🔌 API Usage

### Endpoint

`POST /api/rates.php`

### Request Format

```json
{
  "Unit Name": "Deluxe Suite",
  "Arrival": "15/12/2024",
  "Departure": "20/12/2024",
  "Occupants": 3,
  "Ages": [25, 30, 8]
}
```

### Response Format

```json
{
  "Unit Name": "Deluxe Suite",
  "Rate": 150.00,
  "Date Range": "2024-12-15 to 2024-12-20",
  "Availability": true,
  "Raw Response": { ... }
}
```

### Sample cURL Commands

**Success Case:**

```bash
curl -X POST http://localhost:8000/api/rates.php \
  -H "Content-Type: application/json" \
  -d '{
    "Unit Name": "Standard Room",
    "Arrival": "15/12/2024",
    "Departure": "20/12/2024",
    "Occupants": 2,
    "Ages": [25, 30]
  }'
```

**Error Cases:**

```bash
# Invalid date format
curl -X POST http://localhost:8000/api/rates.php \
  -H "Content-Type: application/json" \
  -d '{
    "Unit Name": "Standard Room",
    "Arrival": "2024-12-15",
    "Departure": "20/12/2024",
    "Occupants": 2,
    "Ages": [25, 30]
  }'

# Mismatched occupants and ages
curl -X POST http://localhost:8000/api/rates.php \
  -H "Content-Type: application/json" \
  -d '{
    "Unit Name": "Standard Room",
    "Arrival": "15/12/2024",
    "Departure": "20/12/2024",
    "Occupants": 3,
    "Ages": [25, 30]
  }'
```

## 🔧 Configuration

### Environment Variables

Copy `.env.example` to `.env` and configure:

```env
# Remote API Configuration
REMOTE_API_URL=https://dev.gondwana-collection.com/Web-Store/Rates/Rates.php
REMOTE_API_TIMEOUT=30

# Age Classification
ADULT_AGE_THRESHOLD=12

# Unit Type Mappings (for testing)
UNIT_TYPE_ID_1=-2147483637
UNIT_TYPE_ID_2=-2147483456
```

## 🧪 Testing

### Backend Tests

```bash
cd backend
./vendor/bin/phpunit
```

### Frontend Tests

```bash
cd frontend
npm test
```

## 🏗️ Business Logic

### Age Classification

- **Adult**: Age ≥ 12 years
- **Child**: Age < 12 years

### Unit Name Mapping

For testing purposes, unit names are mapped to test Unit Type IDs:

- Any unit name → alternates between `-2147483637` and `-2147483456`

### Date Transformation

- Input: `dd/mm/yyyy` format
- Output: `yyyy-mm-dd` format for remote API

### Validation Rules

1. Date format must be `dd/mm/yyyy`
2. Arrival date must be before departure date
3. Occupants count must match Ages array length
4. All ages must be positive integers

## 🔒 Security Features

- Input sanitization and validation
- No hardcoded secrets (environment variables)
- CORS headers configured
- Error messages don't expose internal details
- Request timeout protection

## 🚨 Error Handling

### HTTP Status Codes

- `200`: Success
- `400`: Invalid input (validation errors)
- `405`: Method not allowed (non-POST requests)
- `502`: Remote API failure

### Error Response Format

```json
{
  "error": "Validation failed",
  "details": ["Arrival date must be before departure date"]
}
```

## 🛠️ Troubleshooting

### Common Issues

1. **CORS Errors**

   - Ensure backend is running on correct port
   - Check browser console for specific CORS messages

2. **Composer Dependencies**

   ```bash
   cd backend
   composer install --no-dev
   ```

3. **Node Dependencies**

   ```bash
   cd frontend
   rm -rf node_modules package-lock.json
   npm install
   ```

4. **PHP Server Issues**

   ```bash
   # Check if port 8000 is available
   netstat -an | grep 8000

   # Use alternative port
   php -S localhost:8001 -t src/
   ```

5. **Environment Variables**
   - Ensure `.env` file exists in project root
   - Check variable names match `.env.example`

### Development Tips

- Use browser dev tools to inspect network requests
- Check PHP error logs for backend issues
- Verify environment variables are loaded correctly
- Test API endpoints directly with cURL before frontend integration

## ✨ Features

- **Modern UI**: Clean, responsive design with Tailwind CSS
- **Real-time Validation**: Form validation with instant feedback
- **Dynamic Forms**: Age inputs adjust based on occupant count
- **Loading States**: Smooth loading animations and feedback
- **Error Handling**: User-friendly error messages
- **Mock Responses**: Graceful fallback when remote API is unavailable
- **CORS Support**: Proper cross-origin resource sharing
- **Security**: Input sanitization and validation

## 🎯 Technical Stack

- **Backend**: PHP 8.0+, Composer, Guzzle HTTP, PHPUnit
- **Frontend**: React 18, Vite, Tailwind CSS
- **Development**: Hot reload, ESLint, PostCSS

## 📝 License

This project is created for assessment purposes.
