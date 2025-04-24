FROM php:8.4-fpm

# Установка зависимостей
RUN apt-get update && apt-get install -y \
    nginx \
    unzip \
    git \
    curl \
    libzip-dev \
    libpq-dev \
    libxml2-dev \
    libssl-dev \
    libonig-dev \
    libsqlite3-dev \
    && docker-php-ext-install zip pdo pdo_mysql pdo_sqlite mbstring xml intl opcache \
    && pecl install redis xdebug \
    && docker-php-ext-enable redis xdebug \
    && rm -rf /var/lib/apt/lists/*

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Конфиги
COPY nginx.conf /etc/nginx/nginx.conf

# Копируем только файлы зависимостей — для кэширования слоёв
COPY composer.json composer.lock /var/www/html/

# Устанавливаем зависимости
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Копируем код
COPY . /var/www/html
RUN cp .env.example .env

WORKDIR /var/www/html

# Открываем порт 8080
EXPOSE 8080

CMD service nginx start && php-fpm
