Deploying LibreSignage with Docker
##################################

LibreSignage is distributed via `Docker <https://www.docker.com/>`_
images among other ways, which makes it simple to deploy a containerized
LibreSignage instance. This document describes how to create and configure
a LibreSignage Docker container.

1. Starting a LibreSignage Docker container
-------------------------------------------

A LibreSignage docker container can be started by running::

    docker run \
        -d \
        -p 80:80
        --mount source=ls_vol,target=/var/www/html/data
        eerotal/libresignage:latest

You can also subsitute the *latest* tag with a specific version
to pull if you don't want to pull the latest one. This command
does a few things

1. If the LibreSignage image is not yet downloaded, it is pulled
   from Docker Hub.
2. After pulling the image, a container is started in daemon
   mode (*-d*). Port 80 on the host machine is mapped to port 80
   in the container (*-p 80:80*) and a Docker volume is created
   for storing LibreSignage instance data (*--mount ...*).

The started container is a very basic instance that's useful for testing
LibreSignage. However, if you intend to deploy LibreSignage onto a
production system, you'll want to do some additional configuration.

2. Configuring a LibreSignage Docker container
----------------------------------------------

See the help document *Configuration* or the file src/doc/rst/configuration.rst
in the source tree for information on how to configure a LibreSignage Docker
container.
