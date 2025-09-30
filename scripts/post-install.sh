echo "Running composer install in theme directory..."
cd ./web/app/themes/limerock
composer install
npm install
npm run build