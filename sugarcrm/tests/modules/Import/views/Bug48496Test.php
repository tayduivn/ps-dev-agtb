<?php
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

require_once('include/ListView/ListViewFacade.php');
require_once('modules/Import/views/view.last.php');

class Bug48496Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    var $backup_config;

    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['module']='Imports';
        $_REQUEST['module']='Imports';
        $_REQUEST['import_module']='Accounts';
        $_REQUEST['action']='last';
        $_REQUEST['type']='';
        $_REQUEST['has_header'] = 'off';
        sugar_touch('upload/import/status_'.$GLOBALS['current_user']->id.'.csv');
    }

    public function tearDown()
    {
        unlink('upload/import/status_'.$GLOBALS['current_user']->id.'.csv');
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
        unset($GLOBALS['module']);
        unset($_REQUEST['module']);
        unset($_REQUEST['import_module']);
        unset($_REQUEST['action']);
        unset($_REQUEST['type']);
        unset($_REQUEST['has_header']);
    }

    public function testQueryDoesNotContainDuplicateUsersLastImportClauses() {
        global $current_user;

        $params = array(
            'custom_from' => ', users_last_import',
            'custom_where' => " AND users_last_import.assigned_user_id = '{$current_user->id}'
                AND users_last_import.bean_type = 'Account'
                AND users_last_import.bean_id = accounts.id
                AND users_last_import.deleted = 0
                AND accounts.deleted = 0",
        );

        $seed = SugarModule::get('Accounts')->loadBean();

        $lvfMock = $this->getMock('ListViewFacade', array('setup', 'display', 'build'), array($seed, 'Accounts'));

        $lvfMock->expects($this->any())
            ->method('setup')
            ->with($this->anything(),
            '',
            $params,
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->anything());

        $viewLast = new ImportViewLastWrap();
        $viewLast->init($seed);
        $viewLast->lvf = $lvfMock;

        $viewLast->publicGetListViewResults();
    }

}

class ImportViewLastWrap extends ImportViewLast {
    public function publicGetListViewResults() {
        return $this->getListViewResults();
    }
}
