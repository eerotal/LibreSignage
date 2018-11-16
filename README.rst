######################################################
LibreSignage - An open source digital signage solution
######################################################

.. image:: https://travis-ci.org/eerotal/LibreSignage.svg?branch=master
    :target: https://travis-ci.org/eerotal/LibreSignage

Table Of Contents
-----------------

`1. Introduction`_

`2. Features`_

`3. Project goals`_

`4. Installation`_

* `4.1. Using prebuilt Docker images`_

* `4.2. Building from source`_

  * `4.2.1. Building a native build`_

  * `4.2.2. Building a Docker image`_

`5. Default users`_

`6. How to install npm`_

`7. FAQ`_

`8. Screenshots`_

`9. Make rules`_

`10. Documentation`_

`11. Third-party dependencies`_

`12. Build system dependencies`_

`13. License`_

1. Introduction
---------------

Digital Signage is everything from large-scale commercial billboards
to smaller advertisement displays, notice boards or digital restaurant
menus. The possibilities of digital signage are endless. If you need
to display periodically changing content to users on a wall-mounted
TV for example, digital signage is probably what you are looking for.

LibreSignage is a free and open source, lightweight and easy-to-use
digital signage solution for use in schools, cafés, restaurants and
shops among others. LibreSignage can be used to manage a network of
digital signage displays. Don't let the word network fool you though;
a network can be as small as one display on an office wall or as big
as 50+ displays spread throughout a larger building.

LibreSignage also includes multi-user support with password authentication
and configurable access control to specific features. If a school wants
to setup a digital notice board system for example, they might give
every teacher an account with slide editing permissions so that teachers
could manage the content on the internal digital signage network. This
way the teachers could inform students about important things such as
upcoming tests for example.

LibreSignage uses a HTTP web server to serve content to the individual
signage displays. This means that he displays only need to run a web
browser pointed to the central LibreSignage server to actually display
content. This approach has a few advantages.

1. It's simple - No specific hardware/software platform is required.
   Any system with a fairly recent web browser works.
2. It's cheap - You don't necessarily need to buy lots of expensive
   equipment to get started. Just dust off the old PC in the closet,
   install an up-to-date OS like Linux on it, install a web browser,
   hide the mouse pointer by default and connect the system to a
   display. That's it. The only other thing you need is the server,
   which in fact can run on the same system if needed.
3. It's reliable - The web infrastructure is already implemented and
   well tested so why not use it.
4. It makes editing easy - Displaying content in a browser has the
   advantage of making slide previewing very simple. You can either
   use the 'Live Preview' in the editor or check the exact results
   from the actual 'Display' page that's displayed on the clients too.

2. Features
-----------

* Web interface for editing slides and managing the LibreSignage instance.
* Many per slide settings like durations, transitions, etc.
* Special markup syntax for easily formatting slides.
* Live preview of the slide markup in the slide editor.
* Support for embedding remote or uploaded image and video files.
* Support for scheduling specific slides for a specific time-frame.
* Collaboration features with other users on the network.
* Separate slide queues for different sets of signage clients.
* Multi user support with configurable access control.
* User management features for admin users in the web interface.
* Configurable quota for the amount of slides a user can create.
* Rate limited API for reducing server load.
* Extensive documentation of features including docs for developers.
* Extensive configuration possibilities.

3. Project goals
----------------

* Create a lightweight alternative to other digital signage solutions.
* Create a system that's both easy to set up and easy to use.
* Write a well documented and modular API so that implementing new
  user interfaces is simple.
* Avoid scope creep.
* Document all features.
* Keep it simple.

4. Installation
---------------

4.1. Using prebuilt Docker images
+++++++++++++++++++++++++++++++++

You can easily deploy a containerized LibreSignage instance using the
LibreSignage Docker images from Docker hub. The required steps are
listed below.

1. Install `Docker <https://www.docker.com/>`_ if it's not installed yet.
2. Run the following command, but replace ``[VERSION]`` with the
   LibreSignage version you want to pull::

       docker run \
           -d \
           -p 80:80 \
           --mount source=ls_vol,target=/var/www/html/data \
           eerotal/libresignage:[VERSION]

   This command pulls the LibreSignage image from Docker Hub, binds port
   80 on the host system to the container's port 80 (*-p*) and
   creates a volume *ls_vol* for storing LibreSignage data (*--mount*).
3. Navigate to *localhost* and you should see the LibreSignage login
   page. The file *src/docs/rst/docker.rst* in the LibreSignage source
   distribution contains a more detailed explanation of using the
   LibreSignage Docker image. The documentation can also be accessed in
   the web interface from the *Help* page.

