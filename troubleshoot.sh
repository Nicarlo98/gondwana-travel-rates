#!/bin/bash

echo "🔧 Gondwana Travel Rates - Troubleshooting"
echo "=========================================="

# Make scripts executable
chmod +x codespace-start.sh
chmod +x .devcontainer/setup.sh
chmod +x .devcontainer/verify-setup.sh
chmod +x troubleshoot.sh

echo ""
echo "🔍 Quick Diagnostics:"

# Check if we're in Codespaces
if [ -n "$CODESPACE_NAME" ]; then
    echo "✅ Running in GitHub Codespaces: $CODESPACE_NAME"
else
    echo "⚠️  Not running in GitHub Codespaces"
fi

# Check basic tools
echo ""
echo "🛠️  Available Tools:"
command -v php &> /dev/null && echo "✅ PHP" || echo "❌ PHP missing"
command -v composer &> /dev/null && echo "✅ Composer" || echo "❌ Composer missing"
command -v node &> /dev/null && echo "✅ Node.js" || echo "❌ Node.js missing"
command -v npm &> /dev/null && echo "✅ NPM" || echo "❌ NPM missing"

echo ""
echo "📁 Project Files:"
[ -f ".env" ] && echo "✅ .env file" || echo "❌ .env file missing"
[ -d "backend/vendor" ] && echo "✅ Backend dependencies" || echo "❌ Backend dependencies missing"
[ -d "frontend/node_modules" ] && echo "✅ Frontend dependencies" || echo "❌ Frontend dependencies missing"

echo ""
echo "🚀 Quick Fixes:"
echo "1. Run full setup:"
echo "   bash .devcontainer/setup.sh"
echo ""
echo "2. Verify installation:"
echo "   bash .devcontainer/verify-setup.sh"
echo ""
echo "3. Start application:"
echo "   ./codespace-start.sh"
echo ""
echo "4. Manual start (if script fails):"
echo "   cd backend && php -S 0.0.0.0:8000 &"
echo "   cd frontend && npm run dev -- --host 0.0.0.0 --port 3000 &"
echo ""
echo "5. Check logs:"
echo "   tail -f backend.log"
echo "   tail -f frontend.log"
echo ""
echo "6. Kill all processes:"
echo "   pkill -f 'php -S' && pkill -f 'npm run dev'"
echo ""

# Check running processes
echo "🔄 Currently Running:"
pgrep -f "php -S" > /dev/null && echo "✅ PHP server running" || echo "❌ PHP server not running"
pgrep -f "npm run dev" > /dev/null && echo "✅ Vite server running" || echo "❌ Vite server not running"

echo ""
echo "📞 Need Help?"
echo "Check the logs above and run the suggested commands."
echo "Most issues are resolved by running: bash .devcontainer/setup.sh"