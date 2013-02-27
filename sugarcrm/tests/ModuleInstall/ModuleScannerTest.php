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
require_once 'ModuleInstall/ModuleScanner.php';

class ModuleScannerTest extends Sugar_PHPUnit_Framework_TestCase
{
    var $fileLoc;

	public function setUp()
	{
        $this->fileLoc = "cache/moduleScannerTemp.php";
	}

	public function tearDown()
	{
		if (is_file($this->fileLoc))
			unlink($this->fileLoc);
	}

	public function phpSamples()
	{
	    return array(
	        array("<?php echo blah;", true),
	        array("<? echo blah;", true),
	        array("blah <? echo blah;", true),
	        array("blah <?xml echo blah;", true),
	        array("<?xml version=\"1.0\"></xml>", false),
	        array("<?xml \n echo blah;", true),
	        array("<?xml version=\"1.0\"><? blah ?></xml>", true),
	        array("<?xml version=\"1.0\"><?php blah ?></xml>", true),
	        );
	}

	/**
	 * @dataProvider phpSamples
	 */
	public function testPHPFile($content, $is_php)
	{
        $ms = new ModuleScanner();
	    $this->assertEquals($is_php, $ms->isPHPFile($content), "Bad PHP file result");
	}

	public function testFileTemplatePass()
    {

    	$fileModContents = <<<EOQ
<?PHP
require_once('include/SugarObjects/templates/file/File.php');

class testFile_sugar extends File {
	function fileT_testFiles_sugar(){
		parent::File();
		\$this->file = new File();
		\$file = "file";
	}
}
?>
EOQ;
		file_put_contents($this->fileLoc, $fileModContents);
		$ms = new ModuleScanner();
		$errors = $ms->scanFile($this->fileLoc);
		$this->assertTrue(empty($errors));
    }

	public function testFileFunctionFail()
    {

    	$fileModContents = <<<EOQ
<?PHP
require_once('include/SugarObjects/templates/file/File.php');

class testFile_sugar extends File {
	function fileT_testFiles_sugar(){
		parent::File();
		\$this->file = new File();
		\$file = file('test.php');

	}
}
?>
EOQ;
		file_put_contents($this->fileLoc, $fileModContents);
		$ms = new ModuleScanner();
		$errors = $ms->scanFile($this->fileLoc);
		$this->assertTrue(!empty($errors));
    }

	public function testCallUserFunctionFail()
    {

    	$fileModContents = <<<EOQ
<?PHP
	call_user_func("sugar_file_put_contents", "test2.php", "test");
?>
EOQ;
		file_put_contents($this->fileLoc, $fileModContents);
		$ms = new ModuleScanner();
		$errors = $ms->scanFile($this->fileLoc);
		$this->assertTrue(!empty($errors));
    }

    /**
     * Bug 56717
     *
     * When ModuleScanner is enabled, handle bars templates are invalidating published
     * package installation.
     *
     * @group bug56717
     */
    public function testBug56717ValidExtsAllowed() {
        // Allowed file names
        $allowed = array(
            'php' => 'test.php',
            'htm' => 'test.htm',
            'xml' => 'test.xml',
            'hbt' => 'test.hbt',
        );

        // Disallowed file names
        $notAllowed = array(
            'docx' => 'test.docx',
            'java' => 'test.java',
            'phtm' => 'test.phtm',
        );

        // Get our scanner
        $ms = new ModuleScanner();

        // Test valid
        foreach ($allowed as $ext => $file) {
            $valid = $ms->isValidExtension($file);
            $this->assertTrue($valid, "The $ext extension should be valid on $file but the ModuleScanner is saying it is not");
        }

        // Test not valid
        foreach ($notAllowed as $ext => $file) {
            $valid = $ms->isValidExtension($file);
            $this->assertFalse($valid, "The $ext extension should not be valid on $file but the ModuleScanner is saying it is");
        }
    }

	public function testCallMethodObjectOperatorFail()
    {

    	$fileModContents = <<<EOQ
<?PHP
    //doesnt matter what the class name is, what matters is use of the banned method, setlevel
	\$GlobalLoggerClass->setLevel();
?>
EOQ;
		file_put_contents($this->fileLoc, $fileModContents);
		$ms = new ModuleScanner();
		$errors = $ms->scanFile($this->fileLoc);
		$this->assertNotEmpty($errors, 'There should have been an error caught for use of "->setLevel()');
    }

	public function testCallMethodDoubleColonFail()
    {

    	$fileModContents = <<<EOQ
<?PHP
    //doesnt matter what the class name is, what matters is use of the banned method, setlevel
	\$GlobalLoggerClass::setLevel();
?>
EOQ;
		file_put_contents($this->fileLoc, $fileModContents);
		$ms = new ModuleScanner();
		$errors = $ms->scanFile($this->fileLoc);
		$this->assertNotEmpty($errors, 'There should have been an error caught for use of "::setLevel()');
    }

    /**
     * @group bug58072
     */
	public function testLockConfig()
    {

    	$fileModContents = <<<EOQ
<?PHP
	\$GLOBALS['sugar_config']['moduleInstaller']['test'] = true;
    	\$manifest = array();
    	\$installdefs = array();
?>
EOQ;
		file_put_contents($this->fileLoc, $fileModContents);
		$ms = new MockModuleScanner();
		$ms->config['test'] = false;
		$ms->lockConfig();
		MSLoadManifest($this->fileLoc);
		$errors = $ms->checkConfig($this->fileLoc);
		$this->assertTrue(!empty($errors), "Not detected config change");
		$this->assertFalse($ms->config['test'], "config was changed");
    }
}

class MockModuleScanner extends  ModuleScanner
{
    public $config;
    public function isPHPFile($contents) {
        return parent::isPHPFile($contents);
    }
}

