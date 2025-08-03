#!/bin/bash

# Fix MySQL database corruption
# Usage: ./fix-mysql.sh

set -e

echo "🔧 Fixing MySQL database corruption..."

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

echo "🛑 Stopping all containers..."
$DOCKER_COMPOSE -f docker-compose.prod.yml down

echo "🗑️ Removing corrupted MySQL data..."
docker volume rm public_html_mysql_data 2>/dev/null || echo "Volume not found, creating fresh"

echo "🧹 Cleaning up any remaining containers..."
docker container prune -f

echo "💾 Starting fresh MySQL container..."
$DOCKER_COMPOSE -f docker-compose.prod.yml up -d mysql

echo "⏳ Waiting for MySQL to initialize (this may take 1-2 minutes)..."
sleep 60

echo "🔍 Checking MySQL status..."
$DOCKER_COMPOSE -f docker-compose.prod.yml ps mysql

echo "📊 MySQL logs:"
$DOCKER_COMPOSE -f docker-compose.prod.yml logs mysql --tail=10

echo "✅ MySQL should now be working properly!"
echo ""
echo "🚀 You can now run the full deployment:"
echo "   ./deploy.sh" 