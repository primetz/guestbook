ARG PHP_VERSION

FROM php:${PHP_VERSION}-fpm

ARG TZ
ARG USER_ID
ARG GROUP_ID

RUN apt-get update && apt-get install --no-install-recommends --no-install-suggests -y \
        git \
        curl \
        openssl \
        htop \
        libmemcached-dev \
        cron\
        unzip \
        nano \
        libxslt-dev \
        libicu-dev \
        zlib1g-dev \
        libssl-dev \
        pkg-config \
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
        supervisor \
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
        pcntl \
        bcmath \
        sockets \
        sodium \
        zip \
        xsl \
    && pecl install \
        apcu \
        redis \
        memcached \
        amqp \
        && rm -rf /tmp/pear \
    && docker-php-ext-enable \
        apcu \
        redis \
        memcached \
        amqp

COPY ./php.ini /usr/local/etc/php/php.ini
RUN sed -i -e "s#TIMEZONE#${TZ}#g" /usr/local/etc/php/php.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER 1

RUN addgroup --gid ${GROUP_ID} guestbook && \
  adduser -uid ${USER_ID} --gid ${GROUP_ID} guestbook


COPY supervisord.conf /etc/supervisord.conf

ENTRYPOINT ["/usr/bin/supervisord", "-n", "-c",  "/etc/supervisord.conf"]

WORKDIR /etc/supervisor/conf.d/
