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


/**
 * Bug #55038
 * Multi-Select Field Shows as Drop Down in Mobile Browser
 *
 * @author mgusev@sugarcrm.com
 * @ticked 55038
 */
class Bug55038Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Account
     */
    protected $bean = null;

    /**
     * @var array
     */
    protected $field_defs = array();

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');
        $_POST = array(
            'bug' => array(
                '0',
                '1',
                '2'
            ),
            'name' => __CLASS__,
            'team_id' => 1
        );

        $this->bean = new Account();
        $this->field_defs = $this->bean->field_defs;
        $this->bean->field_defs = array(
            'bug' => array(
                'required' => false,
                'source' => 'custom_fields',
                'name' => 'bug',
                'type' => 'multienum',
                'default' => NULL,
                'isMultiSelect' => true
            )
        );
        $this->bean->bug = '';
        $this->bean->team_id = 1;
        $this->bean->name = __CLASS__;
    }

    public function tearDown()
    {
        $this->bean->field_defs = $this->field_defs;
        $_POST = array();
        SugarTestHelper::tearDown();
    }

    /**
     * Test tries asserts that zero key with zero value saved too
     *
     * @group 55038
     * @return void
     */
    public function testPreSave()
    {
        $viewWirelesSsave = new ViewWirelesssave_55038_Mock();
        $viewWirelesSsave->module = 'Accounts';
        $viewWirelesSsave->bean = $this->bean;
        $viewWirelesSsave->pre_save();

        $this->assertEquals('^0^,^1^,^2^', $this->bean->bug, 'Some keys are missed');
    }
}

class ViewWirelesssave_55038_Mock extends ViewWirelesssave
{
    public function pre_save()
    {
        return parent::pre_save();
    }
}
