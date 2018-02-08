<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/util.php');

class UserQuota {
	const DEF_LIM = array(
		'slides' => 20
	);

	private $user = NULL;
	private $quota = NULL;
	private $ready = FALSE;

	public function __construct(User $user, $def_lim = NULL) {
		if (!$user) {
			throw new Exception('Invalid user for quota.');
		}

		if (file_exists($this->_quota_path($user))) {
			// Load existing quota.
			$this->_load($user);
		} else {
			// Initialize new quota.
			$this->quota = array();
			if ($def_lim) {
				foreach ($def_lim as $k => $l) {
					$this->set_limit($k, $l);
				}
			} else {
				foreach (self::DEF_LIM as $k => $l) {
					$this->set_limit($k, $l);
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
			throw new Exception("Quota file doesn't exist.");
		}
		$this->quota = json_decode(file_lock_and_get($q_path),
						$assoc=TRUE);

		if ($this->quota === NULL &&
			json_last_error() != JSON_ERROR_NONE) {
			throw new Exception('Failed to parse '.
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
		$quota_enc = json_encode($this->quota);
		if ($quota_enc === FALSE &&
			json_last_error() != JSON_ERROR_NONE) {
			throw new Exception('Failed to JSON '.
					'encode quota.');
		}
		file_lock_and_put($this->_quota_path($this->user),
				$quota_enc, TRUE);
	}

	public function set_limit(string $key, int $limit) {
		/*
		*  Set the quota limit for $key.
		*/
		$tmp = 0;
		if (!empty($this->quota[$key]['used'])) {
			$tmp = $this->quota[$key]['used'];
		}
		$this->quota[$key] = array(
			'limit' => $limit,
			'used' => $tmp
		);
	}

	public function has_quota(string $key, int $amount = 1) {
		/*
		*  Check if a user has unused quota.
		*/
		if (empty($this->quota[$key]['limit'])) {
			return FALSE;
		}
		if ($this->quota[$key]['used'] + $amount <=
			$this->quota[$key]['limit']) {
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
			$this->quota[$key]['used'] += $amount;
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function free_quota(string $key, int $amount = 1) {
		/*
		*  Free $amount of $key quota.
		*/
		if (empty($this->quota[$key]['limit'])) {
			return FALSE;
		}
		if ($this->quota[$key]['used'] - $amount >= 0) {
			$this->quota[$key]['used'] -= $amount;
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function get_data() {
		return $this->quota;
	}
}

class User {
	private $user = '';
	private $hash = '';
	private $groups = NULL;
	private $ready = FALSE;

	public function __construct(string $user) {
		$this->load($user);
		return $this;
	}

	public function set(string $user,
				$groups,
				string $hash) {
		if (empty($hash)) {
			throw new Exception('Invalid password hash '.
					'for user object.');
		}

		$this->set_name($user);
		$this->set_groups($groups);
		$this->hash = $hash;
		$this->ready = TRUE;

		return $this;
	}

	public function load(string $user) {
		/*
		*  Load data for the user $user from file.
		*/
		if ($empty($user)) {
			throw new Exception('Invalid username.');
		}

		$dir = $this->get_data_dir($user);
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
		$dir = $this->get_data_dir();
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
		$dir = $this->get_data_dir();
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
		file_lock_and_put($dir.'/data.json', $json);
	}

	function get_data_dir($user=NULL) {
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

	public function set_groups($groups) {
		if ($groups == NULL) {
			$this->groups = array();
		} else if (gettype($groups) == 'array') {
			$this->groups = $groups;
		} else {
			throw new Exception('Invalid type for $groups.');
		}
	}

	public function set_password(string $password) {
		$tmp_hash = password_hash($password, PASSWORD_DEFAULT);
		if ($tmp_hash === FALSE) {
			throw new Exception('Password hashing failed.');
		}
		$this->hash = $tmp_hash;
	}

	public function set_name(string $name) {
		if (empty($name)) {
			throw new Exception('Invalid username.');
		}
		$this->user = $name;
	}
}
