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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

class SugarACLOpiTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @group framework
     */
    public function testCheckRecurringSource()
    {
        $bean = BeanFactory::getBean('Meetings');

        $canEdit = $bean->ACLAccess('edit');
        $this->assertTrue($canEdit);

        $_SESSION['platform'] = 'base';
        $bean->recurring_source = 'Sugar';
        $bean->fetched_row['recurring_source'] = 'Sugar';
        $canEdit = $bean->ACLAccess('edit');
        $this->assertTrue($canEdit);

        $bean->recurring_source = 'Outlook';
        $_SESSION['platform'] = 'opi';
        $bean->recurring_source = 'Outlook';
        $bean->fetched_row['recurring_source'] = 'Outlook';
        $canEdit = $bean->ACLAccess('edit');
        $this->assertTrue($canEdit);

        $bean->recurring_source = 'Outlook';
        $_SESSION['platform'] = 'base';
        $bean->recurring_source = 'Outlook';
        $bean->fetched_row['recurring_source'] = 'Outlook';
        $canEdit = $bean->ACLAccess('edit');
        $this->assertFalse($canEdit);

        $bean->recurring_source = 'Sugar';
        $canList = $bean->ACLAccess('list');
        $this->assertTrue($canList);

        $bean->recurring_source = 'Outlook';
        $canList = $bean->ACLAccess('list');
        $this->assertTrue($canList);

        $bean->recurring_source = 'Sugar';
        $canView = $bean->ACLAccess('view');
        $this->assertTrue($canView);

        $bean->recurring_source = 'Outlook';
        $canView = $bean->ACLAccess('view');
        $this->assertTrue($canView);

        unset($_SESSION['platform']);
        unset($bean);

    }
}
