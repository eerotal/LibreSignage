#
#  LibreSignage Dockerfile. This dockerfile builds a LibreSignage
#  image and configures any required software in the image. The
#  following build arguments can be used when invoking 'docker build':
#
#    vidthumbs = (y/n) - Enable video thumbnail generation w/ ffmpeg.
#    imgthumbs = (y/n) - Enable image thumbnail generation w/ PHP gd.
#    debug = (y/n)     - Enable debugging. This option selects whether
#                        to use PHP's prod or dev config. The development
#                        config is used when $debug == "y" and the prod
#                        config is used otherwise.
#
#  LibreSignage is installed in /var/www/html in Docker images. Virtual
#  Hosts are not used since that functionality is not needed.
#

FROM php:7.2-apache

ARG imgthumbs="n"
ARG vidthumbs="n"
ARG debug="n"
ARG version="v0.0.0"

LABEL description="An open source digital signage solution."
LABEL version="$version"
LABEL maintainer="Eero Talus"
LABEL copyright="Copyright 2018 Eero Talus"
LABEL license="BSD 3-clause license"

USER root

# Setup the user 'docker' that's used to run apache2.
RUN useradd -r docker
RUN addgroup docker www-data

# Set up LibreSignage
COPY --chown=docker:docker dist/ /var/www/html/

## Default file permissions.
RUN find "/var/www/html" -type d -exec chmod 755 "{}" ";" \
&& find "/var/www/html" -type f -exec chmod 644 "{}" ";" \
&& chown -R docker:www-data /var/www/html/data \
&& find "/var/www/html/data" -type d -exec chmod 775 "{}" ";" \
&& find "/var/www/html/data" -type f -exec chmod 664 "{}" ";"

## Install PHP extensions and dependencies.
RUN if [ "$imgthumbs" = "y" ] || [ "$vidthumbs" = "y" ]; then \
apt-get update; \
fi

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

RUN if [ "$vidthumbs" = "y" ]; then \
apt-get install -y ffmpeg; \
fi

# Configure PHP.
RUN if [ "$debug" = "y" ]; then \
cp /usr/local/etc/php/php.ini-development \
	/usr/local/etc/php/conf.d/01-dev.ini; \
else \
cp /usr/local/etc/php/php.ini-production \
	/usr/local/etc/php/conf.d/01-prod.ini; \
fi
COPY server/php/ls-docker.ini \
	/usr/local/etc/php/conf.d/02-ls-docker.ini

# Configure apache2.
COPY server/apache2/ls-docker.conf \
	/etc/apache2/conf-available/ls-docker.conf

RUN a2enconf --quiet ls-docker.conf
RUN a2enmod --quiet rewrite

EXPOSE 80
