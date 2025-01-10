FROM ubuntu:latest
LABEL authors = "Ivan Zorin <creator@localzet.com>"
LABEL org.opencontainers.image.source = "https://github.com/Triangle-org/Engine"
LABEL org.opencontainers.image.description = "Triangle Docker Image"
LABEL org.opencontainers.image.licenses = "AGPL-3.0-or-later"

RUN apt-get update && apt-get install -y \
    software-properties-common && \
    add-apt-repository ppa:ondrej/php && \
    apt-get update && apt-get install -y \
    php8.3 \
    php8.3-bcmath \
    php8.3-cli \
    php8.3-common \
    php8.3-curl \
    php8.3-dev \
    php8.3-gd \
    php8.3-intl \
    php8.3-mbstring \
    php8.3-mongodb \
    php8.3-mysql \
    php8.3-opcache \
    php8.3-pgsql \
    php8.3-redis \
    php8.3-sqlite3 \
    php8.3-swoole \
    php8.3-zip \
    php8.3-zstd \
    git \
    unzip \
    curl \
    build-essential \
    libev-dev \
    libevent-dev \
    libuv1-dev \
    libssl-dev \
    pkg-config && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

RUN pecl install ev event uv && \
    echo "extension=ev" > /etc/php/8.3/cli/conf.d/ev.ini && \
    echo "extension=event" > /etc/php/8.3/cli/conf.d/event.ini && \
    echo "extension=uv" > /etc/php/8.3/cli/conf.d/uv.ini

WORKDIR /opt/triangle

COPY docker/php.ini /etc/php/8.3/cli/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer create-project triangle/web /opt/triangle && \
    composer update

CMD ["php", "/opt/triangle/master", "start"]

