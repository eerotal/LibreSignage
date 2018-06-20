#!/bin/bash

##
## Generate LibreSignage documentation files.
##

shopt -s globstar;
set -e
. build/scripts/conf.sh

if [ ! -d "$DIST_DIR" ]; then
        echo "DIST_DIR doesn't exist!";
        exit 1;
fi

function gen_api_doc {
	# Get the documentation text from the filepath in $1.
        if [ -z "$1" ]; then
                return '';
        fi

        sed -n '/====>/,/<====/p' "$1"  | \
        sed 's/\s*\*\s\{0,2\}//m'       | \
        sed '/====>/d; /<====/d';
}

##
## Generate the API documentation files.
##

echo "Generate API documentation...";

cat > $API_DOC << EndOfText
##############################
LibreSignage API Documentation
##############################

This document was automatically generated from the LibreSignage
source files by the LibreSignage build system on `date`. The
information below can also be found in the API endpoint source
files.

Endpoint documentation
++++++++++++++++++++++

EndOfText

heading='';
for f in $API_ENDPOINTS_DIR/**; do
	if [ "${f##*.}" == "php" ] && [ -f "$f" ]; then
		line="";
		heading=${f#*/};

		echo 'Gen API doc from "'$f'".';
		echo $heading >> $API_DOC;

		# Add the line of dashes below the heading.
		for (( i=0; i<${#heading}; i++ )); do
			line="$line-";
		done
		echo $line >> $API_DOC;

		gen_api_doc "$f" >> $API_DOC;
	fi
done

##
## Generate the HTML documentation files from the
## reStructuredText files.
##

echo "Generate HTML documentation...";

mkdir -p $DIST_DIR'/doc/html';
for f in $RST_DIR/**; do
	FNAME=${f##*/};
	FNAME=${FNAME%.*};
	if [ -f "$f" ]; then
		echo $f' ==> '$FNAME'.html';
		pandoc -o "$HTML_DIR/$FNAME.html" -f rst -t html $f;
	fi
done
