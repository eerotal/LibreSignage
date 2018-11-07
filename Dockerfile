FROM php:7.2-apache

USER root

# Setup the user 'docker' that's used to run apache2.
RUN useradd -r docker
RUN addgroup docker www-data

# Set up LibreSignage
COPY dist/ /var/www/html/

## Default file permissions.
RUN chown -R docker:docker "/var/www/html"
RUN find "/var/www/html" -type d -exec chmod 755 "{}" ";"
RUN find "/var/www/html" -type f -exec chmod 644 "{}" ";"

## Permissions for the 'data' directory.
RUN chown -R docker:www-data /var/www/html/data
RUN find "/var/www/html/data" -type d -exec chmod 775 "{}" ";"
RUN find "/var/www/html/data" -type f -exec chmod 664 "{}" ";"

## Install PHP extensions and dependencies.
RUN apt-get update

ARG imgthumbs="n"
RUN if [ "$imgthumbs" = "y" ]; then \
apt-get install -y \
libfreetype6-dev \
libjpeg62-turbo-dev \
libpng-dev \
&& docker-php-ext-configure gd \
--with-freetype-dir=/usr/include \
--with-jpeg-dir=/usr/include \
&& docker-php-ext-install -j$(nproc) gd; \
fi

ARG vidthumbs="n"
RUN echo "$vidthumbs"
RUN if [ "$vidthumbs" = "y" ]; then \
apt-get install -y ffmpeg; \
fi

# Configure PHP.
ARG debug=n
RUN if [ "$debug" = "y" ]; then \
cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/conf.d/01-dev.ini; \
else \
cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/conf.d/01-prod.ini; \
fi
COPY server/php/ls-docker.ini /usr/local/etc/php/conf.d/02-ls-docker.ini

# Configure apache2.
COPY server/apache2/ls-docker.conf /etc/apache2/conf-available/ls-docker.conf
RUN a2enconf --quiet ls-docker.conf
RUN a2enmod --quiet rewrite

EXPOSE 80