4.2. Building from source
+++++++++++++++++++++++++

4.2.1. Building a native build
..............................

Building LibreSignage from source isn't too difficult. You can build
a native LibreSignage build that runs on the host machine (ie. no
containers) by following the instructions below. Note that currently
the LibreSignage build system only supports Debian.

1. Install software needed for building LibreSignage. You will need the
   following packages: ``git, apache2, php7.2, pandoc, npm, make``. On
   Debian Stretch all other packages except *npm* can installed by running
   ``sudo apt install git apache2 php7.2 pandoc make``. Note the following:

     * PHP needs to be version 7.2.
     * GNU Make needs to be version 4.0 or newer.
     * The recommended *npm* version is 6.4.x. Currently *npm* is only
       available in the Debian Sid repos and even there the package is
       so old it doesn't work correctly. You can, however, install npm
       manually. See `6. How to install NPM`_ for more info.

   More detailed build dependency versions are listed in `12. Build
   system dependencies`_. There are also some optional dependencies:

   * If you want to enable video thumbnail generation, you'll
     need to install ``ffmpeg`` and ``ffprobe``. On Debian you can 
     install them running ``sudo apt install ffmpeg``.

2. Use ``cd`` to move to the directory where you want to download the
   LibreSignage repository.
3. Run ``git clone https://github.com/eerotal/LibreSignage.git``.
   The repository will be cloned into the directory *LibreSignage/*.
4. Run ``cd LibreSignage`` to move into the LibreSignage repository.
5. Install dependencies from NPM by running ``npm install``.
6. Run ``make configure TARGET=apache2-debian``. This script asks you
   to enter the following configuration values:

   * Install directory (default: /var/www)

     * The directory where LibreSignage is installed. A subdirectory
       is created in this directory.

   * Server name (domain)

     * The domain name to use for configuring apache2. If you
       don't have a domain and you are just testing the system,
       you can either use 'localhost', your machines LAN IP or
       a test domain you don't actually own. If you use a test
       domain, you can add it to your */etc/hosts* file to make
       it work on your machine.

   * Server name aliases

     * Domain name aliases for the server. Aliases make it possible
       to have the server respond from multiple domains. One useful
       way to use name aliases is to set *localhost* as the main
       domain and the LAN IP of the server as an alias. This would
       make it possible to connect to the server either by navigating
       to *localhost* on the host machine or by connecting to the LAN
       IP on the local network.

   * Admin name

     * Shown to users on the main page as contact info in case of
       any problems.

   * Admin email

     * Shown to users on the main page as contact info in case of
       any problems.

   * Enable image thumbnail generation (y/N)

     * Enable image thumbnail generation on the server. Currently
       image thumbnails are only generated for uploaded slide
       media. This option only works if the PHP GD extension is
       installed and enabled. You can check whether it's enabled
       by running ``php -m``. If *gd* is in the printed list, it
       is enabled. If *gd* doesn't appear in the list but is
       installed, you can run ``sudo phpenmod gd`` to enable it.

   * Enable video thumbnail generation (y/N) *Requires ffmpeg.*

     * Enable video thumbnail generation. Currently video thumbnails
       are only generated for uploaded slide media. Note that video
       thumbnail generation requires *ffmpeg* and *ffprobe* to be
       available on the host system. If you enable this option,
       you'll also need to configure the binary paths to *ffmpeg*
       and *ffprobe* in the LibreSignage configuration files. The
       paths default to */usr/bin/ffmpeg* and */usr/bin/ffprobe*.

   * Enable debugging (y/N) *Do not enable on production systems.*

     *  Whether to enable debugging. This enables things like
        verbose error reporting through the API etc.

   This command generates aa build configuration file needed
   for building LibreSignage. The file is saved in ``build/`` as
   ``<DOMAIN>.conf`` where ``<DOMAIN>`` is the domain name you
   specified.
7. Run ``make -j$(nproc)`` to build LibreSignage. See `9. Make rules`_
   for more advanced make usage.
8. Finally, to install LibreSignage, run ``sudo make install`` and answer
   the questions asked.
9. Navigate to the domain name you entered and you should see the
   LibreSignage login page.

4.2.2. Building a Docker image
..............................

You can build LibreSignage Docker images by following the instructions
below.

1. Follow the steps 1-5 from `4.2.1. Building a native build`_.
2. Install `Docker <https://www.docker.com/>`_ if it isn't yet installed.
3. Run the following command::

       make configure \
           TARGET=apache2-debian-docker \
           PASS="--features [features]"

   Where ``[features]`` is a comma separated list of features to enable.
   The recognised features are:

   * imgthumbs = Image thumbnail generation using *PHP gd*.
   * vidthumbs = Video thumbnail generation using *ffmpeg*.
   * debug     = Debugging.

