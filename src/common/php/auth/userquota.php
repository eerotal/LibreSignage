<?php

require_once(LIBRESIGNAGE_ROOT.'/common/php/exportable/exportable.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/util.php');

class UserQuota extends Exportable {
	static $PRIVATE = [
		'used',
		'state'
	];

	static $PUBLIC = [
		'limits',
		'used'
	];

	private $limits = [];
	private $used   = [];
	private $state  = [];

	public function __construct($limits = LS_QUOTAS) {
		$this->limits = $limits;
		foreach ($limits as $k => $d) { $this->used[$k] = 0; }
	}

	public function __exportable_set(string $name, $value) {
		$this->{$name} = $value;
	}

	public function __exportable_get(string $name) {
		return $this->{$name};
	}

	public function get_description(string $key): string {
		return $this->limits[$key]['description'];
	}

	public function get_limit(string $key): int {
		return $this->limits[$key]['limit'];
	}

	public function set_used(string $key, int $used): void {
		$this->used[$key] = $used;
	}

	public function get_used(string $key, int $amount): int {
		return $this->used[$key];
	}

	public function has_quota(string $key, int $amount = 1): bool {
		/*
		*  Check whether there's quota left for $key. Returns
		*  TRUE when there is quota left and FALSE otherwise.
		*/
		return ($this->limits[$key]['limit'] - $this->used[$key]) >= $amount;
	}

	public function use_quota(string $key, int $amount = 1): void {
		/*
		*  Try to use quota from $key. A QuotaException is thrown if
		*  there's not enough quota left.
		*/
		if (!$this->has_quota($key, $amount)) {
			throw new QuotaException("Not enough quota left for $key.");
		}
		$this->used[$key] += $amount;
	}

	public function free_quota(string $key, int $amount = 1): void {
		/*
		*  Free quota from $key.
		*/
		if ($this->used[$key] > 0) {
			$this->used[$key] -= $amount;
		}
	}

	public function has_state(string $key): bool {
		return array_key_exists($key, $this->state);
	}

	public function set_state(string $key, $value): void {
		$this->state[$key] = $value;
	}

	public function get_state(string $key) {
		return $this->state[$key];
	}
}
