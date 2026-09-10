FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Apache écoute sur 8080 (non-root friendly) plutôt que 80.
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
    && sed -i 's/:80>/:8080>/' /etc/apache2/sites-available/000-default.conf

COPY app/ /var/www/html/

# Exécution en non-root : www-data existe déjà dans l'image officielle,
# on lui donne la propriété du code et on bind sur un port non privilégié.
RUN chown -R www-data:www-data /var/www/html \
    && sed -i 's/^User .*/User www-data/' /etc/apache2/apache2.conf \
    && sed -i 's/^Group .*/Group www-data/' /etc/apache2/apache2.conf \
    && chown -R www-data:www-data /var/log/apache2 /var/run/apache2 /var/lock/apache2

USER www-data
EXPOSE 8080

HEALTHCHECK --interval=10s --timeout=3s --retries=3 --start-period=10s \
    CMD php -r "http_response_code(200); exit(@file_get_contents('http://127.0.0.1:8080/health.php') === false ? 1 : 0);"
