#!/bin/bash

# Environment setup script for Portal Website
# This script helps you set up the .env file with proper values

set -e

echo "🔧 Setting up environment variables..."

# Detect docker compose command
if command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE="docker-compose"
elif docker compose version &> /dev/null; then
    DOCKER_COMPOSE="docker compose"
else
    echo "❌ Error: Neither docker-compose nor docker compose found"
    exit 1
fi

# Check if .env exists
if [ -f .env ]; then
    echo "⚠️  .env file already exists. Creating backup..."
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
fi

# Copy template
cp env.production.template .env

echo "📝 Please update the following values in your .env file:"
echo ""
echo "🔑 Database passwords (generate secure ones):"
echo "   DB_PASSWORD=your_secure_db_password"
echo "   DB_ROOT_PASSWORD=your_secure_root_password"
echo "   REDIS_PASSWORD=your_secure_redis_password"
echo ""
echo "🔑 Reverb keys (generate with Laravel):"
echo "   REVERB_APP_KEY=your_reverb_key"
echo "   REVERB_APP_SECRET=your_reverb_secret"
echo ""
echo "🔑 Application key (will be generated automatically):"
echo "   APP_KEY="
echo ""

# Generate secure passwords
echo "🔐 Generating secure passwords..."
DB_PASSWORD=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)
DB_ROOT_PASSWORD=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)

echo "✅ Generated secure passwords:"
echo "   DB_PASSWORD=$DB_PASSWORD"
echo "   DB_ROOT_PASSWORD=$DB_ROOT_PASSWORD"
echo ""

# Update .env with generated passwords
sed -i "s/DB_PASSWORD=your_secure_db_password/DB_PASSWORD=$DB_PASSWORD/" .env
sed -i "s/DB_ROOT_PASSWORD=your_secure_root_password/DB_ROOT_PASSWORD=$DB_ROOT_PASSWORD/" .env

echo "📋 Updated .env file with generated passwords"
echo ""
echo "🚀 Next steps:"
echo "1. Review and update other values in .env if needed"
echo "2. Run: ./deploy.sh"
echo "3. The APP_KEY will be generated automatically during deployment"
echo ""
echo "💡 To generate Reverb keys manually:"
echo "   $DOCKER_COMPOSE -f docker-compose.prod.yml run --rm app php artisan reverb:keys" 