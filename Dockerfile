FROM dunglas/frankenphp:php8.4-bookworm

# Enable MySQLi and PDO MySQL extensions
RUN install-php-extensions mysqli pdo_mysql

# Copy application files
COPY . /app

WORKDIR /app

# Copy Caddyfile
COPY Caddyfile /etc/caddy/Caddyfile

# Start FrankenPHP with config
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
