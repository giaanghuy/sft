# Dockerfile cho ứng dụng PHP
FROM php:8.1-apache

# Cài đặt các extension PHP cần thiết
RUN docker-php-ext-install pdo pdo_mysql

# Bật mod rewrite cho Apache
RUN a2enmod rewrite

# Cấu hình Apache DocumentRoot trỏ đến thư mục public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Cấu hình AllowOverride để .htaccess hoạt động
RUN sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Tránh cảnh báo "Could not reliably determine the server's fully qualified domain name"
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf

# Cấu hình PHP default charset UTF-8 (mbstring.internal_encoding deprecated từ PHP 8.1, chỉ dùng default_charset)
RUN echo "default_charset = UTF-8" >> /usr/local/etc/php/conf.d/charset.ini

# Copy source code vào container
COPY . /var/www/html/

# Copy và set quyền cho entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set quyền cho thư mục
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80

# Sử dụng entrypoint script
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

