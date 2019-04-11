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
*  API_E_LOCK error is returned in the 'error' value. If a new
*  slide is created, the slide is automatically locked for the
*  caller.
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

require_once(LIBRESIGNAGE_ROOT.'/api/api.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/slide/slide.php');

$SLIDE_SAVE = new APIEndpoint([
	APIEndpoint::METHOD		    => API_METHOD['POST'],
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

$slide = new Slide();

/*
*  Check permissions and route the API call to the correct
*  handler function.
*/
if ($SLIDE_SAVE->has('id', TRUE)) {
	$slide->load($SLIDE_SAVE->get('id'));
	if (
		check_perm(
			'grp:admin;',
			$SLIDE_SAVE->get_caller()
		)
		|| check_perm(
			'grp:editor&usr:'.$slide->get_owner().';',
			$SLIDE_SAVE->get_caller()
		)
	) {
		// admin or editor+owner => ALLOW modifying.
		modify_slide($SLIDE_SAVE, $slide, TRUE);
	} else if (
		check_perm('grp:editor;', $SLIDE_SAVE->get_caller())
		&& in_array(
			$SLIDE_SAVE->get_caller()->get_name(),
			$slide->get_collaborators()
		)
	) {
		// Restricted modification permissions for collaborators.
		modify_slide($SLIDE_SAVE, $slide, FALSE);
	} else {
		throw new APIException(
			API_E_NOT_AUTHORIZED,
			"Not authorized."
		);
	}
} else if (
	check_perm(
		'grp:admin|grp:editor;',
		$SLIDE_SAVE->get_caller()
	)
) {
	// admin or editor => ALLOW creation.
	create_slide($SLIDE_SAVE, $slide);
}

function ensure_slide_lock(Slide $slide, Session $caller_session): void {
	/*
	*  Ensure that the slide $slide is locked by $session and
	*  the lock is not expired.
	*/
	$lock = $slide->get_lock();
	if ($lock === NULL) {
		throw new APIException(
			API_E_LOCK,
			"Slide not locked."
		);
	} else if (
		!$lock->is_expired()
		&& !$lock->is_owned_by($caller_session)
	) {
		throw new APIException(
			API_E_LOCK,
			"Slide locked by another user."
		);
	}	
}

function set_slide_data(
	APIEndpoint $endpoint,
	Slide $slide,
	bool $owner
): void {
	/*
	*  Set the slide data common to all operations.
	*/

	// Don't set 'queue_name' and 'collaborators' if $owner === FALSE.
	if ($owner === TRUE) {
		$slide->set_queue($endpoint->get('queue_name'));
		$slide->set_collaborators($endpoint->get('collaborators'));
	}

	$slide->set_name($endpoint->get('name'));
	$slide->set_index($endpoint->get('index'));
	$slide->set_duration($endpoint->get('duration'));
	$slide->set_markup($endpoint->get('markup'));
	$slide->set_enabled($endpoint->get('enabled'));
	$slide->set_sched($endpoint->get('sched'));
	$slide->set_sched_t_s($endpoint->get('sched_t_s'));
	$slide->set_sched_t_e($endpoint->get('sched_t_e'));
	$slide->set_animation($endpoint->get('animation'));

	$slide->set_ready(TRUE);
	$slide->check_sched_enabled();
}

function create_slide(
	APIEndpoint $endpoint,
	Slide $slide
): void {
	/*
	*  Handler function for creating the slide $slide.
	*
	*  Note that Slide::set_owner() must be called
	*  before Slide::set_collaborators()!
	*/
	$user = $endpoint->get_caller();
	$slide->gen_id();
	$slide->set_owner($user->get_name());
	$slide->lock_acquire($endpoint->get_session());

	set_slide_data($endpoint, $slide, TRUE);
	if (!$user->get_quota()->has_quota('slides')) {
		/*
		*  The user doesn't have slide quota so abort the slide
		*  creation process. The $queue->load() call below has
		*  $fix_errors = TRUE, which makes the Queue object
		*  remove the non-existent slide from the queue.
		*/
		$queue = new Queue($slide->get_queue_name());
		$queue->load(TRUE);

		throw new APIException(
			API_E_QUOTA_EXCEEDED,
			"Slide quota exceeded."
		);
	} else {
		$user->get_quota()->use_quota('slides');
		$user->write();
	}
	finish_slide($endpoint, $slide);
}

function modify_slide(
	APIEndpoint $endpoint,
	Slide $slide,
	bool $owner
): void {
	/*
	*  Handler function for modifying the slide $slide.
	*/
	ensure_slide_lock($slide, $endpoint->get_session());
	set_slide_data($endpoint, $slide, $owner);
	finish_slide($endpoint, $slide);
}

function finish_slide(
	APIEndpoint $endpoint,
	Slide $slide
): void {
	$slide->write();

	// Juggle slide indices.
	$queue = new Queue($slide->get_queue_name());
	$queue->load();
	$queue->juggle($slide->get_id());

	// Get the slide data from $queue since $queue->juggle() modifies it.
	$endpoint->resp_set(
		$queue->get_slide($slide->get_id())->export(FALSE, FALSE)
	);
	$endpoint->send();
}
