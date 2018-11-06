#!/bin/sh

##
##  LibreSignage build configurator script for the apache2-debian-docker
##  environment for building docker images.
##

set -e
. build/scripts/conf.sh

ICONF_F="build/docker_`date +%F_%T`$ICONF_FILE_EXT";
echo "Write config to '$ICONF_F'.";

echo '#!/bin/sh'                                  >  $ICONF_F;
echo "# Generated on `date` by configure.sh."     >> $ICONF_F;

echo "ICONF_SERVER_ENV=\"$ICONF_SERVER_ENV\""     >> $ICONF_F;
echo "ICONF_DEBUG=\"FALSE\""                      >> $ICONF_F;

# Create the last config file symlink in build/link/last.sh.
mkdir -p "build/link";
rm -f "build/link/last.sh"
ln -s "`pwd`/$ICONF_F" "build/link/last.sh";

