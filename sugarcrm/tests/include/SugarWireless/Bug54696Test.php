<?php
//FILE SUGARCRM flav=pro ONLY
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
