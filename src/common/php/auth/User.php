<?php


namespace libresignage\common\php\auth;

use libresignage\common\php\Config;
use libresignage\common\php\Log;
use libresignage\common\php\Util;
use libresignage\common\php\JSONUtils;
use libresignage\common\php\auth\Session;
use libresignage\common\php\auth\UserQuota;
use libresignage\common\php\auth\exceptions\UserNotFoundException;
use libresignage\common\php\exportable\Exportable;
use libresignage\common\php\exceptions\IntException;
use libresignage\common\php\exceptions\ArgException;
use libresignage\common\php\exceptions\LimitException;

final class User extends Exportable {
	private $user = '';
	private $hash = NULL;
	private $groups = [];
	private $sessions = [];
	private $quota = NULL;
	private $passwordless = TRUE;

	const USERNAME_REGEX = '/^[A-Za-z0-9_]+$/';
	const GROUPS_REGEX = '/^[A-Za-z0-9_]+$/';

	public function __construct() {
		$this->quota = new UserQuota();
	}

	public function __exportable_set(string $name, $value) {
		$this->{$name} = $value;
	}

	public function __exportable_get(string $name) {
		return $this->{$name};
	}

	public function __exportable_write() {
		$this->write();
	}

	public static function __exportable_private(): array {
		return [
			'user',
			'hash',
			'groups',
			'sessions',
			'quota',
			'passwordless'
		];
	}

	public static function __exportable_public(): array {
		return [
			'user',
			'groups',
			'sessions',
			'quota',
			'passwordless'
		];
	}

	/**
	* Load user from file.
	*
	* @param string $name The name of the user to load.
	*
	* @throws UserNotFoundException if no user named $name exists.
	*/
	public function load(string $name) {
		if (!self::exists($name)) {
			throw new UserNotFoundException("Use '$name' doesn't exist.");
		}

		$this->fimport(self::get_data_file_path($name));
		$this->session_cleanup();
	}

	/**
	* Remove the loaded user from file.
	*
	* @throws IntException if the loaded user doesn't exist anymore.
	* @throws IntException if removing the user data fails.
	*/
	public function remove() {
		if (!is_dir($dir = User::get_dir_path($this->user))) {
			throw new IntException("User doesn't exist.");
		}
		if (Util::rmdir_recursive($dir) === FALSE) {
			throw new IntException('Failed to remove userdata.');
		}
	}

	/**
	* Write the user data into file.
	*
	* @throws LimitException if the maximum amount of users is reached.
	*                        A LimitException won't be thrown if the saved
	*                        user already exists since the number of users
	*                        doesn't increase.
	*/
	public function write() {
		if (!is_dir(self::get_dir_path($this->user))) {
			// New user, check max users.
			if (count(self::names()) + 1 > Config::limit('MAX_USERS')) {
				throw new LimitException('Too many users.');
			}
		}

		$json = JSONUtils::encode($this->export(TRUE, TRUE));
		Util::file_lock_and_put(self::get_data_file_path($this->user), $json);
	}

	/**
	* Get the data directory path for a user.
	*
	* @param string $user The username to get the path for.
	*
	* @return string The directory path.
	*/
	private static function get_dir_path(string $user): string {
		return (
			Config::config('LIBRESIGNAGE_ROOT').
			Config::config('USER_DATA_DIR').
			'/'.$user
		);
	}

	/**
	* Get the data file path for a user.
	*
	* @param string $user The username to get the path for.
	*
	* @return string The data file path.
	*/
	private static function get_data_file_path(string $user): string {
		return self::get_dir_path($user).'/data.json';
	}

	/**
	* Create a new session. This function returns an array
	* with the keys 'session' and 'token'. 'session' contains
	* the new Session object and 'token' contains the generated
	* session token.
	*
	* @param string $who       A string describing the caller.
	* @param string $from      The IP address of the caller.
	* @param bool   $permanent (optional) TRUE = Create a permanent session.
	*
	* @return array An array with the created session object
	*               and a session token.
	*/
	public function session_new(
		string $who,
		string $from,
		bool $permanent = FALSE
	) {
		$session = new Session();
		$token = $session->new($this, $who, $from, $permanent);
		$this->sessions[] = $session;
		return [
			'session' => $session,
			'token' => $token
		];
	}

	/**
	* Remove an existing session with the session ID 'id'.
	*
	* @param string $id The ID of the session to remove.
	* @throws ArgException if a session with ID $id doesn't exist.
	*/
	public function session_rm(string $id) {
		foreach ($this->sessions as $i => $s) {
			if ($s->get_id() === $id) {
				array_splice($this->sessions, $i, 1);
				$this->sessions = array_values($this->sessions);
				return;
			}
		}
		throw new ArgException("No such session.");
	}

