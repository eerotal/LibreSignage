<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');

$AUTH_USERS = NULL;
$AUTH_INITED = FALSE;

class User {
	private $user = '';
	private $hash = '';
	private $groups = NULL;
	private $ready = FALSE;

	public function set(string $user,
				array $groups,
				string $hash) {
		if (empty($user)) {
			throw new Exception('Invalid username for '.
					'User object.');
		}
		if (empty($hash)) {
			throw new Exception('Invalid password hash '.
					'for user object.');
		}
		$this->user = $user;
		$this->hash = $hash;
		if ($groups == NULL) {
			$this->groups = array();
		} else {
			$this->groups = $groups;
		}
		$this->ready = TRUE;
		return $this;
	}

	public function load(string $user) {
		/*
		*  Load data for the user $user from file.
		*/
		$dir = $this->_get_data_dir($user);
		$json = '';
		$data = NULL;

		if (!is_dir($dir)) {
			throw new Exception('No user named '.$user.'.');
		}
		try {
			$json = file_lock_and_get($dir.'/data.json');
		} catch(Exception $e) {
			throw $e;
		}
		if ($json === FALSE) {
			throw new Exception('Failed to read user data!');
		}
		$data = json_decode($json, $assoc=TRUE);
		if (json_last_error() != JSON_ERROR_NONE) {
			throw new Exception('JSON user data '.
					'decode error!');
		}

		try {
			$this->set($data['user'],
				$data['groups'],
				$data['hash']);
		} catch(Exception $e) {
			throw $e;
		}
		return $this;
	}

	public function remove() {
		/*
		*  Remove the currently loaded user from the server.
		*/
		$this->_error_on_not_ready();
		$dir = $this->_get_data_dir();
		if (!is_dir($dir)) {
			throw new Error("Failed to remove userdata: ".
					"Directory doesn't exist.");
		}
		if (rmdir_recursive($dir) === FALSE) {
			throw new Error('Failed to remove userdata.');
		}
	}

	public function write() {
		/*
		*  Write the userdata into files.
		*/
		$this->_error_on_not_ready();
		$dir = $this->_get_data_dir();
		$json = json_encode(array(
			'user' => $this->user,
			'groups' => $this->groups,
			'hash' => $this->hash
		));
		if ($json === FALSE &&
			json_last_error() != JSON_ERROR_NONE) {
			throw new Exception('Failed to JSON encode '.
						'userdata!');
		}
		if (!is_dir($dir)) {
			if (!@mkdir($dir, 0775, TRUE)) {
				throw new Exception('Failed to create user '.
						'directory!');
			}
		}

		try {
			file_lock_and_put($dir.'/data.json', $json);
		} catch (Exception $e) {
			throw $e;
		}
	}

	private function _get_data_dir($user=NULL) {
		$tmp = $user;
		if ($tmp == NULL) {
			$this->_error_on_not_ready();
			$tmp = $this->user;
		}
		return LIBRESIGNAGE_ROOT.USER_DATA_DIR.'/'.$tmp;
	}

	private function _error_on_not_ready() {
		if (!$this->is_ready()) {
			throw new Exception('User data not ready!');
		}
	}

	public function get_groups() {
		$this->_error_on_not_ready();
		return $this->groups;
	}

	public function get_name() {
		$this->_error_on_not_ready();
		return $this->user;
	}

	public function is_in_group(string $group) {
		$this->_error_on_not_ready();
		return in_array($group, $this->groups, TRUE);
	}

	public function is_ready() {
		return $this->ready;
	}

	public function set_ready(bool $val) {
		$this->ready = $val;
	}

	public function verify_password(string $pass) {
		$this->_error_on_not_ready();
		return password_verify($pass, $this->hash);
	}

