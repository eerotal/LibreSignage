<?php

/*
*  !!BUILD_VERIFY_NOCONFIG!!
*/

/*
*  Convenience functions for including JS libraries
*  in frontend PHP files.
*/

function js_include_bootstrap() {
	echo '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"'.
	'integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"'.
	'crossorigin="anonymous"></script>';
}

function js_include_popper() {
	echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"'.
	'integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"'.
	'crossorigin="anonymous"></script>';
}

function js_include_jquery() {
	echo '<script src="https://code.jquery.com/jquery-3.3.1.min.js"'.
	'integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="'.
	'crossorigin="anonymous"></script>';
}

function js_include_ace() {
	echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.9/ace.js"></script>';
}
