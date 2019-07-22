<?php
/*
*  ====>
*
*  Remove a slide.
*
*  **Request:** POST, application/json
*
*  Parameters
*    * id = The id of the slide to remove.
*
*  <====
*/

namespace libresignage\api\endpoint\slide;

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/Config.php');

use libresignage\api\APIEndpoint;
use libresignage\api\APIException;
use libresignage\api\HTTPStatus;
use libresignage\common\php\slide\Slide;
use libresignage\common\php\slide\Queue;
use libresignage\common\php\auth\User;

APIEndpoint::POST(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => [],
		'APIJSONValidatorModule' => [
			'schema' => [
				'type' => 'object',
				'properties' => [
					'id' => [
						'type' => 'string'
					]
				],
				'required' => ['id']
			]
		]
	],
	function($req, $module_data) {
		$caller = $module_data['APIAuthModule']['user'];
		$params = $module_data['APIJSONValidatorModule'];

		$slide = new Slide();
		$slide->load($params->id);

		$owner = new User();
		$owner->load($slide->get_owner());

		if (
			!$caller->is_in_group('admin')
			&& (
				!$caller->is_in_group('editor')
				|| $caller->get_name() !== $slide->get_owner()
			)
		) {
			throw new APIException(
				'Not authorized because user is not either in the group '.
				'admin or owner of the slide and in the group editor.',
				HTTPStatus::UNAUTHORIZED
			);
		}

		$slide->remove();

		$owner->get_quota()->free_quota('slides');
		$owner->write();

		return [];
	}
);
