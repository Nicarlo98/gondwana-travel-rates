# GitHub Codespaces Setup

This project is configured to run seamlessly in GitHub Codespaces.

## Quick Start

1. **Open in Codespaces**:
   - Go to your GitHub repository
   - Click the green "Code" button
   - Select "Codespaces" tab
   - Click "Create codespace on main"

2. **Automatic Setup**:
   - The devcontainer will automatically install PHP 8.2, Node.js 18, and Composer
   - Dependencies will be installed automatically
   - Ports 3000 (frontend) and 8000 (backend) will be forwarded

3. **Start the Application**:
   ```bash
   ./codespace-start.sh
   ```

4. **Access Your App**:
   - Frontend: Click the "Open in Browser" popup for port 3000
   - Backend API: Click the "Open in Browser" popup for port 8000

## Manual Commands

If you prefer to run services separately:

```bash
# Backend only
cd backend && php -S 0.0.0.0:8000

# Frontend only  
cd frontend && npm run dev -- --host 0.0.0.0 --port 3000

# Run tests
cd backend && ./vendor/bin/phpunit
cd frontend && npm test
```

## Environment Variables

The `.env` file is automatically created from `.env.example`. Update it if needed:

```bash
# Edit environment variables
nano .env
```

## Troubleshooting

- **Port conflicts**: The startup script automatically kills existing processes
- **Dependencies**: Run `composer install` in backend or `npm install` in frontend
- **Permissions**: Run `chmod +x codespace-start.sh` if needed

## Features Available

- ✅ Full PHP 8.2 environment with Composer
- ✅ Node.js 18 with npm
- ✅ Automatic port forwarding
- ✅ VS Code extensions pre-installed
- ✅ One-command startup
- ✅ Hot reload for both frontend and backend