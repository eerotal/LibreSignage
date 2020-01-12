#
#  Script used to push Docker images to Docker Hub in the CI pipeline.
#
#  $1 = The repository to push the image to.
#  $2 = The build type, ie. either 'nightly' or 'release'.
#  $3 = The Docker tag name for nightly builds. This must be specified if
#       $2 = nightly and otherwise this isn't used.
#

#!/bin/sh

set -e
. build/scripts/conf.sh

REPO="$1"
BUILD="$2"
TAG="$3"

# Check that the necessary env vars are set.
if [ -z "$DOCKER_USER" ]; then
	echo "[Error] Docker username not set."
	exit 1
fi
if [ -z "$DOCKER_PASS" ]; then
	echo "[Error] Docker password not set."
	exit 1
fi

# Login with credentials from environment variables.
echo "$DOCKER_PASS" | docker login --username="$DOCKER_USER" --password-stdin

if [ "$BUILD" = "nightly" ]; then
	if [ -z "$TAG" ]; then
		echo "[Error] Docker tag must be specified for nightly builds."
		exit 1
	fi
	docker tag "libresignage:$LS_VER" "$REPO/libresignage:$TAG"
	docker push "$REPO/libresignage:$TAG"
elif [ "$BUILD" = "release" ]; then
	docker tag "libresignage:$LS_VER" "$REPO/libresignage:$LS_VER"
	docker push "$REPO/libresignage:$LS_VER"

	docker tag "libresignage:$LS_VER" "$REPO/libresignage:latest"
	docker push "$REPO/libresignage:latest"
else
	echo "[Error] Unknown build type to push."
	exit 1
fi

echo "[Info] Image(s) pushed to Docker Hub."
