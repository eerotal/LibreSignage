<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/api/modules/module.php');

// API type flags.
const API_P_STR          = (1 << 0);
const API_P_INT          = (1 << 1);
const API_P_FLOAT        = (1 << 2);
const API_P_OPT          = (1 << 3);
const API_P_NULL         = (1 << 4);
const API_P_BOOL         = (1 << 5);

// API array type flags.
const API_P_ARR_INT      = (1 << 6);
const API_P_ARR_STR      = (1 << 7);
const API_P_ARR_FLOAT    = (1 << 8);
const API_P_ARR_BOOL     = (1 << 9);
const API_P_ARR_MIXED    = (1 << 10);

// API data flags.
const API_P_EMPTY_STR_OK = (1 << 11);

// API convenience flags.
const API_P_ARR_ANY	= API_P_ARR_STR
                     |API_P_ARR_INT
                     |API_P_ARR_FLOAT
                     |API_P_ARR_BOOL
                     |API_P_ARR_MIXED;

const API_P_ANY     = API_P_STR
                     |API_P_INT
                     |API_P_FLOAT
                     |API_P_OPT
                     |API_P_NULL
                     |API_P_BOOL
                     |API_P_ARR_ANY;

const API_P_UNUSED  = API_P_ANY
                     |API_P_EMPTY_STR_OK
                     |API_P_OPT;

class APIValidatorKeyException extends ArgException {};
class APIValidatorTypeException extends ArgException {};
class APIValidatorValueException extends ArgException {};

class APIValidatorModule extends APIModule {
	/*
	*  Validate API request data and assign it into the
	*  supplied endpoint.
	*/

	private $format      = NULL;
	private $strict      = NULL;
	private $data_getter = NULL;
	
	public function __construct(
		array $format,
		$data_getter,
		bool $strict
	) {
		/*
		*  Construct a new APIValidatorModule object with a new
		*  format $format. The $format array is an array
		*  that contains integer values created by OR'ing
		*  together a subset of the API_P_* constants above.
		*  Below is an example of a valid $format array.
		*
		*  [
		*      'VALUE_1' => API_P_INT,
		*      'VALUE_2' => API_P_STR|API_P_OPT,
		*      'ARRAY'   => [
		*          'VALUE_A' => API_P_BOOL,
		*          'VALUE_B' => API_P_ANY
		*      ] 
		*  ]
		*
		*  As you can see, the $format array can also contain
		*  nested arrays that contain specific keys with specific
		*  types. A nested array can also be specified with
		*  the API_P_ARR_* constants. When APIValidatorModule validates
		*  such an array, it makes sure _all_ of the values in the
		*  array are of the same configured type, eg. 'string' if
		*  API_P_ARR_STR is used.
		*
		*  $data_getter is a callback used to get the data to validate
		*  when run() is called. $data_getter must return the data as
		*  an array. If run() is not used, $data_getter can be left NULL.
		*
		*  When $strict === TRUE, keys that exist in the validated
		*  data but not in the configured format are considered
		*  invalid.
		*/
		parent::__construct();

		$this->format = $format;
		$this->strict = $strict;
		$this->data_getter = $data_getter;
	}

	public function run(APIEndpoint $endpoint) {
		assert($this->data_getter !== NULL);
		$this->validate(call_user_func($this->data_getter));
	}

	public function validate(array $data) {
		$this->validate_array($data, $this->format);
	}

	private function validate_array(array $data, array $format) {
		/*
		*  Validate the array $data using $format. This function
		*  also calls itself recursively to validate all nested
		*  values in the $data array. This is an internal function.
		*  Call APIValidatorModule::validate() from outside code.
		*/

		// Check that each required value in $format also exists in $data.
		foreach ($format as $k => $f) {
			if (!array_key_exists($k, $data)) {
				if ((API_P_OPT & $f) === 0) { // Is required?
					throw new APIValidatorKeyException(
						"Missing required key '$k'."
					);
				}
			} else {
				if (gettype($f) === 'array') {
					$this->validate_array($data[$k], $f);
				} else {
					$this->check_type($data[$k], $f, $k);
					$this->check_value($data[$k], $f, $k);
				}
			}
		}

		// Consider extra keys in data invalid if $this->strict === TRUE.
		if ($this->strict === TRUE) {
			/*
			*  Don't change the following to use array_diff_key(), 
			*  it won't work. array_diff_key() does compute it's
			*  return value using array keys *BUT* the returned
			*  array still contains the array *entries* and not
			*  the *keys*.
			*/
			$diff = array_diff(array_keys($data), array_keys($format));
			if (!empty($diff)) {
				throw new APIValidatorKeyException(
					"Extra keys: [ ".implode(', ',$diff)." ]"
				);
			}
		}
	}

	private function check_type($data, int $format, $key) {
		/*
		*  Check the type of $data to make sure it matches
		*  the format $format.
		*/
		$extracted = $this->extract_format($data);
		if ($format & $extracted === 0) {
			if (
				$format & API_P_ARR_ANY !== 0
				&& $extracted & API_P_ARR_ANY !== 0
			) {
				throw new APIValidatorTypeException(
					"Invalid array item types for '$key'."
				);
			} else {
				throw new APIValidatorTypeException(
					"Invalid type '".gettype($data)."' for '$key'. "
				);
			}
		}
	}

	private function extract_array_format(array $array): int {
		/*
		*  Extract an array format bitmask from $array.
		*  This function is used in APIValidatorModule::extract_format().
		*/
		$types = [
			'string'  => API_P_ARR_STR,
			'integer' => API_P_ARR_INT,
			'double'  => API_P_ARR_FLOAT,
			'boolean' => API_P_ARR_BOOL
		];
		foreach ($types as $t => $bitmask) {
			if ($this->check_array_types($array, $t)) { return $bitmask; }
		}
		return API_P_ARR_MIXED;
	}

	private function extract_format($value): int {
		/*
		*  Extract a format bitmask from $value.
		*/
		switch (gettype($value)) {
			case 'NULL':    return API_P_NULL;
			case 'string':  return API_P_STR;
			case 'integer': return API_P_INT;
			case 'boolean': return API_P_BOOL;
			case 'double':  return API_P_FLOAT;
			case 'array':   return $this->extract_array_format($value);
			default:
				throw new APIValidatorTypeException(
					'Unknown variable type.'
				);
		}
	}

	private function check_array_types(array $data, string $type): bool {
		foreach ($data as $k => $v) {
			if (gettype($v) != $type) { return FALSE; }
		}
		return TRUE;
	}

	private function check_value($data, int $format, $key) {
		/*
		*  Check the data flags in $format against $data. Note
		*  that this function only works reliably if you call
		*  APIValidatorModule::check_type() first since this function
		*  doesn't do any type validation.
		*/
		if (
			(API_P_EMPTY_STR_OK & $format) === 0
			&& gettype($data) === 'string'
			&& empty($data)
		) {
			throw new APIValidatorValueException(
				"Invalid empty string '$key'."
			);
		}
	}
}
