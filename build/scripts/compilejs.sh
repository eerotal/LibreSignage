#!/bin/sh

##
## Compile LibreSignage JavaScript files. $1 is the entry point JS
## file used by browserify. The output file is a file with the same
## name and path in the dist/ directory.
##

set -e;
. build/scripts/conf.sh

IN=$1;
OUT=`echo "$1"|sed 's/src/dist/g'`;
NPMBIN=`npm bin --global 2>/dev/null`;

echo "$IN ==> $OUT";
$NPMBIN/browserify $IN -o $OUT


