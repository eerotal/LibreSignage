##########################
LibreSignage Display Setup
##########################

Setting up a LibreSignage display instance is quite simple. The only
things that are needed are a standard web browser, an internet
connection and the LibreSignage server to connect to. A LibreSignage
display can be setup by simply making a web browser autostart at boot
and by making it navigate to the display page of LibreSignage. After
this the administrator of the display must login to the LibreSignage
instance on the display device AND select the *Start a display session*
checkbox on the *Advanced* tab of the login page. This makes the login
session permanent so that credentials don't need to be input every
time the display is restarted. **Note that you should login as a user
that has less rights than normal users, just as a good precaution.
Never use eg. the admin account when logging into a display. The only
group needed by a display user is the *display* group.**

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

