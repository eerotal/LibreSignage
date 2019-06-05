<?php
/*
*  API error definitions.
*/

/*
*  This controls whether the API exception handler returns
*  detailed exception information in the JSON error message.
*/
const API_ERROR_TRACE = LIBRESIGNAGE_DEBUG;

/*
*  API error codes. These can be fetched via the
*  api_err_codes.php API endpoint.
*/
const API_E = [
	"API_E_OK"               => 0,
	"API_E_INTERNAL"         => 1,
	"API_E_INVALID_REQUEST"  => 2,
	"API_E_NOT_AUTHORIZED"   => 3,
	"API_E_QUOTA_EXCEEDED"   => 4,
	"API_E_LIMITED"          => 5,
	"API_E_CLIENT"           => 6, // Only for client side use!
	"API_E_RATE"             => 7,
	"API_E_INCORRECT_CREDS"  => 8,
	"API_E_LOCK"             => 9,
	"API_E_UPLOAD"           => 10,
	"API_E_INVALID_FILETYPE" => 11
];

// Define the error codes in the global namespace too.
foreach (API_E as $err => $code) {
	define($err, $code);
}

/*
*  Human readabled API error messages. These can be
*  fetched via the api_err_msgs.php API endpoint.
*/
const API_E_MSG = [
	API_E_OK => [
		"short" =>"No error",
		"long" => "No error occured."
	],
	API_E_INTERNAL => [
		"short" => "Internal server error",
		"long" => "The server encountered an internal ".
			"server error."
	],
	API_E_INVALID_REQUEST => [
		"short" => "Invalid request",
		"long" => "The server responded with an invalid ".
			"request error. This is probably due to a ".
			"software bug."
	],
	API_E_NOT_AUTHORIZED => [
		"short" => "Not authorized",
		"long" => "You are not authorized to perform this ".
			"action."
	],
	API_E_QUOTA_EXCEEDED => [
		"short" => "Quota exceeded",
		"long" => "You have exceeded your quota for this ".
			"action."
	],
	API_E_LIMITED => [
		"short" => "Limited",
		"long" => "The server prevented this action because a ".
			"server limit would have been exceeded."
	],
	API_E_CLIENT => [
		"short" => "Client error",
		"long" => "The client encountered an error."
	],
	API_E_RATE => [
		"short" => "API rate limited",
		"long" => "The server ignored an API call because the ".
			"API rate limit was exceeded."
	],
	API_E_INCORRECT_CREDS => [
		"short" => "Incorrect credentials received",
		"long" => "The authentication system received ".
			"incorrect credentials."
	],
	API_E_LOCK => [
		"short" => "Slide lock error",
		"long" => "A slide locking error occured."
	],
	API_E_UPLOAD => [
		"short" => "Upload error",
		"long" => "An error occured while uploading a file."
	],
	API_E_INVALID_FILETYPE => [
		"short" => "Invalid filetype",
		"long" => "An uploaded file had an invalid filetype."
	]
];

class APIException extends Exception {
	private $api_err = 0;

	public function __construct(
		int $api_err,
		string $message = "",
		int $code = 0,
		Throwable $previous = NULL
	) {
		$this->api_err = $api_err;
		parent::__construct($message, $code, $previous);
	}

	public function get_api_err() {
		return $this->api_err;
	}

	public static function make_json_string(
		int $api_err,
		Throwable $e
	): string {
		/*
		*  Get the JSON string representation of an
		*  Exception object. $api_err is an API error code
		*  and $e is the exception object. Note that $e
		*  doesn't necessarily have to be an APIException
		*  object; any exception will do.
		*/
		$err = ['error' => $api_err];

		if (API_ERROR_TRACE) {
			$err['thrown_at'] = $e->getFile().' @ ln: '.$e->getLine();
			$err['e_msg'] = $e->getMessage();
			$err['e_trace'] = $e->getTraceAsString();
		}

		$err_str = json_encode($err);
		if (
			$err_str == FALSE
			&& json_last_error() !== JSON_ERROR_NONE
		) {
			return '{"error": '.API_E_INTERNAL.'}';
		}
		return $err_str;
	}

	public static function map_to_code(Throwable $e): int {
		/*
		*  Map a LibreSignage exception to an API error
		*  code. Using a try ... catch statement instead of
		*  eg. switch(get_class($e)) provides a nicer way of
		*  handling extended exceptions.
		*/
		try {
			throw $e;
		} catch (APIException $e) {
			return $e->get_api_err();
		} catch (ArgException $e) {
			return API_E_INVALID_REQUEST;			
		} catch (IntException $e) {
			return API_E_INTERNAL;
		} catch (LimitException $e) {
			return API_E_LIMITED;
		} catch (FileTypeException $e) {
			return API_E_INVALID_FILETYPE;
		} catch (ConfigException $e) {
			return API_E_INTERNAL;
		} catch (Exception $e) {
			return API_E_INTERNAL;
		}
	}
}

function api_error_setup() {
	/*
	*  Setup exception handling for the API system.
	*/
	set_exception_handler(function(Throwable $e) {
		try {
			header('Content-Type: application/json');
			echo APIException::make_json_string(APIException::map_to_code($e), $e);
			ls_log($e->__toString(), LOGERR);
		} catch (Exception $e) {
			/*
			*  Exceptions thrown in the exception handler
			*  cause hard to debug fatal errors. Handle them.
			*/
			echo '{"error":'.API_E_INTERNAL.', "e_msg":"'.$e->getMessage().
				' (in exception handler, '.$e->getFile().' '.$e->getLine().')"}';
		}
		exit(1);
	});
}
