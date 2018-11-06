FROM php:7.2-apache

USER root

# Setup the user 'docker' that's used to run apache2.
RUN useradd -r docker
RUN addgroup docker www-data

RUN apt-get update && apt-get install -y ffmpeg

COPY dist/ /var/www/html/

# Default file permissions.
RUN chown -R docker:docker "/var/www/html"
RUN find "/var/www/html" -type d -exec chmod 755 "{}" ";"
RUN find "/var/www/html" -type f -exec chmod 644 "{}" ";"

# Permissions for the 'data' directory.
RUN chown -R docker:www-data /var/www/html/data
RUN find "/var/www/html/data" -type d -exec chmod 775 "{}" ";"
RUN find "/var/www/html/data" -type f -exec chmod 664 "{}" ";"

# Configure apache2.
COPY server/conf-available/ls-docker.conf /etc/apache2/conf-available/.
RUN a2enconf --quiet ls-docker.conf
RUN a2enmod --quiet rewrite
RUN apache2ctl restart

EXPOSE 80
