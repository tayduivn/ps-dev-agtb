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

require_once 'PHPUnit/Extensions/OutputTestCase.php';

class Bugs39819_39820Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @ticket 39819
     * @ticket 39820
     */
    public function setUp()
    {
        if (!is_dir("custom/modules/Accounts/language")) {
            mkdir("custom/modules/Accounts/language", 0700, TRUE); // Creating nested directories at a glance
        }
    }
    
    public function testLoadEnHelp()
    {
        // en_us help on a standard module.
        file_put_contents("modules/Accounts/language/en_us.help.DetailView.html", "<h1>ENBugs39819-39820</h1>");
        
        $_SERVER['HTTP_HOST'] = "";
        $_SERVER['SCRIPT_NAME'] = "";
        $_SERVER['QUERY_STRING'] = "";

        $_REQUEST['view'] = 'documentation';
        $_REQUEST['lang'] = 'en_us';
        $_REQUEST['help_module'] = 'Accounts';
        $_REQUEST['help_action'] = 'DetailView';

        ob_start();
        require "modules/Administration/SupportPortal.php";

        $tStr = ob_get_contents();
        ob_end_clean();
        
        unlink("modules/Accounts/language/en_us.help.DetailView.html");
        
        // I expect to get the en_us normal help file....
        $this->assertRegExp("/.*ENBugs39819\-39820.*/", $tStr);
    }
    
    public function testLoadCustomItHelp()
    {
        // Custom help (NOT en_us) on a standard module.
        file_put_contents("custom/modules/Accounts/language/it_it.help.DetailView.html", "<h1>Bugs39819-39820</h1>");

        $_SERVER['HTTP_HOST'] = "";
        $_SERVER['SCRIPT_NAME'] = "";
        $_SERVER['QUERY_STRING'] = "";

        $_REQUEST['view'] = 'documentation';
        $_REQUEST['lang'] = 'it_it';
        $_REQUEST['help_module'] = 'Accounts';
        $_REQUEST['help_action'] = 'DetailView';
        
        ob_start();
        require "modules/Administration/SupportPortal.php";

        $tStr = ob_get_contents();
        ob_end_clean();

        unlink("custom/modules/Accounts/language/it_it.help.DetailView.html");
        
        // I expect to get the it_it custom help....
        $this->assertRegExp("/.*Bugs39819\-39820.*/", $tStr);
    }
}
