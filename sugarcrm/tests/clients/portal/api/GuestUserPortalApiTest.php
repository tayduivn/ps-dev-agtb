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

require_once 'clients/portal/api/CurrentUserPortalApi.php';

class GuestUserPortalApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var CurrentUserPortalApi
     */
    public $currentUserApi;

    /**
     * @var ServiceBase
     */
    public $service;

    public function setUp()
    {

        SugarTestHelper::setUp("app_strings");
        SugarTestHelper::setUp("app_list_strings");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp('current_user');

        $_SESSION['contact_id'] = false;

        $this->currentUserApi= new CurrentUserPortalApiMock();
        $this->service = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown()
    {
        $this->service = null;
        $this->currentUserApi = null;

        unset($_SESSION['contact_id']);

        SugarTestHelper::tearDown();
    }

    public function testGetIsGuest()
    {
        $this->assertTrue($this->currentUserApi->getIsGuest());
    }

    public function testDefaultEnforceModuleACLs()
    {
        $expected = array(
            'admin' => 'no', 
            'developer' => 'no',
            'delete' => 'no',
            'import' => 'no',
            'export' => 'no',
            'massupdate' => 'no',
            'edit' => 'no',
        );

        $acls = $this->currentUserApi->getEnforcedModuleACLs($expected);

        foreach ($acls as $module => $acl) {
            $this->assertEquals($expected, $acl);
        }
    }

    public function testRestrictedAccessEnforceModuleACLs()
    {
        $expected = array(
            'access' => 'no',
        );

        $acls = $this->currentUserApi->getEnforcedModuleACLs($expected);
        $this->assertEquals($expected, $acls['Bugs']);
        $this->assertEquals($expected, $acls['Contacts']);
        $this->assertEquals($expected, $acls['Accounts']);
        $this->assertEquals($expected, $acls['Cases']);
    }

}

class CurrentUserPortalApiMock extends CurrentUserPortalApi
{

    public function getEnforcedModuleACLs(array $filter = array())
    {
        $acls = $this->getAcls('support_portal');
        $result = $this->enforceModuleACLs($acls);
        $filter = array_flip(array_keys($filter));

        foreach ($result as $module => $acls) {
            if (!empty($filter)) {
                $result[$module] = array_intersect_key($acls, $filter);	
            }
        }

        return $result;
    }

}
