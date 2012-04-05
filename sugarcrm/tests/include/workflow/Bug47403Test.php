<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once('include/workflow/action_utils.php');

class Bug47403Test extends Sugar_PHPUnit_Framework_TestCase
{

    protected $_focus;
    protected $_actionArray;

    public function setUp()
    {
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->_focus = SugarTestAccountUtilities::createAccount();

        $this->_focus->field_defs['assigned_user_id']['type'] = 'relate';
        $this->_focus->field_defs['assigned_user_id']['module'] = 'Users';

        $this->_actionArray = array (
            'action_module' => '',
            'action_type' => 'update',
            'rel_module' => '',
            'rel_module_type' => 'all',
            'basic_ext' => array (),
            'advanced' => array (),
        );
    }

    public function tearDown()
    {
        unset($this->_actionArray);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        unset($GLOBALS['current_user']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
    }

    public function testWorkflowCanSetNonRequiredFieldToEmpty() {
        $this->_focus->assigned_user_id = $GLOBALS['current_user']->id;
        $this->_actionArray['basic'] = array('assigned_user_id' => '');

        $this->assertSame($GLOBALS['current_user']->id, $this->_focus->assigned_user_id);
        process_action_update($this->_focus, $this->_actionArray);
        $this->assertSame('', $this->_focus->assigned_user_id);
    }

    public function testWorkflowCanNotSetRequiredFieldToEmpty() {
        $this->_focus->user_name = $GLOBALS['current_user']->user_name;
        $this->_actionArray['basic'] = array('name' => '');

        $this->assertSame($GLOBALS['current_user']->user_name, $this->_focus->user_name);
        process_action_update($this->_focus, $this->_actionArray);
        $this->assertNotSame('', $this->_focus->user_name);
    }
}

?>