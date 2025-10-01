#!/bin/bash

echo "🚀 Setting up Gondwana Travel Rates in Codespaces..."

# Install backend dependencies
echo "📦 Installing PHP dependencies..."
cd backend
composer install --no-dev --optimize-autoloader
cd ..

# Install frontend dependencies
echo "📦 Installing Node.js dependencies..."
cd frontend
npm install
cd ..

# Copy environment file
echo "⚙️ Setting up environment..."
cp .env.example .env

# Make scripts executable
chmod +x codespace-start.sh

echo "✅ Setup complete! Run './codespace-start.sh' to start the application."