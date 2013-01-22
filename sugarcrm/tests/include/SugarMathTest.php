<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

/**
 * SugarMathTest
 *
 * unit tests for math library
 *
 * @author Monte Ohrt <mohrt@sugarcrm.com>
 */
class SugarMathTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * test instance type of new instantiation
     *
     * @group math
     * @access public
     */
    public function testInstanceType()
    {
        // test default instance
        $math = new SugarMath();
        $this->assertInstanceOf('SugarMath', $math);

        // test default static instance
        $math = SugarMath::init();
        $this->assertInstanceOf('SugarMath', $math);
    }


    /**
     * test default values of new instantiation
     *
     * @group math
     * @access public
     */
    public function testDefaultValues()
    {
        // test default instance
        $math = new SugarMath();
        // default value is 0
        $this->assertEquals(0, $math->result());
        // default scale is 2
        $this->assertEquals(2, $math->getScale());

        // test default static instance
        $math = SugarMath::init();
        // default value is 0
        $this->assertEquals(0, $math->result());
        // default scale is 2
        $this->assertEquals(2, $math->getScale());
    }

    /**
     * test __toString brings back result
     *
     * @group math
     * @access public
     */
    public function testToString()
    {
        // test __toString brings back value.
        $math = new SugarMath(100);
        $this->assertEquals(100,sprintf("%s",$math));

        // test static instance
        $math = SugarMath::init(100);
        $this->assertEquals(100,sprintf("%s",$math));
    }

    /**
     * test setting value and getting result
     *
     * @dataProvider setGetValueProvider
     * @param mixed $setVal the value to set
     * @param int   $scale the math precision to use
     * @group math
     * @access public
     */
    public function testSetGetValue($setVal,$scale)
    {
        // test initialize with object, get result
        $math = new SugarMath($setVal,$scale);
        $this->assertEquals($setVal, $math->result());
        // do the same with static instance
        $this->assertEquals($setVal, SugarMath::init($setVal)->result());
    }

    /**
     * get/set value data provider
     *
     * @group math
     * @access public
     */
    public function setGetValueProvider()
    {
        return array(
            array(100,0),
            array(100.01,2),
            array(100.0000000000000001,16),
            array(-100,0),
            array(-100.01,2),
            array(-100.0000000000000001,16),
            array('100',0),
            array('100.01',2),
            array('100.0000000000000001',16),
            array('-100',0),
            array('-100.01',2),
            array('-100.0000000000000001',16),
        );
    }

    /**
     * test setting value and getting scale value
     *
     * @dataProvider setGetScaleProvider
     * @param mixed $setVal the value to set
     * @group math
     * @access public
     */
    public function testSetGetScale($setVal)
    {
        // test initialize with object, get result
        $math = new SugarMath(0,$setVal);
        $this->assertEquals($setVal, $math->getScale());
        // do the same with static instance
        $this->assertEquals($setVal, SugarMath::init(0,$setVal)->getScale());
    }

    /**
     * get/set value data provider
     *
     * @group math
     * @access public
     */
    public function setGetScaleProvider()
    {
        return array(
            array(0),
            array(1),
            array(100),
            array(100000),
            array('0'),
            array('1'),
            array('100'),
            array('100000'),
        );
    }


    /**
     * test basic math operations
     *
     * @dataProvider basicOperationsProvider
     * @param mixed  $initVal the value to set
     * @param string $method the method to invoke
     * @group math
     * @access public
     */
    public function testBasicOperations($initVal,$method,$methodVal,$result)
    {
        $math = SugarMath::init($initVal)->$method($methodVal);
        $this->assertEquals($result, $math->result());

    }

    /**
     * basic math operations data provider
     *
     * @group math
     * @access public
     */
    public function basicOperationsProvider()
    {
        return array(
            array(100,'add',100,200),
            array(100.1,'add',100.1,200.2),
            array(100,'sub',50,50),
            array(100,'sub',50.2,49.8),
            array(100,'mul',100,10000),
            array(100,'mul',100.1,10010),
            array(100,'div',50,2),
            array(100,'div',50.1,1.99),
            array(100,'pow',2,10000),
            array(100,'mod',3,1),
        );
    }

    /**
     * test chained math operations
     *
     * @group math
     * @access public
     */
    public function testChainedOperations() {
        $math = SugarMath::init(10)->pow(2)->mod(3);
        $this->assertEquals(1, $math->result());
        // common test where PHP fails from rounding error
        $this->assertEquals(8,floor(SugarMath::init(0.1)->add(0.7)->mul(10)->result()));
        $this->assertEquals(1,SugarMath::init(10)->add(5)->sub(5)->mul(10)->div(10)->pow(2)->mod(3)->result());
    }

    /**
     * test powmod() math operations
     *
     * @group math
     * @access public
     */
    public function testPowModOperations() {
        $math = SugarMath::init(10,0)->powmod(2,3);
        $this->assertEquals(1, $math->result());
    }

    /**
     * test sqrt() math operations
     *
     * @dataProvider sqrtOperationsProvider
     * @param mixed  $result
     * @param mixed  $initVal
     * @param int    $scale
     * @group math
     * @access public
     */
    public function testSqrtOperations($result,$initVal,$scale) {
        $math = SugarMath::init($initVal,$scale)->sqrt();
        $this->assertEquals($result, $math->result());
    }

    /**
     * comp operations data provider
     *
     * @group math
     * @access public
     */
    public static function sqrtOperationsProvider() {
        return array(
            array(3,9,0),
            array(9.1651,84,4),
            array(10,100,0),
        );
    }


    /**
     * test comp() math operations
     *
     * @dataProvider compOperationsProvider
     * @param mixed $initVal the initial value to compare from
     * @param mixed $compVal the value to compare to
     * @param int   $result the expected result
     * @group math
     * @access public
     */
    public function testCompOperations($initVal,$compVal,$result) {
        $math = SugarMath::init($initVal);
        $this->assertEquals($result, $math->comp($compVal));
    }

    /**
     * comp operations data provider
     *
     * @group math
     * @access public
     */
    public static function compOperationsProvider() {
        return array(
            array(100,100,0),
            array(100,99,1),
            array(100,101,-1)
         );
    }

    /**
     * test expression engine empty expressions
     *
     * @group math
     * @access public
     */
    public function testExpressionsEmpty()
    {
        // empty expression
        $this->assertEquals(0, SugarMath::init()->exp('')->result());
        $this->assertEquals(0, SugarMath::init()->exp('()')->result());
    }

    /**
     * test operations where PHP will normally create rounding errors, or fail on long precision
     *
     * @dataProvider longPrecisionOperationsProvider
     * @param mixed  $initVal
     * @param string $method the operator to apply
     * @param mixed  $opVal
     * @param mixed  $result
     * @param int    $scale
     * @group math
     * @access public
     */
    public function testLongPrecisionOperations($initVal,$method,$opVal,$result,$scale)
    {
        // 50 digits, 50 decimals, adding
        $math = SugarMath::init($initVal,$scale)->$method($opVal);
        $this->assertEquals($result, $math->result());
    }

    /**
     * comp operations data provider
     *
     * @group math
     * @access public
     */
    public static function longPrecisionOperationsProvider() {
        return array(
            array(
                '99999999999999999999999999999999999999999999999999',
                'add',
                '100000000000000000000000000000000000000000000000000.00000000000000000000000000000000000000000000000001',
                '199999999999999999999999999999999999999999999999999.00000000000000000000000000000000000000000000000001',
                50,
            ),
        );
    }


    /**
     * test expression engine computations
     *
     * @dataProvider expressionsProvider
     * @param mixed  $result the expected result of the computation
     * @param string $exp the expression to test
     * @param array  $args the arguments to the expression
     * @param int    $scale the math precision to use
     * @group math
     * @access public
     */
    public function testExpressions($result, $exp, $args, $scale)
    {
        $math = SugarMath::init(0,$scale);
        $this->assertEquals($result,$math->exp($exp,$args)->result());
    }

    /**
     * expression engine data provider
     *
     * @group math
     * @access public
     */
    public static function expressionsProvider() {
        return array(
            array(3,'1+2',null,null),
            array(11,'1+2*3+4',null,null),
            array(13,'(1+2)*3+4',null,null),
            array(21,'(1+2)*(3+4)',null,null),
            array(147,'(1+2)*(3+4)^2',null,null),
            array(441,'((1+2)*(3+4))^2',null,null),
            array(30.25,'(3 * 2 - (4 / 8)) ^ 2',null,null),
            array(3.33,'10/3',null,2),
            array(3.3333,'10/3',null,4),
            array(3.3333333333333333333333333,'10/3',null,25),
            array(3.33,'10/?',array(3),2),
            array(3.33,'?/?',array(10,3),2),
            array(200,'(?+?)*10',array(10,10),null),
            array(1,'10%3',null,null),
        );
    }

    /**
     * test setValue exceptions on class
     *
     * @dataProvider setValueExceptionsProvider
     * @expectedException SugarMath_Exception
     * @expectedExceptionMessage must be numeric
     * @param mixed  $val the value to pass
     * @group math
     * @access public
     */
    public function testSetValueExceptions($val)
    {
        $math = new SugarMath();
        $math->setValue($val);
    }

    /**
     * setValue exceptions data provider
     *
     * @group math
     * @access public
     */
    public function setValueExceptionsProvider()
    {
        return array(
            array('foo'),
            array('10,00.30'),
            array('10.20.30'),
            array('$10'),
            array('10,00'),
        );
    }

    /**
     * test setScale exceptions on class
     *
     * @dataProvider setScaleExceptionsProvider
     * @expectedException SugarMath_Exception
     * @expectedExceptionMessage scale must be a positive integer
     * @param mixed  $val the value to pass
     * @group math
     * @access public
     */
    public function testSetScaleExceptions($val)
    {
        $math = new SugarMath();
        $math->setScale($val);
    }

    /**
     * setScale exceptions data provider
     *
     * @group math
     * @access public
     */
    public function setScaleExceptionsProvider()
    {
        return array(
            array(10.44),
            array(-10.44),
            array(-2),
        );
    }


    /**
     * test expression exceptions
     *
     * @dataProvider nonStringExpressionExceptionsProvider
     * @expectedException SugarMath_Exception
     * @expectedExceptionMessage expression must be a string
     * @param string $exp string the expression
     * @param array  $args array the argument array for the expression
     * @param int    $scale the decimal precision to use
     * @group math
     * @access public
     */
    public function testNonStringExpressionExceptions($exp,$args,$scale)
    {
        $math = new SugarMath(0,$scale);
        $math->exp($exp,$args)->result();
    }

    /**
     * non-string exceptions data provider
     *
     * @group math
     * @access public
     */
    public static function nonStringExpressionExceptionsProvider() {
        return array(
            array(100,null,null),
        );
    }

    /**
     * test non-array args exceptions
     *
     * @dataProvider nonArrayArgsExceptionsProvider
     * @expectedException SugarMath_Exception
     * @expectedExceptionMessage expression args must be an array
     * @param string $exp string the expression
     * @param array  $args array the argument array for the expression
     * @param int    $scale the decimal precision to use
     * @group math
     * @access public
     */
    public function testNonArrayArgsExceptions($exp,$args,$scale)
    {
        $math = new SugarMath(0,$scale);
        $math->exp($exp,$args)->result();
    }

    /**
     * arg exceptions data provider
     *
     * @group math
     * @access public
     */
    public static function nonArrayArgsExceptionsProvider() {
        return array(
            array('1+2',100,null,'non-array args should be caught'),
        );
    }

    /**
     * test scale exceptions
     *
     * @dataProvider scaleExceptionsProvider
     * @expectedException SugarMath_Exception
     * @expectedExceptionMessage scale must be a positive integer
     * @param string $exp string the expression
     * @param array  $args array the argument array for the expression
     * @param int    $scale the decimal precision to use
     * @group math
     * @access public
     */
    public function testScaleExceptions($exp,$args,$scale)
    {
        $math = new SugarMath(0,$scale);
        $math->exp($exp,$args)->result();
    }

    /**
     * scale exceptions data provider
     *
     * @group math
     * @access public
     */
    public static function scaleExceptionsProvider() {
        return array(
            array('1+2',array(),-99),
        );
    }

    /**
     * test non-matching parenthesis exceptions
     *
     * @dataProvider nonMatchingParenthesisExceptionsProvider
     * @expectedException SugarMath_Exception
     * @expectedExceptionMessage parenthesis mismatch
     * @param string $exp string the expression
     * @param array  $args array the argument array for the expression
     * @param int    $scale the decimal precision to use
     * @group math
     * @access public
     */
    public function testNonMatchingParenthesisExceptions($exp,$args,$scale)
    {
        $math = new SugarMath(0,$scale);
        $math->exp($exp,$args)->result();
    }

    /**
     * non-string exceptions data provider
     *
     * @group math
     * @access public
     */
    public static function nonMatchingParenthesisExceptionsProvider() {
        return array(
            array('((1+2)',null,null),
            array('(1+2))',null,null),
        );
    }

    /**
     * test non-numeric args exceptions
     *
     * @dataProvider nonNumericArgsExceptionsProvider
     * @expectedException SugarMath_Exception
     * @expectedExceptionMessage arguments must be numeric
     * @param string $exp string the expression
     * @param array  $args array the argument array for the expression
     * @param int    $scale the decimal precision to use
     * @group math
     * @access public
     */
    public function testNonNumericArgsExceptions($exp,$args,$scale)
    {
        $math = new SugarMath(0,$scale);
        $math->exp($exp,$args)->result();
    }

    /**
     * non-string exceptions data provider
     *
     * @group math
     * @access public
     */
    public static function nonNumericArgsExceptionsProvider() {
        return array(
            array('1+?',array('abc'),null),
            array('1+?',array('abc'),null),
        );
    }

    /**
     * test invalid expressions exceptions
     *
     * @dataProvider nonInvalidExpressionsExceptionsProvider
     * @expectedException SugarMath_Exception
     * @expectedExceptionMessage invalid expression syntax
     * @param string $exp string the expression
     * @param array  $args array the argument array for the expression
     * @param int    $scale the decimal precision to use
     * @group math
     * @access public
     */
    public function testInvalidExpressionsExceptions($exp,$args,$scale)
    {
        $math = new SugarMath(0,$scale);
        $math->exp($exp,$args)->result();
    }

    /**
     * non-string exceptions data provider
     *
     * @group math
     * @access public
     */
    public static function nonInvalidExpressionsExceptionsProvider() {
        return array(
            array('1+2* abc(3+4)',null,null),
        );
    }

    /**
     * test groups operators expressions exceptions
     *
     * @dataProvider nonGroupedOperatorsExpressionsExceptionsProvider
     * @expectedException SugarMath_Exception
     * @expectedExceptionMessage grouped operators error
     * @param string $exp string the expression
     * @param array  $args array the argument array for the expression
     * @param int    $scale the decimal precision to use
     * @group math
     * @access public
     */
    public function testGroupedOperatorsExpressionExceptions($exp,$args,$scale)
    {
        $math = new SugarMath(0,$scale);
        $math->exp($exp,$args)->result();
    }

    /**
     * non-string exceptions data provider
     *
     * @group math
     * @access public
     */
    public static function nonGroupedOperatorsExpressionsExceptionsProvider() {
        return array(
            array('1+*2',null,null),
        );
    }

}
