<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/session.php');

class UserQuota {
	const Q_LIMIT = 'limit';
	const Q_DISP = 'disp';
	const Q_USED = 'used';

	const K_QUOTA = 'quota';
	const K_STATE = 'state';

	private $user = NULL;
	private $data = array(
		'quota' => NULL,
		'state' => NULL
	);
	private $ready = FALSE;

	public function __construct(User $user, $def_lim = NULL) {
		if (!$user) {
			throw new ArgException(
				'Invalid user for quota.'
			);
		}

		if (file_exists($this->_quota_path($user))) {
			// Load existing quota.
			$this->_load($user);
		} else {
			// Initialize new quota.
			$this->data = array(
				self::K_QUOTA => array(),
				self::K_STATE => array()
			);
			if ($def_lim) {
				foreach ($def_lim as $k => $l) {
					$this->set_limit($k, $l);
				}
			} else {
				foreach (DEFAULT_QUOTA as $k => $l) {
					$this->set_limit($k,
						$l[self::Q_LIMIT]);
					$this->set_disp($k,
						$l[self::Q_DISP]);
				}
			}
			$this->user = $user;
			$this->ready = TRUE;

			// Write the quota to file.
			$this->flush();
		}
		return $this;
	}

	private function _error_on_not_ready() {
		if (!$this->ready) {
			throw new Exception('Quota object not ready.');
		}
	}

	private function _quota_path(User $user) {
		return $user->get_data_dir().'/quota.json';
	}

	private function _load(User $user) {
		/*
		*  Load the quota data for $user from file.
		*/
		$q_path = $this->_quota_path($user);
		if (!is_file($q_path)) {
			throw new IntException("Quota file doesn't exist.");
		}

		$tmp = file_lock_and_get($q_path);
		$this->data = json_decode(
			$tmp,
			$assoc=TRUE
		);

		if ($this->data === NULL &&
			json_last_error() != JSON_ERROR_NONE) {
			throw new IntException(
				"Failed to parse quota JSON."
			);
		}
		$this->user = $user;
		$this->ready = TRUE;
		return $this;
	}

	public function flush() {
		/*
		*  Write the quota data to disk.
		*/
		$this->_error_on_not_ready();
		$data_enc = json_encode($this->data);
		if ($data_enc === FALSE &&
			json_last_error() != JSON_ERROR_NONE) {
			throw new IntException('Failed to JSON '.
					'encode quota data.');
		}
		file_lock_and_put(
			$this->_quota_path($this->user),
			$data_enc,
			TRUE
		);
	}

	public function get_limit(string $key) {
		if (isset($this->data[self::K_QUOTA][$key])) {
			return $this->data[self::K_QUOTA][$key][self::Q_LIMIT];
		}
		return NULL;
	}

	public function set_limit(string $key, int $limit) {
		/*
		*  Set the quota limit for $key.
		*/
		$tmp = 0;
		if ($this->get_limit($key) != NULL) {
			$tmp = $this->get_limit($key);
		}
		$this->data[self::K_QUOTA][$key] = array(
			self::Q_LIMIT => $limit,
			self::Q_USED => $tmp
		);
	}

	public function set_disp(string $key, string $disp) {
		/*
		*  Set the display name of a quota key.
		*/
		$this->data[self::K_QUOTA][$key][self::Q_DISP] = $disp;
	}

