<?php

class ThumbnailGeneratorException extends Exception {};

function get_thumbnail_resolution(int $w, int $h, int $wmax, int $hmax) {
	$ratio = ($wmax/$w < $hmax/$h) ? $wmax/$w : $hmax/$h;
	$nw = round($w*$ratio);
	$nh = round($h*$ratio);
	return [ 'width' => $nw, 'height' => $nh ];
}
