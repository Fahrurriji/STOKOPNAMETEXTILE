FROM dunglas/frankenphp:php8.4.21-bookworm

# Enable MySQLi and PDO MySQL extensions
RUN install-php-extensions mysqli pdo_mysql

# Copy application files
COPY . /app

WORKDIR /app

# Expose port
EXPOSE 80

# Start FrankenPHP (FrankenPHP runs on port 80 by default)
CMD ["frankenphp", "run"]
