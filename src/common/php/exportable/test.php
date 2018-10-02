<?php

require 'exportable.php';

class Test extends Exportable {
	static $PUBLIC = [
		'var1',
		'var2',
		'var3'
	];
	static $PRIVATE = [
		'var1',
		'var2',
		'var3',
		'var4'
	];

	private $var1 = 1;
	private $var2 = NULL;
	private $var3 = NULL;
	private $var4 = NULL;

	public function __construct() {
		$this->var2 = new Test2();
		$this->var3 = [
			new Test2(),
			new Test2()
		];
		$this->var4 = new Test2();
	}

	public  function __exportable_set(string $name, $value) {
		$this->{$name} = $value;
	}

	public function __exportable_get(string $name) {
		return $this->{$name};
	}
}

class Test2 extends Exportable {
	static $PUBLIC = [
		'a', 'b'
	];
	static $PRIVATE = [
		'a', 'b', 'c'
	];

	private $a = 'asd';
	private $b = 'qwer';
	private $c = 'vbn';

	public  function __exportable_set(string $name, $value) {
		$this->{$name} = $value;
	}

	public function __exportable_get(string $name) {
		return $this->{$name};
	}
}

function test() {
	$asd = new Test();
	$a = $asd->export(TRUE, TRUE);
	$asd->import($a, TRUE);
}
