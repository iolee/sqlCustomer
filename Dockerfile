# Use an official PHP image with Apache
FROM php:8.2-apache

# Install the MySQL extension for PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite (useful for many PHP projects)
RUN a2enmod rewrite

# Copy your project files into the container
COPY . /var/www/html/

# Set permissions so Apache can read your files
RUN chown -R www-data:www-data /var/www/html