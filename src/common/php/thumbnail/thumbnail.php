<?php

/*
*  Thumbnail generator for LibreSignage. generate_thumbnail() is the
*  main entry point.
*/

require($_SERVER['DOCUMENT_ROOT'].'/common/php/thumbnail/imgthumb.php');

const MIME_HANDLER_MAP = [
	'/image\/.*/' => 'ImgThumb\gen_img_thumb'
];

class ThumbnailException extends Exception {};

function generate_thumbnail(
	string $src,
	string $dest,
	int $wmax,
	int $hmax
) {
	/*
	*  Generate a thumbnail for the file 'src'. This function
	*  generates thumbnails for many different file types depending
	*  on the implemented handlers and installed PHP modules.
	*/
	foreach(MIME_HANDLER_MAP as $regex => $handler) {
		if (preg_match($regex, mime_content_type($src))) {
			if ($handler($src, $dest, $wmax, $hmax)) {
				return TRUE;
			}
		}
	}
	return FALSE;
}
