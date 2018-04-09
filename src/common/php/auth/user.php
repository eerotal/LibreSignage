<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');

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
			throw new ArgException('Invalid user for quota.');
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
			throw new IntException("Quota file doesn't ".
						"exist.");
		}
		$this->data = json_decode(file_lock_and_get($q_path),
						$assoc=TRUE);

		if ($this->data === NULL &&
			json_last_error() != JSON_ERROR_NONE) {
			throw new IntException('Failed to parse '.
					'quota JSON.');
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
		file_lock_and_put($this->_quota_path($this->user),
				$data_enc, TRUE);
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
	const API_KEY_LEN = 15;
	const API_KEY_MAX_AGE = 600;

	private $user = '';
	private $hash = '';
	private $groups = NULL;
	private $api_keys = NULL;
	private $ready = FALSE;

	public function __construct($name = NULL) {
		/*
		*  If $name != NULL, load the userdata for the
		*  user. Otherwise do nothing.
		*/
		if (empty($name)) {
			return;
		}
		$this->load($name);
	}

	private function _error_on_not_ready() {
		if (!$this->is_ready()) {
			throw new Exception('User data not ready!');
		}
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
			throw new IntException('Failed to read '.
						'user data!');
		}
		$data = json_decode($json, $assoc=TRUE);
		if (json_last_error() != JSON_ERROR_NONE) {
			throw new IntException('JSON user data '.
						'decode error!');
		}

		$this->set_name($data['user']);
		$this->set_groups($data['groups']);
		$this->set_hash($data['hash']);
		$this->set_api_keys($data['api_keys']);
		$this->set_ready(TRUE);
	}

	public function remove() {
		/*
		*  Remove the currently loaded user from the server.
		*/
		$this->_error_on_not_ready();
		$dir = $this->get_data_dir();
		if (!is_dir($dir)) {
			throw new IntException("Userdata doesn't ".
						"exist.");
		}
		if (rmdir_recursive($dir) === FALSE) {
			throw new IntException('Failed to remove '.
						'userdata.');
		}
	}

	public function write() {
		/*
		*  Write the userdata into files. Returns FALSE
		*  if the maximum amount of users is exceeded and
		*  TRUE otherwise.
		*/
		$this->_error_on_not_ready();
		$dir = $this->get_data_dir();
		$json = json_encode(array(
			'user' => $this->user,
			'groups' => $this->groups,
			'hash' => $this->hash,
			'api_keys' => $this->api_keys
		));
		if ($json === FALSE &&
			json_last_error() != JSON_ERROR_NONE) {
			throw new IntException('Failed to JSON encode '.
						'userdata!');
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

	function get_data_dir($user=NULL) {
		$tmp = $user;
		if ($tmp == NULL) {
			$this->_error_on_not_ready();
			$tmp = $this->user;
		}
		return LIBRESIGNAGE_ROOT.USER_DATA_DIR.'/'.$tmp;
	}

	// -- API key functions --
	public function set_api_keys($api_keys) {
		$this->api_keys = $api_keys;
	}

	public function get_api_keys() {
		$this->_error_on_not_ready();
		return $this->api_keys;
	}

	public function gen_api_key() {
		$this->_error_on_not_ready();

		$new_key = bin2hex(random_bytes(self::API_KEY_LEN));
		$tmp = array(
			'api_key' => $new_key,
			'created' => time(),
			'max_age' => self::API_KEY_MAX_AGE
		);
		array_push($this->api_keys, $tmp);

		return $tmp;
	}

	public function rm_api_key(string $api_key) {
		$this->_error_on_not_ready();
		foreach ($this->api_keys as $i => $d) {
			if ($d["api_key"] == $api_key) {
				array_splice($this->api_keys, $i, 1);
				return;
			}
		}
		throw new ArgException("No such API key.");
	}

	public function verify_api_key(string $api_key) {
		$this->_error_on_not_ready();
		$new_keys = $this->api_keys;

		foreach ($this->api_keys as $i => $d) {
			$tmp = $d['created'] + $d['max_age'];
			if ($d['api_key'] === $api_key && time() <= $tmp) {
				return TRUE;
			} else if (time() > $tmp) {
				// Mark the API expired key for purging.
				$new_keys[$i] = NULL;
			}
		}

		$this->api_keys = array_filter($new_keys);
		$this->write();

		return FALSE;
	}

	// -- Group functions --
	public function get_groups() {
		$this->_error_on_not_ready();
		return $this->groups;
	}

	public function is_in_group(string $group) {
		$this->_error_on_not_ready();
		return in_array($group, $this->groups, TRUE);
	}

	public function set_groups($groups) {
		if ($groups == NULL) {
			$this->groups = array();
		} else if (gettype($groups) == 'array') {
			if (count($groups) > gtlim('MAX_USER_GROUPS')) {
				throw new ArgException('Too many user '.
							'groups.');
			}
			$this->groups = $groups;
		} else {
			throw new ArgException('Invalid type for '.
						'$groups.');
		}
	}

	// -- Password functions --
	public function verify_password(string $pass) {
		$this->_error_on_not_ready();
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
		$this->_error_on_not_ready();
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
		$this->_error_on_not_ready();
		return $this->user;
	}

	// -- Object ready setting/checking functions --
	public function set_ready(bool $val) {
		$this->ready = $val;
	}

	public function is_ready() {
		return $this->ready;
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
	$user_dirs = array_diff($user_dirs, array('.', '..'));
	foreach ($user_dirs as $k => $d) {
		if (!user_exists($d)) {
			$user_dirs[$k] = NULL;
		}
	}
	return array_values(array_diff($user_dirs, array(NULL)));
}

function user_array() {
	/*
	*  Get an array of all the existing user objects.
	*/
	$names = user_name_array();
	$ret = array();
	foreach ($names as $n) {
		array_push($ret, new User($n));
	}
	return $ret;
}

function user_count() {
	/*
	*  Get the number of existing users.
	*/
	return count(user_array());
}
