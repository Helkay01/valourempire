# Use the official PHP image with Apache
FROM php:8.2-apache

# Install required system packages and PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && docker-php-ext-enable pdo_pgsql pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite (useful for many PHP frameworks)
RUN a2enmod rewrite

# Copy application files to the container
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Change permissions if needed (optional)
RUN chown -R www-data:www-data /var/www/html/
