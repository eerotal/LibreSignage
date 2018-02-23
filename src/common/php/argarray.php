<?php

/*
*  A general purpose system for accepting PHP function arguments
*  as an array.
*
*  Supported features
*
*    * argument type checking
*    * optional and required arguments
*    * default argument values for optional arguments
*    * specifying accepted argument values as an array
*/

class ArgumentArray {
	private $proto = NULL;
	private $def = NULL;
	public function __construct(array $proto, array $def) {
		/*
		*  Construct the ArgumentArray object describing the
		*  valid arguments and default values. $proto is
		*  an associative array of the form
		*
		*  $proto = array(
		*  	'ARG1' => 'TYPE1',
		*              .
		*              .
		*              .
		*       'ARGN' => 'TYPEN'
		*  );
		*
		*  where ARG1, ..., ARGN are the argument names and
		*  TYPE1, ..., TYPEN are the corresponding argument
		*  types. The argument names and types must be strings.
		*  The type strings are the ones returned by the PHP
		*  gettype() command. See the PHP docs for a list of
		*  possible types.
		*
		*  $def is an associative array of default values for
		*  the arguments and is of the form
		*
		*  $def = array(
		*  	'ARG1' => VALUE1,
		*              .
		*              .
		*              .
		*       'ARGN' => VALUEN
		*  );
		*
		*  where ARG1, ..., ARGN are the argument names and
		*  VALUE1, ..., VALUEN are the default values. If a
		*  default value is defined for an argument, the
		*  argument is considered optional. Note that the types
		*  of the default values are not checked to match the
		*  types of the arguments, so it's up to caller to make
		*  sure the types are correct.
		*/
		foreach($proto as $k) {
			if (gettype($k) != 'string' &&
				gettype($k) != 'array') {
				throw new ArgException(
					"Prototype variable types ".
					"must be strings or arrays."
				);
			}
		}
		foreach($def as $k => $v) {
			if (gettype($k) != 'string') {
				throw new ArgException(
					"Variable names in the $def ".
					"array must be strings."
				);
			}
		}
		$this->proto = $proto;
		$this->def = $def;
	}

	private function _chk_val($val, $arg_proto) {
		if (gettype($arg_proto) == 'string') {
			return gettype($val) == $arg_proto;
		} else if (gettype($arg_proto) == 'array'){
			return in_array($val, array_values($arg_proto));
		}
	}

	public function chk(array $args) {
		/*
		*  Check the argument array $args against the configured
		*  argument formats. This function returns the final
		*  argument array that also contains the proper default
		*  values for arguments that were not specified by the
		*  caller. If required arguments are missing or the
		*  types are wrong, exceptions are thrown.
		*/
		$ret = array();
		$def = $this->def;
		$proto = $this->proto;

		foreach($proto as $k => $v) {
			if (!in_array($k, array_keys($args))) {
				if (in_array($k, array_keys($def))) {
					$ret[$k] = $def[$k];
				} else {
					throw new ArgException(
						"Argument '".$k."' not ".
						"specified but no ".
						"default value exists."
					);
				}
				continue;
			}
			if ($this->_chk_val($args[$k], $v)) {
				$ret[$k] = $args[$k];
			} else if (gettype($v) == 'string') {
				throw new ArgException(
					"Expected type '$v' for '$k' ".
					"but got '{gettype($args[$k])}' ".
					"instead."
				);
			} else if (gettype($v) == 'array') {
				$astr = implode(', ', array_values($v));
				throw new ArgException(
					"'$k' must be one of [ $astr ]."
				);
			}
		}
		return $ret;
	}
}
