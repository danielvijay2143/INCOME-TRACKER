# Use official PHP image with Apache
FROM php:8.2-apache

# Install MySQL extensions for PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache rewrite module (useful for routing)
RUN a2enmod rewrite

# Copy all your PHP code into the web server directory
COPY . /var/www/html/

# Expose HTTP port
EXPOSE 80
