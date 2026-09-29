# KolegaOps — image PHP + Apache untuk deploy di Coolify (Docker).
FROM php:8.2-apache

# Ekstensi MySQL untuk PDO.
RUN docker-php-ext-install pdo_mysql

WORKDIR /var/www/html
COPY . .

# init.php dijalankan sekali sebelum Apache menyala (buat tabel + data awal).
RUN chown -R www-data:www-data /var/www/html \
    && chmod +x docker-entrypoint.sh

EXPOSE 80
ENTRYPOINT ["./docker-entrypoint.sh"]
