FROM php:8.3-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . /app

RUN mkdir -p uploads storage/worksheets \
    && chmod -R 777 uploads storage/worksheets

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
