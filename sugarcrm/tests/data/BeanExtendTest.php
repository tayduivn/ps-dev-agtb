<?php

/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('data/SugarBean.php');

class BeanExtendTest extends Sugar_PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    public static function tearDownAfterClass()
    {
	    SugarTestHelper::tearDown();
	}

	public function testBeans()
	{
	    for($i=1;$i<=8;$i++) {
	        $name = "TestBean$i";
	        $bean = new $name;
	        $this->assertTrue($bean->ok);
	    }
	}
}

class TestBean1 extends SugarBean
{
    public $ok;
    function TestBean1() {
		parent::__construct();
		$this->ok = true;
	}
}

class TestBean2 extends SugarBean
{
    public $ok;
    function TestBean2() {
        parent::SugarBean();
		$this->ok = true;
    }
}

class TestBean3 extends SugarBean
{
    public $ok;
    function __construct() {
        parent::SugarBean();
        $this->ok = true;
    }
}

class TestBean4 extends SugarBean
{
    public $ok;
    function __construct() {
        parent::__construct();
        $this->ok = true;
    }
}

class TestBean5 extends Basic
{
    public $ok;
    function TestBean5() {
        parent::__construct();
        $this->ok = true;
    }
}

class TestBean6 extends Basic
{
    public $ok;
    function TestBean6() {
        parent::Basic();
        $this->ok = true;
    }
}

class TestBean7 extends Basic
{
    public $ok;
    function __construct() {
        parent::Basic();
        $this->ok = true;
    }
}

class TestBean8 extends Basic
{
    public $ok;
    function __construct() {
        parent::__construct();
        $this->ok = true;
    }
}

class TestBean9 extends Basic
{
    public $ok;

    function __construct() {
        parent::__construct();
        $this->ok = true;
    }

    function TestBean9() {
        self::__construct();
    }
}

class TestBean10 extends TestBean9
{
    public $ok;

    function __construct() {
        parent::TestBean9();
        $this->ok = true;
    }
}



