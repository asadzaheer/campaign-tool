web: composer install --optimize-autoloader --no-dev && php artisan config:clear && php artisan route:clear && php artisan cache:clear && php artisan storage:link && php artisan migrate --force && chmod -R 777 storage bootstrap/cache && vendor/bin/heroku-php-apache2 -F fpm_custom.conf public/