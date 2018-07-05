<?php

/*
*  Convenience functions for including CSS libraries.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');

const CSS_LIBS = array(
	'font-awesome' => '<link rel="stylesheet" '.
				'href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" '.
				'integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" '.
				'crossorigin="anonymous">',
	'bootstrap'    => '<link rel="stylesheet" '.
				'href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" '.
				'integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" '.
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
