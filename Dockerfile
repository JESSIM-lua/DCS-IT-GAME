FROM php:8.3-apache

# Activer les modules nécessaires
RUN a2enmod ssl && a2enmod rewrite

# Copier les certificats SSL
COPY certs/server.crt /etc/ssl/certs/server.crt
COPY certs/server.key /etc/ssl/private/server.key

# Copier la configuration Apache
COPY apache-ssl.conf /etc/apache2/sites-available/default-ssl.conf

# Activer la configuration SSL
RUN a2ensite default-ssl.conf

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install mysqli pdo pdo_mysql

EXPOSE 8046 443
