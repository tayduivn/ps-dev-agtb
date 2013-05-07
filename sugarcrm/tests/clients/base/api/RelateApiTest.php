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



require_once ('include/api/RestService.php');
require_once ("clients/base/api/RelateApi.php");


/**
 * @group ApiTests
 */
class RelateApiTest extends Sugar_PHPUnit_Framework_TestCase {

    public $accounts;
    public $contacts;
    public $roles;
    public $relateApi;

    public function setUp() {
        SugarTestHelper::setUp("current_user");
        // load up the unifiedSearchApi for good times ahead
        $this->relateApi = new RelateApi();
        $account = BeanFactory::newBean('Accounts');
        $account->name = "RelateApi setUp Account";
        $account->save();
        $this->accounts[] = $account;

        $contact = BeanFactory::newBean('Contacts');
        $contact->first_name = 'RelateApi setUp';
        $contact->last_name = 'Contact';
        $contact->save();
        $this->contacts[] = $contact;

        $account->load_relationship('contacts');
        $account->contacts->add($contact);

    }

    public function tearDown() {
        $GLOBALS['current_user']->is_admin = 1;        
        // delete the bunch of accounts crated
        foreach($this->accounts AS $account) {
            $account->mark_deleted($account->id);
        }
        foreach($this->contacts AS $contact) {
            $contact->mark_deleted($contact->id);
        }

        foreach($this->roles AS $role) {
            $role->mark_deleted($role->id);
            $role->mark_relationships_deleted($role->id);
            $GLOBALS['db']->query("DELETE FROM acl_fields WHERE role_id = '{$role->id}'");
        }
        unset($_SESSION['ACL']);

        SugarTestHelper::tearDown();
        parent::tearDown();        
    }

    // test set favorite
    public function testRelateRecordViewNone() {
        $modules = array('Contacts');
        $this->roles[] = $role = $this->createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'edit', 'list', 'export'));

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }

        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);

        $result = $this->relateApi->listRelated(new RelateApiServiceMockUp, array('module' => 'Accounts','record' => $this->accounts[0]->id, 'link_name' => 'contacts'));
        $this->assertNotEmpty($result['records'], "Records were empty");
        $this->assertEquals($result['records'][0]['id'], $this->contacts[0]->id, "ID Does not match");
    }

    protected function createRole($name, $allowedModules, $allowedActions, $ownerActions = array()) {
        $role = new ACLRole();
        $role->name = $name;
        $role->description = $name;
        $role->save();
        $GLOBALS['db']->commit();

        $roleActions = $role->getRoleActions($role->id);
        foreach ($roleActions as $moduleName => $actions) {
            // enable allowed modules
            if (isset($actions['module']['access']['id']) && !in_array($moduleName, $allowedModules)) {
                $role->setAction($role->id, $actions['module']['access']['id'], ACL_ALLOW_DISABLED);
            } elseif (isset($actions['module']['access']['id']) && in_array($moduleName, $allowedModules)) {
                $role->setAction($role->id, $actions['module']['access']['id'], ACL_ALLOW_ENABLED);
            } else {
                foreach ($actions as $action => $actionName) {
                    if (isset($actions[$action]['access']['id'])) {
                        $role->setAction($role->id, $actions[$action]['access']['id'], ACL_ALLOW_DISABLED);
                    }
                }
            }

            if (in_array($moduleName, $allowedModules)) {
                foreach ($actions['module'] as $actionName => $action) {
                    if(in_array($actionName, $allowedActions) && in_array($actionName, $ownerActions)) {
                        $aclAllow = ACL_ALLOW_OWNER;
                    }
                    elseif (in_array($actionName, $allowedActions)) {
                        $aclAllow = ACL_ALLOW_ALL;
                    } else {
                        $aclAllow = ACL_ALLOW_NONE;
                    }

                    $role->setAction($role->id, $action['id'], $aclAllow);
                }
            }

        }
        return $role;
    }


}

class RelateApiServiceMockUp extends RestService
{
    public function __construct() {$this->user = $GLOBALS['current_user'];}
    public function execute() {}
    protected function handleException(Exception $exception) {}
}
