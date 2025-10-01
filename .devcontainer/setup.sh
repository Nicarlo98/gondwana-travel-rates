#!/bin/bash

echo "🚀 Setting up Gondwana Travel Rates in Codespaces..."

# Update package lists
echo "📋 Updating package lists..."
sudo apt-get update

# Install additional PHP extensions
echo "🔧 Installing PHP extensions..."
sudo apt-get install -y php8.2-curl php8.2-mbstring php8.2-xml php8.2-zip php8.2-gd

# Verify installations
echo "✅ Verifying installations..."
echo "PHP version: $(php --version | head -n 1)"
echo "Composer version: $(composer --version)"
echo "Node version: $(node --version)"
echo "NPM version: $(npm --version)"

# Install backend dependencies
echo "📦 Installing PHP dependencies..."
cd backend
composer install --optimize-autoloader
cd ..

# Install frontend dependencies
echo "📦 Installing Node.js dependencies..."
cd frontend
npm install
cd ..

# Copy environment file
echo "⚙️ Setting up environment..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ Environment file created"
else
    echo "✅ Environment file already exists"
fi

# Make scripts executable
echo "🔐 Setting script permissions..."
chmod +x codespace-start.sh
chmod +x .devcontainer/setup.sh

# Test installations
echo "🧪 Testing installations..."
cd backend
php -v > /dev/null && echo "✅ PHP working"
composer --version > /dev/null && echo "✅ Composer working"
cd ../frontend
node --version > /dev/null && echo "✅ Node.js working"
npm --version > /dev/null && echo "✅ NPM working"
cd ..

echo ""
echo "🎉 Setup complete!"
echo "📍 Next steps:"
echo "   1. Run: ./codespace-start.sh"
echo "   2. Open the forwarded ports when prompted"
echo "   3. Start developing!"
echo ""