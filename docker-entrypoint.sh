#!/bin/sh
set -e

# Install PHP dependencies if vendor folder doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Install NPM dependencies & Puppeteer Chromium if node_modules doesn't exist
if [ ! -d "node_modules" ]; then
    echo "Installing node dependencies..."
    npm install
    echo "Installing Puppeteer Chromium..."
    npx puppeteer install
fi

# Build frontend assets if public/build doesn't exist
if [ ! -d "public/build" ]; then
    echo "Building frontend assets..."
    npm run build
fi

# Generate app key if not set in .env
if ! grep -q "APP_KEY=base64:" .env; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Set permissions
chmod -R 777 storage bootstrap/cache

# Wait for database connection
echo "Waiting for database..."
until php -r "
    \$host = getenv('DB_HOST') ?: 'db';
    \$db   = getenv('DB_DATABASE') ?: 'amcortex';
    \$user = getenv('DB_USERNAME') ?: 'amcortex_user';
    \$pass = getenv('DB_PASSWORD') ?: 'AMCortex_Secure_P@ssw0rd_2026!';
    try {
        new PDO(\"mysql:host=\$host;dbname=\$db\", \$user, \$pass);
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
"; do
    sleep 2
done

echo "Database connected successfully!"

# Run migrations automatically
echo "Running database migrations..."
php artisan migrate --force

echo "Starting PHP-FPM..."
exec php-fpm
