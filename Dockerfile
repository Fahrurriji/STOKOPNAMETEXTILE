FROM dunglas/frankenphp:php8.4.21-bookworm

# Enable MySQLi and PDO MySQL extensions
RUN install-php-extensions mysqli pdo_mysql

# Copy application files
COPY . /app

WORKDIR /app

# Expose port
EXPOSE 80

# Start FrankenPHP
CMD ["frankenphp", "run", "--bind", "0.0.0.0:80"]
