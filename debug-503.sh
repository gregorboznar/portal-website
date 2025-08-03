#!/bin/bash

# Debug script for 503 Service Unavailable error
# Usage: ./debug-503.sh

set -e

echo "🔍 Debugging 503 Service Unavailable error..."

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
echo "📊 System Status:"
echo "=================="
echo "Docker containers:"
$DOCKER_COMPOSE -f docker-compose.prod.yml ps

echo ""
echo "🔍 Container Logs:"
echo "=================="
echo "Nginx logs:"
$DOCKER_COMPOSE -f docker-compose.prod.yml logs nginx --tail=10

echo ""
echo "App logs:"
$DOCKER_COMPOSE -f docker-compose.prod.yml logs app --tail=10

echo ""
echo "MySQL logs:"
$DOCKER_COMPOSE -f docker-compose.prod.yml logs mysql --tail=5

echo ""
echo "🌐 Port Check:"
echo "============="
echo "Checking if port 8088 is listening:"
netstat -tuln | grep ":8088" || echo "Port 8088 not listening"

echo ""
echo "🔍 HestiaCP Configuration:"
echo "=========================="
echo "Checking if HestiaCP is configured to proxy to port 8088..."
echo "You should have a proxy configuration pointing to http://127.0.0.1:8088"

echo ""
echo "🧪 Test Application:"
echo "==================="
echo "Testing direct access to Docker container:"
curl -I http://127.0.0.1:8088 2>/dev/null || echo "Cannot connect to port 8088"

echo ""
echo "📋 Quick Fixes:"
echo "==============="
echo "1. If containers are not running:"
echo "   ./deploy.sh"
echo ""
echo "2. If port 8088 is not accessible:"
echo "   docker compose -f docker-compose.prod.yml restart nginx"
echo ""
echo "3. If HestiaCP proxy is not configured:"
echo "   ./setup-hestia-proxy.sh"
echo ""
echo "4. Check HestiaCP proxy configuration:"
echo "   - Go to HestiaCP → Web → portal.glab.si → Edit"
echo "   - Ensure Proxy Support is enabled"
echo "   - Proxy URL should be: http://127.0.0.1:8088" 