	public function has_quota(string $key, int $amount = 1) {
		/*
		*  Check if a user has unused quota.
		*/
		if ($this->get_limit($key) == NULL) {
			return FALSE;
		}
		if ($this->get_quota($key) + $amount <=
				$this->get_limit($key)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function use_quota(string $key, int $amount = 1) {
		/*
		*  Use $amount of $key quota.
		*/
		if ($this->has_quota($key, $amount)) {
			$this->set_quota($key,
				$this->get_quota($key) + $amount);
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function free_quota(string $key, int $amount = 1) {
		/*
		*  Free $amount of $key quota.
		*/
		if ($this->get_quota($key) - $amount >= 0) {
			$this->set_quota($key,
				$this->get_quota($key) - $amount);
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function set_quota(string $key, int $amount) {
		if ($this->get_limit($key) == NULL) {
			throw new Exception('No such quota limit.');
		}
		$this->data[self::K_QUOTA][$key][self::Q_USED] = $amount;
	}

	public function get_quota(string $key) {
		if ($this->get_limit($key) == NULL) {
			throw new Exception('No such quota limit exists.');
		}
		return $this->data[self::K_QUOTA][$key][self::Q_USED];
	}


	public function has_state_var($key) {
		return isset($this->data[self::K_STATE][$key]);
	}

	public function set_state_var($key, $val) {
		$this->data[self::K_STATE][$key] = $val;
	}

	public function get_state_var($key) {
		if (!$this->has_state_var($key)) {
			throw new Exception('No such state variable.');
		}
		return $this->data[self::K_STATE][$key];
	}

	public function get_quota_data() {
		return $this->data[self::K_QUOTA];
	}
}

class User {
	private $user = '';
	private $hash = '';
	private $groups = array();
	private $sessions = array();

	public function __construct($name = NULL) {
		/*
		*  If $name != NULL, load the userdata for the
		*  user. Otherwise do nothing.
		*/
		assert(!empty($name));
		$this->load($name);
	}

	public function load(string $user) {
		/*
		*  Load data for the user $user from file.
		*/
		$json = '';
		$data = NULL;
		$dir = NULL;

		if (empty($user)) {
			throw new ArgException('Invalid username.');
		}
		$dir = $this->get_data_dir($user);
		if (!is_dir($dir)) {
			throw new ArgException("No user named $user.");
		}
		$json = file_lock_and_get($dir.'/data.json');
		if ($json === FALSE) {
			throw new IntException('Failed to read user data!');
		}
		$data = json_decode($json, $assoc=TRUE);
		if (
			$data === NULL &&
			json_last_error() !== JSON_ERROR_NONE
		) {
			throw new IntException('JSON user data decode error!');
		}

		$this->set_name($data['user']);
		$this->set_groups($data['groups']);
		$this->set_hash($data['hash']);

		$tmp = NULL;
		foreach($data['sessions'] as $s) { 
			$tmp = new Session();
			$tmp->load($s);
			$this->sessions[] = $tmp;
		}
	}

	public function remove() {
		/*
		*  Remove the currently loaded user from the server.
		*/
		$dir = $this->get_data_dir();
		if (!is_dir($dir)) {
			throw new IntException("Userdata doesn't exist.");
		}
		if (rmdir_recursive($dir) === FALSE) {
			throw new IntException('Failed to remove userdata.');
		}
	}

	public function write() {
		/*
		*  Write the userdata into files. Returns FALSE
		*  if the maximum amount of users is exceeded and
		*  TRUE otherwise.
		*/
		$dir = $this->get_data_dir();

		$data = [
			'user' => $this->user,
			'groups' => $this->groups,
			'hash' => $this->hash,
			'sessions' => []
		];
		foreach ($this->sessions as $s) {
			$data['sessions'][] = $s->export();
		}

		$json = json_encode($data);
		if (
			$json === FALSE &&
			json_last_error() !== JSON_ERROR_NONE
		) {
			throw new IntException('Failed to JSON encode userdata!');
		}
		if (!is_dir($dir)) {
			// New user, check max users.
			if (user_count() + 1 > gtlim('MAX_USERS')) {
				return FALSE;
			}
		}
		file_lock_and_put($dir.'/data.json', $json);
		return TRUE;
	}

	function get_data_dir($user = NULL) {
		$tmp = $user;
		if ($tmp == NULL) { $tmp = $this->user; }
		return LIBRESIGNAGE_ROOT.USER_DATA_DIR.'/'.$tmp;
	}

	// -- Session functions --
	public function session_new(
		string $who,
		string $from,
		bool $permanent = FALSE
	) {
		/*
		*  Create a new session. This function returns an array
		*  with the keys 'session' and 'token'. 'session' contains
		*  the new Session object and 'token' contains the generated
		*  session token.
		*/
		$session = new Session();
		$token = $session->new($this, $who, $from, $permanent);
		$this->sessions[] = $session;
		return [
			'session' => $session,
			'token' => $token
		];
	}

	public function session_rm(string $id) {
		/*
		*  Remove an existing session with the session ID 'id'.
		*/
		foreach ($this->sessions as $i => $s) {
			if ($s->get_id() === $id) {
				array_splice($this->sessions, $i, 1);
				$this->sessions = array_values($this->sessions);
				return;
			}
		}
		throw new ArgException("No such session.");
	}

	public function session_n_rm(string $id) {
		/*
		*  'Negated' session_rm(). Remove all sessions except
		*  the session corresponding to the session ID 'id'.
		*/
		$s_new = $this->sessions;
		foreach ($s_new as $i => $s) {
			if ($s->get_id() !== $id) { $s_new[$i] = NULL; }
		}
		$this->sessions = array_values(array_filter($s_new));
	}

	public function session_token_verify(string $token) {
		/*
		*  Verify a session token against the sessions of
		*  this user. If a session matches the token, the
		*  Session object for the matching session is returned.
		*  This function also purges all expired sessions and
		*  writes the changes to disk.
		*/
		$s_new = $this->sessions;
		$ret = NULL;
		foreach ($this->sessions as $k => $s) {
			if ($s->is_expired()) {
				$s_new[$k] = NULL;
				continue;
			}
			if ($ret === NULL && $s->verify($token)) { $ret = $s; }
		}
		$this->sessions = array_values(array_filter($s_new));
		$this->write();
		return $ret;
	}

	public function session_get(string $id) {
		/*
		*  Get a session by its ID. NULL is returned if
		*  a session with the supplied ID doesn't exist.
		*/
		foreach ($this->sessions as $i => $s) {
			if ($s->get('id') === $id) { return $s; }
		}
		return NULL;
	}

	public function set_sessions($sessions) {
		/*
		*  Set the Session object array.
		*/
		$this->sessions = array_values($sessions);
	}

	public function get_sessions() {
		/*
		*  Get the Session object array.
		*/
		return $this->sessions;
	}

	// -- Group functions --
	public function get_groups() {
		return $this->groups;
	}

	public function is_in_group(string $group) {
		return in_array($group, $this->groups, TRUE);
	}

	public function set_groups($groups) {
		if ($groups == NULL) {
			$this->groups = [];
		} else if (gettype($groups) == 'array') {
			if (count($groups) > gtlim('MAX_USER_GROUPS')) {
				throw new ArgException('Too many user groups.');
			}
			$this->groups = $groups;
		} else {
			throw new ArgException("Invalid type for $groups.");
		}
	}

	// -- Password functions --
	public function verify_password(string $pass) {
		return password_verify($pass, $this->hash);
	}

	public function set_password(string $password) {
		if (strlen($password) > gtlim('PASSWORD_MAX_LEN')) {
			throw new ArgException('Password too long.');
		}

		$tmp_hash = password_hash($password, PASSWORD_DEFAULT);
		if ($tmp_hash === FALSE) {
			throw new IntException('Password hashing failed.');
		}

		$this->hash = $tmp_hash;
	}

	public function set_hash(string $hash) {
		if (empty($hash)) {
			throw new ArgException('Invalid password hash.');
		}
		$this->hash = $hash;
	}

	public function get_hash() {
		return $this->hash;
	}

	// -- Name functions --
	public function set_name(string $name) {
		if (empty($name)) {
			throw new ArgException('Invalid username.');
		}
		if (strlen($name) > gtlim('USERNAME_MAX_LEN')) {
			throw new ArgException('Username too long.');
		}
		$this->user = $name;
	}

	public function get_name() {
		return $this->user;
	}
}

function user_exists(string $user) {
	/*
	*  Check whether $user exists.
	*/
	try {
		new User($user);
	} catch (ArgException $e) {
		return FALSE;
	}
	return TRUE;
}

function user_name_array() {
	/*
	*  Get an array of all the existing usernames.
	*/
	$user_dirs = @scandir(LIBRESIGNAGE_ROOT.USER_DATA_DIR);
	if ($user_dirs === FALSE) {
		throw new IntException('scandir() on users dir failed.');
	}
	$user_dirs = array_diff($user_dirs, ['.', '..']);
	foreach ($user_dirs as $k => $d) {
		if (!user_exists($d)) { $user_dirs[$k] = NULL; }
	}
	return array_values(array_diff($user_dirs, array(NULL)));
}

function user_array() {
	/*
	*  Get an array of all the existing user objects.
	*/
	$names = user_name_array();
	$ret = array();
	foreach ($names as $n) { array_push($ret, new User($n)); }
	return $ret;
}

function user_count() {
	/*
	*  Get the number of existing users.
	*/
	return count(user_array());
}
