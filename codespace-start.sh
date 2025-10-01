#!/bin/bash

echo "🌍 Starting Gondwana Travel Rates..."

# Verify required tools are available
if ! command -v php &> /dev/null; then
    echo "❌ PHP not found. Please run the setup first:"
    echo "   bash .devcontainer/setup.sh"
    exit 1
fi

if ! command -v npm &> /dev/null; then
    echo "❌ NPM not found. Please run the setup first:"
    echo "   bash .devcontainer/setup.sh"
    exit 1
fi

# Function to check if port is in use
check_port() {
    if lsof -Pi :$1 -sTCP:LISTEN -t >/dev/null 2>&1; then
        echo "⚠️  Port $1 is already in use"
        return 0
    else
        return 1
    fi
}

# Kill any existing processes on our ports
echo "🧹 Cleaning up existing processes..."
pkill -f "php -S" 2>/dev/null || true
pkill -f "npm run dev" 2>/dev/null || true
pkill -f "vite" 2>/dev/null || true

# Wait a moment for processes to clean up
sleep 2

# Check if .env exists
if [ ! -f .env ]; then
    echo "⚙️ Creating environment file..."
    cp .env.example .env
fi

# Start backend server
echo "🔧 Starting PHP backend server on port 8000..."
cd backend

# Test PHP first
if ! php -v > /dev/null 2>&1; then
    echo "❌ PHP is not working properly"
    exit 1
fi

# Start PHP server
nohup php -S 0.0.0.0:8000 > ../backend.log 2>&1 &
BACKEND_PID=$!
echo "✅ Backend started (PID: $BACKEND_PID)"
cd ..

# Wait for backend to start
sleep 3

# Test backend
if curl -s http://localhost:8000/api/test.php > /dev/null; then
    echo "✅ Backend is responding"
else
    echo "⚠️  Backend might not be ready yet"
fi

# Start frontend server
echo "⚛️ Starting Vite frontend server on port 3000..."
cd frontend

# Test npm first
if ! npm --version > /dev/null 2>&1; then
    echo "❌ NPM is not working properly"
    exit 1
fi

# Start Vite server
nohup npm run dev -- --host 0.0.0.0 --port 3000 > ../frontend.log 2>&1 &
FRONTEND_PID=$!
echo "✅ Frontend started (PID: $FRONTEND_PID)"
cd ..

# Wait for frontend to start
sleep 5

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
echo "📋 Process IDs:"
echo "   Backend PHP: $BACKEND_PID"
echo "   Frontend Vite: $FRONTEND_PID"
echo ""
echo "📄 Logs:"
echo "   Backend: tail -f backend.log"
echo "   Frontend: tail -f frontend.log"
echo ""
echo "⏹️ To stop:"
echo "   pkill -f 'php -S' && pkill -f 'npm run dev'"
echo "   or press Ctrl+C if running in foreground"
echo ""

# Keep script running and show logs
echo "📺 Showing live logs (Ctrl+C to stop):"
echo "----------------------------------------"
tail -f backend.log frontend.log 2>/dev/null &
wait