4. Run ``make`` to build the LibreSignage distribution.
5. Run ``make install`` to package LibreSignage in a Docker image.
   This will take some time as Docker needs to download a lot of stuff.
   After this command has completed the LibreSignage image is saved in
   your machine's Docker registry as *libresignage:[version]*. You can
   use it by following the instructions in `4.1. Using prebuilt Docker
   images`_.

Extra
*****

 You can also build LibreSignage Docker images automatically using the
 helper script *build/helpers/docker/build_img.sh*. If you want to build
 a release image just run the script. If you want to build a development
 image, pass *dev* as the first argument.

 The *build/helpers/docker/* directory also contains the script
 *run_dev.sh* for starting a development/testing docker container.

5. Default users
----------------

The default users and their groups and passwords are listed below.
It goes without saying that you should create new users and change
the passwords if you intend to use LibreSignage on a production
system.

=========== ======================== ==========
    User             Groups           Password
=========== ======================== ==========
admin        admin, editor, display   admin
user         editor, display          user
display      display                  display
=========== ======================== ==========

6. How to install npm
---------------------

If npm doesn't exist in the repos of your Linux distribution of choice,
is very outdated (like in the case of Debian) or you are not using a
Linux based distribution at all, you must install it manually. You can
follow the installation instructions for your OS on the
`node.js website <https://nodejs.org/en/download/package-manager/>`_.

There are other ways to install npm too. One alternative way to install
npm is described below. *Note that if you use this method to install
npm, you shouldn't update npm via it's own update mechanism
(running npm install npm) since that will install the new version into
a different directory. To update npm when it's installed this way,
you should just follow steps 1-3 again.*

1. Download the *node.js* binaries for your system from
   https://nodejs.org/en/download/.
2. Extract the tarball with ``tar -xvf <name of tarball>``.
3. Create a new directory ``/opt/npm`` and copy the extracted
   files into it.
4. Run ``ln -s /opt/npm/bin/npm /usr/local/bin/npm`` and
   ``ln -s /opt/npm/bin/npx /usr/local/bin/npx``. You need to
   be root when running these commands so prefix them with ``sudo``
   or log in as root first.
5. Run ``cd ~/`` to go back to your home directory and verify the
   installation by running ``npm -v``. This should now print the
   installed npm version.

7. FAQ
------

Why doesn't LibreSignage use framework/library X?
  To avoid bloat; LibreSignage is designed to be minimal and lightweight
  and it only uses external libraries where they are actually needed. 
  Most UI frameworks for example are huge. LibreSignage does use
  Bootstrap though, since it's a rather clean and simple framework.

Why doesn't LibreSignage have feature X?
  You can suggest new features in the bug tracker. If you know a bit
  about programming in PHP, JS, HTML and CSS, you can also implement
  the feature yourself and create a pull request.

Is LibreSignage really free?
  YES! In fact LibreSignage is not only free, it's also open source.
  You can find information about the LibreSignage license in the
  section `13. License`_.

8. Screenshots
---------------

Open these images in a new tab to view the full resolution versions.

**LibreSignage Login**

.. image:: http://etal.mbnet.fi/libresignage/v0.2.0/login.png
   :width: 320 px
   :height: 180 px

**LibreSignage Control Panel**

.. image:: http://etal.mbnet.fi/libresignage/v0.2.0/control.png
   :width: 320 px
   :height: 180 px

**LibreSignage Editor**

.. image:: http://etal.mbnet.fi/libresignage/v0.2.0/editor.png
   :width: 320 px
   :height: 180 px

**LibreSignage Media Uploader**

.. image:: http://etal.mbnet.fi/libresignage/v0.2.0/media_uploader.png
   :width: 320 px
   :height: 180 px

**LibreSignage User Manager**

.. image:: http://etal.mbnet.fi/libresignage/v0.2.0/user_manager.png
   :width: 320 px
   :height: 180 px

**LibreSignage User Settings**

.. image:: http://etal.mbnet.fi/libresignage/v0.2.0/user_settings.png
   :width: 320 px
   :height: 180 px

**LibreSignage Display**

.. image:: http://etal.mbnet.fi/libresignage/v0.2.0/display.png
   :width: 320 px
   :height: 180 px

**LibreSignage Documentation**

.. image:: http://etal.mbnet.fi/libresignage/v0.2.0/docs.png
   :width: 320 px
   :height: 180 px

9. Make rules
--------------

The following ``make`` rules are implemented in the makefile.

all
  The default rule that builds the LibreSignage distribution. You
  can pass ``NOHTMLDOCS=y`` if you don't want to generate any HTML
  documentation.

configure
  Generate a LibreSignage build configuration file. You need to use
  ``TARGET=[target]`` to select a build target to use. You can also
  optionally use ``PASS=[pass]`` to pass any target specific arguments
  to the build configuration script. The recognized targets are:

  * apache2-debian (Build target for a native install on Debian.)
  * apache2-debian-docker (Build target for building Docker images.)

    * You can use PASS with ``--features [features]`` where ``[features]``
      is a comma separated list of features to enable. See the section
      `4.2.2. Building a Docker image`_ for more info.      

install
  Install the LibreSignage distribution on the machine. Note that
  the meaning of install depends on the target you are building for.
  Running ``make install`` for the *apache2-debian-docker* target,
  for example, builds the Docker image (ie. installs LibreSignage into
  the Docker image).

utest
  Run the LibreSignage unit testing scripts. Note that you must install
  LibreSignage before running this rule.

clean
  Clean files generated by building LibreSignage.

realclean
  Same as *clean* but removes all generated files and build config files
  too. This rule effectively resets the LibreSignage directory to how it
  was right after cloning the repo.

LOC
  Count the lines of code in LibreSignage.

LOD
  Count the lines of documentation in LibreSignage. This target will
  only work after building LibreSignage since the documentation lines
  are counted from the docs in the dist/ directory. This way the
  generated API endpoint docs can be taken into account too.

You can also pass some other settings to the LibreSignage makefile.

CONF=<config file> - (default: Last generated config.)
  Manually specify a config file to use. This setting can be used with
  the targets *all* and *install*.

VERBOSE=<Y/n>
  Print verbose log output. This setting can be used with any target.

10. Documentation
-----------------

LibreSignage documentation is written in reStructuredText, which is
a plaintext format often used for writing technical documentation.
The reStructuredText syntax is also human-readable as-is, so you can
read the documentation files straight from the source tree. The docs
are located in the directory *src/doc/rst/*.

The reStructuredText files are also compiled into HTML when LibreSignage
is built and they can be accessed from the *Help* page of LibreSignage.

11. Third-party dependencies
----------------------------

Bootstrap (Library, MIT License)
  Copyright (c) 2011-2016 Twitter, Inc.

JQuery (Library, MIT License)
  Copyright JS Foundation and other contributors, https://js.foundation/

Popper.JS (Library, MIT License)
  Copyright (C) 2016 Federico Zivolo and contributors

Ace (Library, 3-clause BSD License)
  Copyright (c) 2010, Ajax.org B.V. All rights reserved.

Raleway (Font, SIL Open Font License 1.1) 
  Copyright (c) 2010, Matt McInerney (matt@pixelspread.com),  

  Copyright (c) 2011, Pablo Impallari (www.impallari.com|impallari@gmail.com),  

  Copyright (c) 2011, Rodrigo Fuenzalida (www.rfuenzalida.com|hello@rfuenzalida.com),  
  with Reserved Font Name Raleway

Montserrat (Font, SIL Open Font License 1.1)
  Copyright 2011 The Montserrat Project Authors (https://github.com/JulietaUla/Montserrat)  

Inconsolata (Font, SIL Open Font License 1.1)
  Copyright 2006 The Inconsolata Project Authors (https://github.com/cyrealtype/Inconsolata)

Font-Awesome (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License)
  Font Awesome Free 5.1.0 by @fontawesome - https://fontawesome.com

The full licenses for these third party libraries and resources can be
found in the file *src/doc/rst/LICENSES_EXT.rst* in the source
distribution.

12. Build system dependencies
-----------------------------

* Installed manually

  * GNU Make (> 4.0) (https://www.gnu.org/software/make/)
  * PHP (7.2) (http://www.php.net/)
  * Pandoc (> 2.2.1) (https://pandoc.org/)

* Installed via npm:

  * SASS (https://sass-lang.com/)
  * Browserify (http://browserify.org/)
  * PostCSS (https://postcss.org/)
  * Autoprefixer (https://github.com/postcss/autoprefixer)

13. License
-----------

LibreSignage is licensed under the BSD 3-clause license, which can be
found in the files *LICENSE.rst* and *src/doc/rst/LICENSE.rst* in the
source distribution. Third party libraries and resources are licensed
under their respective licenses. See `11. Third-party dependencies`_ for
more information.

Copyright Eero Talus 2018
