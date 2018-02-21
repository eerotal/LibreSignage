<?php
/*
*  API error definitions.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');

// API errors
const API_E = array(
	"API_E_OK"		=> 0,
	"API_E_INTERNAL"	=> 1,
	"API_E_INVALID_REQUEST"	=> 2,
	"API_E_NOT_AUTHORIZED"	=> 3,
	"API_E_QUOTA_EXCEEDED"	=> 4,
	"API_E_LIMITED"		=> 5,
	"API_E_CLIENT"		=> 6, // Only for client side use!
	"API_E_RATE"		=> 7
);

// Define the error codes in the symbol table too.
foreach (API_E as $err => $code) {
	define($err, $code);
}

const API_E_MSG = array(
	API_E_OK => array(
		"short" =>"No error",
		"long" => "No error occured."
	),
	API_E_INTERNAL => array(
		"short" => "Internal server error",
		"long" => "The server encountered an internal server error."
	),
	API_E_INVALID_REQUEST => array(
		"short" => "Invalid request",
		"long" => "The server responded with and invalid request ".
			"error. This is probably due to a software bug."
	),
	API_E_NOT_AUTHORIZED => array(
		"short" => "Not authorized",
		"You are not authorized to perform this action."
	),
	API_E_QUOTA_EXCEEDED => array(
		"short" => "Quota exceeded",
		"long" => "You have exceeded your quota for this action."
	),
	API_E_LIMITED => array(
		"short" => "Limited",
		"long" => "The server prevented this action because a ".
			"server limit would have been exceeded."
	),
	API_E_CLIENT => array(
		"short" => "Client error",
		"long" => "The client encountered an error."
	),
	API_E_RATE => array(
		"short" => "API rate limited",
		"long" => "The server ignored an API call because the ".
			"API rate limit was exceeded."
	)
);

/*
*  Return detailed stack trace information with
*  API errors when this is TRUE.
*/
define("API_ERROR_TRACE", LIBRESIGNAGE_DEBUG);

class APIException extends Exception {
	private $api_err = 0;

	public function __construct(int $api_err,
				string $message = "",
				int $code = 0,
				Throwable $previous = NULL) {

		$this->api_err = $api_err;
		parent::__construct($message, $code, $previous);
	}

	public function get_api_err() {
		return $this->api_err;
	}

	public static function __to_api_string(int $api_err,
					Throwable $e) {
		/*
		*  Get the JSON string representation of an
		*  Exception object.
		*/
		$err = array(
			'error' => $api_err
		);

		if (API_ERROR_TRACE) {
			$err['thrown_at'] = $e->getFile().' @ ln: '.
						$e->getLine();
			$err['e_msg'] = $e->getMessage();
			$err['e_trace'] = $e->getTraceAsString();
		}

		$err_str = json_encode($err);
		if ($err_str == FALSE &&
			json_last_error() != JSON_ERROR_NONE) {
			return '{"error": '.API_E_INTERNAL.'}';
		}
		return $err_str;
	}
}

set_exception_handler(function(Throwable $e) {
	/*
	*  Set the exception handler for the API system.
	*/
	if (get_class($e) == 'APIException') {
		echo APIException::__to_api_string($e->get_api_err(), $e);
	} else {
		echo APIException::__to_api_string(API_E_INTERNAL, $e);
	}
	exit(1);
});
