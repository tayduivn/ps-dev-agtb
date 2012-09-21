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

require_once('tests/service/RestTestCase.php');

class Bug48748Test extends RestTestCase
{

    protected $package = 'Accounts';
    protected $packageExists = false;
    protected $aclRole;
    protected $aclField;


    public function setUp()
    {
        parent::setUp();

        //If somehow this package already exists copy it
        if(file_exists('custom/modules/' . $this->package))
        {
           $this->packageExists = true;
           mkdir_recursive('custom/modules/' . $this->package . '_bak');
           copy_recursive('custom/modules/' . $this->package, 'custom/modules/' . $this->package . '_bak');
        }

        //Make the custom package directory and simulate copying the file in
        mkdir_recursive('custom/modules/' . $this->package . '/Ext/WirelessLayoutdefs');

        $theArray = array ($this->package => array('subpanel_setup' => array ( $this->package.'_accounts' => array(
          'order' => 100,
          'module' => 'Contacts',
          'subpanel_name' => 'default',
          'title_key' => 'LBL_BUG48784TEST',
          'get_subpanel_data' => 'Bug48748Test',
        ))));
        $theFile = 'custom/modules/' . $this->package . '/Ext/WirelessLayoutdefs/wireless.subpaneldefs.ext.php';
        write_array_to_file('layout_defs', $theArray, $theFile);

        sugar_chmod('custom/modules/' . $this->package . '/Ext/WirelessLayoutdefs/wireless.subpaneldefs.ext.php', 0655);

        global $beanList, $beanFiles, $current_user;
        //$beanList['Contacts'] = 'Contact';
        //$beanFiles['Bug48784Mock'] = 'modules/Contacts/Contact.php';

        //Create an anonymous user for login purposes/
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user->status = 'Active';
        $current_user->is_admin = 1;
        $current_user->save();
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing

        $_SESSION['avail_modules'][$this->package] = 'write';
    }

    public function tearDown()
    {
        parent::tearDown();
        if($this->packageExists)
        {
            //Copy original contents back in
            copy_recursive('custom/modules/' . $this->package . '_bak', 'custom/modules/' . $this->package);
            rmdir_recursive('custom/modules/' . $this->package . '_bak');
        } else {
            rmdir_recursive('custom/modules/' . $this->package);
        }

        unset($_SESSION['avail_modules'][$this->package]);
    }

    public function testWirelessModuleLayoutForCustomModule()
    {

        $this->assertTrue(file_exists('custom/modules/' . $this->package . '/Ext/WirelessLayoutdefs/wireless.subpaneldefs.ext.php'));
        //$contents = file_get_contents('custom/modules/' . $this->package . '/Ext/WirelessLayoutdefs/wireless.subpaneldefs.ext.php');
        include('custom/modules/' . $this->package . '/Ext/WirelessLayoutdefs/wireless.subpaneldefs.ext.php');

        global $current_user;
        $result = $this->_login($current_user);
        $session = $result['id'];
        $results = $this->_makeRESTCall('get_module_layout',
        array(
            'session' => $session,
            'module' => array($this->package),
            'type' => array('wireless'),
            'view' => array('subpanel'),
            )
        );

        $this->assertEquals('Bug48748Test', $results[$this->package]['wireless']['subpanel']["{$this->package}_accounts"]['get_subpanel_data'], 'Cannot load custom wireless.subpaneldefs.ext.php file');
    }
}
