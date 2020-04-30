<?php

namespace libresignage\tests\backend\common\classes;

use libresignage\common\php\exportable\Exportable;

/**
* A class for testing the Exportable system.
*/
class ExportableTestClass extends Exportable {
	public $a = 0;
	private $b = 0;

	public function __exportable_set(string $name, $value) {
		$this->{$name} = $value;
	}

	public function __exportable_get(string $name) {
		return $this->{$name};
	}

	public function __exportable_write() {
		// TODO
	}

	public static function __exportable_public(): array {
		return ['a'];
	}

	public static function __exportable_private(): array {
		return ['b'];
	}

	public function set_a($value) { $this->a = $value; }
	public function set_b($value) { $this->b = $value; }

	public function get_a() { return $this->a; }
	public function get_b() { return $this->b; }

}
