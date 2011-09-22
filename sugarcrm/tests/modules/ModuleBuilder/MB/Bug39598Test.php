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


/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

class Bug39598Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $tmpDir;
    private $tmpFile;

    public function setUp()
    {
        $this->tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bug32821';
        mkdir($this->tmpDir);
        $this->tmpFile = tempnam($this->tmpDir, 'bug32821');
        //rename file to 'relationships.php'
        rename($this->tmpFile, $this->tmpDir . DIRECTORY_SEPARATOR . 'relationships.php');
        $this->tmpFile = $this->tmpDir . DIRECTORY_SEPARATOR . 'relationships.php';
        file_put_contents($this->tmpFile, '[\'test_oldname\'] => ');
    }

    public function tearDown()
    {
        unlink($this->tmpFile);
        rmdir($this->tmpDir);
    }

    public function testRelationshipName()
    {
        $mbModule = new MBModule('newname', $this->tmpDir, 'test', 'test');
        $mbModule->renameMetaData($this->tmpDir, 'test_oldname');
        $replacedContents = file_get_contents($this->tmpFile);
        $this->assertEquals('[\'test_newname\'] => ', $replacedContents, 'Module name replaced correctly in relationships metadata');
        
    }
}