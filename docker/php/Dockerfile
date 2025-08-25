FROM php:8.1-fpm

# Installer dépendances système utiles
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql \
    zip

# Installer Composer depuis l'image officielle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le répertoire comme sûr pour Git (évite "dubious ownership")
RUN git config --global --add safe.directory /var/www/symfony

WORKDIR /var/www/symfony
