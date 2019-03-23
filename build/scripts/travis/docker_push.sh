#!/bin/sh

set -e
. build/scripts/conf.sh

REPO="$1"
BUILD="$2"

# Check that the necessary env vars are set.
if [ -z "$DOCKER_USER" ]; then
	echo "[Error] Docker username not set."
	exit 1
fi
if [ -z "$DOCKER_PASS" ]; then
	echo "[Error] Docker password not set."
	exit 1
fi

if [ "$BUILD" = "nightly" ]; then
	DEST="$REPO/libresignage:nightly"
elif [ "$BUILD" = "release" ]; then
	DEST="$REPO/libresignage:$LS_VER"
else
	echo "[Error] Unknown build type to push."
	exit 1
fi

# Login, tag and push to Docker Hub.
echo "$DOCKER_PASS" | docker login --username="$DOCKER_USER" --password-stdin
docker tag "libresignage:$LS_VER" "$DEST"
docker push "$DEST"

echo "[Info] Image '$DEST' pushed to Docker Hub."
