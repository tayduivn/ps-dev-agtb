<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */
require_once('include/SugarFields/SugarFieldHandler.php');

class SugarFieldFloatTest extends Sugar_PHPUnit_Framework_TestCase
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
        $field = SugarFieldHandler::getSugarField('float');
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
            array('1.000', '1.000'),
            array('1,000', '1000'),
            array('1,000.00', '1000.00'),
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

        $field = SugarFieldHandler::getSugarField('float');
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
            array('1.000,65', '1000.65'),
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
        $field = SugarFieldHandler::getSugarField('float');
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

}
