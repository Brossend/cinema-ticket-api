FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
        git \
        unzip \
        postgresql-dev \
        libzip-dev \
        oniguruma-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-install \
        pdo_pgsql \
        mbstring \
        zip \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install \
    --no-interaction \
    --prefer-dist \
    --no-scripts

COPY . .

RUN chown -R www-data:www-data storage bootstrap/cache

CMD ["php-fpm"]
