#
#  Script used to rename Docker images in Docker Hub.
#
#  $1 = The repository to push the image to.
#  $2 = The build type, ie. either 'nightly' or 'release'.
#  $3 = The source tag name.
#  $4 = The Docker tag name for nightly builds. This must be specified if
#       $2 = nightly and otherwise this isn't used.
#

#!/bin/sh

set -e
. build/scripts/conf.sh

REPO="$1"
BUILD="$2"
SRC_TAG="$3"
DEST_TAG="$4"

if [ "$BUILD" = "nightly" ]; then
	if [ -z "$DEST_TAG" ]; then
		echo "[Error] Docker tag must be specified for nightly builds."
		exit 1
	fi
	docker buildx imagetools create \
		-t "$REPO/libresignage:$DEST_TAG" "$REPO/libresignage:$SRC_TAG"
elif [ "$BUILD" = "release" ]; then
	docker buildx imagetools create \
		-t "$REPO/libresignage:$LS_VER" "$REPO/libresignage:$SRC_TAG"
	docker buildx imagetools create \
		-t "$REPO/libresignage:latest" "$REPO/libresignage:$SRC_TAG"
else
	echo "[Error] Unknown build type to push."
	exit 1
fi

echo "[Info] Image(s) pushed to Docker Hub."
