##########################
LibreSignage Display Setup
##########################


Basic setup
-----------

Setting up a LibreSignage display client is quite simple. The only
things that are needed are a standard web browser, an internet
connection and the LibreSignage server to connect to. A LibreSignage
display can be setup by simply making a web browser autostart on boot
and by making it navigate to the display page of LibreSignage. You
also need to choose whether you require authentication for viewing
the display contents or not. **Note that you should login as a user
that has less rights than normal users, just as a good precaution.
Never use eg. the admin account when logging into a display. The only
group needed by a display user is the *display* group.**

Setting up a client when authentication is required
+++++++++++++++++++++++++++++++++++++++++++++++++++

If you require authentication for users who want to view the contents
of a display, you can setup a client to login as normal user who belongs to
the *display* group. This group has permissions to access all the API endpoints
required for viewing slide contents. *display* users can't modify any data
on the server.

When logging in a display client as a normal user, you need to select the
*Start a display* session checkbox under the *Advanced* tab on the login page.
This starts a permanent session which never expires so that you don't need to
login manually every time the client reboots.

Setting up a client without authentication
++++++++++++++++++++++++++++++++++++++++++

If you want to give anyone access to view the contents of any display, you can
create a passwordless user who belongs to the group *display*. This way anyone
can login as the passwordless user to view slides on any display without having
permissions to modify any data on the server. As an admin user you can create
passwordless users from the `User Manager </doc?doc=user_manager>`_.

You can use the same login procedure as described in `Setting up a client when
authentication is required`_ with passwordless users aswell. There is, however,
another way to setup a client to log in as a passwordless user automatically:
You can make the client browser navigate to ``http://<server>/app_bootstrap?user=<username>``
instead of ``http://<server>/app``. The *app_bootstrap* page automatically tries
to login as the **passwordless** user provided in the *user* parameter. After
a successful login the client is redirected to the actual display page. Any
additional GET parameters, eg. *queue* and *noui*, are passed onto the actual
display page.


Hiding the mouse cursor
-----------------------

There's one problem in the web browser based approach LibreSignage
uses: There doesn't seem to be a way to programmatically hide the
cursor on the LibreSignage web page. (Actually there is a way,
however the cursor only hides itself after it's moved slightly, which
can't be done on a standalone display device. This approach would use
the ``cursor: none`` CSS property of web browsers.) Until someone comes
up with a better way to hide the cursor, below is a list of workarounds
for different systems.

Linux
-----

Workaround 1
++++++++++++

If you're using X as the windowing system, you can start X with
the ``-nocursor`` option. This makes the cursor permanently hidden.

Workaround 2
++++++++++++

1. Install *unclutter*. On Debian systems this can be accomplised by
running ``sudo apt install unclutter``.

2. Configure *unclutter* to start with the X windowing system. First
copy the file ``/etc/X11/xinit/xinitrc`` to ``~/.xinitrc`` and add the
line below to the end of the file.

``unclutter -idle 0.01 -root &``

Android
-------

Android only displays the cursor when a pointer device is attached.
The simplest way to keep the cursor hidden is to just not connect a
pointer device.

Windows
-------

Hiding the cursor on Windows is probably possible but no workaround has
been tried yet.

