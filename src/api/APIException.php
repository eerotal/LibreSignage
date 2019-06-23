<?php

require_once(LIBRESIGNAGE_ROOT.'/api/HTTPStatus.php');

use Symfony\Component\HttpFoundation\Response;
use common\php\JSONUtils;
use common\php\JSONException;

/**
* APIException class for sending error responses to clients. This class can
* either be instantiated directly or you can rely on the global error handler
* to convert all exceptions to APIExceptions. If you're writing code for the
* API system itself, try to stick to directly creating APIExceptions though,
* since it's a bit more flexible. Call APIException::setup() to setup the
* global error handler.
*/
class APIException extends Exception {
	/**
	* Get the API JSON representation of an Exception.
	*
	* @return string
	* @throws IntException If JSON encoding fails.
	*/
	public static function as_json(Throwable $e): string {
		$str = '';
		try {
			$str = JSONUtils::encode([
				'thrown_at' => $e->getFile().' @ ln: '.$e->getLine(),
				'e_msg' => $e->getMessage(),
				'e_trace' => $e->getTraceAsString()
			]);
		} catch (JSONException $e) {
			throw new APIException(
				'Failed to encode error response JSON',
				HTTPStatus::INTERNAL_SERVER_ERROR
			);
		}
		return $str;
	}

	/**
	* Setup exception handling for the API system.
	*/
	static function setup() {
		set_exception_handler(function(Throwable $e) {
			try {
				$response = new Response();
				$response->setStatusCode(HTTPStatus::to_http_status($e));
				$response->headers->set('Content-Type', 'application/json');
				if (LIBRESIGNAGE_DEBUG) {
					$response->setContent(APIException::as_json($e));
				}

				$response->send();
				ls_log($e->__toString(), LOGERR);
			} catch (Exception $e) {
				/*
				* Exceptions thrown in the exception handler
				* cause hard to debug fatal errors; handle them
				* here. Use as little code as possible in this
				* catch block to prevent further exceptions.
				*/
				http_response_code(500);
				header('Content-Type: text/plain');
				echo 'Exception thrown in global handler: '.$e->getMessage();
			}
			exit(1);
		});
	}
}
