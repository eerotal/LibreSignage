set -e

##
##  Install handler for the apache2-debian-docker target
##  environment. This script loads the build configuration
##  using the ldiconf.sh script.
##

. build/scripts/conf.sh
. build/scripts/ldiconf.sh

args=""

if [ "$CONF_DEBUG" = "TRUE" ]; then
	args="$args --build-arg debug=y"
fi

if [ "$CONF_FEATURE_IMGTHUMBS" = "TRUE" ]; then
	args="$args --build-arg imgthumbs=y"
fi

if [ "$CONF_FEATURE_VIDTHUMBS" = "TRUE" ]; then
	args="$args --build-arg vidthumbs=y"
fi

# Remove the leading space in $args.
args="`echo "$args" | sed 's:^\s::'`"

echo "[INFO] Args for 'docker build': $args";

docker build -t libresignage $args .
