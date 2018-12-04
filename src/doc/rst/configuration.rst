
LibreSignage configuration
##########################


1. Media uploader
-----------------

1.2. Thumbnails
---------------

The LibreSignage web interface includes a media uploader that can be
used to upload files to the LibreSignage server. These files can then
be embedded in slides to create more rich content.

When building LibreSignage, the configuration script asks the user
whether image and video thumbnail generation should be enabled. These
can be enabled independent of each other. There are, however, some
additional dependencies needed for both of them.

Image thumbnails:

* The PHP extension `gd` is required for image thumbnail generation.
  On Debian and Ubuntu it can be installed by installing the `php-gd`
  package from the distribution repos.

Video thumbnails:

* The `ffmpeg` and `ffprobe` are required for video thumbnail generation.
  On Debian and Ubuntu they can be installed by installing the `ffmpeg`
  package from the distribution repos. You also need to configure the
  binary paths to these in the LibreSignage configuration files. The
  config values you need to modify are `FFMPEG_PATH` and `FFPROBE_PATH`.
