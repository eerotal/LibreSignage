set -e

. build/scripts/conf.sh
. build/scripts/ldiconf.sh

args=""

if [ "$ICONF_DEBUG" = "TRUE" ]; then
	args="$args --build-arg debug=y"
fi

if [ "$ICONF_FEATURE_IMGTHUMBS" = "TRUE" ]; then
	args="$args --build-arg imgthumbs=y"
fi

if [ "$ICONF_FEATURE_VIDTHUMBS" = "TRUE" ]; then
	args="$args --build-arg vidthumbs=y"
fi

echo "[INFO] Args for 'docker build': $args";

docker build -t libresignage $args .
