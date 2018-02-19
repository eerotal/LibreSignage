#!/bin/bash

# A script to convert the LibreSignage Markdown documentation to HTML.

shopt -s globstar;
set -e
. build/scripts/build_conf.sh

if [ ! -d "$DIST_DIR" ]; then
        echo "DIST_DIR doesn't exist!";
        exit 1;
fi

# Generate the API documentation files.
function gen_api_doc {
        if [ -z "$1" ]; then
                return '';
        fi

        sed -n '/====>/,/<====/p' "$1"  | \
        sed 's/\s*\*\s\{0,2\}//m'       | \
        sed '/====>/d; /<====/d; /^\s*$/d; s/$/  /mg'
}

echo "# LibreSignage API Documentation" > $API_DOC;

echo "This document was automatically generated from the" >> $API_DOC;
echo "LibreSignage source files by the LibreSignage" >> $API_DOC;
echo "build system on `date`. The information below can also" >> $API_DOC;
echo "be found in the API endpoint source files." >> $API_DOC;

echo "" >> $API_DOC;
for f in $API_ENDPOINTS_DIR/**; do
	if [ "${f##*.}" == "php" ] && [ -f "$f" ]; then
		echo 'Gen API doc from "'$f'".';
		echo '**'${f#*/}'**  ' >> $API_DOC;
		echo '```' >> $API_DOC;
		gen_api_doc "$f" >> $API_DOC;
		echo '```' >> $API_DOC;
	elif [ -d "$f" ]; then
		echo "#####>> [DIR] $f" >> $API_DOC;
	fi
done

# Convert the Markdown documentation files to HTML files.
mkdir -p $DIST_DIR'/doc/html';
for f in $MD_DIR/**; do
	FNAME=${f##*/};
	FNAME=${FNAME%.*};
	if [ -f "$f" ]; then
		echo $f' ==> '$FNAME'.html';
		pandoc -o "$HTML_DIR/$FNAME.html" -f markdown -t html $f;
	fi
done
