FROM php:8.2-apache

RUN apt-get update && \
    apt-get install -y apache2-utils default-mysql-client libmariadb-dev-compat libmariadb-dev && \
    docker-php-ext-install pdo pdo_mysql mysqli && \
    a2enmod headers expires && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    rm -rf /var/lib/apt/lists/*

# Ajouter www-data au groupe users (GID 100) existant
RUN usermod -u 1000 www-data && \
    usermod -g 100 www-data && \
    chown -R www-data:users /var/www/html