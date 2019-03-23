#!/bin/sh

set -e
. build/scripts/conf.sh

REPO="$1"
BUILD="$2"

# Check that the necessary env vars are set.
if [ -n "$DOCKER_USER" ]; then
	echo "[Error] Docker username not set."
	exit 1
fi
if [ -n "$DOCKER_PASS" ]; then
	echo "[Error] Docker password not set."
	exit 1
fi

if [ "$BUILD" = "nightly" ]; then
	TAG="$REPO/libresignage:nightly-$LS_VER"
elif [ "$BUILD" = "release" ]; then
	TAG="$REPO/libresignage:$LS_VER"
else
	echo "[Error] Unknown build type to push."
	exit 1
fi

# Login, tag and push to Docker Hub.
echo "$DOCKER_USER" | docker login --username="$DOCKER_USER" --password-stdin
docker tag "libresignage:$LS_VER" "$TAG"
docker push "eerotal/$REPO:$LS_VER"

echo "[Info] Image '$TAG' pushed to Docker Hub."
