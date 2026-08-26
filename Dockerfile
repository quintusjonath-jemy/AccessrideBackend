FROM php:8.2-apache

# Install required PHP extensions for MySQL (PDO, MySQLi, mbstring, curl)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite & mod_headers
RUN a2enmod rewrite headers

# Copy application files to Apache root
COPY . /var/www/html/

# Set proper permissions for upload folders
RUN chmod -R 777 /var/www/html/login/uploads \
    /var/www/html/history_and_profile/uploads \
    /var/www/html/logs || true

# Set Apache DocumentRoot port binding for Render (reads $PORT or defaults to 80)
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

EXPOSE 80 10000

CMD ["apache2-foreground"]
