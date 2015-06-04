<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
require_once('include/SugarFields/SugarFieldHandler.php');
require_once('include/database/MysqlManager.php');
require_once('include/database/OracleManager.php');
require_once('include/database/IBMDB2Manager.php');
require_once('include/database/SqlsrvManager.php');

class SugarFieldIntTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     *
     * @access public
     */
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    /**
     *
     * @access public
     */
    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
    }

    public function setUp()
    {
        parent::setUp();
        $current_user = $GLOBALS['current_user'];
        $current_user->setPreference('dec_sep', '.');
        $current_user->setPreference('num_grp_sep', ',');
        $current_user->setPreference('default_currency_significant_digits', 2);
        $current_user->save();
        //force static var reset
        get_number_seperators(true);
    }

    public function tearDown()
    {
        $current_user = $GLOBALS['current_user'];
        $current_user->setPreference('dec_sep', '.');
        $current_user->setPreference('num_grp_sep', ',');
        $current_user->setPreference('default_currency_significant_digits', 2);
        $current_user->save();
        //force static var reset
        get_number_seperators(true);
        parent::tearDown();
    }

    /**
     * @dataProvider unformatFieldProvider
     * @param $value
     * @param $expectedValue
     */
    public function testUnformatField($value, $expectedValue)
    {
        $field = SugarFieldHandler::getSugarField('int');
        $this->assertEquals($expectedValue, $field->unformatField($value, null));
    }

    /**
     * testUnformatField data provider
     *
     * @group currency
     * @access public
     */
    public static function unformatFieldProvider()
    {
        return array(
            array('1000', '1000'),
            array('1.000', '1'),
            array('1,000', '1000'),
            array('1,000.00', '1000'),
        );
    }

    /**
     * @dataProvider unformatFieldProviderCommaDotFlip
     * @param $value
     * @param $expectedValue
     */
    public function testUnformatFieldCommaDotFlip($value, $expectedValue)
    {
        $current_user = $GLOBALS['current_user'];
        $current_user->setPreference('dec_sep', ',');
        $current_user->setPreference('num_grp_sep', '.');
        $current_user->setPreference('default_currency_significant_digits', 2);
        $current_user->save();

        //force static var reset
        get_number_seperators(true);

        $field = SugarFieldHandler::getSugarField('int');
        $this->assertEquals($expectedValue, $field->unformatField($value, null));
    }

    /**
     * testUnformatFieldCommaDotFlip data provider
     *
     * @group currency
     * @access public
     */
    public static function unformatFieldProviderCommaDotFlip()
    {
        return array(
            array('1,000', '1'),
            array('1000,00', '1000'),
            array('1.000,65', '1000'),
            array('1.065', '1065'),
        );
    }

    /**
     * @dataProvider apiUnformatFieldProvider
     * @param $value
     * @param $expectedValue
     */
    public function testApiUnformatField($value, $expectedValue)
    {
        $field = SugarFieldHandler::getSugarField('int');
        $this->assertEquals($expectedValue, $field->apiUnformatField($value));
    }

    /**
     * testApiUnformatField data provider
     *
     * @group currency
     * @access public
     */
    public static function apiUnformatFieldProvider()
    {
        return array(
            array('1000', '1000'),
            array('1.000', '1.000'),
            array('1,000', '1,000'),
            array('1,000.00', '1,000.00'),
        );
    }

    /**
     * @dataProvider apiValidateProvider
     * @param $db
     * @param $bean
     * @param $name
     * @param $value
     * @param $vardef
     * @param $valid
     */
    public function testApiValidate($db, $bean, $name, $value, $vardef, $valid)
    {
        $field = new SugarFieldIntMock($db);
        $this->assertEquals($valid, $field->apiValidate($bean, array($name=>$value), $name, $vardef));
    }
    
    /**
     * testApiValidate data provider
     * @access public
     */
    public function apiValidateProvider()
    {
        $bean = new SugarBean();
        $vardef = array('name'=>'test','type'=>'int');
        $mysql = new MysqlManager();
        $db2 = new IBMDB2Manager();
        $oracle = new OracleManager();
        $sqlsrv = new SqlsrvManager();

        return array(
            array($mysql, $bean, 'test', 0, $vardef, true),
            array($mysql, $bean, 'test', -12345678901, $vardef, false),
            array($mysql, $bean, 'test', 12345678901, $vardef, false),

            array($oracle, $bean, 'test', 0, $vardef, true),
            array($oracle, $bean, 'test', -12345678901, $vardef, true),
            array($oracle, $bean, 'test', 12345678901, $vardef, true),

            array($db2, $bean, 'test', 0, $vardef, true),
            array($db2, $bean, 'test', -12345678901, $vardef, false),
            array($db2, $bean, 'test', 12345678901, $vardef, false),

            array($sqlsrv, $bean, 'test', 0, $vardef, true),
            array($sqlsrv, $bean, 'test', -12345678901, $vardef, false),
            array($sqlsrv, $bean, 'test', 12345678901, $vardef, false),
        );
    }
}

require_once('include/SugarFields/Fields/Int/SugarFieldInt.php');

class SugarFieldIntMock extends SugarFieldInt
{
    protected $db;

    public function __construct($db) 
    {
        $this->db = $db;
    }

    public function getFieldRange($vardef) 
    {
        $fieldRange = $this->db->getFieldRange($vardef);

        if (!empty($fieldRange)) {
            return array('min_value' => max(-PHP_INT_MAX,  $fieldRange['min_value']), 'max_value' => min(PHP_INT_MAX, $fieldRange['max_value']));
        }

        return false;
    }
}