FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Copy project files into container
COPY . /var/www/html/

# Enable Apache mod_rewrite
RUN a2enmod rewrite
