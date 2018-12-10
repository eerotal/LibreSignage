<?php
/*
*  ====>
*
*   Save a slide. Whether a user is allowed to access this
*   API endpoint depends on the parameters passed to the
*   endpoint.
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
*  This endpoint only allows slide modification if the caller
*  has locked the slide by calling slide_lock_acquire.php first.
*  If the slide is not locked or is locked by someone else, the
*  API_E_LOCK error is returned in the 'error' value.
*
*  Note!
*
*  This endpoint accepts a few unused parameters to simplify
*  implementing the client interface for this endpoint.
*  Specifically, clients *must* be able to send all data
*  received from slide_get.php back to this endpoint, even if
*  it's not actually used. This makes it possible to implement
*  a simple Object Oriented interface that just sends all data
*  fields in the client side Slide object to this endpoint
*  without filtering what can be sent.
*
*  **Request:** POST, application/json
*
*  Parameters
*    * id            = The ID of the slide to modify or either
*      undefined or null for new slide.
*    * name          = The name of the slide.
*    * index         = The index of the slide.
*    * duration      = The duration of the slide.
*    * markup        = The markup of the slide.
*    * enabled       = Whether the slide is enabled or not.
*    * sched         = Whether the slide is scheduled or not.
*    * sched_t_s     = The slide schedule starting timestamp.
*    * sched_t_e     = The slide schedule ending timestamp.
*    * animation     = The slide animation identifier.
*    * queue_name    = The name of the slide queue of this slide.
*    * collaborators = A list of slide collaborators.
*    * owner         = Unused (see above)
*    * lock          = Unused (see above)
*    * assets        = Unused (see above)
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
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/slide/slide.php');

$SLIDE_SAVE = new APIEndpoint([
	APIEndpoint::METHOD		=> API_METHOD['POST'],
	APIEndpoint::RESPONSE_TYPE	=> API_MIME['application/json'],
	APIEndpoint::FORMAT_BODY => [
		'id' => API_P_STR|API_P_NULL,
		'name' => API_P_STR,
		'index' => API_P_INT,
		'markup' => API_P_STR|API_P_EMPTY_STR_OK,
		'owner' => API_P_UNUSED,
		'duration' => API_P_INT,
		'enabled' => API_P_BOOL,
		'sched' => API_P_BOOL,
		'sched_t_s' => API_P_INT,
		'sched_t_e' => API_P_INT,
		'animation' => API_P_INT,
		'queue_name' => API_P_STR,
		'collaborators' => API_P_ARR_STR,
		'lock' => API_P_UNUSED,
		'assets' => API_P_UNUSED
	],
	APIEndpoint::REQ_QUOTA		=> TRUE,
	APIEndpoint::REQ_AUTH		=> TRUE
]);

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

/*
*  Check slide lock.
*/
if ($OP === 'modify' || $OP === 'modify_collab') {
	$lock = $slide->get_lock();
	if ($lock === NULL) {
		throw new APIException(
			API_E_LOCK,
			"Slide not locked."
		);
	} else if (
		!$lock->is_expired()
		&& !$lock->is_owned_by($SLIDE_SAVE->get_session())
	) {
		throw new APIException(
			API_E_LOCK,
			"Slide locked by another user."
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
$slide->set_duration($SLIDE_SAVE->get('duration'));
$slide->set_markup($SLIDE_SAVE->get('markup'));
$slide->set_enabled($SLIDE_SAVE->get('enabled'));
$slide->set_sched($SLIDE_SAVE->get('sched'));
$slide->set_sched_t_s($SLIDE_SAVE->get('sched_t_s'));
$slide->set_sched_t_e($SLIDE_SAVE->get('sched_t_e'));
$slide->set_animation($SLIDE_SAVE->get('animation'));
$slide->check_sched_enabled();

$slide->set_ready(TRUE);

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

$SLIDE_SAVE->resp_set($slide->export(FALSE, FALSE));
$SLIDE_SAVE->send();
