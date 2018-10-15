<?php
/*
*  Image thumbnail generator handler.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/thumbnail/common.php');

function create_img_object(string $src, string $mime) {
	/*
	*  Wrapper for loading an image with GD.
	*/
	switch($mime) {
		case 'image/png':
			return imagecreatefrompng($src);
		case 'image/jpeg':
			return imagecreatefromjpeg($src);
		case 'image/gif':
			return imagecreatefromgif($src);
		default:
			return NULL;
	}
}

function put_img_object(resource $img, string $dest, string $mime) {
	/*
	*  Wrapper for writing and image with GD.
	*/
	switch($mime) {
		case 'image/png':
			return imagepng($img, $dest);
		case 'image/jpeg':
			return imagejpeg($img, $dest);
		case 'image/gif':
			return imagegif($img, $dest);
		default:
			return NULL;
	}
}

function gen_img_thumb(
	string $src,
	string $dest,
	int $wmax,
	int $hmax
) {
	/*
	*  Generate a thumbnail from $src that fits in a rectangle of
	*  size $wmax x $hmax. The resulting thumbnail is written into
	*  $dest. This thumbnail generator function can be enabled with
	*  the ENABLE_GD_THUMBS constant in config.php. This function
	*  requires the PHP GD extension and automatically checks whether
	*  it's loaded before trying to create the thumbnail. Returns TRUE
	*  on success and FALSE if GD thumbnail generation is disabled.
	*/
	if (ENABLE_GD_THUMBS !== TRUE) { return FALSE; }
	if (!extension_loaded('gd')) {
		throw new ConfigException("Extension 'gd' not loaded.");
	}

	$mime = mime_content_type($src);
	$img = create_img_object($src, $mime);
	if (img === NULL) {
		throw new ThumbnailGeneratorException(
			"Invalid source image type."
		);
	}

	$w = imagesx($img);
	$h = imagesy($img);

	$dim = get_thumbnail_resolution($w, $h, $wmax, $hmax);		
	$new = imagecreatetruecolor($dim['width'], $dim['height']);
	imagecopyresized(
		$new, $img, 0, 0, 0, 0, $dim['width'], $dim['height'], $w, $h
	);
	put_img_object($new, $dest, $mime);

	return TRUE;
}
