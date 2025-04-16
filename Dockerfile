FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    build-essential \
    autoconf

# Install PHP extensions required by Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install PostgreSQL extension
RUN apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Install OpenTelemetry PHP extension from source
RUN pecl install opentelemetry-1.0.0beta3 && docker-php-ext-enable opentelemetry

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install Laravel dependencies
RUN composer install --optimize-autoloader --no-dev

# Expose port 8000 for php artisan serve
EXPOSE 8000

# Start Laravel server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

# CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000", "--threads=50"]
