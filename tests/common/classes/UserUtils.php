<?php

namespace libresignage\tests\common\classes;

use libresignage\tests\common\classes\APIInterface;
use libresignage\common\php\JSONUtils;
use GuzzleHttp\Psr7\Response;

final class UserUtils {
	/**
	* Create a new user.
	*
	* @param APIInterface $api    The APIInterface to use.
	* @param string       $user   The name of the new user.
	* @param array        $groups The groups to assign the user to.
	*/
	public static function create(
		APIInterface $api,
		string $user,
		array $groups,
		bool $passwordless = FALSE
	): Response {
		return $api->call_return_raw_response(
			'POST',
			'user/user_create.php',
			[
				'user' => $user,
				'groups' => $groups,
				'passwordless' => $passwordless
			],
			[],
			TRUE
		);
	}

	/**
	* Remove a user.
	*
	* @param APIInterface $api  The APIInterface to use.
	* @param string       $user The name of the user to remove.
	*/
	public static function remove(
		APIInterface $api,
		string $user
	): Response {
		return $api->call_return_raw_response(
			'POST',
			'user/user_remove.php',
			['user' => $user],
			[],
			TRUE
		);
	}

}
