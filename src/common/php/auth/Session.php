<?php

namespace common\php\auth;

use \common\php\Config;
use \common\php\Util;
use \common\php\Exportable;
use \common\php\auth\User;
use \common\php\exceptions\IntException;
use \common\php\exceptions\ArgException;

/**
* Class for handling session data and authentication.
*/
final class Session extends Exportable{
	static $PUBLIC = [
		'id',
		'who',
		'from',
		'created',
		'max_age',
		'permanent'
	];

	static $PRIVATE = [
		'id',
		'who',
		'from',
		'created',
		'max_age',
		'permanent',
		'token_hash'
	];

	const ID_DELIMITER = '_';
	const TRUNC_LEN    = 45;

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
	* and the actual token is not. Note that $who and $from are truncated
	* to 45 characters.
	*
	* @param User $user      The User object the new session belongs to.
	* @param string $who     A human readable identification string.
	* @param string $from    The IP address of this session.
	* @param bool $permanent Whether to create a permanent session or not.
	*
	* @return string The generated session token.
	*/
	public function new(
		User $user,
		string $who,
		string $from,
		bool $permanent = FALSE
	): string {
		$t = time();
		$this->id = $user->get_name().Session::ID_DELIMITER.$t;
		$this->who = substr($who, 0, Session::TRUNC_LEN);
		$this->from = substr($from, 0, Session::TRUNC_LEN);
		$this->created = $t;
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
		return time() > $this->created + $this->max_age;
	}

	/**
	* Verify the session token $token against the loaded session object.
	*
	* @return bool TRUE if the token matches, FALSE otherwise.
	*/
	public function verify(string $token): bool {
		return (
			password_verify($token, $this->token_hash)
			&& (
				$this->permanent
				|| !$this->is_expired()
			)
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
