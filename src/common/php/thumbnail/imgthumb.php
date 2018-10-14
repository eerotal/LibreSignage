<?php

namespace ImgThumb;

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
			throw new ThumbnailException(
				"Unknown filetype."
			);
	}
}

function put_img_object(string $dest, string $mime) {
	/*
	*  Wrapper for writing and image with GD.
	*/
	switch($mime) {
		case 'image/png':
			return imagepng($src);
		case 'image/jpeg':
			return imagejpeg($src);
		case 'image/gif':
			return imagegif($src);
		default:
			throw new ThumbnailException(
				"Unknown filetype."
			);
	}
}

function gen_img_thumb(
	string $src,
	string $dest,
	int $wmax,
	int $hmax
) {
	/*
	*  Generate a thumbnail for the image in $src and put the
	*  thumbnail in $dest. The image is resized so that it fits
	*  in a rectangle of size $wmax x $hmax. Returns TRUE on
	*  success and FALSE on failure. This function requires GD
	*  and automatically checks whether it's loaded before trying
	*  to create the thumbnail.
	*/
	if (extension_loaded('gd')) {
		$img = create_img_object($src, mime_content_type($src));
		$w = imagesx($img);
		$h = imagesy($img);

		$ratio = ($wmax/$w < $hmax/$h) ? $wmax/$w : $hmax/$h;
		$nw = round($w*$ratio);
		$nh = round($h*$ratio);

		$new = imagecreatetruecolor($nw, $nh);
		imagecopyresized($new, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
		imagepng($new, $dest);
		return TRUE;
	} else {
		return FALSE;		
	}
}
