<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/exportable/exportable.php');

class Session extends Exportable{
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

	private $id = NULL;
	private $who = NULL;
	private $from = NULL;
	private $created = NULL;
	private $max_age = NULL;
	private $permanent = NULL;
	private $token_hash = NULL;

	public function get_id() { return $this->id; }
	public function get_who() { return $this->who; }
	public function get_from() { return $this->from; }
	public function get_created() { return $this->created; }
	public function get_max_age() { return $this->max_age; }
	public function is_permanent() { return $this->permanent; }
	public function get_token_hash() { return $this->token_hash; }

	public function __exportable_set(string $name, $value) {
		$this->{$name} = $value;
	}

	public function __exportable_get(string $name) {
		return $this->{$name};
	}

	public function new(
		User $user,
		string $who,
		string $from,
		bool $permanent = FALSE
	) {
		/*
		*  Create a new session and return the generated
		*  session token. Note that only the session token
		*  hash is stored in this object and the actual token
		*  is not.
		*
		*  $user      = The User object the new session belongs to.
		*  $who       = A human readable identification string.
		*  $from      = The IP address of this session.
		*  $permanent = Whether to create a permanent session or not.
		*
		*  Note that $who and $from are truncated to 45 characters.
		*/
		$t = time();
		$this->id = $user->get_name().'_'.$t;
		$this->who = substr($who, 0, 45);
		$this->from = substr($from, 0, 45);
		$this->created = $t;
		$this->max_age = SESSION_MAX_AGE;
		$this->permanent = $permanent;
		return $this->generate_token();
	}

	public function renew() {
		/*
		*  Renew this session.
		*/
		$this->created = time();
		$this->generate_token();
	}

	private function generate_token() {
		/*
		*  Generate a new cryptographically secure authentication
		*  token and return it. The hash of the token is stored in
		*  $this->token_hash.
		*/
		$token = bin2hex(random_bytes(AUTH_TOKEN_LEN));
		$this->token_hash = password_hash($token, PASSWORD_DEFAULT);
		if ($this->token_hash === FALSE) {
			throw new IntException(
				"Failed to hash authentication token."
			);
		}
		return $token;
	}

	public function is_expired() {
		return time() > $this->created + $this->max_age;
	}

	public function verify(string $token) {
		/*
		*  Verify the authentication token $token against this
		*  session object. Returns TRUE if the token matches
		*  and FALSE otherwise.
		*/
		return (
			password_verify($token, $this->token_hash)
			&& (
				$this->permanent
				|| !$this->is_expired()
			)
		);
	}
}
