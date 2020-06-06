<?php

namespace libresignage\tests\backend\common\classes;

use libresignage\tests\backend\common\classes\APIInterface;
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
	*
	* @return Response The API response.
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
	*
	* @return Response The API response.
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

	/**
	* Add a slide to a queue.
	*
	* @param APIInterface $api The APIInterface to use.
	* @param string queue_name The name of the queue.
	* @param string slide_id   The slide ID.
	* @param int    pos        The position in the queue where
	*                          the slide is added.
	*
	* @return Response The API response.
	*/
	public static function add_slide(
		APIInterface $api,
		string $queue_name,
		string $slide_id,
		int $pos
	) {
		return $api->call_return_raw_response(
			'POST',
			'queue/queue_add_slide.php',
			[
				'queue_name' => $queue_name,
				'slide_id' => $slide_id,
				'pos' => $pos
			],
			[],
			TRUE
		);
	}

	/**
	* Remove a slide from a queue.
	*
	* @param APIInterface $api The APIInterface to use.
	* @param string queue_name The name of the queue.
	* @param string slide_id   The slide ID.
	*
	* @return Response The API response.
	*/
	public static function remove_slide(
		APIInterface $api,
		string $queue_name,
		string $slide_id
	) {
		return $api->call_return_raw_response(
			'POST',
			'queue/queue_remove_slide.php',
			[
				'queue_name' => $queue_name,
				'slide_id' => $slide_id
			],
			[],
			TRUE
		);
	}
}
