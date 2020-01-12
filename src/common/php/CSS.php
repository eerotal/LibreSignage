<?php

namespace libresignage\common\php;

use libresignage\common\php\Util;
use libresignage\common\php\exceptions\IntException;

/**
* Functions for conveniently including CSS stylesheets in PHP code.
*/
final class CSS {
	const STYLESHEETS = [
		'font-awesome' => [
			'rel' => 'stylesheet',
			'href' => '/libs/@fortawesome/fontawesome-free/css/all.css'
		]
	];

	/**
	* Echo a HTML tag that includes the stylesheets in $req. $req must
	* be an array of stylesheet names defined in CSS::STYLESHEETS.
	*
	* @param array $req The stylesheets to use.
	* @throws ArgException if a stylesheet doesn't exist.
	*/
	public static function req(array $req) {
		foreach ($req as $l) {
			if (!array_key_exists($l, self::STYLESHEETS)) {
				throw newArgException("Stylesheet '$l' doesn't exist.");
			}
			echo Util::htmltag('link', '', self::STYLESHEETS[$l]);
		}
	}
}
