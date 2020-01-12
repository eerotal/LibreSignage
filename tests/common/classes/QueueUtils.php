<?php

namespace libresignage\tests\common\classes;

use libresignage\tests\common\classes\APIInterface;
use GuzzleHttp\Psr7\Response;

/**
* Functions for working with Queue endpoints.
*/
final class QueueUtils {
	/**
	* Create a new queue.
	*
	* @param APIInterface $api  The APIInterface to use.
	* @param string       $name The name of the new queue.
	*/
	public static function create(
		APIInterface $api,
		string $name
	): Response {
		return $api->call_return_raw_response(
			'POST',
			'queue/queue_create.php',
			['name' => $name],
			[],
			TRUE
		);
	}

	/**
	* Remove a queue.
	*
	* @param APIInterface $api  The APIInterface to use.
	* @param string       $name The name of the queue to remove.
	*/
	public static function remove(
		APIInterface $api,
		string $name
	): Response {
		return $api->call_return_raw_response(
			'POST',
			'queue/queue_remove.php',
			['name' => $name],
			[],
			TRUE
		);
	}
}
