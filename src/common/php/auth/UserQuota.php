<?php

namespace libresignage\common\php\auth;

use libresignage\common\php\Config;
use libresignage\common\php\exportable\Exportable;
use libresignage\common\php\Util;

final class QuotaException extends \Exception {};

/**
* Class for handling user quotas.
*/
final class UserQuota extends Exportable {
	private $limits = [];
	private $used   = [];
	private $state  = [];

	public function __construct(array $limits = NULL) {
		if ($limits === NULL) { $limits = Config::get_quotas(); }
		$this->limits = $limits;
		foreach ($limits as $k => $d) { $this->used[$k] = 0; }
	}

	public function __exportable_set(string $name, $value) {
		$this->{$name} = $value;
	}

	public function __exportable_get(string $name) {
		return $this->{$name};
	}

	public function __exportable_write() {}

	public static function __exportable_private(): array {
		return ['used', 'state'];
	}

	public static function __exportable_public(): array {
		return ['used', 'limits'];
	}

	/**
	* Check whether there's quota left.
	*
	* @param string $key The key to check.
	* @param int $amount (default: 1) The amount of quota to require.
	*
	* @return bool TRUE = Quota left, FALSE = No quota left.
	*/
	public function has_quota(string $key, int $amount = 1): bool {
		return ($this->limits[$key]['limit'] - $this->used[$key]) >= $amount;
	}

	/**
	* Try to use quota.
	*
	* @param string $key The key to use.
	* @param int $amount (default: 1) The amount of quota to use.
	*
	* @throws QuotaException if there's not enough quota left.
	*/
	public function use_quota(string $key, int $amount = 1) {
		if (!$this->has_quota($key, $amount)) {
			throw new QuotaException("Not enough quota left for $key.");
		}
		$this->used[$key] += $amount;
	}

	/**
	* Set the amount of used quota.
	*
	* @param string $key The quota to set.
	* @param int $used The amount of used quota.
	*/
	public function set_used(string $key, int $used) {
		$this->used[$key] = $used;
	}

	/**
	* Free quota from $key.
	*
	* @param string $key The key to use.
	* @param int $amount (default: 1) The amount of quota to free.
	*/
	public function free_quota(string $key, int $amount = 1) {
		if ($this->used[$key] > 0) {
			$this->used[$key] -= $amount;
		}
	}

	/**
	* Check whether this UserQuota object has the state value $key.
	*
	* @param string $key The key to check.
	*
	* @return bool TRUE = Exists, FALSE = Doesn't exist.
	*/
	public function has_state(string $key): bool {
		return array_key_exists($key, $this->state);
	}

	/**
	* Set a state value.
	*
	* @param string $key The state value to set.
	* @param int $value The state value.
	*/
	public function set_state(string $key, $value) {
		$this->state[$key] = $value;
	}

	public function get_state(string $key) {
		return $this->state[$key];
	}

	public function get_description(string $key): string {
		return $this->limits[$key]['description'];
	}

	public function get_limit(string $key): int {
		return $this->limits[$key]['limit'];
	}

	public function get_used(string $key, int $amount): int {
		return $this->used[$key];
	}
}
