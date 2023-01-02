ARG PHP_VERSION

FROM php:${PHP_VERSION}-fpm

ARG TZ
ARG USER_ID
ARG GROUP_ID

RUN apt-get update && apt-get install --no-install-recommends --no-install-suggests -y \
        git \
        graphviz \
        curl \
        openssl \
        htop \
        libmemcached-dev \
        lsb-release \
        cron\
        unzip \
        nano \
        libxslt-dev \
        libicu-dev \
        zlib1g-dev \
        libssl-dev \
        pkg-config \
        procps \
        libzip-dev \
        libpq-dev \
        libpng-dev \
        libwebp-dev \
        libjpeg-dev \
        libfreetype-dev \
        librabbitmq-dev \
        libssh-dev \
        libsodium-dev \
        zsh \
    && rm -rf /var/lib/apt/lists/*

RUN set -xe \
    && docker-php-ext-configure \
        intl \
    && docker-php-ext-configure \
        gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
        intl \
        gd \
        opcache \
        pdo \
        pdo_pgsql \
        bcmath \
        sockets \
        sodium \
        zip \
        xsl \
    && pecl install \
        apcu \
        xdebug \
        redis \
        memcached \
        amqp \
        && rm -rf /tmp/pear \
    && docker-php-ext-enable \
        apcu \
        xdebug \
        redis \
        memcached \
        amqp

RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

COPY ./php.ini /usr/local/etc/php/php.ini
RUN sed -i -e "s#TIMEZONE#${TZ}#g" /usr/local/etc/php/php.ini

COPY ./xdebug.ini /tmp/xdebug.ini
RUN cat /tmp/xdebug.ini >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN rm /tmp/xdebug.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER 1

RUN mkdir -p /var/www/.composer && chown -R www-data /var/www/.composer

RUN usermod -u ${USER_ID} www-data && \
    groupmod -g ${GROUP_ID} www-data

WORKDIR /app

USER www-data

CMD ["php-fpm"]
