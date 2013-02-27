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


/**
 * Bug #43069
 *
 * User Still Able To Create Knowledge Base Articles With Module Access Disabled Via Roles
 * @ticket 43069
 * @author arymarchik@sugarcrm.com
 */
class Bug43069Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    protected $role;
    public function setUp()
    {
        /*
        global $current_user;
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('mod_strings', array('ACL'));
        SugarTestHelper::setUp('app_strings');

        $_SESSION['ACL'] = array();

        $this->role = new ACLRole();
        $this->role->id = create_guid();
        $this->role->new_with_id = true;
        $this->role->name = 'test43069role3';
        $this->role->load_relationship('users');
        $this->role->users->add($current_user);
        $this->role->save();
        */
    }

    /**
     * Creating KBDocument bean and try to display view for action EditView for user with access rights
     *
     * @group 43069
     */
    public function testAccessToKBDocument()
    {
        $this->markTestSkipped('Mark skipped for now');
        return;
        global $current_user;

        $acl = new ACLAction();
        $acl->name = 'access';
        $acl->category = 'KBDocuments';
        $acl->aclaccess = ACL_ALLOW_ENABLED;
        $acl->acltype = 'module';
        $acl->modified_user_id = 1;
        $acl->created_by = 1;
        $acl->save();

        $this->role->setAction($this->role->id, $acl->id, ACL_ALLOW_ENABLED);

        $acl = new ACLAction();
        $acl->name = 'edit';
        $acl->category = 'KBDocuments';
        $acl->aclaccess = ACL_ALLOW_ALL;
        $acl->acltype = 'module';
        $acl->modified_user_id = 1;
        $acl->created_by = 1;
        $acl->save();

        $this->role->setAction($this->role->id, $acl->id, ACL_ALLOW_ALL);

        ACLAction::getUserActions($current_user->id, true, 'KBDocuments', 'module');

        $this->expectOutputNotRegex('/.*(' . preg_quote($GLOBALS['mod_strings']['LBL_NO_ACCESS'], '/') . ')+.*/');
        $this->render();
    }

    /**
     * Creating KBDocument bean and try to display view for action EditView for user with no access rights
     *
     * @group 43069
     */
    public function testNoAccessToKBDocument()
    {
        $this->markTestSkipped('Mark skipped for now');
        return;
        global $current_user;
        $acl = new ACLAction();
        $acl->name = 'access';
        $acl->category = 'KBDocuments';
        $acl->aclaccess = ACL_ALLOW_DISABLED;
        $acl->acltype = 'module';
        $acl->modified_user_id = 1;
        $acl->created_by = 1;
        $acl->save();

        $this->role->setAction($this->role->id, $acl->id, ACL_ALLOW_DISABLED);

        ACLAction::getUserActions($current_user->id, true, 'KBDocuments', 'module');

        ACLAction::addActions('KBDocuments');
        $this->expectOutputRegex('/.*(' . preg_quote($GLOBALS['mod_strings']['LBL_NO_ACCESS'], '/') . ')+.*/');
        $this->render();
    }

    protected function render()
    {
        $controller = new KBDocument();
        $view_object_map = array('remap_action' => 'edit');
        $view = ViewFactory::loadView('classic', 'KBDocuments', $controller, $view_object_map, null);
        $view->action = 'EditView';
        $GLOBALS['current_view'] = $view;
        $view->display();
    }

    public function tearDown()
    {
        /*
        ACLAction::removeActions('KBDocuments');
        $this->role->mark_deleted($this->role->id);
        unset($_SESSION['ACL']);
        SugarTestHelper::tearDown();
        */
    }
}