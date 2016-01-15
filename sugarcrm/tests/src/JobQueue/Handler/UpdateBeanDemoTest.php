<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTests\JobQueue\Handler;

use Sugarcrm\Sugarcrm\JobQueue\Handler\UpdateBeanDemo;

class UpdateBeanDemoTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var \SugarBean
     */
    protected $targetBean;

    public function setUp()
    {
        \SugarTestHelper::setUp('current_user', array(true, 1));
        $this->targetBean = \SugarTestAccountUtilities::createAccount();
    }

    public function tearDown()
    {
        \SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        \SugarTestAccountUtilities::removeAllCreatedAccounts();
        \SugarTestHelper::tearDown();
    }

    /**
     * @expectedException \Exception
     */
    public function testInvalidId()
    {
        new UpdateBeanDemo($this->targetBean->module_name, 'InvalidId', array());
    }

    /**
     * @expectedException \Exception
     */
    public function testNoAccess()
    {
        $user = \SugarTestUserUtilities::createAnonymousUser();
        $role = new \ACLRole();
        $role->name = 'newrole';
        $role->save();

        $aclActions = $role->getRoleActions($role->id);
        $role->setAction($role->id, $aclActions['Accounts']['module']['edit']['id'], ACL_ALLOW_NONE);

        $role->load_relationship('users');
        $role->users->add($user->id);

        $GLOBALS['current_user'] = $user;

        $handler = new UpdateBeanDemo($this->targetBean->module_name, $this->targetBean->id, array());
        $handler->run();
    }

    public function testDeleteBean()
    {
        $data = array(
            'account_type' => $GLOBALS['app_list_strings']['account_type_dom']['Press']
        );
        $this->targetBean->account_type = '';
        $this->targetBean->save();

        $handler = new UpdateBeanDemo($this->targetBean->module_name, $this->targetBean->id, $data);
        $resolution = $handler->run();

        $this->targetBean->retrieve();
        $this->assertEquals(\SchedulersJob::JOB_SUCCESS, $resolution);
        $this->assertEquals(
            $GLOBALS['app_list_strings']['account_type_dom']['Press'],
            $this->targetBean->account_type
        );
    }
}
