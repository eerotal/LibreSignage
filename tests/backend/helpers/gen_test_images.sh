#!/bin/sh

filename="$1"
width=480
height=420

if [ -z "$filename" ]; then
	echo "[Error] Please specify a filename for the image."
	exit 1
fi

convert -size "$width"x"$height" canvas:white "$filename"
