FROM php:8.4-fpm

RUN getent group www-data || groupadd -g 1000 www-data && \
    id -u www-data || useradd -u 1000 -g www-data -m -s /bin/bash www-data

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
	pkg-config \
    && docker-php-ext-install zip pdo pdo_mysql pdo_sqlite mbstring xml intl opcache \
    && pecl install redis xdebug \
	&& pecl install mongodb \
	&& docker-php-ext-enable mongodb \
    && docker-php-ext-enable redis xdebug \
    && rm -rf /var/lib/apt/lists/*

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


# Копируем только файлы зависимостей — для кэширования слоёв
COPY composer.json composer.lock /var/www/html/

# Устанавливаем зависимости
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Latest releases available at https://github.com/aptible/supercronic/releases
ENV SUPERCRONIC_URL=https://github.com/aptible/supercronic/releases/download/v0.2.29/supercronic-linux-amd64 \
    SUPERCRONIC=supercronic-linux-amd64 \
    SUPERCRONIC_SHA1SUM=cd48d45c4b10f3f0bfdd3a57d054cd05ac96812b

RUN curl -fsSLO "$SUPERCRONIC_URL" \
 && echo "${SUPERCRONIC_SHA1SUM}  ${SUPERCRONIC}" | sha1sum -c - \
 && chmod +x "$SUPERCRONIC" \
 && mv "$SUPERCRONIC" "/usr/local/bin/${SUPERCRONIC}" \
 && ln -s "/usr/local/bin/${SUPERCRONIC}" /usr/local/bin/supercronic

# You might need to change this depending on where your crontab is located
COPY crontab crontab

# Копируем код
COPY . /var/www/html
RUN cp .env.example .env

WORKDIR /var/www/html

RUN chown -R www-data:www-data /var

# Конфиги
COPY nginx.conf /etc/nginx/nginx.conf

# Открываем порт 8080
EXPOSE 8080

USER root
CMD ["sh", "-c", "service nginx start && php-fpm & supercronic /var/www/html/crontab"]

