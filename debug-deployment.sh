#!/bin/bash

# Debug script for deployment issues
# Usage: ./debug-deployment.sh

set -e

echo "🔍 Debugging deployment issues..."

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

echo ""
echo "📊 System Information:"
echo "======================"
echo "Docker version:"
docker --version
echo ""
echo "Docker Compose version:"
$DOCKER_COMPOSE --version
echo ""
echo "Available disk space:"
df -h
echo ""
echo "Available memory:"
free -h
echo ""

echo "🔍 Environment Check:"
echo "===================="
if [ -f .env ]; then
    echo "✅ .env file exists"
    echo ""
    echo "📋 Database configuration:"
    grep -E "DB_|REDIS_" .env | grep -v "PASSWORD"
    echo ""
    echo "🔐 Checking if passwords are set (not template values):"
    if grep -q "your_secure_db_password" .env; then
        echo "❌ DB_PASSWORD still has template value"
    else
        echo "✅ DB_PASSWORD is set"
    fi
    if grep -q "your_secure_root_password" .env; then
        echo "❌ DB_ROOT_PASSWORD still has template value"
    else
        echo "✅ DB_ROOT_PASSWORD is set"
    fi
    if grep -q "your_secure_redis_password" .env; then
        echo "❌ REDIS_PASSWORD still has template value"
    else
        echo "✅ REDIS_PASSWORD is set"
    fi
else
    echo "❌ .env file does not exist"
fi

echo ""
echo "🐳 Docker Status:"
echo "================"
echo "Running containers:"
docker ps
echo ""
echo "All containers (including stopped):"
docker ps -a
echo ""

echo "🔍 Port Check:"
echo "============="
echo "Checking if port 3306 is in use:"
if netstat -tuln | grep -q ":3306 "; then
    echo "❌ Port 3306 is already in use"
    netstat -tuln | grep ":3306"
else
    echo "✅ Port 3306 is available"
fi

echo ""
echo "📁 Volume Check:"
echo "==============="
echo "Docker volumes:"
docker volume ls
echo ""

echo "🔍 Recent Docker Logs:"
echo "====================="
echo "MySQL container logs:"
$DOCKER_COMPOSE -f docker-compose.prod.yml logs mysql --tail=20 2>/dev/null || echo "No MySQL logs found"

echo ""
echo "🚀 Quick Fix Attempts:"
echo "====================="
echo "1. Stopping all containers..."
$DOCKER_COMPOSE -f docker-compose.prod.yml down 2>/dev/null || echo "No containers to stop"

echo "2. Removing old volumes..."
docker volume rm public_html_mysql_data 2>/dev/null || echo "Volume not found or already removed"

echo "3. Starting only MySQL to test..."
$DOCKER_COMPOSE -f docker-compose.prod.yml up -d mysql

echo "4. Waiting 30 seconds for MySQL to start..."
sleep 30

echo "5. Checking MySQL status..."
$DOCKER_COMPOSE -f docker-compose.prod.yml ps mysql

echo "6. MySQL logs:"
$DOCKER_COMPOSE -f docker-compose.prod.yml logs mysql --tail=10

echo ""
echo "💡 Recommendations:"
echo "=================="
echo "1. If passwords are template values, run: ./setup-env.sh"
echo "2. If port 3306 is in use, stop the conflicting service"
echo "3. If disk space is low, clean up Docker: docker system prune -a"
echo "4. If memory is low, consider stopping other services"
echo ""
echo "🔧 To restart fresh:"
echo "   $DOCKER_COMPOSE -f docker-compose.prod.yml down"
echo "   docker volume rm public_html_mysql_data"
echo "   ./deploy.sh" 