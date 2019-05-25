#
#  Script used to push Docker images to Docker Hub in the CI pipeline.
#  $1 is the repository to push the image to and $2 is the build type, ie
#  either 'nightly' or 'release'.
#

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

# Login with credentials from environment variables.
echo "$DOCKER_PASS" | docker login --username="$DOCKER_USER" --password-stdin

if [ "$BUILD" = "nightly" ]; then
	docker tag "libresignage:$LS_VER" "$REPO/libresignage:nightly"
	docker push "$REPO/libresignage:nightly"
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
