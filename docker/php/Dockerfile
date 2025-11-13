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

# Aligner l'UID de www-data avec celui de l'utilisateur hôte (1000)
RUN usermod -u 1000 www-data

# Installer Composer depuis l'image officielle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le répertoire comme sûr pour Git (évite "dubious ownership")
RUN git config --global --add safe.directory /var/www/symfony

# Créer le dossier pour les uploads et donner les permissions à www-data
RUN mkdir -p /var/www/symfony/public/uploads/figures && \
    chown -R www-data:www-data /var/www/symfony/public/uploads/figures && \
    chmod -R 775 /var/www/symfony/public/uploads/figures

WORKDIR /var/www/symfony
