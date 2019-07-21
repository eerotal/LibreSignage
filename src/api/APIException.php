<?php

namespace libresignage\api;

use libresignage\api\HTTPStatus;
use libresignage\common\php\Config;
use libresignage\common\php\Log;
use libresignage\common\php\JSONUtils;
use libresignage\common\php\exceptions\JSONException;
use Symfony\Component\HttpFoundation\Response;

/**
* APIException class for sending error responses to clients. This class can
* either be instantiated directly or you can rely on the global error handler
* to convert all exceptions to APIExceptions. If you're writing code for the
* API system itself, try to stick to directly creating APIExceptions though,
* since it's a bit more flexible. Call APIException::setup() to setup the
* global error handler.
*/
class APIException extends \Exception {
	/**
	* Get the API JSON representation of an Exception.
	*
	* @return string The JSON representation of the Exception.
	* @throws IntException If JSON encoding fails.
	*/
	public static function as_json(\Throwable $e): string {
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
	public static function setup() {
		set_exception_handler(function(\Throwable $e) {
			try {
				$response = new Response();
				$response->setStatusCode(HTTPStatus::to_http_status($e));
				$response->headers->set('Content-Type', 'application/json');

				if (Config::config('LIBRESIGNAGE_DEBUG')) {
					$response->setContent(APIException::as_json($e));
				}

				$response->send();
				Log::logs($e, Log::LOGERR);
			} catch (\Exception $e) {
				/*
				* Exceptions thrown in the exception handler
				* cause hard to debug fatal errors; handle them
				* here.
				*/
				echo "\n\nException thrown in global handler:\n";
				echo $e;
			}
			exit(1);
		});
	}
}
