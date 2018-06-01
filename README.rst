######################################################
LibreSignage - An open source digital signage solution
######################################################

LibreSignage is a free and open source, lightweight and easy-to-use
digital signage solution. LibreSignage runs on a HTTP web server serving
content to normal web browsers. This makes it possible to use basically
any device with the ability to display web pages from the internet as a
client for a LibreSignage instance.

Installation
------------

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

1. Install the required dependencies. On Debian Buster this can be
   accomplished by running ``sudo apt install apache2 php7.0 git
   pandoc``. *pandoc* is needed for compiling the reStructuredText
   documentation files to HTML.
2. Use ``cd`` to move to the directory where you want to download the
   LibreSignage repository.
3. Run ``git clone https://github.com/eerotal/LibreSignage.git``.
   The repository will be cloned into the directory *LibreSignage/*.
4. Run ``cd LibreSignage`` to move into the LibreSignage repository.
5. Run ``sudo make dist docs install``, read the instructions and answer
   the questions.

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

**LibreSignage Editor**

.. image:: http://etal.mbnet.fi/libresignage/editor.png
   :width: 320 px
   :height: 180 px

**LibreSignage User Manager**

.. image:: http://etal.mbnet.fi/libresignage/user_manager.png
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

The full licenses for these third party libraries and resources can be found
in the file *src/doc/rst/LICENSES_EXT.rst* in the source distribution.

License
-------

LibreSignage is licensed under the BSD 3-clause license, which can be found
in the file *src/doc/rst/LICENSE.rst* in the source distribution. Third party
libraries and resources are licensed under their respective licenses. See the
section *Used third party libraries and resources* for more information.

Copyright Eero Talus 2018
