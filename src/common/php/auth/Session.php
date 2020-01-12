<?php

namespace libresignage\common\php\auth;

use libresignage\common\php\Config;
use libresignage\common\php\Util;
use libresignage\common\php\exportable\Exportable;
use libresignage\common\php\auth\User;
use libresignage\common\php\exceptions\IntException;
use libresignage\common\php\exceptions\ArgException;

/**
* Class for handling session data and authentication.
*/
final class Session extends Exportable {
	const ID_DELIMITER = '_';
	const TRUNC_LEN    = 45;
	const WHO_REGEX    = '/^[A-Za-z0-9_-]+$/';

	private $id = NULL;
	private $who = NULL;
	private $from = NULL;
	private $created = NULL;
	private $max_age = NULL;
	private $permanent = NULL;
	private $token_hash = NULL;

	public function __exportable_set(string $name, $value) {
		$this->{$name} = $value;
	}

	public function __exportable_get(string $name) {
		return $this->{$name};
	}

	public function __exportable_write() {}

	public static function __exportable_private(): array {
		return [
			'id',
			'who',
			'from',
			'created',
			'max_age',
			'permanent',
			'token_hash'
		];
	}

	public static function __exportable_public(): array {
		return [
			'id',
			'who',
			'from',
			'created',
			'max_age',
			'permanent'
		];
	}

	/**
	* Validate a caller description string.
	*
	* @param string $who The string to validate.
	*
	* @throws ArgException if $who is empty.
	* @throws IntException if preg_match() fails.
	* @throws ArgException if $who contains invalid characters.
	*/
	public static function validate_who(string $who) {
		if (empty($who)) {
			throw new ArgException("Invalid empty 'who'.");
		}

		$tmp = preg_match(self::WHO_REGEX, $who);
		if ($tmp === FALSE) {
			throw new IntException('preg_match() failed.');
		} else if ($tmp === 0) {
			throw new ArgException("Invalid characters in 'who'.");
		}
	}

	/**
	* Set the 'who' description of a session.
	*
	* @param string $who The description.
	*
	* @see Session::validate_who() for validation exceptions.
	*/
	public function set_who(string $who) {
		self::validate_who($who);
		$this->who = substr($who, 0, Session::TRUNC_LEN);
	}

	/**
	* Validate a 'from' IP address.
	*
	* @param string $from The IP address to validate.
	*
	* @throws ArgException if $from is not a valid IP address.
	*/
	public static function validate_from(string $from) {
		if (!filter_var($from, FILTER_VALIDATE_IP)) {
			throw new ArgException("Invalid 'from' IP address.");
		}
	}

	/**
	* Set the 'from' IP address of a session.
	*
	* @param strinf $from The IP address.
	*
	* @see Session::validate_from() for validation exceptions.
	*/
	public function set_from(string $from) {
		self::validate_from($from);
		$this->from = substr($from, 0, Session::TRUNC_LEN);
	}

	/**
	* Generate an ID for a session.
	*
	* @param User $user The user the session belongs to.
	*/
	private function gen_id(User $user) {
		$this->id = $user->get_name().Session::ID_DELIMITER.Util::get_uid();
	}

	/**
	* Load a session based on its ID.
	*
	* @param string $id The ID of the session to load.
	*
	* @return Session|NULL The Session on success or NULL on failure.
	*/
	public static function from_id(string $id) {
		$u = new User();
		$u->load(explode(Session::ID_DELIMITER, $id)[0]);
		return $u->session_get($id);
	}

	/**
	* Create a new session and return the generated session token.
	* Note that only the session token hash is stored in this object
	* and the actual token is not. $who and $from are truncated
	* to 45 characters.
	*
	* @param User $user      The User object the new session belongs to.
	* @param string $who     A human readable identification string.
	* @param string $from    The IP address of this session.
	* @param bool $permanent Whether to create a permanent session or not.
	*
	* @return string The generated session token.
	*
	* @see Session::validate_who() for validation exceptions.
	* @see Session::validate_from() for validation exceptions.
	*/
	public function new(
		User $user,
		string $who,
		string $from,
		bool $permanent = FALSE
	): string {
		$this->gen_id($user);
		$this->set_who($who);
		$this->set_from($from);

		$this->created = time();
		$this->max_age = Config::config('SESSION_MAX_AGE');
		$this->permanent = $permanent;

		return $this->generate_token();
	}

	/**
	* Renew the loaded session. The session token is preserved.
	*
	* @throws ArgException if the session is already expired.
	*/
	public function renew() {
		if ($this->is_expired()) {
			throw new ArgException("Can't renew an expired session.");
		}
		$this->created = time();
	}

	/**
	* Generate a new cryptographically secure authentication token
	* and return it. The hash of the token is stored in $this->token_hash.
	*
	* @return string The generated token.
	*
	* @throws IntException if password_hash() fails.
	*/
	private function generate_token(): string {
		$token = bin2hex(random_bytes(Config::config('AUTH_TOKEN_LEN')));
		$this->token_hash = password_hash($token, PASSWORD_DEFAULT);
		if ($this->token_hash === FALSE) {
			throw new IntException(
				"Failed to hash authentication token."
			);
		}
		return $token;
	}

	/**
	* Check whether the loaded session is expired.
	*
	* @return bool TRUE = Expired, FALSE = Not expired.
	*/
	public function is_expired(): bool {
		return !$this->permanent && time() > $this->created + $this->max_age;
	}

	/**
	* Verify the session token $token against the loaded session object.
	*
	* @return bool TRUE if the token matches, FALSE otherwise.
	*/
	public function verify(string $token): bool {
		return (
			password_verify($token, $this->token_hash)
			&& !$this->is_expired()
		);
	}

	public function get_id(): string { return $this->id; }
	public function get_who(): string { return $this->who; }
	public function get_from(): string { return $this->from; }
	public function get_created(): int { return $this->created; }
	public function get_max_age(): int { return $this->max_age; }
	public function is_permanent(): bool { return $this->permanent; }
	public function get_token_hash(): string { return $this->token_hash; }
}
