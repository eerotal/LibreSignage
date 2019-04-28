#
#  LibreSignage Dockerfile. This dockerfile builds a LibreSignage
#  image and configures any required software in the image. The
#  following build arguments can be used when invoking 'docker build':
#
#    vidthumbs = (y/n)  - Enable video thumbnail generation w/ ffmpeg.
#    imgthumbs = (y/n)  - Enable image thumbnail generation w/ PHP gd.
#    debug = (y/n)      - Enable debugging. This option selects whether
#                         to use PHP's prod or dev config. The development
#                         config is used when $debug == "y" and the prod
#                         config is used otherwise.
#    version = (string) - The version number used in the image labels.
#    logdir = (path)    - The log directory path for LibreSignage.
#    docroot = (path)   - The docroot for LibreSignage.
#

FROM php:7.2-apache

ARG imgthumbs="n"
ARG vidthumbs="n"
ARG debug="n"
ARG version="v0.0.0"
ARG logdir=""
ARG docroot=""

LABEL description="An open source digital signage solution."
LABEL version="$version"
LABEL maintainer="Eero Talus"
LABEL copyright="Copyright 2018 Eero Talus"
LABEL license="BSD 3-clause license"

USER root

# Sanity check install paths and setup users.
RUN if [ -z "$docroot" ]; then echo '[Error] Empty docroot path.'; exit 1; fi \
	&& if [ -z "$logdir" ]; then echo '[Error] Empty log dir path.'; exit 1; fi \
	&& useradd -r docker && addgroup docker www-data

# Copy LibreSignage files.
COPY --chown=docker:docker "dist/" "$docroot"

# Set default file permissions and create the log directory.
RUN chown -R docker:www-data "$docroot/data" \
	&& find "$docroot" -type d -print0 | xargs -0 chmod 755 \
	&& find "$docroot/data" -type d -print0 | xargs -0 chmod 775 \
	&& find "$docroot" -type f -print0 | xargs -0 chmod 644 \
	&& find "$docroot/data" -type f -print0 | xargs -0 chmod 664 \
	&& mkdir -p "$logdir" \
	&& chown root:www-data "$logdir" \
	&& chmod 775 "$logdir"

# Install dependencies for image thumbnail generation.
RUN if [ "$imgthumbs" = "y" ]; then \
		apt-get update \
		&& apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
		&& docker-php-ext-configure gd \
			--with-freetype-dir=/usr/include \
			--with-jpeg-dir=/usr/include \
		&& docker-php-ext-install -j$(nproc) gd; \
	fi

# Install dependencies for video thumbnail generation.
RUN if [ "$vidthumbs" = "y" ]; then \
		apt-get update \
		&& apt-get install -y ffmpeg; \
	fi

# Configure PHP.
RUN PHPDIR="/usr/local/etc/php"; \
	if [ "$debug" = "y" ]; then \
		cp "$PHPDIR/php.ini-development" "$PHPDIR/conf.d/01-dev.ini"; \
	else \
		cp "$PHPDIR/php.ini-production" "$PHPDIR/conf.d/01-prod.ini"; \
	fi
COPY server/php/ls-docker.ini /usr/local/etc/php/conf.d/02-ls-docker.ini

# Configure apache2.
COPY server/apache2/ls-docker.conf /etc/apache2/conf-available/ls-docker.conf

RUN a2enconf --quiet ls-docker.conf
RUN a2enmod --quiet rewrite

EXPOSE 80