	/**
	* 'Inverted' session_rm(). Remove all sessions except
	* the session corresponding to the session ID 'id'.
	*
	* @param string $id The session ID that's NOT removed.
	*/
	public function session_n_rm(string $id) {
		$s_new = $this->sessions;
		foreach ($s_new as $i => $s) {
			if ($s->get_id() !== $id) { $s_new[$i] = NULL; }
		}
		$this->sessions = array_values(array_filter($s_new));
	}

	/**
	* Cleanup all expired sessions.
	*/
	private function session_cleanup() {
		foreach ($this->sessions as $i => $s) {
			if ($s->is_expired()) {
				$this->sessions[$i] = NULL;
			}
		}
		$this->sessions = array_values(array_filter($this->sessions));
		$this->write();
	}

	/**
	* Verify a session token against the sessions of this user.
	* If a session matches the token, the Session object for the
	* matching session is returned. Otherwise NULL is returned.
	*
	* @param string $token The session token to verify.
	* @return Session|NULL The matching Session or NULL if not session matches.
	*/
	public function session_token_verify(string $token) {
		foreach ($this->sessions as $s) {
			if ($s->verify($token)) { return $s; }
		}
		return NULL;
	}

	/**
	* Get a session by its ID. NULL is returned if a session with
	* the supplied ID doesn't exist.
	*
	* @param string $id The session ID to use.
	*
	* @return Session|NULL The matching Session or NULL if no session matches.
	*/
	public function session_get(string $id) {
		foreach ($this->sessions as $i => $s) {
			if ($s->get_id() === $id) { return $s; }
		}
		return NULL;
	}

	/**
	* Validate that the $groups array contains valid group names.
	*
	* @param array $groups The groups array.
	*
	* @throws ArgException if count($groups) > MAX_USER_GROUPS.
	* @throws ArgException if there are non-string elements in $groups.
	* @throws ArgException if $groups contains empty strings.
	* @throws ArgException if there's a group longer than MAX_USER_GROUP_LEN.
	* @throws ArgException if a group name contains invalid chars.
	* @throws IntException if preg_match() fails.
	*/
	public static function validate_groups(array $groups) {
		if (count($groups) > Config::limit('MAX_USER_GROUPS')) {
			throw new ArgException('Too many groups.');
		}
		foreach ($groups as $g) {
			if (gettype($g) !== 'string') {
				throw new ArgException('Invalid type for group name.');
			} else if (empty($g)) {
				throw new ArgException('Invalid empty group name.');
			} else if (strlen($g) > Config::limit('MAX_USER_GROUP_LEN')) {
				throw new ArgException('Group name too long.');
			}
			$tmp = preg_match(User::GROUPS_REGEX, $g);
			if ($tmp === 0) {
				throw new ArgException('Invalid chars in groups names.');
			} else if ($tmp === FALSE) {
				throw new IntException('preg_match() failed.');
			}
		}
	}

	/**
	* Set the groups of a User. Calling this function with $groups = NULL
	* removes the User from all groups.
	*
	* @see User::validate_group() For validation exceptions.
	*
	* @param array $groups An array of groups or NULL for no groups.
	*/
	public function set_groups(array $groups = NULL) {
		if ($groups === NULL) {
			$this->groups = [];
		} else {
			User::validate_groups($groups);
			$this->groups = $groups;
		}
	}

	/**
	* Check whether a user is in a a group or in an array of groups. Strict
	* comparison is used when comparing groups (strings).
	*
	* @param mixed $groups A group or an array of groups (strings) to check.
	*
	* @return bool TRUE if the loaded user is in $groups, FALSE otherwise.
	*
	* @throws InvalidArgumentException if $groups is not a string or an array.
	*/
	public function is_in_group($groups): bool {
		assert(
			is_string($groups) || is_array($groups),
			new \InvalidArgumentException('$groups must be an array or a string.')
		);
		if (!is_array($groups)) { $groups = [$groups]; }

		foreach ($this->groups as $g) {
			if (in_array($g, $groups, TRUE)) { return TRUE; }
		}
		return FALSE;
	}

	/**
	* Make sure $password is a valid password string.
	*
	* @param string $password The password to validate.
	* @throws ArgException if $password is empty.
	* @throws ArgException if strlen($password) > PASSWORD_MAX_LEN.
	*/
	public static function validate_password(string $password) {
		if (strlen($password) === 0) {
			throw new ArgException('Invalid empty password.');
		} else if (strlen($password) > Config::limit('PASSWORD_MAX_LEN')) {
			throw new ArgException('Password too long.');
		}
	}

