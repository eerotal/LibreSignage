<?php

namespace libresignage\common\php\thumbnail;

/**
* Interface for implementing thumbnail generators for
* different file formats.
*/
interface ThumbnailGeneratorInterface {
	/**
	* Get an array of import MIME types the thumbnail generator
	* supports.
	*
	* @return array An array of import MIME types (strings).
	*/
	public static function provides_import(): array;

	/**
	* Get an array of export MIME types the thumbnail generator
	* supports.
	*
	* @return array An array of export MIME types (strings).
	*/
	public static function provides_export(): array;

	/**
	* Ensure that any prerequisites for a thumbnail generator are met.
	* This function should throw a ThumbnailGeneratorException if
	* prerequisites are not met.
	*/
	public static function ensure_prerequisites_met();

	/**
	* Generate a thumbnail from $src that fits in a rectangle of
	* size $wmax x $hmax. The resulting thumbnail is written into
	* $dest.
	*
	* @param string $src  The source path.
	* @param string $dest The destionation path.
	* @param int $wmax    Maximum width.
	* @param int $hmax    Maximum height
	*
	* @throws ThumbnailGeneratorException if the MIME type of $src is not supported.
	*/
	public static function create(string $src, string $dest, int $wmax, int $hmax);
}
