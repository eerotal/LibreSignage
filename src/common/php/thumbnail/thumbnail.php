<?php

/*
*  Thumbnail generator for LibreSignage. generate_thumbnail() is the
*  main entry function.
*/

require($_SERVER['DOCUMENT_ROOT'].'/common/php/thumbnail/common.php');
require($_SERVER['DOCUMENT_ROOT'].'/common/php/thumbnail/imgthumb.php');
require($_SERVER['DOCUMENT_ROOT'].'/common/php/thumbnail/vidthumb.php');

const MIME_HANDLER_MAP = [
	'/image\/.*/' => 'gen_img_thumb',
	'/video\/.*/' => 'gen_vid_thumb'
];

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
			/*
			*  Handle ThumbnailGeneratorExceptions and IntExceptions
			*  gracefully but let other exceptions bubble up.
			*/
			try {
				if ($handler($src, $dest, $wmax, $hmax)) { return TRUE; }
			} catch (ThumbnailGeneratorException $e) {
			} catch (IntException $e) {}
		}
	}
	return FALSE;
}
