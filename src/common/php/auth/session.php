<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/exportable/exportable.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/user.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/uid.php');

class Session extends Exportable {
	static $PUBLIC = [
		'username',
		'id',
		'who',
		'from',
		'created',
		'max_age',
		'permanent',
		'orphan',
		'orphan_master'
	];

	static $PRIVATE = [
		'username',
		'id',
		'who',
		'from',
		'created',
		'max_age',
		'permanent',
		'token_hash',
		'orphan',
		'orphan_master'
	];

	const ID_DELIMITER  = '_';
	const TRUNC_LEN     = 45;
	const ORPHAN_PERIOD = 25;

	private $username = NULL;
	private $id = NULL;
	private $who = NULL;
	private $from = NULL;
	private $created = NULL;
	private $max_age = NULL;
	private $permanent = NULL;
	private $token = NULL;
	private $token_hash = NULL;
	private $orphan = NULL;
	private $orphan_master = NULL;

	public function get_username(): string { return $this->username; }
	public function get_id(): string { return $this->id; }
	public function get_who(): string { return $this->who; }
	public function get_from(): string { return $this->from; }
	public function get_created(): int { return $this->created; }
	public function get_max_age(): int { return $this->max_age; }
	public function is_permanent(): bool { return $this->permanent; }
	public function get_token(): string { return $this->token; }
	public function get_token_hash(): string { return $this->token_hash; }
	public function is_orphan(): bool { return $this->orphan !== NULL; }
	public function get_orphan_master() { return $this->orphan_master; }

	public function __exportable_set(string $name, $value) {
		$this->{$name} = $value;
	}

	public function __exportable_get(string $name) {
		return $this->{$name};
	}

	public static function from_id(string $id) {
		/*
		*  Load a session based on its ID. Returns the Session
		*  object on success or NULL on failure.
		*/
		$p = explode(Session::ID_DELIMITER, $id);
		$u = new User($p[0]);
		return $u->session_get($id);
	}

	public function new(
		string $username,
		string $who,
		string $from,
		bool $permanent = FALSE
	): void {
		/*
		*  Create a new session. You can get the generated session
		*  token by calling Session::get_token(). Note that Session::get_token()
		*  will only return the token for sessions that are not loaded from disk.
		*
		*  $user      = The User object the new session belongs to.
		*  $who       = A human readable identification string.
		*  $from      = The IP address of this session.
		*  $permanent = Whether to create a permanent session or not.
		*
		*  Note that $who and $from are truncated to 45 characters.
		*/
		$this->username = $username;
		$this->id = $username.Session::ID_DELIMITER.get_uid();
		$this->who = substr($who, 0, Session::TRUNC_LEN);
		$this->from = substr($from, 0, Session::TRUNC_LEN);
		$this->created = time();
		$this->max_age = SESSION_MAX_AGE;
		$this->permanent = $permanent;
		$this->generate_token();
	}

	public function renew(): Session {
		/*
		*  Renew a session. Returns a new renewed session object
		*  and marks the old one as orphan.
		*/
		assert(!$this->is_orphan(), "Won't renew an orphan session.");
		assert(!$this->is_permanent(), "Won't renew a permanent session.");

		$ret = new Session();
		$ret->new(
			$this->get_username(),
			$this->get_who(),
			$this->get_from(),
			false
		);
		$this->set_orphan($ret);

		return $ret;
	}

	private function set_orphan(Session $master): void {
		/*
		*  Mark this session orphan and set $master as the master session
		*  this one belongs to.
		*/
		$this->orphan_master = $master;
		$this->orphan = time();
	}

	public function is_orphan_of(Session $master): bool {
		/*
		*  Return TRUE if $master is the master session of this one and
		*  FALSE otherwise.
		*/
		return (
			$this->is_orphan() &&
			$this->get_orphan_master()->get_id() == $master->get_id()
		);
	}

	private function generate_token(): void {
		/*
		*  Generate a new cryptographically secure authentication
		*  token and store it in $this->token. Note that even though
		*  the token is stored in $this->token, it's never saved on disk
		*  to keep tokens secure. Use Session::get_token() to get the
		*  token string. The hash of the token is stored in $this->token_hash.
		*/
		$this->token = bin2hex(random_bytes(AUTH_TOKEN_LEN));
		$this->token_hash = password_hash($this->token, PASSWORD_DEFAULT);
		if ($this->token_hash === FALSE) {
			throw new IntException("Failed to hash authentication token.");
		}
	}

	public function is_expired(): bool {
		return (
			($this->is_orphan() && time() > $this->orphan + Session::ORPHAN_PERIOD)
			|| (time() > $this->created + $this->max_age)
		);
	}

	public function verify(string $token): bool {
		/*
		*  Verify the authentication token $token against this
		*  session object. Returns TRUE if the token matches
		*  and FALSE otherwise.
		*/
		return (
			password_verify($token, $this->token_hash)
			&& ($this->permanent || !$this->is_expired())
		);
	}
}