	public function get_session_data() {
		/*
		*  Get data that can be set into the $_SESSION
		*  array.
		*/
		$this->_error_on_not_ready();
		return array(
			'user' => $this->get_name(),
			'groups' => $this->get_groups()
		);
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

	public function set_groups(array $groups) {
		$this->groups = $groups;
	}

	public function set_password(string $password) {
		$tmp_hash = password_hash($password, PASSWORD_DEFAULT);
		if ($tmp_hash === FALSE) {
			throw new Exception('Password hashing failed.');
		}
		$this->hash = $tmp_hash;
	}

	public function set_name(string $name) {
		$this->user = $name;
	}
}

function _auth_error_on_no_session() {
	if (session_status() == PHP_SESSION_NONE) {
		throw new Exception('Auth: No session active.');
	}
}

function _auth_inited_check(string $additional_msg = '') {
	global $AUTH_INITED;
	if (!$AUTH_INITED) {
		throw new Exception('Authentication system not '.
				'initialized. '.$additional_msg);
	}
}

function _auth_write_users(array $users) {
	/*
	*  Write the data from the User objects in $users to
	*  the userdata files. Throws an error if the authentication
	*  system is not initialized.
	*/
	_auth_inited_check();
	foreach ($AUTH_USERS as $u) {
		try {
			$u->write();
		} catch (Exception $e) {
			throw $e;
		}
	}
}

function _auth_load_users() {
	/*
	*  Load all the users from the userdata files.
	*  Returns an array with User objects in it or
	*  throws an Exception on error.
	*/
	$users = array();
	$tmp = NULL;

	$users_data_dir = LIBRESIGNAGE_ROOT.USER_DATA_DIR;
	$user_dirs = @scandir($users_data_dir);

	if ($user_dirs === FALSE) {
		throw new Exception('Failed to scan user data dir!');
	}
	$user_dirs = array_diff($user_dirs, array('.', '..'));

	foreach ($user_dirs as $d) {
		if (!is_dir($users_data_dir.'/'.$d)) { continue; }
		try {
			array_push($users, (new User())->load($d));
		} catch(Exception $e) {
			throw $e;
		}
	}
	return $users;
}

function auth_get_users() {
	global $AUTH_USERS;
	_auth_inited_check();
	return $AUTH_USERS;
}

function _auth_get_user_by_name(string $username) {
	/*
	*  Get the User object for $username from the
	*  $users User object array. Throws an error if
	*  the authentication system is not initialized.
	*/
	_auth_inited_check();
	if (empty($username)) {
		return NULL;
	}
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
	$user_obj = _auth_get_user_by_name($username);
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
	_auth_error_on_no_session();
	if (auth_is_authorized()) {
		// Already logged in in the current session.
		return TRUE;
	}

	if (!empty($username) && !empty($password)) {
		$tmp = _auth_verify_credentials($username, $password);
		if ($tmp != NULL) {
			// Login success.
			$_SESSION['user'] = $tmp->get_session_data();
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
	_auth_error_on_no_session();
	$_SESSION = array();

	if (ini_get('session.use_cookies')) {
		$cp = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$cp['path'], $cp['domain'],
			$cp['secure'], $cp['httponly']);
	}

	session_destroy();
}


function auth_is_authorized(array $groups = NULL,
				array $users = NULL,
				bool $redir = FALSE) {
	/*
	*  Check if the current session is authorized to access
	*  a page and return TRUE if it is. FALSE is returned
	*  otherwise. $groups and $users can optionally be used
	*  to filter which groups or users can access a page.
	*  These arguments are simply lists of group and user names.
	*  Note that if both $groups and $users are defined, the
	*  logged in user must only belong to either a group in
	*  $groups or be a user listed in $users. Both aren't required
	*  for access to be granted. If 'redir' is TRUE, this
	*  function also redirects the client to the login page
	*  or the HTTP 403 page if access is not granted.
	*
	*  If both $groups and $users are NULL, access is granted
	*  to all logged in users.
	*/
	_auth_error_on_no_session();
	$auth = FALSE;

	if (!empty($_SESSION['user'])) {
		if ($groups == NULL && $users == NULL) {
			//  Don't load data from files when not needed.
			return TRUE;
		} else {
			_auth_inited_check('auth_init() call required '.
					'when $groups != NULL or '.
					'$users != NULL in an '.
					'auth_is_authorized() call.');

			$user_obj = _auth_get_user_by_name(
				$_SESSION['user']['user']);

			if ($users != NULL) {
				if (in_array($_SESSION['user']['user'],
						$users)) {
					$auth = TRUE;
				}
			}
			if ($groups != NULL) {
				foreach ($groups as $g) {
					if ($user_obj->is_in_group($g)) {
						$auth = TRUE;
						break;
					}
				}
			}
			if (!$auth) {
				if ($redir) {
					error_redir(403);
				} else {
					return FALSE;
				}
			} else {
				return TRUE;
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

function auth_session_user() {
	_auth_error_on_no_session();
	return _auth_get_user_by_name($_SESSION['user']['user']);
}

function auth_init() {
	/*
	*  Initialize the authentication system. The caller must
	*  start a session before calling this function. If no
	*  session is active, this function throws an exception.
	*/
	global $AUTH_USERS, $AUTH_INITED;
	_auth_error_on_no_session();
	try {
		$AUTH_USERS = _auth_load_users();
	} catch (Exception $e) {
		// TODO: Error logging.
		throw $e;
	}
	$AUTH_INITED = TRUE;
}
