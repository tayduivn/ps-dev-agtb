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

class Bug50678Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $_backupConfig;

    public function setUp()
    {
        global $sugar_config;

        $this->_backupConfig = $sugar_config;

        if(!empty($sugar_config['custom_help_url'])) {
            unset ($sugar_config['custom_help_url']);
        }
        if(!empty($sugar_config['custom_help_base_url'])) {
            unset ($sugar_config['custom_help_base_url']);
        }
    }

    public function tearDown()
    {
        global $sugar_config;
        $sugar_config = $this->_backupConfig;
    }

    public function testGetDefaultHelpURL() {
        global $sugar_config;

        $this->assertSame('http://www.sugarcrm.com/crm/product_doc.php?edition=arg0&version=arg1&lang=arg2&module=arg3&help_action=arg4&status=arg5&key=arg6',
            get_help_url('arg0', 'arg1', 'arg2', 'arg3', 'arg4', 'arg5', 'arg6'));
        $this->assertSame('http://www.sugarcrm.com/crm/product_doc.php?edition=arg0&version=arg1&lang=arg2&module=arg3&help_action=arg4&status=arg5&key=arg6&anchor=arg7',
            get_help_url('arg0', 'arg1', 'arg2', 'arg3', 'arg4', 'arg5', 'arg6', 'arg7'));
    }

    public function testGetCustomHelpURL() {
        global $sugar_config;

        $url = 'http://example.com';

        $sugar_config['custom_help_url'] = $url;

        $this->assertSame($url, get_help_url());
        $this->assertSame($url, get_help_url('arg0', 'arg1', 'arg2', 'arg3', 'arg4', 'arg5', 'arg6'));
        $this->assertSame($url, get_help_url('arg0', 'arg1', 'arg2', 'arg3', 'arg4', 'arg5', 'arg6', 'arg7'));
    }

    public function testGetCustomBaseHelpURL() {
        global $sugar_config;

        $url = 'http://example.com';

        $sugar_config['custom_help_base_url'] = $url;

        $this->assertSame($url."?edition=arg0&version=arg1&lang=arg2&module=arg3&help_action=arg4&status=arg5&key=arg6",
            get_help_url('arg0', 'arg1', 'arg2', 'arg3', 'arg4', 'arg5', 'arg6'));
        $this->assertSame($url."?edition=arg0&version=arg1&lang=arg2&module=arg3&help_action=arg4&status=arg5&key=arg6&anchor=arg7",
            get_help_url('arg0', 'arg1', 'arg2', 'arg3', 'arg4', 'arg5', 'arg6', 'arg7'));
    }

}
