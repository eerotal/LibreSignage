<?php

/*
*  !!BUILD_VERIFY_NOCONFIG!!
*/

/*
*  ====>
*
*  *Save a slide. Whether a user is allowed to access this
*   API endpoint depends on the parameters passed to the
*   endpoint.*
*
*  Permissions
*   * id != null => Allow if the caller is in the admin
*     group or the caller is the owner of the slide and is
*     in the editor group.
*   * id == null => Allow if the caller is in the admin or
*     editor group.
*   * Otherwise allow restricted access if the caller is in
*     the collaborators array of the slide. In this case the
*     queue_name and collaborators parameters of the API
*     call are silently discarded.
*
*  POST JSON parameters
*    * id            = The ID of the slide to modify or either
*      undefined or null for new slide.
*    * name          = The name of the slide.
*    * index         = The index of the slide.
*    * time          = The amount of time the slide is shown.
*    * markup        = The markup of the slide.
*    * enabled       = Whether the slide is enabled or not.
*    * sched         = Whether the slide is scheduled or not.
*    * sched_t_s     = The slide schedule starting timestamp.
*    * sched_t_e     = The slide schedule ending timestamp.
*    * animation     = The slide animation identifier.
*    * queue_name    = The name of the slide queue of this slide.
*    * collaborators = A list of slide collaborators.
*
*  Return value
*    This endpoint returns all the parameters above as well as
*    two additional parameters:
*
*    * owner   = The owner of the slide.
*    * error   = An error code or API_E_OK on success.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/api/api.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/slide.php');

$SLIDE_SAVE = new APIEndpoint([
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_RESPONSE['JSON'],
	APIEndpoint::FORMAT => [
		'id' => API_P_STR|API_P_NULL,
		'name' => API_P_STR,
		'index' => API_P_INT,
		'markup' => API_P_STR|API_P_EMPTY_STR_OK,
		'owner' => API_P_UNUSED,
		'time' => API_P_INT,
		'enabled' => API_P_BOOL,
		'sched' => API_P_BOOL,
		'sched_t_s' => API_P_INT,
		'sched_t_e' => API_P_INT,
		'animation' => API_P_INT,
		'queue_name' => API_P_STR,
		'collaborators' => API_P_ARR_STR
	],
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
]);
api_endpoint_init($SLIDE_SAVE);

$OP = '';
$ALLOW = FALSE;

$user = $SLIDE_SAVE->get_caller();
$quota = new UserQuota($user);

$slide = new Slide();
if ($SLIDE_SAVE->has('id', TRUE)) {
	$slide->load($SLIDE_SAVE->get('id'));
}

/*
*  Check permissions.
*/
if ($SLIDE_SAVE->has('id', TRUE)) {
	// admin or editor+owner => ALLOW modifying.
	$ALLOW |= check_perm(
		'grp:admin;',
		$SLIDE_SAVE->get_caller()
	);
	$ALLOW |= check_perm(
		'grp:editor&usr:'.$slide->get_owner().';',
		$SLIDE_SAVE->get_caller()
	);
	$OP = 'modify';
} else {
	// admin or editor => ALLOW creation.
	$ALLOW |= check_perm(
		'grp:admin|grp:editor;',
		$SLIDE_SAVE->get_caller()
	);
	$OP = 'create';
}

if (!$ALLOW) {
	// Allow restricted access for collaborators.
	if (
		check_perm('grp:editor;', $SLIDE_SAVE->get_caller())
		&& in_array(
			$SLIDE_SAVE->get_caller()->get_name(),
			$slide->get_collaborators()
		)
	) {
		$ALLOW = TRUE;
		$OP = 'modify_collab';
	} else {
		throw new APIException(
			API_E_NOT_AUTHORIZED,
			"Not authorized."
		);
	}
}

if ($OP === 'create') {
	/*
	*  Set the current user as the owner and
	*  generate an ID for the new slide. Note
	*  that Slide::set_owner() must be called before
	*  Slide::set_collaborators(), which is why
	*  this if statement is here. Don't move it.
	*/
	$slide->gen_id();
	$slide->set_owner($user->get_name());
}

/*
*  Silently discard attempts to modify queue_name and collaborators
*  if a collaborator is saving the slide.
*/
if ($OP !== 'modify_collab') {
	$slide->set_queue($SLIDE_SAVE->get('queue_name'));
	$slide->set_collaborators($SLIDE_SAVE->get('collaborators'));
}

$slide->set_name($SLIDE_SAVE->get('name'));
$slide->set_index($SLIDE_SAVE->get('index'));
$slide->set_time($SLIDE_SAVE->get('time'));
$slide->set_markup($SLIDE_SAVE->get('markup'));
$slide->set_enabled($SLIDE_SAVE->get('enabled'));
$slide->set_sched($SLIDE_SAVE->get('sched'));
$slide->set_sched_t_s($SLIDE_SAVE->get('sched_t_s'));
$slide->set_sched_t_e($SLIDE_SAVE->get('sched_t_e'));
$slide->set_animation($SLIDE_SAVE->get('animation'));

if ($OP === 'create') {
	// Use quota.
	if (!$quota->use_quota('slides')) {
		throw new APIException(
			API_E_QUOTA_EXCEEDED,
			"Slide quota exceeded."
		);
	}
	$quota->flush();
}

$slide->write();

// Juggle slide indices.
$queue = new Queue($slide->get_queue_name());
$queue->load();
$queue->juggle($slide->get_id());

$SLIDE_SAVE->resp_set($slide->get_data_array());
$SLIDE_SAVE->send();
