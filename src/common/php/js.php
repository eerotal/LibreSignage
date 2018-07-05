<?php

/*
*  Convenience functions for including JS libraries.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');

const JS_LIBS = array(
	'bootstrap' => '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"'.
			'integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"'.
			'crossorigin="anonymous"></script>',
	'popper'    => '<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"'.
			'integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"'.
			'crossorigin="anonymous"></script>',
	'jquery'    => '<script src="https://code.jquery.com/jquery-3.3.1.min.js"'.
			'integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="'.
			'crossorigin="anonymous"></script>',
	'ace'       => '<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.9/ace.js"></script>'
);

function js_include(array $libs) {
	foreach ($libs as $l) {
		if (!array_key_exists($l, JS_LIBS)) {
			throw new ArgException(
				"Attempted to include non-existent JS ".
				"library '$l'."
			);
		}
		echo JS_LIBS[$l];
	}
}
