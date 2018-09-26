<?php

/*
*  User quota object definition for the User class.
*/

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
