<?php
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


require_once('modules/UpgradeWizard/uw_utils.php');

/**
 * @ticket 40793
 */
class Bug40793Test extends Sugar_PHPUnit_Framework_TestCase
{

    const WEBALIZER_DIR_NAME = 'bug40793';
    private $_notIncludeDir;
    private $_includeDir;

    public function setUp()
    {
        $this->_notIncludeDir = self::WEBALIZER_DIR_NAME . "/this_dir_should_not_include";
        $this->_includeDir = self::WEBALIZER_DIR_NAME . "/1";
        mkdir(self::WEBALIZER_DIR_NAME, 0755);
        mkdir($this->_notIncludeDir, 0755);
        mkdir($this->_includeDir, 0755);
    }

    public function tearDown()
    {
        rmdir($this->_notIncludeDir);
        rmdir($this->_includeDir);
        rmdir(self::WEBALIZER_DIR_NAME);
    }

    public function testIfDirIsNotIncluded()
    {
        $skipDirs = array($this->_notIncludeDir);
        $files = uwFindAllFiles( self::WEBALIZER_DIR_NAME, array(), true, $skipDirs);
        $this->assertNotContains($this->_notIncludeDir, $files, "Directory {$this->_notIncludeDir} shouldn't been included in this list");
        $this->assertContains($this->_includeDir, $files, "Directory {$this->_includeDir} should been included in this list");
    }
}