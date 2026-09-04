FROM php:8.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends git unzip libsqlite3-dev libzip-dev libicu-dev libxml2-dev libcurl4-openssl-dev libonig-dev \
    && docker-php-ext-install bcmath curl intl mbstring opcache pdo_sqlite xml zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=node:22-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

WORKDIR /app
COPY composer.json composer.lock package.json package-lock.json* ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts
RUN npm install
RUN npx playwright install --with-deps chromium
COPY . .
RUN mkdir -p database storage/logs storage/framework/cache storage/framework/sessions storage/framework/views \
    && touch database/database.sqlite \
    && composer dump-autoload --no-dev --optimize \
    && npm run build

ENV APP_ENV=production APP_DEBUG=false DB_CONNECTION=sqlite DB_DATABASE=/app/database/database.sqlite
EXPOSE 10000
CMD ["sh", "-c", "php artisan migrate --force && (API_URL=${API_URL:-https://cf-manager-ykl2.onrender.com} node scripts/tiktok-grabber.cjs > storage/logs/tiktok-grabber.log 2>&1 &) && exec php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]
