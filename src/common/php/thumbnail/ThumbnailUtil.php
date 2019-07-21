<?php

namespace libresignage\common\php\thumbnail;

/**
* Utility functions for thumbnail generation.
*/
final class ThumbnailUtil {
	/**
	* Calculate the scaled thumbnail resolution.
	*
	* @param in $w    Original width.
	* @param in $h    Original height.
	* @param in $wmax Maximum width.
	* @param in $hmax Maximum height.
	*
	* @return array An array with the new 'width' and 'height' values.
	*/
	public static function calc_thumbnail_resolution(
		int $w,
		int $h,
		int $wmax,
		int $hmax
	): array {
		$ratio = ($wmax/$w < $hmax/$h) ? $wmax/$w : $hmax/$h;
		$nw = round($w*$ratio);
		$nh = round($h*$ratio);
		return [ 'width' => $nw, 'height' => $nh ];
	}
}
