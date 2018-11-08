######################################################
LibreSignage - An open source digital signage solution
######################################################

Table Of Contents
-----------------

`1. General`_

`2. Features`_

`3. Project goals`_

`4. Installation`_

`5. Default users`_

`6. How to install npm`_

`7. FAQ`_

`8. Screenshots`_

`9. Make rules`_

`10. Documentation`_

`11. Third-party dependencies`_

`12. Build system dependencies`_

`13. License`_

1. General
----------

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

LibreSignage has currently only been tested on Linux based systems,
however it should be possible to run it on other systems aswell. Running
LibreSignage on other systems will require some manual configuration
though, since the build and installation systems won't work out of the
box. The only requirement for running a LibreSignage server instance is
the Apache web server with PHP support, which should be available on most
systems. Building LibreSignage on the other hand requires some additional
software.

LibreSignage is designed to be used with Apache 2.0 and the default
install system is programmed to use Apache's Virtual Host configuration
features.

In a nutshell, Virtual Hosts are a way of hosting multiple websites on
one server, which is ideal in the case of LibreSignage. Using Virtual
Hosts makes it really simple to host one or more LibreSignage instances
on a server and adding or removing instances is also rather easy. You
can look up more information about Virtual Hosts on the
`Apache website <https://httpd.apache.org/docs/2.4/vhosts/>`_.

Doing a basic install of LibreSignage is quite simple. The required steps
are listed below.

1. Install software needed for building LibreSignage. You will need the
   following packages: ``git, apache2, php7.2, pandoc ruby-sass, npm``.
   On Debian Stretch all other packages except *npm* can installed by
   running ``sudo apt install git apache2 php7.2 pandoc ruby-sass``.
   Currently *npm* is only available in the Debian Sid repos and even
   there the package is so old it doesn't work correctly. You can,
   however, install npm manually. See `6. How to install NPM`_ for
   more info. There are also some optional dependencies:

     * If you want to run or build LibreSignage Docker images,
       you'll also need to install `Docker <https://www.docker.com/>`_.
     * If you want to enable video thumbnail generation, you'll
       need to install ``ffmpeg``. On Debian you can install it by
       running ``sudo apt install ffmpeg``.

2. Use ``cd`` to move to the directory where you want to download the
   LibreSignage repository.
3. Run ``git clone https://github.com/eerotal/LibreSignage.git``.
   The repository will be cloned into the directory *LibreSignage/*.
4. Run ``cd LibreSignage`` to move into the LibreSignage repository.
5. Install dependencies from NPM by running ``npm install``.
6. Run ``make configure``. This script asks you to enter the
   following configuration values.

   * Document root (default: /var/www)

     * The document root to use.

   * Server name (domain)

     * The domain name to use for configuring apache2. If you
       don't have a domain and you are just testing the system,
       you can either use 'localhost', your machines LAN IP or
       a testing domain you don't actually own. If you use a testing
       domain, you can add that domain to your */etc/hosts* file.
       See the end of this section for more info.

   * Server name aliases
   * Admin name

     * Shown to users on the main page.

   * Admin email

     * Shown to users on the main page.

   * Enable debugging (y/N)

     *  Whether to enable debugging. N is default.

   This command generates an instance configuration file needed
   for building LibreSignage. The file is saved in ``build/`` as
   ``<DOMAIN>.iconf`` where ``<DOMAIN>`` is the domain name you
   specified.
7. Run ``make`` to build LibreSignage. You can use the ``-j<MAXJOBS>``
   CLI option to specify a maximum number of parallel jobs to speed up
   the building process. The usual recommended value for the max number
   of jobs is one per CPU core, meaning that for eg. a quad core CPU you
   should use -j4. See `9. Make rules`_ for more advanced options.
8. Finally, to install LibreSignage, run ``sudo make install`` and answer
   the questions asked.

After this the LibreSignage instance is fully installed and ready to be
used via the web interface. If you specified a domain name you don't
actually own just for testing the install, you can add it to your
*/etc/hosts* file to be able to test the site using a normal browser.
This only applies on Linux based systems of course. For example, if you
specified the server name *example.com*, you could add the following
line to your */etc/hosts* file.

``example.com    127.0.0.1``

This will redirect all requests for *example.com* to *127.0.0.1*
(loopback), making it possible to access the site by connecting
to *example.com*.

5. Default users
----------------

The initial configured users and their groups and passwords are listed
below. It goes without saying that you should create new users and
change the passwords if you intend to use LibreSignage on a production
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
  `13. License`_ section.

8. Screenshots
---------------

Open these images in a new tab to view the full resolution versions.
*Note that these screenshots are always the latest ones no matter what
branch or commit you are viewing.*

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
  The default rule that builds the LibreSignage distribution.

install
  Install LibreSignage. This copies the LibreSignage distribution files
  into a virtual host directory in the configured document root.

utest
  Run the LibreSignage unit testing scripts. Note that you must install
  LibreSignage before running this rule.

clean
  Clean files generated by building LibreSignage.

realclean
  Same as *clean* but removes all generated files too. This rule
  effectively resets the LibreSignage directory to how it was right
  after cloning the repo.

LOC
  Count the lines of code in LibreSignage.

LOD
  Count the lines of documentation in LibreSignage. This target will
  only work after building LibreSignage since the documentation lines
  are counted from the docs in the dist/ directory. This way the
  generated API endpoint docs can be taken into account too.

You can also pass some other arguments to the LibreSignage makefile.

INST=<config file> - (default: Last generated config.)
  Manually specify a config file to use.

VERBOSE=<y/n> - (default: y)
  Print verbose log output.

NOHTMLDOCS=<y/n> - (default: n)
  Don't generate HTML documentation from the reStructuredText docs
  or the API endpoint files. This setting can be used with make rules
  that build files. Using it with eg. ``make install`` has no effect.
  
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
