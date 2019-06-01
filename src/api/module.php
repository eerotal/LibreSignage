<?php

/*
*  Base class for API modules.
*/

require_once(LIBRESIGNAGE_ROOT.'/common/php/util.php');

abstract class APIModule {
	public function __construct() {}
	abstract function run(APIEndpoint $e, array $args);

	final public function check_args(array $req, array $args) {
		/*
		*  Check that all the required arguments in $req are defined
		*  in $args. Throws an ArgException if all required arguments
		*  are not defined.
		*/
		$diff = array_diff($req, array_keys($args));
		if (count($diff) === 0) { return; }

		throw new ArgException(
			"Missing arguments ".implode(', ', $diff)." for API module '"
			.get_class($this)."'."
		);
	}
}
