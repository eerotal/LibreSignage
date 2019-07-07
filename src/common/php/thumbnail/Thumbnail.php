<?php

namespace common\php\thumbnail;

use \common\php\exceptions\ArgException;

final class Thumbnail {
	private $generators = [];

	public function __construct() {
		$this->register_generator('\\commmon\\php\\thumbnail\ImgThumbnailGenerator');
		$this->register_generator('\\commmon\\php\\thumbnail\VidThumbnailGenerator');
	}

	/**
	* Register a thumbnail generator class. $class must
	* implement ThumbnailGeneratorInterface.
	*
	* @param string $class The class name to register as a generator.
	*
	* @throws ArgException if $class is not defined.
	* @throws ArgException if $class doesn't implement ThumbnailGeneratorInterface.
	*/
	public function register_generator(string $class) {
		if (!class_exists($class, TRUE)) {
			throw new ArgException("Class $class not defined.");
		}

		if (
			!in_array(
				'ThumbnailGeneratorInterface',
				class_implements($class)
			)
		) {
			new ArgException(
				'$class must implement ThumbnailGeneratorInterface.'
			);
		}

		$this->generators[] = $class;
	}

	/**
	* Create a thumbnail for an image. The aspect ratio of the
	* original image is preserved while creating the thumbnail.
	* The original image is scaled so that it fits into the bounds
	* defined by $wmax x $hmax.
	*
	* @param string $src  The source path.
	* @param string $dest The destionation path.
	* @param int $wmax    Maximum width.
	* @param int $hmax    Maximum height
	*
	* @throws ThumbnailGeneratorException if a suitable generator is not found.
	*/
	public function create(
		string $src,
		string $dest,
		int $wmax,
		int $hmax
	) {
		foreach ($this->generators as $g) {
			if (
				in_array(
					mime_content_type($src),
					$g::provides_import(),
					TRUE
				)
				&& in_array(
					mime_content_type($dest),
					$g::provides_export(),
					TRUE
				)
				&& $g::is_enabled()
			) {
				$g::create($src, $dest, $wmax, $hmax);
			} else {
				throw new ThumbnailGeneratorException('No suitable thumbnail generator.');
			}
		}
	}
}
