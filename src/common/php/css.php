<?php

/*
*  Convenience functions for including CSS libraries.
*/

require_once(LIBRESIGNAGE_ROOT.'/common/php/util.php');

const STYLESHEETS = [
	'font-awesome' => [
		'rel' => 'stylesheet',
		'href' => '/libs/@fortawesome/fontawesome-free/css/all.css'
	]
];

function require_css(array $req) {
	foreach ($req as $l) {
		if (!array_key_exists($l, STYLESHEETS)) {
			throw new IntException(
				"Attempted to include non-existent CSS library '$l'."
			);
		}
		echo htmltag('link', '', STYLESHEETS[$l]);
	}
}
