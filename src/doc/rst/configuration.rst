LibreSignage configuration
##########################

1. First steps after installation
---------------------------------

The most important thing to do after installing LibreSignage is to change the
default passwords for the initial users or create entirely new ones. The default
usernames and passwords are listed in the readme. As an admin user you can create
new users and delete old ones from the *User Manager* page. You can change the
password of the logged in user from the *Settings* page.

2. Build time configuration
---------------------------

2.1. Media uploader thumbnails
++++++++++++++++++++++++++++++

The LibreSignage web interface includes a media uploader that can be used to
upload files to the LibreSignage server. These files can then be embedded in
slides to create more rich content.

When building LibreSignage, the configuration script asks the user whether
image and video thumbnail generation should be enabled. These can be enabled
independent of each other. There are, however, some additional dependencies
needed for both of them.

Image thumbnails:

* The PHP extension `gd` is required for image thumbnail generation. On Debian
  and Ubuntu it can be installed by installing the `php-gd` package from the
  distribution repos.

Video thumbnails:

* The `ffmpeg` and `ffprobe` are required for video thumbnail generation. On
  Debian and Ubuntu they can be installed by installing the `ffmpeg` package
  from the distribution repos. You also need to configure the binary paths to
  these in the LibreSignage configuration files. The config values you need to
  modify are `FFMPEG_PATH` and `FFPROBE_PATH`.

  The selection made during build time is not final; thumbnail generation can be
  enabled or disabled later by modifying the config files.

3. Configuration files
----------------------

All LibreSignage config files are located in the directory *config/* in
the LibreSignage install directory. The install directory on a native install
is by default */var/www/<domain>*. In Docker containers the install directory
is always */var/www/html* inside the container. The directory tree in *config/*
is as follows::

    config
        --> conf
            --> 00-default.php  // Default general config.
        --> limits
            --> 00-default.php  // Default server limits.
        --> quota
            --> 00-default.php  // Default quota config.

The *00-default.php* files contain the default LibreSignage configuration.
**You should not edit these files directly.** You should instead create custom
config files to override any combination of the default configuration values.
Custom config files should be named so that they have an integer prefix followed
by a dash and a freeform filename, eg. *01-custom.php*. The config files are read
in alphabetically increasing order, so numbering them makes the order obvious.
In this case *00-default.php* is read first and *01-custom.php* is read second.

As the config files are actually PHP files, they must be valid PHP. A custom
config file must start with the PHP tag ``<?php`` and it must return an array
with the config options to modify like so::

    <?php

    return [
        'OPTION_TO_MODIFY' => 'NEW_VALUE'
    ];

You can read through the default config files to get an idea of the
available config options and what they do.

3.2. Accessing configuration files in a Docker container
++++++++++++++++++++++++++++++++++++++++++++++++++++++++

You can modify the config files of a LibreSignage Docker container by using
Docker volumes. To create a Docker volume that points to the internal *config/*
directory, you can add the following CLI switch to your ``docker run`` command::

    --mount source=ls_config,target=/var/www/html/config

After starting the container, you can find out the path where the volume is
mounted by running ``docker volume inspect ls_config``. This command should
print something like the following::

    [
        {
            "CreatedAt": "2020-02-14T21:21:52+02:00",
            "Driver": "local",
            "Labels": null,
            "Mountpoint": "/var/lib/docker/volumes/ls_config/_data",
            "Name": "ls_config",
            "Options": null,
            "Scope": "local"
        }
    ]

The mountpoint is the path after ``"Mountpoint":``. If you navigate into that
directory on the host machine, you should see the contents of the *config/*
directory in the container.
