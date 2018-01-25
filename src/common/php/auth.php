<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');

$AUTH_USERS = NULL;
$AUTH_INITED = FALSE;

class User {
	private $user = '';
	private $pass_hash = '';
	private $groups = array();

	public function __construct(string $user, array $data) {
		if (empty($user)) {
			throw new Exception('Invalid username for '.
					'User object.');
		}
		if (empty($data) ||
			empty($data['hash']) ||
			!isset($data['groups'])) {
			throw new Error('Invalid data for User object.');
		}
		$this->user = $user;
		$this->pass_hash = $data['hash'];
		$this->groups = $data['groups'];
	}

	public function is_in_group(string $group) {
		return in_array($group, $this->groups, TRUE);
	}

	public function verify_password(string $pass) {
		return password_verify($pass, $this->pass_hash);
	}

	public function get_name() {
		return $this->user;
	}

	public function get_data_json() {
		/*
		*  Get the JSON encoded userdata that's used
		*  when writing it to the userdata file.
		*  Returns the JSON encoded data on success
		*  or throws an exception on failure.
		*/
		$tmp = json_encode(array(
			'hash' => $this->pass_hash,
			'groups' => $this->groups
		));
		if (!$tmp && json_last_error() != JSON_ERROR_NONE) {
			throw new Exception('Userdata JSON '.
					'encode failure.');
		}
		return $tmp;
	}

	public function get_data_session() {
		/*
		*  Get data that can be set into the $_SESSION
		*  array.
		*/
		return array(
			'groups' => $this->groups,
			'user' => $this->user
		);
	}

	public function get_groups() {
		return $this->groups;
	}

	public function add_group(string $group) {
		if (!in_array($group, $this->groups, TRUE)) {
			array_push($this->groups, $group);
		}
	}

	public function remove_group(string $group) {
		$i = array_search($group, $this->groups, TRUE);
		if ($i !== FALSE) {
			array_splice($this->groups, $i, 1);
		}
	}

	public function set_password(string $password) {
		$tmp_hash = password_hash($password, PASSWORD_DEFAULT);
		if ($tmp_hash === FALSE) {
			throw new Exception('Password hashing failed.');
		}
		$this->pass_hash = $tmp_hash;
	}
}

function _auth_write_users(array $users) {
	/*
	*  Write the data from the User objects in $users to
	*  the userdata file. Throws an error if the authentication
	*  system is not initialized.
	*/

	_auth_inited_check();

	$data_str = '';
	$users_file = LIBRESIGNAGE_ROOT.USER_DATA_DIR.'/passwd.json';
	foreach ($users as $u) {
		try {
			if ($data_str != '') {
				$data_str .= ',';
			}
			$data_str .= '"'.$u->get_name().'": '.
					$u->get_data_json();
		} catch (Exception $e) {
			// TODO: Error logging.
			throw $e;
		}
	}
	if (@file_put_contents($users_file,
		'{'.$data_str.'}') === FALSE) {
		throw new Exception('Failed to write userdata file.');
	}
}

function _auth_load_users() {
	/*
	*  Load all the users from the userdata file.
	*  Returns an array with User objects in it or
	*  throws an Exception on error.
	*/

	$users = array();
	$users_file = LIBRESIGNAGE_ROOT.USER_DATA_DIR.'/passwd.json';
	$users_data = json_decode(file_get_contents($users_file),
					$assoc=true);

	if ($users_data == NULL && json_last_error() != JSON_ERROR_NONE) {
		throw new Exception('Userdata JSON parse error.');
	}

	foreach (array_keys($users_data) as $user) {
		array_push($users, new User($user, $users_data[$user]));
	}
	return $users;
}

function auth_get_users() {
	global $AUTH_USERS;
	return $AUTH_USERS;
}

function _auth_get_user_by_name(string $username) {
	/*
	*  Get the User object for $username from the
	*  $users User object array. Throws an error if
	*  the authentication system is not initialized.
	*/

	_auth_inited_check();

	foreach (auth_get_users() as $u) {
		if ($u->get_name() == $username) {
			return $u;
		}
	}
	return NULL;
}

