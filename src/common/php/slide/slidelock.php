<?php

/*
*  SlideLock object implementation needed in the Slide class.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/exportable/exportable.php');

class SlideLock extends Exportable{
	static $PRIVATE = [
		'session_id',
		'expire'
	];
	static $PUBLIC = [
		'session_id',
		'expire'
	];

	private $session_id = NULL;
	private $expire = NULL;

	public function __construct($session = NULL) {
		if (!empty($session)) {
			$this->session_id = $session->get_id();
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
		return empty($this->session_id) || time() > $this->expire;
	}

	public function is_owned_by(Session $session) {
		return $this->session_id === $session->get_id();
	}

	public function get_session_id() { return $this->session_id; }
	public function get_expire() { return $this->expire; }
}
