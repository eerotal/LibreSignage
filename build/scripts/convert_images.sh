#
# Utility functions for converting SVG images into PNG images.
# Transparency is preserved when converting.
# This script uses rsvg-convert for the conversion.
#

#!/bin/sh

split_name() { echo $1 | rev | cut -f 2 -d '.' | rev; }
echo_debug() { echo $1"/"$3" >> "$2"/"$(split_name $3)"_"$4".png"; }

svg_to_png() {
	src_dir=$1
	dest_dir=$2
	src_img=$3
	width=$(echo "$4" | cut -d'x' -f1)
	height=$(echo "$4" | cut -d'x' -f2)

	echo_debug $@
	rsvg-convert \
		--width="$width" \
		--height="$height" \
		--format="png" \
		--output="$dest_dir/$(split_name $src_img)_$(echo $width)x$(echo $height).png" \
		"$src_dir/$src_img"
}
