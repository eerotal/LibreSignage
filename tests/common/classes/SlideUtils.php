<?php

namespace classes;

use \classes\APIInterface;
use \GuzzleHttp\Psr7\Response;
use \GuzzleHttp\Psr7\MultipartStream;
use \common\php\JSONUtils;

/**
* Slide utility functions. These are mainly supposed to be used
* in test setup and teardown code where actually testing the
* endpoint is not intended.
*/
final class SlideUtils {
	/**
	* Lock a slide.
	*
	* @param APIInterface $api An APIInterface object.
	* @param string       $id  The ID of the slide to lock.
	*
	* @return Response The API response.
	*/
	public static function slide_lock(
		APIInterface $api,
		string $id
	): Response {
		return $api->call_return_raw_response(
			'POST',
			'slide/slide_lock_acquire.php',
			['id' => $id],
			[],
			TRUE
		);
	}

	/**
	* Release a slide lock.
	*
	* @param APIInterface $api An APIInterface object.
	* @param string       $id  The ID of the slide to release
	*
	* @return Response The API response.
	*/
	public static function slide_release(
		APIInterface $api,
		string $id
	): Response {
		return $api->call_return_raw_response(
			'POST',
			'slide/slide_lock_release.php',
			['id' => $id],
			[],
			TRUE
		);
	}

	/**
	* Save a slide.
	*
	* @param APIInterface $api An APIInterface object.
	* @param string|NULL  $id
	* @param string       $name
	* @param int          $index
	* @param int          $duration
	* @param string       $markup
	* @param bool         $enabled
	* @param bool         $sched
	* @param int          $sched_t_s
	* @param int          $sched_t_e
	* @param int          $animation
	* @param string       $queue_name
	* @param array        $collaborators
	*
	* @return Response The API response.
	*/
	public static function save_slide(
		APIInterface $api,
		$id,
		string $name,
		int $index,
		int $duration,
		string $markup,
		bool $enabled,
		bool $sched,
		int $sched_t_s,
		int $sched_t_e,
		int $animation,
		string $queue_name,
		array $collaborators
	): Response {
		return $api->call_return_raw_response(
			'POST',
			'slide/slide_save.php',
			[
				'id' => $id,
				'name' => $name,
				'index' => $index,
				'duration' => $duration,
				'markup' => $markup,
				'enabled' => $enabled,
				'sched' => $sched,
				'sched_t_s' => $sched_t_s,
				'sched_t_e' => $sched_t_e,
				'animation' => $animation,
				'queue_name' => $queue_name,
				'collaborators' => $collaborators
			],
			[],
			TRUE
		);
	}

	/**
	* Remove a slide.
	*
	* @param APIInterface $api An APIInterface object.
	* @param string       $id  The ID of the slide to remove.
	*
	* @return Response The API response.
	*/
	public static function remove_slide(
		APIInterface $api,
		string $id
	): Response {
		return $api->call_return_raw_response(
			'POST',
			'slide/slide_rm.php',
			['id' => $id],
			[],
			TRUE
		);
	}

	/**
	* Upload an asset to a slide.
	*
	* @param APIInterface $api  An APIInterface object.
	* @param string       $id   The id of the destination slide.
	* @param string       $path The path of the file to upload.
	*
	* @return Response The API response.
	*/
	public static function upload_asset(
		APIInterface $api,
		string $id,
		string $path
	): Response {
		$ms = new MultipartStream(
			[
				[
					'name' => 'body',
					'contents' => JSONUtils::encode([
						'id' => $id,
						'name' => basename($path)
					])
				],
				[
					'name' => '0',
					'contents' => fopen($path, 'r'),
					'filename' => basename($path)
				]
			]
		);
		return $api->call_return_raw_response(
			'POST',
			'slide/asset/slide_upload_asset.php',
			$ms,
			['Content-Type' => 'multipart/form-data; boundary='.$ms->getBoundary()],
			TRUE
		);
	}

	/**
	* Remove a slide asset.
	*
	* @param APIInterface $api  An APIInterface object.
	* @param string       $id   The id of the destination slide.
	* @param string       $name The name of the file to remove.
	*
	* @return Response The API response.
	*/
	public static function remove_asset(
		APIInterface $api,
		string $id,
		string $name
	): Response {
		return $api->call_return_raw_response(
			'POST',
			'slide/asset/slide_remove_asset.php',
			[
				'id' => $id,
				'name' => $name
			],
			[],
			TRUE
		);
	}
}
