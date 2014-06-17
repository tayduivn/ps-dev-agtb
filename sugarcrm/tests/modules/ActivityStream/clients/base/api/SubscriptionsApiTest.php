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
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

require_once("modules/ActivityStream/clients/base/api/SubscriptionsApi.php");

/**
 * @group api
 * @group subscriptions
 */
class SubscriptionsApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $api;
    private $subscriptionApi;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');
        $this->api             = SugarTestRestUtilities::getRestServiceMock();
        $this->api->user       = $GLOBALS['current_user'];
        $this->subscriptionApi = new SubscriptionsApi();
    }

    public function tearDown()
    {
        BeanFactory::setBeanClass('Leads');
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @expectedException     SugarApiExceptionNotFound
     */
    public function testSubscribeToRecord_RecordNotFound_ThrowsException()
    {
        $this->subscriptionApi->subscribeToRecord(
            $this->api,
            array(
                'module' => 'Leads',
                'record' => create_guid(),
            )
        );
    }

    /**
     * @expectedException     SugarApiExceptionNotAuthorized
     */
    public function testSubscribeToRecord_NoAccess_ThrowsException()
    {
        $mockLead = $this->getMock('Lead', array('ACLAccess'));
        $mockLead->expects($this->any())
            ->method('ACLAccess')
            ->will($this->returnValue(false));

        BeanFactory::setBeanClass('Leads', get_class($mockLead));

        $lead = BeanFactory::newBean('Leads');
        $lead->id = create_guid();

        BeanFactory::registerBean($lead);

        $this->subscriptionApi->subscribeToRecord(
            $this->api,
            array(
                'module' => 'Leads',
                'record' => $lead->id,
            )
        );

        BeanFactory::unregisterBean($lead);
    }

    /**
     * @expectedException     SugarApiExceptionNotFound
     */
    public function testUnSubscribeFromRecord_RecordNotFound_ThrowsException()
    {
        $lead = SugarTestLeadUtilities::createLead();
        $lead->mark_deleted($lead->id);

        $this->subscriptionApi->unsubscribeFromRecord(
            $this->api,
            array(
                'module' => 'Leads',
                'record' => $lead->id,
            )
        );
    }

    /**
     * @expectedException     SugarApiExceptionNotAuthorized
     */
    public function testUnSubscribeFromRecord_NoAccess_ThrowsException()
    {
        $mockLead = $this->getMock('Lead', array('ACLAccess'));
        $mockLead->expects($this->any())
            ->method('ACLAccess')
            ->will($this->returnValue(false));

        BeanFactory::setBeanClass('Leads', get_class($mockLead));

        $lead = BeanFactory::newBean('Leads');
        $lead->id = create_guid();
        BeanFactory::registerBean($lead);

        $this->subscriptionApi->unsubscribeFromRecord(
            $this->api,
            array(
                'module' => 'Leads',
                'record' => $lead->id,
            )
        );

        BeanFactory::unregisterBean($lead);
    }
}
