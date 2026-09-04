FROM php:8.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends git unzip libsqlite3-dev libzip-dev \
    && docker-php-ext-install pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=node:22-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

WORKDIR /app
COPY composer.json composer.lock package.json package-lock.json* ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
RUN npm install
COPY . .
RUN mkdir -p database storage/framework/cache storage/framework/sessions storage/framework/views \
    && touch database/database.sqlite \
    && npm run build

ENV APP_ENV=production APP_DEBUG=false DB_CONNECTION=sqlite DB_DATABASE=/app/database/database.sqlite
EXPOSE 10000
CMD ["sh", "-c", "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]
