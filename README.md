cp .env.example .env

vim .env

composer install

php artisan key:generate

cd app/

php artisan serve
