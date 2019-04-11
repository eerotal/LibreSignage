<?php

require_once(LIBRESIGNAGE_ROOT.'/common/php/exportable/exportable.php');

class UserQuota extends Exportable {
	static $PRIVATE = [
		'quota',
		'state'
	];

	static $PUBLIC = [
		'quota'
	];

	private $quota = [];
	private $state = [];

	public function __construct($limits = []) {
		/*
		*  Initialize the UserQuota object with data
		*  from $limits. $limits can be an empty array
		*  if no initial limits are needed.
		*/
		foreach ($limits as $key => $data) {
			// Set limit.
			if (array_key_exists('limit', $data)) {
				$this->quota[$key]['limit'] = $data['limit'];
			} else {
				// Skip data with no quota limit.
				continue;
			}

			// Set description.
			if (array_key_exists('description', $data)) {
				$this->quota[$key]['description'] = $data['description'];
			} else {
				$this->quota[$key]['description'] = '';
			}

			// Set used quota.
			if (array_key_exists('used', $data)) {
				$this->quota[$key]['used'] = $data['used'];
			} else {
				$this->quota[$key]['used'] = 0;
			}
		}
	}

	public function __exportable_set(string $name, $value) {
		$this->{$name} = $value;
	}

	public function __exportable_get(string $name) {
		return $this->{$name};
	}

	public function get_description(string $key): string {
		return $this->quota[$key]['description'];
	}

	public function get_limit(string $key): int {
		return $this->quota[$key]['limit'];
	}

	public function set_used(string $key, int $used): void {
		$this->quota[$key]['used'] = $used;
	}

	public function get_used(string $key, int $amount): int {
		return $this->quota[$key]['used'];
	}

	public function has_quota(string $key, int $amount = 1): bool {
		/*
		*  Check whether there's quota left for $key. Returns
		*  TRUE when there is quota left and FALSE otherwise.
		*/
		return (
			$this->quota[$key]['limit']
			- $this->quota[$key]['used']
		) >= $amount;
	}

	public function use_quota(string $key, int $amount = 1): void {
		/*
		*  Try to use quota from $key. A QuotaException is thrown if
		*  there's not enough quota left.
		*/
		if (!$this->has_quota($key, $amount)) {
			throw new QuotaException("Not enough quota left for $key.");
		}
		$this->quota[$key]['used'] += $amount;
	}

	public function free_quota(string $key, int $amount = 1): void {
		/*
		*  Free quota from $key.
		*/
		if ($this->quota[$key]['used'] > 0) {
			$this->quota[$key]['used'] -= $amount;
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
