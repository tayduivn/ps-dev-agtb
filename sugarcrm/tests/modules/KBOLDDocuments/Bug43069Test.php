<?php

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
 * Bug #43069
 *
 * User Still Able To Create Knowledge Base Articles With Module Access Disabled Via Roles
 * @ticket 43069
 * @author arymarchik@sugarcrm.com
 */
class Bug43069Test extends Sugar_PHPUnit_Framework_TestCase
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
     * Creating KBOLDDocument bean and try to display view for action EditView for user with access rights
     *
     * @group 43069
     */
    public function testAccessToKBOLDDocument()
    {
        $this->markTestSkipped('Mark skipped for now');
        return;
        global $current_user;

        $acl = new ACLAction();
        $acl->name = 'access';
        $acl->category = 'KBOLDDocuments';
        $acl->aclaccess = ACL_ALLOW_ENABLED;
        $acl->acltype = 'module';
        $acl->modified_user_id = 1;
        $acl->created_by = 1;
        $acl->save();

        $this->role->setAction($this->role->id, $acl->id, ACL_ALLOW_ENABLED);

        $acl = new ACLAction();
        $acl->name = 'edit';
        $acl->category = 'KBOLDDocuments';
        $acl->aclaccess = ACL_ALLOW_ALL;
        $acl->acltype = 'module';
        $acl->modified_user_id = 1;
        $acl->created_by = 1;
        $acl->save();

        $this->role->setAction($this->role->id, $acl->id, ACL_ALLOW_ALL);

        ACLAction::getUserActions($current_user->id, true, 'KBOLDDocuments', 'module');

        $this->expectOutputNotRegex('/.*(' . preg_quote($GLOBALS['mod_strings']['LBL_NO_ACCESS'], '/') . ')+.*/');
        $this->render();
    }

    /**
     * Creating KBOLDDocument bean and try to display view for action EditView for user with no access rights
     *
     * @group 43069
     */
    public function testNoAccessToKBOLDDocument()
    {
        $this->markTestSkipped('Mark skipped for now');
        return;
        global $current_user;
        $acl = new ACLAction();
        $acl->name = 'access';
        $acl->category = 'KBOLDDocuments';
        $acl->aclaccess = ACL_ALLOW_DISABLED;
        $acl->acltype = 'module';
        $acl->modified_user_id = 1;
        $acl->created_by = 1;
        $acl->save();

        $this->role->setAction($this->role->id, $acl->id, ACL_ALLOW_DISABLED);

        ACLAction::getUserActions($current_user->id, true, 'KBOLDDocuments', 'module');

        ACLAction::addActions('KBOLDDocuments');
        $this->expectOutputRegex('/.*(' . preg_quote($GLOBALS['mod_strings']['LBL_NO_ACCESS'], '/') . ')+.*/');
        $this->render();
    }

    protected function render()
    {
        $controller = new KBOLDDocument();
        $view_object_map = array('remap_action' => 'edit');
        $view = ViewFactory::loadView('classic', 'KBOLDDocuments', $controller, $view_object_map, null);
        $view->action = 'EditView';
        $GLOBALS['current_view'] = $view;
        $view->display();
    }

    public function tearDown()
    {
        /*
        ACLAction::removeActions('KBOLDDocuments');
        $this->role->mark_deleted($this->role->id);
        unset($_SESSION['ACL']);
        SugarTestHelper::tearDown();
        */
    }
}
