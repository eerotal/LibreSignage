#!/bin/sh

#
# Installation script for the apache2-debian-interactive target.
#

. build/scripts/fancyread.sh

NO_PRESERVE_DATA=$(fancyread \
	"Preserve old instance data?" \
	Y \
	Y N y n\
)

echo \
	$(case "$NO_PRESERVE_DATA" in [yY]*) echo '--no-preserve-data '; esac)\
| xargs './build/target/apache2-debian/install.sh'
