FROM php:8.2.12-apache
COPY . /var/www/html
RUN mkdir -p /var/www/html/uploads/ccarchivos && \
    chown -R www-data:www-data /var/www/html/uploads && \
    chmod -R 755 /var/www/html/uploads
EXPOSE 80