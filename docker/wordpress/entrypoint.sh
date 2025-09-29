#!/bin/bash
set -e

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
while ! nc -z db 3306; do
  sleep 1
done
echo "MySQL is ready!"

echo "Running composer install in root directory..."
cd /var/www/html
composer install

echo "Running composer install in theme directory..."
cd /var/www/html/web/app/themes/limerock
composer install
npm install
npm run build
cd /var/www/html

# Execute the command passed to the container
exec "$@" 