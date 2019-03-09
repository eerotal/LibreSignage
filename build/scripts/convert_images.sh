#
# Functions for converting SVG images into PNG images.
#

#!/bin/sh

split_name() { echo $1 | rev | cut -f 2 -d '.' | rev; }
echo_debug() { echo $1"/"$3" > "$2"/"`split_name $3`"_"$4".png"; }

convert_to_png() {
	src_dir=$1
	dest_dir=$2
	src_img=$3
	size=$4

	echo_debug $@
	convert \
		-size 1000x1000 \
		$src_dir"/"$src_img \
		-resize "$size" \
		-background white \
		-alpha off \
		$dest_dir"/"`split_name $src_img`"_"$size".png"
}

convert_to_transparent_png() {
	src_dir=$1
	dest_dir=$2
	src_img=$3
	size=$4

	echo_debug $@
	convert \
		-size 1000x1000 \
		$src_dir"/"$src_img \
		-resize "$size" \
		$dest_dir"/"`split_name $src_img`"_"$size".png"
}
