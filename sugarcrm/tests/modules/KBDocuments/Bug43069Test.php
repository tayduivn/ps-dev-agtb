<?php
//File SUGARCRM flav=pro ONLY
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
     * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
     ********************************************************************************/

    /**
     * Bug #43069
     *
     * User Still Able To Create Knowledge Base Articles With Module Access Disabled Via Roles
     * @ticket 43069
     * @author arymarchik@sugarcrm.com
     */
require_once 'PHPUnit/Extensions/OutputTestCase.php';

class Bug43069Test extends PHPUnit_Extensions_OutputTestCase
{
    private $_current_user = null;
    private $_current_acl = null;

    public function setUp()
    {
        $GLOBALS['app_strings'] = return_application_language('en_us');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $_SESSION['ACL'] = array();
        $_SESSION['ACL'][$GLOBALS['current_user']->id]['KBDocuments'] =
        array(
            'module' => array(
                'access' =>
                array(
                    'id' => '576ff3d9-ae73-4332-bf9f-4f196ec6b887',
                    'aclaccess' => '-98',
                    'isDefault' => false,
                ),
                'admin' => array(
                    'id' => '4b3f757d-f5c4-2c55-2253-4f196e813bc7',
                    'aclaccess' => '1',
                    'isDefault' => true,
                ),
                'delete' => array(
                    'id' => '94c5715e-225d-85ff-1405-4f196ef5fcf4',
                    'aclaccess' => '90',
                    'isDefault' => true,
                ),
                'edit' => array(
                    'id' => '866f6463-8c7b-0108-f2c2-4f196e14cb70',
                    'aclaccess' => '90',
                    'isDefault' => true,
                ),
                'export' => array(
                    'id' => 'b55c8527-15f4-308e-762a-4f196e414a41',
                    'aclaccess' => '90',
                    'isDefault' => true,
                ),
                'import' => array(
                    'id' => 'a302ad84-6808-9301-62b0-4f196ee27ed9',
                    'aclaccess' => '90',
                    'isDefault' => true,
                ),
                'list' => array(
                    'id' => '762b0348-4d7e-a65d-b4db-4f196e586388',
                    'aclaccess' => '90',
                    'isDefault' => true,
                ),
                'view' => array(
                    'id' => '65b3bd7e-114d-eb10-2c73-4f196efc3550',
                    'aclaccess' => '90',
                    'isDefault' => true,
                ),
            ),
            'fields' => array(),
        );
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'ACL');
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        // reset cached ACLs
        SugarACL::$acls = array();
    }

    /**
     * Creating KBDocument bean and try to display view for action EditView
     * @group 43069
     */
    public function testAccessToKBDocument()
    {
        $this->expectOutputRegex('/.*(' . quotemeta($GLOBALS['mod_strings']['LBL_NO_ACCESS']) . ')+.*/');
        $controller = new KBDocument();
        $view_object_map = array();
        $view_object_map['remap_action'] = 'edit';
        $view = ViewFactory::loadView('classic', 'KBDocuments', $controller, $view_object_map, null);
        $view->action='EditView';
        $GLOBALS['current_view'] = $view;
        $this->assertFalse($view->display());
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($_SESSION['ACL']);
        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['app_strings']);
    }
}