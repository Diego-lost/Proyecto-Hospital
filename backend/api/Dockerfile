FROM php:8.3-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
  && docker-php-ext-install pdo_pgsql pgsql mbstring bcmath zip \
  && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
  && chmod +x docker/render-start.sh \
  && chmod -R 775 storage bootstrap/cache

ENV PORT=10000
EXPOSE 10000

CMD ["docker/render-start.sh"]
