<?php
class B {
	public $bound = array();
	public $a, $b, $c;
	function test_bind_impl($x, $a, $b, $c) {
		$this->a =& $a;
		$this->b =& $b;
		$this->c =& $c;
	}
	function test_bind($num) {
		$this->bound = $bound = array_fill(0, $num, null);
		for($i=0;$i<$num; $i++) {
			$bound[$i] =& $this->bound[$i];
		}
		array_unshift($bound, "foo");
		call_user_func_array(array($this, "test_bind_impl"), $bound);
	}
	function test_run($a, $b, $c) {
		$this->bound[0] = $a;
		$this->bound[1] = $b;
		$this->bound[2] = $c;
		var_dump($this->a);
		var_dump($this->b);
		var_dump($this->c);
	}
}

$b = new B();
$b->test_bind(3);
$b->test_run(1,2,3);
