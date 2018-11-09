##################################
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
        [!TODO!]/libresignage:latest

This command does a few things

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

All LibreSignage config files are located in the directory *config/* in
the LibreSignage install directory, which is */var/www/html* in Docker
containers. By default the directory tree in *config/* is as follows::

    config
        --> conf
            --> 00-default.php  // Default general config.
        --> limits
            --> 00-default.php  // Default server limits.
        --> quota
            --> 00-default.php  // Default quota config.

You can access the config directory by bind mounting it onto your host
machine. You can do this by adding the following switch to your
``docker run`` command::

    -v [HOSTDIR]:/var/www/html/config

Replace ``[HOSTDIR]`` with a directory path on the host machine. After
you run the container with this switch, you can access the config files
in the container by navigating to ``[HOSTDIR]``.

The *00-default.php* files contain the default LibreSignage configuration.
*You should not edit these files directly.* You should instead create new
files prefixed with a double-digit number, eg. *01-custom.php* in each
of the subdirectories. The config files are read in alphabetically
increasing order, so numbering them makes the order obvious. In this case
*00-default.php* is read first and *01-custom.php* is read second.

As the config files are actually PHP files, they must be valid PHP. A
custom config file must start with the PHP tag ``<?php`` and it must
return an array with the config options to modify like so::

    <?php
    
    return [
        'OPTION_TO_MODIFY' => 'NEW_VALUE'
    ];

You can read through the default config files to get an idea of the
available config options and what they do.

3. First steps
--------------

First of all, you **need** to at least change the passwords for the
default users and/or create new ones. The default usernames/passwords
are listed in the Readme. You can do this in the web interface from
the *User Manager* page when you're logged in as an admin user.

You may also want to change the default admin contact info to something
meaningful. This information is shown to users of the system on the main
*Control Panel* page. Keeping the contact info up-to-date is recommended
so that users can report any problems to the server admin via email. There
are two config options that affect the admin contact info:

  * *ADMIN_NAME* - The name of the admin.
  * *ADMIN_EMAIL* - The email address of the admin.

These are the most important things to configure. Now you can read
through the documentation and start learning the system. Have fun!
