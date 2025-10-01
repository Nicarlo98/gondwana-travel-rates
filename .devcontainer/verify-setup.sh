#!/bin/bash

echo "🔍 Verifying Codespace Setup..."
echo "================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to check command
check_command() {
    if command -v $1 &> /dev/null; then
        echo -e "✅ ${GREEN}$1${NC} is installed: $($1 --version | head -n 1)"
        return 0
    else
        echo -e "❌ ${RED}$1${NC} is NOT installed"
        return 1
    fi
}

# Function to check file
check_file() {
    if [ -f "$1" ]; then
        echo -e "✅ ${GREEN}$1${NC} exists"
        return 0
    else
        echo -e "❌ ${RED}$1${NC} is missing"
        return 1
    fi
}

# Function to check directory
check_directory() {
    if [ -d "$1" ]; then
        echo -e "✅ ${GREEN}$1${NC} directory exists"
        return 0
    else
        echo -e "❌ ${RED}$1${NC} directory is missing"
        return 1
    fi
}

echo ""
echo "🔧 System Tools:"
check_command "php"
check_command "composer"
check_command "node"
check_command "npm"
check_command "curl"
check_command "git"

echo ""
echo "📁 Project Structure:"
check_file ".env"
check_file "backend/composer.json"
check_file "frontend/package.json"
check_directory "backend/vendor"
check_directory "frontend/node_modules"

echo ""
echo "🧪 PHP Extensions:"
php -m | grep -q curl && echo -e "✅ ${GREEN}curl${NC} extension" || echo -e "❌ ${RED}curl${NC} extension missing"
php -m | grep -q mbstring && echo -e "✅ ${GREEN}mbstring${NC} extension" || echo -e "❌ ${RED}mbstring${NC} extension missing"
php -m | grep -q xml && echo -e "✅ ${GREEN}xml${NC} extension" || echo -e "❌ ${RED}xml${NC} extension missing"

echo ""
echo "🌐 Network Tests:"
if curl -s --max-time 5 https://api.exchangerate-api.com/v4/latest/USD > /dev/null; then
    echo -e "✅ ${GREEN}External API${NC} is reachable"
else
    echo -e "⚠️  ${YELLOW}External API${NC} might be unreachable (this is OK for development)"
fi

echo ""
echo "📋 Summary:"
echo "================================"

# Count issues
issues=0

# Check critical components
command -v php &> /dev/null || ((issues++))
command -v npm &> /dev/null || ((issues++))
[ -f ".env" ] || ((issues++))
[ -d "backend/vendor" ] || ((issues++))
[ -d "frontend/node_modules" ] || ((issues++))

if [ $issues -eq 0 ]; then
    echo -e "🎉 ${GREEN}All systems ready!${NC}"
    echo -e "🚀 Run: ${GREEN}./codespace-start.sh${NC}"
else
    echo -e "⚠️  ${YELLOW}Found $issues issues${NC}"
    echo -e "🔧 Run: ${YELLOW}bash .devcontainer/setup.sh${NC}"
fi

echo ""