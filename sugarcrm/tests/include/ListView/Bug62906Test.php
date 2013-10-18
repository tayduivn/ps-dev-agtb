<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/


require_once('include/ListView/ListView.php');
require_once('modules/ACLFields/actiondefs.php');

/**
 * Bug #62906 unit test
 *
 * @ticked 62906
 */
class Bug62906Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $lead = null;
    protected $task = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->lead = SugarTestLeadUtilities::createLead();
        $this->task = SugarTestTaskUtilities::createTask();
    }

    public function tearDown()
    {
        unset($_SESSION['ACL']);
        ACLField::$acl_fields = array();

        SugarTestHelper::tearDown();

        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestTaskUtilities::removeAllCreatedTasks();
    }

    /**
     * data provider
     * @return array
     */
    public function permissionDataProvider()
    {
        // should be false if either one is read only
        return array(
            array(ACL_READ_WRITE, ACL_READ_WRITE, true),
            array(ACL_READ_ONLY, ACL_READ_WRITE, false),
            array(ACL_READ_WRITE, ACL_READ_ONLY, false),
        );
    }

    /**
     * Test to check if the user has unlink permission
     *
     * @dataProvider permissionDataProvider
     *
     * @group 62906
     * @return void
     */
    public function testUnlinkPermission($parentIDPermission, $parentTypePermission, $expected)
    {
        global $current_user;

        $listview = new ListViewMock();

        // setting acl values
        ACLField::$acl_fields[$current_user->id]['Tasks']['parent_id'] = $parentIDPermission;
        ACLField::$acl_fields[$current_user->id]['Tasks']['parent_type'] = $parentTypePermission;
        $_SESSION['ACL'][$current_user->id]['Tasks']['fields']['parent_id'] = $parentIDPermission;
        $_SESSION['ACL'][$current_user->id]['Tasks']['fields']['parent_type'] = $parentTypePermission;

        $permission = $listview->checkUnlinkPermission('tasks', $this->task, $this->lead);

        $this->assertEquals($expected, $permission, 'Incorrect permission.');
    }
}

class ListViewMock extends ListView
{
    public function checkUnlinkPermission($linked_field, $aItem, $parentBean) {
        return parent::checkUnlinkPermission($linked_field, $aItem, $parentBean);
    }
}
