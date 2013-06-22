<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once('modules/CampaignLog/CampaignLogApiHelper.php');
require_once('include/api/RestService.php');

class CampaignLogApiHelperTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $bean = null;
    protected $helper = null;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');

        $this->bean = BeanFactory::newBean('CampaignLog');
        $this->bean->id = create_guid();

        $this->helper = new CampaignLogApiHelper(new CampaignLogServiceMockup());
    }

    public function tearDown()
    {
        unset($this->helper);
        unset($this->bean);
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testFormatForApi_WithRelatedCampaignTracker_ReturnsCampaignTrackerUrl()
    {
        $campaignTracker = SugarTestCampaignUtilities::createCampaignTracker('123');
        $this->bean->related_id = $campaignTracker->id;
        $this->bean->related_type = 'CampaignTrackers';

        $data = $this->helper->formatForApi($this->bean, array('related_name'));

        $this->assertEquals($data['related_name'], $campaignTracker->tracker_url, "Tracker URL does not match");

        //cleanup
        unset($campaignTracker);
        SugarTestCampaignUtilities::removeAllCreatedCampaignTrackers();
    }

    public function testFormatForApi_WithRelatedContact_ReturnsContactFullName()
    {
        $contact = SugarTestContactUtilities::createContact();
        $this->bean->related_id = $contact->id;
        $this->bean->related_type = 'Contacts';

        $data = $this->helper->formatForApi($this->bean, array('related_name'));

        $this->assertEquals($data['related_name'], $contact->full_name, "Contact name does not match");

        //cleanup
        unset($contact);
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    public function testFormatForApi_WithRelatedAccount_ReturnsAccountName()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $this->bean->related_id = $account->id;
        $this->bean->related_type = 'Accounts';

        $data = $this->helper->formatForApi($this->bean, array('related_name'));

        $this->assertEquals($data['related_name'], $account->name, "Account name does not match");

        //cleanup
        unset($account);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

}

class CampaignLogServiceMockup extends ServiceBase
{
    public function __construct()
    {
        $this->user = $GLOBALS['current_user'];
    }

    public function execute()
    {
    }

    protected function handleException(Exception $exception)
    {
    }
}
