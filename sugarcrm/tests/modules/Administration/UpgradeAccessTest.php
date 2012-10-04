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

/**
 * UpgradeAccessTest.php
 *
 * This file tests the code run when UpgradeAccess.php is invoked.
 */

class UpgradeAccessTest extends Sugar_PHPUnit_Framework_TestCase
{

public function setUp()
{
    if(!file_exists('.htaccess'))
    {
        $this->markTestSkipped('This may be an instance that does not support the use of .htaccess files');
        return;
    }

    if(!is_writable('.htaccess'))
    {
        $this->markTestSkipped('Cannot write to .htaccess file.');
        return;
    }

    //Create a backup file just in case things go wrong
    file_put_contents('.htaccess_test.bak', file_get_contents('.htaccess'));

    SugarTestHelper::setUp('mod_strings', array('Administration'));
}


public function tearDown()
{
    //Restore .htaccess from .htaccess_test.bak
    if(file_exists('.htaccess_test.bak'))
    {
        file_put_contents('.htaccess', file_get_contents('.htaccess_test.bak'));
        unlink('.htaccess_test.bak');
    }
    SugarTestHelper::tearDown();
}


/**
 * This function tests to see the UpgradeAccess file correctly builds the .htaccess file when run.
 * In particular, the mod rewrite rule for rest URLs should be created.
 *
 */
public function testUpgradeAccessCreatesRewriteRule()
{
    require('modules/Administration/UpgradeAccess.php');
    $contents = file_get_contents('.htaccess');

    preg_match('/RewriteRule \^rest\/\(\.\*\)\$ api\/rest.php\?\_\_sugar\_url=\$1 \[L\,QSA\]/', $contents, $matches);
    $this->assertNotEmpty($matches, 'Could not find RewriteRule');
    $this->assertEquals(1, count($matches), 'Duplicate blocks were created for the RewriteRule');
    $this->assertTrue(strpos($contents, '<FilesMatch') !== false, 'Code outside of restrictions was not copied over');
}

}