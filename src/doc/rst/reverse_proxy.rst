#########################################
Using LibreSignage behind a reverse proxy
#########################################

A common way to run a server is to run the actual server software behind
a `reverse proxy <https://en.wikipedia.org/wiki/Reverse_proxy>`_. This document
is about configuring an nginx reverse proxy to work with LibreSignage.
Configuration examples are only provided for nginx as that's the most commonly
used software for reverse proxies. Apache can also be used as a reverse proxy
and the configuration needed for that is somewhat similar to the required nginx
configuration.

In the examples in this document a LibreSignage server is running on
``localhost`` on port ``8080``. If you are running a LibreSignage Docker
container, you can bind the container to listen on port ``8080`` by passing
``-p 8080:80`` to ``docker run``.

If you want to run the native build of LibreSignage on a port other than ``80``,
you need to manually edit the Apache VHost configuration file. To make apache
listen on port ``8080``, for example, you need to add ``Listen 8080`` at the
start of the VirtualHost config file and change the port number in the
VirtualHost tag to ``8080`` like this: ``<VirtualHost *:8080>``.

Configuring an nginx reverse proxy (without SSL)
------------------------------------------------

A sample nginx reverse proxy configuration *without SSL* support is included
below.

::

	server {
		listen 80;
		server_name example.com;

		location / {
			proxy_http_version 1.1;
			proxy_set_header Host $http_host;
			proxy_set_header X-Real-IP $remote_addr;
			proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
			proxy_pass http://localhost:8080/;

			client_max_body_size 200M;
		}
	}

This configuration creates an nginx reverse proxy that listens on
``example.com:80`` (or ``http://example.com``) and proxies all requests to
the LibreSignage server running on ``localhost:8080``.

Configuring an nginx reverse proxy (with SSL)
---------------------------------------------

A sample nginx reverse proxy configuration *with SSL* support is included
below.

::

	server {
		listen 443 ssl;
		server_name example.com;
		ssl_certificate /path/to/ssl/certificate.cert;
		ssl_certificate_key /path/to/ssl/certificate/key.key;

		location / {
			proxy_http_version 1.1;
			proxy_set_header Host $http_host;
			proxy_set_header X-Real-IP $remote_addr;
			proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
			proxy_set_header X-Forwarded-Proto $scheme;
			proxy_pass http://localhost:8080/;
			proxy_redirect http:// $scheme://;

			client_max_body_size 200M;
		}
	}

Replace the SSL certificate paths with proper paths on your system.

This configuration creates an nginx reverse proxy that listens on
``example.com:443`` (or ``https://example.com``) and proxies all requests to
the LibreSignage server running on ``localhost:8080``.
