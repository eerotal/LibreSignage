<?php

namespace libresignage\common\php\thumbnail;

use libresignage\common\php\Config;
use libresignage\common\php\thumbnail\ThumbnailUtil;
use libresignage\common\php\thumbnail\ThumbnailGeneratorException;
use libresignage\common\php\thumbnail\ThumbnailGeneratorInterface;

/**
* Thumbnail generator for image files.
*
* * This generator can be enabled with the ENABLE_GD_THUMBS config value.
*/
final class ImgThumbnailGenerator implements ThumbnailGeneratorInterface {
	/**
	* @see ThumbnailGeneratorInterface::provides_import()
	*/
	public static function provides_import(): array {
		return ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'];
	}

	/**
	* @see ThumbnailGeneratorInterface::provides_export()
	*/
	public static function provides_export(): array {
		return self::provides_import();
	}

	/**
	* @see ThumbnailGeneratorInterface::is_enabled()
	*/
	public static function is_enabled(): bool {
		return Config::config('ENABLE_GD_THUMBS');
	}

	/**
	* @see ThumbnailGeneratorInterface::ensure_prerequisites_met()
	*/
	public static function ensure_prerequisites_met() {
		if (!extension_loaded('gd')) {
			throw new ThumbnailGeneratorException(
				"Extension 'gd' needed for image thumbnail generation."
			);
		}
	}

	/**
	* @see ThumbnailGeneratorInterface::create()
	*/
	public static function create(
		string $src,
		string $dest,
		int $wmax,
		int $hmax
	) {
		if (!self::is_enabled()) { return; }
		$img = self::load_img($src);

		$w = imagesx($img);
		$h = imagesy($img);

		$dim = ThumbnailUtil::calc_thumbnail_resolution($w, $h, $wmax, $hmax);		
		$new = imagecreatetruecolor($dim['width'], $dim['height']);
		imagecopyresized(
			$new,
			$img,
			0, 0, 0, 0,
			$dim['width'],
			$dim['height'],
			$w,
			$h
		);
		self::write_img($new, $dest);
	}

	/**
	* Create an image resource.
	*
	* @param string $src The source image.
	*
	* @return resource   The loaded image resource.
	*
	* @throws ThumbnailGeneratorException if a file of invalid type is supplied.
	*/
	public static function load_img(string $src) {
		$mime = mime_content_type($src);
		switch($mime) {
			case 'image/png':
				return imagecreatefrompng($src);
			case 'image/jpg':
			case 'image/jpeg':
				return imagecreatefromjpeg($src);
			case 'image/gif':
				return imagecreatefromgif($src);
			default:
				throw new ThumbnailGeneratorException(
					self::class." doesn't provide import for ".$mime
				);
		}
	}

	/**
	* Write an image resource to file.
	*
	* @param resource $img The image resource.
	* @param string $dest  The destination path.
	*/
	public static function write_img($img, string $dest) {
		$mime = 'image/'.pathinfo($dest, PATHINFO_EXTENSION);
		switch($mime) {
			case 'image/png':
				return imagepng($img, $dest);
			case 'image/jpg':
			case 'image/jpeg':
				return imagejpeg($img, $dest);
			case 'image/gif':
				return imagegif($img, $dest);
			default:
				throw new ThumbnailGeneratorException(
					self::class." doesn't provide export for ".$mime
				);
		}
	}
}
