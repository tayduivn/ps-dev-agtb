<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once 'include/generic/LayoutManager.php';

/**
 * Bug #51568
 *  Currency symbol didn't export to the CVS or pdf file in report module
 *
 * @author aryamrchik@sugarcrm.com
 * @ticket 51568
 */
class Bug51568Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var LayoutManager
     */
    protected $lm;

    /**
     * @var Currency
     */
    protected $currency_51568;

    /**
     * @var Currency
     */
    protected $currency_system;

    /**
     * @var string
     */
    protected $backupSymbol;

    public function setUp()
    {
        global $current_user, $sugar_config;
        SugarTestHelper::setUp('current_user', array(true));
        $current_user->setPreference('dec_sep', ',');
        $current_user->setPreference('num_grp_sep', '.');
        $current_user->setPreference('default_currency_significant_digits', 3);

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        parent::setUp();

        $this->lm = new LayoutManager();
        $this->lm->setAttribute('reporter', new stdClass());

        $this->currency_51568 = new Currency();
        $this->currency_51568->symbol = 'TT';
        $this->currency_51568->conversion_rate = 0.5;
        $this->currency_51568->save(false);
        $this->currency_system = new Currency();
        $this->currency_system->retrieve(-99);
        $this->backupSymbol = $this->currency_system->symbol;
        $this->currency_system->symbol = '¥';
        $this->currency_system->save(false);
        $sugar_config['default_currency_symbol'] = '¥';
        get_number_seperators(true);
    }

    /**
     * @group 51568
     */
    public function testFieldCurrencyPlainWithLayoutDef()
    {
        $data = array(
            'currency_id' => $this->currency_51568->id,
            'currency_symbol' => $this->currency_51568->symbol
        );
        $result = $this->getResults($data);
        $this->assertEquals('TT100.500,000', $result);
    }

    /**
     * @group 51568
     */
    public function testFieldCurrencyPlainWithCurrencyField()
    {
        $data = array(
            'fields' => array(
                '51568table_some_field_currency' => $this->currency_51568->id)
        );
        $result = $this->getResults($data);
        $this->assertEquals('TT100.500,000', $result);
    }

    /**
     * @group 51568
     */
    public function testFieldCurrencyPlainWithAnotherCurrencyField()
    {
        $data = array(
            'fields' => array(
                '51568TABLE_SOME_FIELD_CURRENCY' => $this->currency_51568->id)
        );
        $result = $this->getResults($data);
        $this->assertEquals('TT100.500,000', $result);
    }

    /**
     * @group 51568
     */
    public function testFieldCurrencyPlainWithSystemCurrencyField()
    {
        format_number(0, 0, 0, array(
            'currency_id' => $this->currency_51568->id,
            'currency_symbol' => $this->currency_51568->symbol
        ));

        format_number(0, 0, 0, array(
            'currency_id' => -99,
            'currency_symbol' => $this->currency_51568->getDefaultCurrencySymbol()
        ));

        $data = array(
            'name' => 'some_field_usdoll',
            'column_key' => 'self::some_field_usdoll',
            'fields' => array(
                '51568TABLE_SOME_FIELD_USDOLL' => 100500
            )
        );
        $result = $this->getResults($data);
        $this->assertEquals('¥100.500,000', $result);
    }

    /**
     * @group 51568
     */
    public function testFieldCurrencyPlainWithWrongCurrency()
    {
        $data = array(
            'currency_id' => '-51568',
            'currency_symbol' => '£'
        );
        $result = $this->getResults($data);
        $this->assertEquals('¥100.500,000', $result);
    }

    protected function getResults($layout_def_addon)
    {
        $layout_def = array(
            'column_key' => 'self::some_field',
            'fields' => array(
                '51568TABLE_SOME_FIELD' => 100500,
            ),
            'name' => 'some_field',
            'table_key' => 'self',
            'table_alias' => '51568table',
            'type' => 'currency'
        );
        foreach($layout_def_addon as $k => $v)
        {
            if(is_array($v))
            {
                $layout_def = array_merge_recursive($layout_def, array($k => $v));
            }
            else
            {
                $layout_def[$k] = $v;
            }
        }
        $sf = $this->getMock('SugarWidgetFieldCurrency',
            array('getTruncatedColumnAlias'),
            array(&$this->lm));
        $sf->expects($this->any())
            ->method('getTruncatedColumnAlias')
            ->will($this->returnArgument(0));
        return $sf->displayListPlain($layout_def);
    }

    public function tearDown()
    {
        global $sugar_config;
        $this->currency_system->symbol = $this->backupSymbol;
        $this->currency_system->save(false);
        $sugar_config['default_currency_symbol'] = $this->backupSymbol;

        format_number(0, 0, 0, array(
            'currency_id' => $this->currency_51568->id,
            'currency_symbol' => $this->currency_51568->symbol
        ));

        format_number(0, 0, 0, array(
            'currency_id' => -99,
            'currency_symbol' => $this->currency_51568->getDefaultCurrencySymbol()
        ));

        $this->currency_51568->mark_deleted($this->currency_51568->id);
        SugarTestHelper::tearDown();
        get_number_seperators(true);
    }
}
