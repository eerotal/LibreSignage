######################################################
LibreSignage - An open source digital signage solution
######################################################

LibreSignage is a free and open source, lightweight and easy-to-use
digital signage solution. LibreSignage runs on a HTTP web server serving
content to normal web browsers. This makes it possible to use basically
any device with the ability to display web pages from the internet as a
client for a LibreSignage instance.

Features
--------

* Web interface for editing slides and managing the
  LibreSignage instance.
* Configurable slide duration, transition animations
  and other parameters.
* Special markup syntax for easily formatting slides.
* Possibility to schedule slides for a specific time-frame.
* Possibility to give slide modification permissions to
  other users.
* Separate slide queues for different digital signage
  screens.
* Multiple user accounts with permissions based on
  user groups.
* User settings view for changing passwords and viewing
  logged insessions.
* User management/creation/deletion via the web intarface
  for admin users.
* Configurable quotas for actions such as creating slides.
* Rate limited API for reducing server load.
* Extensive documentation of all features.
* Modular design of the codebase.

Installation
------------

LibreSignage has currently only been tested on Linux based systems,
however it should be possible to run it on other systems aswell. Running
LibreSignage on other systems will require some manual configuration though,
since the build and installation systems won't work out of the box. The only
requirement for running a LibreSignage server instance is the Apache web
server with PHP support, which should be available on most systems. Building
LibreSignage on the other hand requires some additional software.

LibreSignage is designed to be used with Apache 2.0 and the default install
system is programmed to use Apache's Virtual Host configuration features.

In a nutshell, Virtual Hosts are a way of hosting multiple websites on
one server, which is ideal in the case of LibreSignage. Using Virtual
Hosts makes it really simple to host one or more LibreSignage instances
on a server and adding or removing instances is also rather easy. You
can look up more information about Virtual Hosts on the
`Apache website <https://httpd.apache.org/docs/2.4/vhosts/>`_.

Doing a basic install of LibreSignage is quite simple. The required steps
are listed below.

1. Install software needed for building LibreSignage. On Debian Stretch
   this can be accomplished by running ``sudo apt install git npm
   apache2 php7.0 pandoc``. *pandoc* is needed for compiling the
   reStructuredText documentation files to HTML.
2. Install the required JavaScript dependencies by running ``npm install``.
   If this command fails to install Browserify because of a permission
   error, you need to fix your npm installation. Check `the npm documentation
   page <https://docs.npmjs.com/getting-started/fixing-npm-permissions>`_
   for instructions. Both of the options on that page should work but
   at least *Option 2* has been tested.
2. Use ``cd`` to move to the directory where you want to download the
   LibreSignage repository.
3. Run ``git clone https://github.com/eerotal/LibreSignage.git``.
   The repository will be cloned into the directory *LibreSignage/*.
4. Run ``cd LibreSignage`` to move into the LibreSignage repository.
5. Run ``make``, read the instructions and answer the questions.
   This command saves the config values it asks to a file in the *build/*
   directory with the name *<DOMAIN>.iconf* where *<DOMAIN>* is the
   domain name you entered. On subsequent invocations of ``make`` you
   can add ``INST=<DOMAIN>.iconf`` to the command to use the same config
   values that you specified earlier. For more advanced usage of ``make``,
   see the section *Make rules*
6. Finally, to install LibreSignage, run ``sudo make install`` and answer
   the questions asked.

After this the LibreSignage instance is fully installed and ready to be
used via the web interface. If you specified a domain name you don't
actually own just for testing the install, you can add it to your
*/etc/hosts* file to be able to test the site using a normal browser.
This only applies on Linux based systems of course. For example, if you
specified the server name *example.com*, you could add the following
line to your */etc/hosts* file.

``example.com    127.0.0.1``

This will redirect all requests for *example.com* to *localhost*,
making it possible to access the site by connecting to *example.com*.

Default users
-------------

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

Screenshots
-----------

Open these images in a new tab to view the full resolution versions.
*Note that these screenshots are always the latest ones no matter what
branch or commit you are viewing.*

**LibreSignage Login**

.. image:: http://etal.mbnet.fi/libresignage/login.png
   :width: 320 px
   :height: 180 px

**LibreSignage Control Panel**

.. image:: http://etal.mbnet.fi/libresignage/control.png
   :width: 320 px
   :height: 180 px

**LibreSignage Editor**

.. image:: http://etal.mbnet.fi/libresignage/editor.png
   :width: 320 px
   :height: 180 px

**LibreSignage User Manager**

.. image:: http://etal.mbnet.fi/libresignage/user_manager.png
   :width: 320 px
   :height: 180 px

**LibreSignage User Settings**

.. image:: http://etal.mbnet.fi/libresignage/settings.png
   :width: 320 px
   :height: 180 px

**LibreSignage Display**

.. image:: http://etal.mbnet.fi/libresignage/display.png
   :width: 320 px
   :height: 180 px

**LibreSignage Documentation**

.. image:: http://etal.mbnet.fi/libresignage/docs.png
   :width: 320 px
   :height: 180 px

Make rules
----------

The following ``make`` rules are implemented in the makefile.

all
  The default rule that builds LibreSignage.

install
  Install LibreSignage. This copies the LibreSignage disribution files
  into a virtual host directory in */var/www*.

utest
  Run the LibreSignage unit testing scripts. Note that you must install
  LibreSignage before this rule works correctly.

clean
  Clean files generated by building LibreSignage.

realclean
  Same as *clean* but also remove generated config files from *build/*.

LOC
  Count the lines of code in LibreSignage.

You can also pass ``INST=[CONFIG FILE]`` with all the build/installation rules
to specify an existing install config to use. 

Used third-party libraries and resources
----------------------------------------

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

The full licenses for these third party libraries and resources can be found
in the file *src/doc/rst/LICENSES_EXT.rst* in the source distribution.

License
-------

LibreSignage is licensed under the BSD 3-clause license, which can be found
in the file *src/doc/rst/LICENSE.rst* in the source distribution. Third party
libraries and resources are licensed under their respective licenses. See the
section *Used third party libraries and resources* for more information.

Copyright Eero Talus 2018
