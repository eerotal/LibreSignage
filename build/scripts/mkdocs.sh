#!/bin/bash

# A script to convert the LibreSignage Markdown documentation to HTML.

set -e
. build/scripts/build_conf.sh

mkdir -p $DIST_DIR'/doc/html';
shopt -s globstar;

# Convert the Markdown documentation files to HTML files.
for f in $MD_DIR/**; do
	FNAME=${f##*/};
	FNAME=${FNAME%.*};
	if [ -f "$f" ]; then
		echo $f' ==> '$FNAME'.html';
		pandoc -o "$HTML_DIR/$FNAME.html" -f markdown -t html $f;
	fi
done
