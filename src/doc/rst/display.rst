####################
LibreSignage display
####################

The LibreSignage display is the page where all the LibreSignage slides
can be viewed. This page is meant to be displayed on the client machines.

Setting up a LibreSignage display instance is quite simple. The only
things that are needed are a standard web browser, an internet
connection and the LibreSignage server to connect to. A LibreSignage
display can be setup by simply making a web browser autostart at boot
and by making it navigate to the display page of LibreSignage. After
this the administrator of the display must login to the LibreSignage
instance on the display device AND select the *Start a display session*
checkbox on the login page. This makes the login session permanent
so that credentials don't need to be input every time the display
is restarted. **Note that you should login as a user that has less rights
than normal users, just as a good precaution. Never use eg. the admin
account when logging into a display. The only group needed by a display
user is the *display* group.**


