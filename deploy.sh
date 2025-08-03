#!/bin/bash

# Production deployment script for Portal Website (Full Docker)
# Usage: ./deploy.sh

set -e

echo "🚀 Starting full Docker deployment..."

# Detect docker compose command
if command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE="docker-compose"
elif docker compose version &> /dev/null; then
    DOCKER_COMPOSE="docker compose"
else
    echo "❌ Error: Neither docker-compose nor docker compose found"
    exit 1
fi

echo "🔧 Using: $DOCKER_COMPOSE"

# Pull latest changes
echo "📥 Pulling latest changes..."
git pull origin main

# Copy production environment if it doesn't exist
if [ ! -f .env ]; then
    echo "📋 Setting up environment file..."
    cp env.production.template .env
    echo "⚠️  Please update .env with your production values!"
    echo "🔑 Generate APP_KEY with: $DOCKER_COMPOSE -f docker-compose.prod.yml run --rm app php artisan key:generate --show"
    exit 1
fi

# Stop existing containers
echo "🛑 Stopping existing containers..."
$DOCKER_COMPOSE -f docker-compose.prod.yml down

# Check for MySQL corruption and fix if needed
echo "🔍 Checking for MySQL corruption..."
if docker volume ls | grep -q "public_html_mysql_data"; then
    echo "⚠️  MySQL volume exists, checking for corruption..."
    # Start MySQL to check if it's corrupted
    $DOCKER_COMPOSE -f docker-compose.prod.yml up -d mysql
    sleep 10
    
    # Check if MySQL is running properly
    if ! $DOCKER_COMPOSE -f docker-compose.prod.yml ps mysql | grep -q "Up"; then
        echo "❌ MySQL container failed to start, checking logs..."
        $DOCKER_COMPOSE -f docker-compose.prod.yml logs mysql --tail=5
        
        # Check for corruption indicators
        if $DOCKER_COMPOSE -f docker-compose.prod.yml logs mysql 2>/dev/null | grep -q "Table 'mysql.user' doesn't exist"; then
            echo "🔧 Detected MySQL corruption, fixing..."
            $DOCKER_COMPOSE -f docker-compose.prod.yml down
            docker volume rm public_html_mysql_data
            echo "✅ Removed corrupted MySQL data"
        fi
    else
        echo "✅ MySQL appears to be working properly"
    fi
fi

# Build new images
echo "🏗️  Building production containers..."
$DOCKER_COMPOSE -f docker-compose.prod.yml build --no-cache app reverb

# Start database and Redis first
echo "💾 Starting database and Redis..."
$DOCKER_COMPOSE -f docker-compose.prod.yml up -d mysql redis

# Wait for database to be ready with better health check
echo "⏳ Waiting for database to be ready..."
sleep 10

# Check if database is accessible with improved logic
echo "🔍 Checking database connection..."
max_attempts=60
attempt=1

while [ $attempt -le $max_attempts ]; do
    # Check if MySQL container is running
    if ! $DOCKER_COMPOSE -f docker-compose.prod.yml ps mysql | grep -q "Up"; then
        echo "⏳ MySQL container not running yet... (attempt $attempt/$max_attempts)"
        sleep 5
        attempt=$((attempt + 1))
        continue
    fi
    
    # Try to connect to MySQL
    if $DOCKER_COMPOSE -f docker-compose.prod.yml exec -T mysql mysqladmin ping -h localhost -u root -p${DB_ROOT_PASSWORD} --silent >/dev/null 2>&1; then
        echo "✅ Database is ready!"
        break
    fi
    
    echo "⏳ Waiting for database... (attempt $attempt/$max_attempts)"
    sleep 5
    attempt=$((attempt + 1))
done

if [ $attempt -gt $max_attempts ]; then
    echo "❌ Database connection failed after $max_attempts attempts"
    echo "🔍 Checking container logs..."
    $DOCKER_COMPOSE -f docker-compose.prod.yml logs mysql
    exit 1
fi

# Additional wait to ensure MySQL is fully ready
echo "⏳ Additional wait for MySQL to be fully ready..."
sleep 10

# Start application containers
echo "🚀 Starting application containers..."
$DOCKER_COMPOSE -f docker-compose.prod.yml up -d app reverb

# Wait for application to be ready
echo "⏳ Waiting for application to start..."
sleep 15

# Copy .env file into the container
echo "📋 Copying .env file into container..."
$DOCKER_COMPOSE -f docker-compose.prod.yml cp .env app:/var/www/html/.env

# Install/update dependencies
echo "📦 Installing dependencies..."
$DOCKER_COMPOSE -f docker-compose.prod.yml exec -T app composer install --optimize-autoloader --no-dev

# Generate application key if needed
if ! $DOCKER_COMPOSE -f docker-compose.prod.yml exec -T app php artisan env:get APP_KEY >/dev/null 2>&1; then
    echo "🔑 Generating application key..."
    $DOCKER_COMPOSE -f docker-compose.prod.yml exec -T app php artisan key:generate
fi

# Run migrations
echo "🗄️  Running database migrations..."
$DOCKER_COMPOSE -f docker-compose.prod.yml exec -T app php artisan migrate --force

# Seed database if needed (first deployment)
if [ "$1" = "--seed" ]; then
    echo "🌱 Seeding database..."
    $DOCKER_COMPOSE -f docker-compose.prod.yml exec -T app php artisan db:seed
fi

# Clear and cache config
echo "🧹 Clearing and caching configuration..."
$DOCKER_COMPOSE -f docker-compose.prod.yml exec -T app php artisan config:clear
$DOCKER_COMPOSE -f docker-compose.prod.yml exec -T app php artisan config:cache
$DOCKER_COMPOSE -f docker-compose.prod.yml exec -T app php artisan route:cache
$DOCKER_COMPOSE -f docker-compose.prod.yml exec -T app php artisan view:cache

# Set permissions
echo "🔐 Setting permissions..."
$DOCKER_COMPOSE -f docker-compose.prod.yml exec -T app chown -R www-data:www-data storage bootstrap/cache
$DOCKER_COMPOSE -f docker-compose.prod.yml exec -T app chmod -R 775 storage bootstrap/cache

# Create symbolic link for storage
echo "🔗 Creating storage symlink..."
$DOCKER_COMPOSE -f docker-compose.prod.yml exec -T app php artisan storage:link

# Start nginx
echo "🌐 Starting web server..."
$DOCKER_COMPOSE -f docker-compose.prod.yml up -d nginx

echo "✅ Deployment completed successfully!"
echo ""
echo "🌐 Your application should be available at: https://portal.glab.si"
echo "🔧 PHPMyAdmin available at: http://portal.glab.si:8082 (use --profile tools to enable)"
echo "📊 Check logs with: $DOCKER_COMPOSE -f docker-compose.prod.yml logs -f"
echo "🐛 Debug with: $DOCKER_COMPOSE -f docker-compose.prod.yml ps"
echo ""
echo "📝 Next steps:"
echo "1. Set up SSL certificates with Let's Encrypt"
echo "2. Configure domain DNS to point to your server"
echo "3. Set up backup strategy for MySQL data" 