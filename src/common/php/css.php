<?php

/*
*  Convenience functions for including CSS libraries.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');

const CSS_LIBS = array(
	'font-awesome' => '<link rel="stylesheet"'.
				'href="https://use.fontawesome.com/releases/v5.1.0/css/all.css"'.
				'integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt"'.
				'crossorigin="anonymous">'
);

function css_include(array $libs) {
	foreach ($libs as $l) {
		if (!array_key_exists($l, CSS_LIBS)) {
			throw new IntException(
				"Attempted to include non-existent ".
				"CSS library '$l'."
			);
		}
		echo CSS_LIBS[$l];
	}
}