	/**
	* Set the password of the loaded user.
	*
	* @see User::validate_password() For validation exceptions.
	*
	* @param string $password The new password.
	* @throws IntException if password_hash() fails.
	*/
	public function set_password(string $password) {
		User::validate_password($password);
		$tmp_hash = password_hash($password, PASSWORD_DEFAULT);
		if ($tmp_hash === FALSE) {
			throw new IntException('Password hashing failed.');
		}
		$this->hash = $tmp_hash;
		$this->passwordless = FALSE;
	}

	/**
	* Verify the password $pass against the loaded user.
	*
	* If the password hash in $this->hash is NULL, this method
	* always returns TRUE. This makes it possible to create
	* "no login" users.
	*
	* @param string $pass The password to verify.
	* @return bool TRUE if the password is correct, FALSE otherwise.
	*/
	public function verify_password(string $pass): bool {
		if ($this->hash === NULL) {
			// No password hash -> no login required.
			return TRUE;
		} else {
			return password_verify($pass, $this->hash);
		}
	}

	/**
	* Make sure $hash is a valid password hash.
	*
	* @param string $hash A password hash.
	* @throws ArgException if $hash is empty.
	*/
	public static function validate_hash(string $hash) {
		if (strlen($hash) === 0) {
			throw new ArgException('Invalid password hash.');
		}
	}

	/**
	* Set the password hash of the loaded user.
	*
	* @see User::validate_hash() For validation exceptions.
	*/
	public function set_hash(string $hash) {
		User::validate_hash($hash);
		$this->hash = $hash;
	}

	/**
	* Make sure $name is a valid username.
	*
	* @param string $name The username to validate.
	* @throws ArgException if the username is empty.
	* @throws ArgException if strlen($name) > USERNAME_MAX_LEN.
	* @throws ArgException if the username contains invalid chars.
	* @throws IntException if preg_match() fails.
	*/
	public static function validate_name(string $name) {
		if (strlen($name) === 0) {
			throw new ArgException('Invalid empty username.');
		} else if (strlen($name) > Config::limit('USERNAME_MAX_LEN')) {
			throw new ArgException('Username too long.');
		}
		$tmp = preg_match(User::USERNAME_REGEX, $name);
		if ($tmp === 0) {
			throw new ArgException('Username contains invalid characters.');
		} else if ($tmp === FALSE) {
			throw new IntException('preg_match() failed.');
		}
	}

	/**
	* Set the username of the loaded user.
	*
	* @see User::validate_name() for validation exceptions.
	*
	* @param string $name The new username.
	*/
	public function set_name(string $name) {
		User::validate_name($name);
		$this->user = $name;
	}

	/**
	* Check whether a user is a specific user or in a list of users. Strict
	* comparison is used when comparing usernames.
	*
	* @param array|string $names A username or an array of usernames (strings).
	*
	* @return bool TRUE if the loaded user is in $names.
	*
	* @throws InvalidArgumentException if $names is not a string or an array.
	*/
	public function is_user($names): bool {
		assert(
			is_string($names) || is_array($names),
			new \InvalidArgumentException('$names must be a string or an array.')
		);
		if (!is_array($names)) { $names = [$names]; }
		return in_array($this->name, $names, TRUE);
	}

	public function get_sessions() { return $this->sessions; }
	public function get_groups() { return $this->groups; }
	public function get_hash() { return $this->hash; }
	public function get_name() { return $this->user; }
	public function get_quota() { return $this->quota; }
	public function is_passwordless() { return $this->passwordless; }

	/**
	* Get an array of all the existing user objects.
	*
	* @return array An array of User objects.
	*/
	public static function all(): array {
		$names = self::names();
		$ret = [];
		foreach ($names as $n) {
			$u = new User();
			$u->load($n);
			array_push($ret, $u);
		}
		return $ret;
	}

	/**
	* Get an array of all the existing usernames.
	*
	* @return array An array of usernames.
	*
	* @throws IntException if scandir() fails.
	*/
	public static function names(): array {
		$users = NULL;

		try {
			$users = scandir(
				Config::config('LIBRESIGNAGE_ROOT').
				Config::config('USER_DATA_DIR')
			);
		} catch (\ErrorException $e) {
			throw new IntException('scandir() on users dir failed.');
		}

		$users = array_diff($users, ['.', '..']);
		foreach ($users as &$u) {
			if (
				!is_dir(self::get_dir_path($u))
				|| !is_file(self::get_data_file_path($u))
			) { $u = NULL; }
		}
		return array_values(array_filter($users));
	}

	/**
	* Check whether $user exists.
	*
	* @return bool TRUE if $user exists, FALSE otherwise.
	*/
	public static function exists(string $user): bool {
		return in_array($user, self::names(), TRUE);
	}
}
