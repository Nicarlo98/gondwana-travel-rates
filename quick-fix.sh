#!/bin/bash

echo "🔧 Quick Fix for Current Codespace Issues"
echo "========================================"

# Stop existing processes
echo "🛑 Stopping existing processes..."
pkill -f "php -S" 2>/dev/null || true
pkill -f "npm run dev" 2>/dev/null || true
sleep 2

# Fix Node.js version issue for Vite
echo "🔄 Fixing Node.js compatibility..."
cd frontend
rm -rf node_modules package-lock.json
npm cache clean --force
npm install
cd ..

# Start backend with correct document root
echo "🔧 Starting PHP backend (fixed)..."
cd backend
nohup php -S 0.0.0.0:8000 -t . > ../backend.log 2>&1 &
echo "✅ Backend started"
cd ..

# Wait and test backend
sleep 3
if curl -s http://localhost:8000/health.php > /dev/null; then
    echo "✅ Backend health check passed"
else
    echo "⚠️  Backend health check failed"
fi

# Start frontend
echo "⚛️ Starting Vite frontend..."
cd frontend
nohup npm run dev -- --host 0.0.0.0 --port 3000 > ../frontend.log 2>&1 &
echo "✅ Frontend started"
cd ..

echo ""
echo "🎉 Quick fix complete!"
echo ""
echo "📍 Test URLs:"
echo "   Backend Health: https://$CODESPACE_NAME-8000.app.github.dev/health.php"
echo "   Backend API: https://$CODESPACE_NAME-8000.app.github.dev/src/api/test.php"
echo "   Frontend: https://$CODESPACE_NAME-3000.app.github.dev"
echo ""
echo "📄 Check logs:"
echo "   tail -f backend.log frontend.log"