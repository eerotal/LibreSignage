<?php

/*
*  SlideLock object implementation needed in the Slide class.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/exportable/exportable.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/session.php');

class SlideLock extends Exportable {
	static $PRIVATE = [
		'expire',
		'session'
	];
	static $PUBLIC = [
		'expire',
		'session'
	];

	private $session = NULL;
	private $expire = NULL;

	public function __construct($session = NULL) {
		if ($session !== NULL) {
			$this->session = $session;
			$this->expire = time() + SLIDE_LOCK_MAX_AGE;
		}
	}

	public function __exportable_set(string $name, $value) {
		$this->{$name} = $value;
	}

	public function __exportable_get(string $name) {
		return $this->{$name};
	}

	public function is_expired() {
		/*
		*  Return TRUE if this SlideLock is expired and FALSE otherwise.
		*/
		if ($this->session === NULL) { return TRUE; }
		$s = Session::from_id($this->session->get_id());
		return $s === NULL || time() > $this->expire;
	}

	public function is_owned_by(Session $session) {
		/*
		*  Return TRUE if this SlideLock is owned by $session and
		*  FALSE otherwise.
		*/
		return (
			$this->session->get_id() === $session->get_id()
			|| $session->is_orphan_of($this->session)
		);
	}

	public function get_expire() { return $this->expire; }
	public function get_session() { return $this->session; }
}
