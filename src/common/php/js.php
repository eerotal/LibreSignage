<?php

/*
*  Convenience functions for including JS libraries.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');

const JS_LIBS = array(
	'bootstrap' => '<script src="/libs/bootstrap/dist/js/bootstrap.min.js"></script>',
	'popper'    => '<script src="/libs/popper.js/dist/popper.min.js"></script>',
	'jquery'    => '<script src="/libs/jquery/dist/jquery.min.js"></script>',
	'ace'       => '<script src="/libs/ace-builds/src-noconflict/ace.js"></script>'
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
