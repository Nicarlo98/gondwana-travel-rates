#!/bin/bash

echo "🌍 Starting Gondwana Travel Rates..."

# Function to check if port is in use
check_port() {
    if lsof -Pi :$1 -sTCP:LISTEN -t >/dev/null ; then
        echo "Port $1 is already in use"
        return 0
    else
        return 1
    fi
}

# Kill any existing processes on our ports
echo "🧹 Cleaning up existing processes..."
pkill -f "php -S" 2>/dev/null || true
pkill -f "npm run dev" 2>/dev/null || true

# Wait a moment for processes to clean up
sleep 2

# Start backend server
echo "🔧 Starting PHP backend server on port 8000..."
cd backend
php -S 0.0.0.0:8000 &
BACKEND_PID=$!
cd ..

# Wait for backend to start
sleep 3

# Start frontend server
echo "⚛️ Starting Vite frontend server on port 3000..."
cd frontend
npm run dev -- --host 0.0.0.0 --port 3000 &
FRONTEND_PID=$!
cd ..

echo ""
echo "🎉 Application started successfully!"
echo ""
echo "📍 Access your application:"
echo "   Frontend: https://$CODESPACE_NAME-3000.app.github.dev"
echo "   Backend:  https://$CODESPACE_NAME-8000.app.github.dev"
echo ""
echo "🔍 Test the API:"
echo "   curl https://$CODESPACE_NAME-8000.app.github.dev/api/test.php"
echo ""
echo "⏹️ To stop: Press Ctrl+C or run 'pkill -f \"php -S\"' and 'pkill -f \"npm run dev\"'"
echo ""

# Keep script running
wait