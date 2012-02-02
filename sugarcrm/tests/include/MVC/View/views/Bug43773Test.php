<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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

require_once('modules/Contracts/Contract.php');
require_once('include/MVC/View/views/view.list.php');
require_once('include/MVC/View/ViewFactory.php');

class Bug43773Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp() {
        parent::setUp();
	    sugar_mkdir("custom/modules/Contracts/metadata",null,true);

        require('modules/Contracts/metadata/listviewdefs.php');
        $listViewDefs['Contracts']['TIME_TO_EXPIRY'] = 
            array (
                'type' => 'int',
                'label' => 'LBL_TIME_TO_EXPIRY',
                'width' => '10%',
                'default' => true,
            );
        
        $fd = fopen('custom/modules/Contracts/metadata/listviewdefs.php','w');
        fwrite($fd,'<'.'?'."php\n");
        fwrite($fd,'$listViewDefs["Contracts"] = '.var_export($listViewDefs['Contracts'],true).";\n");
        fclose($fd);
        
        $GLOBALS['action'] = 'index';
        $GLOBALS['module'] = 'Contracts';
		$GLOBALS['app_strings'] = return_application_language('en_us');
		$GLOBALS['app_list_strings'] = return_app_list_strings_language('en_us');
        $GLOBALS['mod_strings'] = return_module_language('en_us','Contracts');
        $GLOBALS['db'] = DBManagerFactory::getInstance();
        $GLOBALS['current_user'] = new User();
        $GLOBALS['current_user']->retrieve('1');
    }
    
    public function tearDown() {
	    rmdir_recursive("custom/modules/Contracts");
        unset($GLOBALS['module']);
        unset($GLOBALS['action']);
        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['current_user']);
        parent::tearDown();
    }
    /*
     * @group bug43773
     */
    public function testNonSortableNonDb()
    {
        $bean = new Contract();
        $view = ViewFactory::loadView('list','Contracts',$bean);

        ob_start();
        $view->listViewPrepare();
        ob_end_clean();

        $this->assertFalse($view->listViewDefs['Contracts']['TIME_TO_EXPIRY']['sortable'], 'TIME_TO_EXPIRY should not be sortable because it is not stored in the DB');
    }
}