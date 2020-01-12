<?php

namespace libresignage\api;

use libresignage\common\php\exceptions\ArgException;
use libresignage\common\php\exceptions\FileTypeException;
use libresignage\common\php\exceptions\LimitException;

/**
* HTTPStatus contains all standard HTTP status codes as constants
* and also some handy function for using them.
*/
final class HTTPStatus {
	/* 1xx */
	const CONTINUE = 100;
	const SWITCHING_PROTOCOLS = 101;
	const PROCESSING = 102;
	const EARLY_HINTS = 103;

	/* 2xx */
	const OK = 200;
	const CREATED = 201;
	const ACCEPTED = 202;
	const NON_AUTHORITATIVE_INFORMATION = 203;
	const NO_CONTENT = 204;
	const RESET_CONTENT = 205;
	const PARTIAL_CONTENT = 206;
	const MULTI_STATUS = 207;
	const ALREADY_REPORTED = 208;
	const IM_USED = 226;

	/* 3xx */
	const MULTIPLE_DEVICES = 300;
	const MOVED_PERMANENTLY = 301;
	const FOUND = 302;
	const SEE_OTHER = 303;
	const NOT_MODIFIED = 304;
	const USE_PROXY = 305;
	const SWITCH_PROXY = 306;
	const TEMPORARY_REDIRECT = 307;
	const PERMANENT_REDIRECT = 308;

	/* 4xx */
	const BAD_REQUEST = 400;
	const UNAUTHORIZED = 401;
	const PAYMENT_REQUIRED = 402;
	const FORBIDDEN = 403;
	const NOT_FOUND = 404;
	const METHOD_NOT_ALLOWED = 405;
	const NOT_ACCEPTABLE = 406;
	const PROXY_AUTHENTICATION_REQUIRED = 407;
	const REQUEST_TIMEOUT = 408;
	const CONFLICT = 409;
	const GONE = 410;
	const LENGTH_REQUIRED = 411;
	const PRECONDITION_FAILED = 412;
	const PAYLOAD_TOO_LARGE = 413;
	const URI_TOO_LONG = 414;
	const UNSUPPORTED_MEDIA_TYPE = 415;
	const RANGE_NOT_SATISFIABLE = 416;
	const EXPECTATION_FAILED = 417;
	const IM_A_TEAPOT = 418;
	const MISDIRECTED_REQUEST = 421;
	const UNPROCESSABLE_ENTITY = 422;
	const LOCKED = 423;
	const FAILED_DEPENDENCY = 424;
	const TOO_EARLY = 425;
	const UPGRADE_REQUIRED = 426;
	const PRECONDITION_REQUIRED = 428;
	const TOO_MANY_REQUESTS = 429;
	const REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
	const UNAVAILABLE_FOR_LEGAL_REASONS = 451;

	/* 5xx */
	const INTERNAL_SERVER_ERROR = 500;
	const NOT_IMPLEMENTED = 501;
	const BAD_GATEWAY = 502;
	const SERVICE_UNAVAILABLE = 503;
	const GATEWAY_TIMEOUT = 504;
	const HTTP_VERSION_NOT_SUPPORTED = 505;
	const VARIANT_ALSO_NEGOTIATES = 506;
	const INSUFFICIENT_STORAGE = 507;
	const LOOP_DETECTED = 508;
	const NOT_EXTENDED = 510;
	const NETWORK_AUTHENTICATION_REQUIRED = 511;

	/**
	* Map different exceptions to HTTP error codes.
	*
	* @param Throwable $e The Throwable object to map to an HTTP error.
	* @return int The resulting HTTP error code.
	*/
	public static function to_http_status(\Throwable $e): int {
		try {
			throw $e;
		} catch (APIException $e) {
			return $e->getCode();
		} catch (ArgException $e) {
			return self::BAD_REQUEST;
		} catch (LimitException $e) {
			return self::FORBIDDEN;
		} catch (FileTypeException $e) {
			return self::BAD_REQUEST;
		} catch (\Exception $e) {
			return self::INTERNAL_SERVER_ERROR;
		}
	}
}
