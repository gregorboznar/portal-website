#!/bin/bash

echo "Setting up Docker environment for Laravel..."

if [ ! -f .env ]; then
    cp .env.docker .env
    echo "✓ Created .env file from .env.docker"
else
    echo "! .env file already exists, skipping..."
fi

if [ ! -f .env ] || [ -z "$(grep APP_KEY .env | cut -d'=' -f2)" ]; then
    echo "Generating application key..."
    docker-compose run --rm app php artisan key:generate
fi

echo "Building Docker containers..."
docker-compose build

echo "Starting containers..."
docker-compose up -d

echo "Installing PHP dependencies..."
docker-compose exec app composer install

echo "Installing Node dependencies..."
docker-compose exec node npm install

echo "Running migrations..."
docker-compose exec app php artisan migrate

echo "Linking storage..."
docker-compose exec app php artisan storage:link

echo ""
echo "🎉 Setup complete!"
echo ""
echo "Your Laravel application is now running at:"
echo "  - Laravel app: http://localhost:8081"
echo "  - Vite dev server: http://localhost:5173"
echo ""
echo "To stop the containers: docker-compose down"
echo "To view logs: docker-compose logs -f" 