<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once 'include/SugarWireless/SugarWirelessListView.php';

/**
 * Bug #54696 - Conversion Field in Mobile View
 *
 * @ticket 54696
 */
class Bug54696Test extends Sugar_PHPUnit_Framework_TestCase
{

    protected $mobileListView;

    protected function setUp()
    {
        $this->mobileListView = new Bug54696Mock();
        parent::setUp();
    }

    protected function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Tests if the currency_id field will be added in the mobile list view if
     * any field of type currency is defined to appear on the columns.
     *
     * This is required to show the currency symbol on the currency field.
     *
     * @group bug54696
     * @covers SugarWirelessListView::get_filter_fields
     * @dataProvider providerCurrencyFieldsOnMobileListView
     */
    public function testCurrencyFieldsOnMobileListView($displayCols, $mustHaveCurrency)
    {
        $this->mobileListView->init(new Quote());
        $this->mobileListView->displayColumns = $displayCols;
        $fields = $this->mobileListView->get_filter_fields('Quotes');

        if ($mustHaveCurrency) {
            $this->assertArrayHasKey('currency_id', $fields);
        } else {
            $this->assertArrayNotHasKey('currency_id', $fields);
        }
    }

    /**
     * The data provider for the testCurrencyFieldsOnMobileListView method.
     *
     * @return array
     *   An array of displayColumns and the expected result (TRUE if they must
     *   have currency_id, FALSE otherwise).
     *
     * @see Bug54696Test::testCurrencyFieldsOnMobileListView()
     */
    public function providerCurrencyFieldsOnMobileListView()
    {
        return array(
            array(
                array(
                    'NAME' => array(
                        'width' => '32%',
                        'label' => 'LBL_NAME',
                        'default' => true,
                        'link' => true,
                    ),
                ),
                // currency id not needed
                false,
            ),
            array(
                array(
                    'TOTAL' => array(
                        'type' => 'currency',
                        'label' => 'LBL_TOTAL',
                        'currency_format' => true,
                        'width' => '10%',
                        'default' => true,
                    ),
                ),
                // currency id needed
                true,
            ),
            array(
                array(
                    'NAME' => array(
                        'width' => '32%',
                        'label' => 'LBL_NAME',
                        'default' => true,
                        'link' => true,
                    ),
                    'TOTAL_USDOLLAR' => array(
                        'type' => 'currency',
                        'label' => 'LBL_TOTAL_USDOLLAR',
                        'currency_format' => true,
                        'width' => '10%',
                        'default' => true,
                    ),
                ),
                // currency id needed
                true,
            ),
        );
    }
}

/**
 * This is temporary until we support only the PHP 5.3.2 or above.
 *
 * We can remove this and use the ReflectionClass or when the currency fields
 * are refactored.
 *
 * @see http://sebastian-bergmann.de/archives/881-Testing-Your-Privates.html
 */
class Bug54696Mock extends SugarWirelessListView
{
    public $displayColumns;

    public function init($bean = null, $view_object_map = array())
    {
        $this->bean = $bean;
    }

    public function get_filter_fields($module)
    {
        return parent::get_filter_fields($module);
    }
}
