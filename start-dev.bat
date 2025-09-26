@echo off
echo Starting Rates API Development Environment...
echo.

echo Starting Backend Server (PHP)...
start "Backend Server" cmd /k "cd backend && php -S localhost:8000 -t src/"

timeout /t 2 /nobreak >nul

echo Starting Frontend Server (React)...
start "Frontend Server" cmd /k "cd frontend && npm run dev"

echo.
echo Development servers are starting...
echo Backend: http://localhost:8000
echo Frontend: http://localhost:5173
echo.
echo Press any key to continue...
pause >nul