function _auth_verify_credentials(string $username, string $password) {
	/*
	*  Verify that the login system has the user 'username' and
	*  that the password matches 'password'. Returns the User object
	*  if the verification is successful and NULL otherwise.
	*  This function throws an exception if the authentication
	*  system is not initialized.
	*/

	_auth_inited_check();

	$user_obj = _auth_get_user_by_name($username, auth_get_users());
	if ($user_obj) {
		if ($user_obj->verify_password($password)) {
			return $user_obj;
		}
	}
	return NULL;
}

function auth_login(string $username, string $password) {
	/*
	*  Attempt to login with $username and $password.
	*  Returns TRUE if the login succeeds and FALSE
	*  otherwise. The $_SESSION data is also set when
	*  the login succeeds.
	*/

	if (session_status() != PHP_SESSION_ACTIVE) {
		throw new Exception('No session active when attempted '.
				'to login.');
	}

	if (auth_is_authorized()) {
		// Already logged in.
		return TRUE;
	}

	if (!empty($username) && !empty($password)) {
		$tmp = _auth_verify_credentials($username, $password);
		if ($tmp != NULL) {
			// Login success.
			$_SESSION['user'] = $tmp->get_data_session();
			return TRUE;
		}
	}
	// Login failed.
	return FALSE;
}

function auth_logout() {
	/*
	*  Logout the currently logged in user. The session needs to
	*  be started by the caller before calling this function.
	*  If no session is active, this function throws an exception.
	*/

	if (session_status() != PHP_SESSION_ACTIVE) {
		throw new Exception('No session active when attempted '.
				'to logout.');
	}

	$_SESSION = array();

	if (ini_get('session.use_cookies')) {
		$cp = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$cp['path'], $cp['domain'],
			$cp['secure'], $cp['httponly']);
	}

	session_destroy();
}


function auth_is_authorized(string $group = NULL, bool $redir = FALSE) {
	/*
	*  Check if the current session is authorized to access
	*  a page that only the users in the group $group can access
	*  and return TRUE if it is. FALSE is returned otherwise.
	*  If 'redir' is TRUE, this function also redirects
	*  the client to the login page or the HTTP 403 page if access
	*  is not granted.
	*
	*  If $group is set to NULL (which it is by default), this
	*  function grants access to all logged in users.
	*/

	if (!empty($_SESSION['user'])) {
		if ($group == NULL) {
			/*
			*  This shortcut speeds things up by not
			*  loading the userdata from file when not
			*  needed.
			*/
			return TRUE;
		} else {
			_auth_inited_check('auth_init() call required '.
					'when $group != NULL in an '.
					'auth_is_authorized() call.');
			$user_obj = _auth_get_user_by_name(
					$_SESSION['user']['user']);
			if ($user_obj->is_in_group($group)) {
				return TRUE;
			} else {
				if (!$redir) {
					return FALSE;
				}
				header($_SERVER['SERVER_PROTOCOL'].
					' 403 Forbidden');
				header('Location: '.ERR_403);
				exit(0);
			}
		}
	} else {
		if ($redir) {
			header('Location: '.LOGIN_PAGE);
			exit(0);
		}
		return FALSE;
	}
}

function _auth_inited_check(string $additional_msg = '') {
	global $AUTH_INITED;
	if (!$AUTH_INITED) {
		throw new Exception('Authentication system not '.
				'initialized. '.$additional_msg);
	}
}

function auth_init() {
	/*
	*  Initialize the authentication system. The caller must
	*  start a session before calling this function. If no
	*  session is active, this function throws an exception.
	*/
	global $AUTH_USERS, $AUTH_INITED;

	if (session_status() != PHP_SESSION_ACTIVE) {
		throw new Exception('No session active when attempted to'.
				'initialize authentication system.');
	}

	try {
		$AUTH_USERS = _auth_load_users();
	} catch (Exception $e) {
		// TODO: Error logging.
		throw $e;
	}
	$AUTH_INITED = TRUE;
}
