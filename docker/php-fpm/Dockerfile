ARG PHP_VERSION

FROM php:${PHP_VERSION}-fpm

ARG TZ
ARG USER_ID
ARG GROUP_ID
ARG NODEJS_VERSION

RUN apt-get update && apt-get install --no-install-recommends --no-install-suggests -y \
        gpg \
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
        zsh

RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource.gpg.key | gpg --dearmor | tee /usr/share/keyrings/nodesource.gpg >/dev/null \
    && gpg --no-default-keyring --keyring /usr/share/keyrings/nodesource.gpg --list-keys \
    && echo "deb [signed-by=/usr/share/keyrings/nodesource.gpg] https://deb.nodesource.com/node_${NODEJS_VERSION} $(lsb_release -s -c) main" | tee /etc/apt/sources.list.d/nodesource.list \
    && echo "deb-src [signed-by=/usr/share/keyrings/nodesource.gpg] https://deb.nodesource.com/node_${NODEJS_VERSION} $(lsb_release -s -c) main" | tee -a /etc/apt/sources.list.d/nodesource.list \
    && apt-get update && apt-get install --no-install-recommends --no-install-suggests -y nodejs \
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
