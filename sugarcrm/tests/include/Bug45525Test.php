<?php
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
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

class Bug45525 extends Sugar_PHPUnit_Framework_TestCase
{
   
    /**
     * @group Bug45525
     */
    var $testLangFile = "cache/upload/myLang.php";

    public function setUp()
    {
        if(!file_exists('cache/upload'))
        {
            mkdir_recursive('cache/upload');
        }
    }


    public function tearDown()
    {
    }

    public function testOverwriteDropDown()
    {
      global $app_list_strings;
      $app_list_strings = array("TestList" => array ("A" => "Option A", "B" => "Option B", "C" => "Option C"));

      require_once 'include/utils.php';

      $file =

      file_put_contents($this->testLangFile, '<?php
        $app_list_strings[\'TestList\'] = array(\'D\' => \'Option D\');
        ?>');

      // Initially TestList should have 3 items
      $this->assertEquals(3, count($app_list_strings['TestList']));

      $app_list_strings = _mergeCustomAppListStrings($this->testLangFile, $app_list_strings);

      // After merge with custom language file, TestList should have just 1 item (standard behaviour)
      $this->assertEquals(1, count($app_list_strings['TestList']));

      unlink($this->testLangFile);

      unset($GLOBALS['app_list_strings']);
    }

    public function testAppendDropDown()
    {
      global $app_list_strings;
      $app_list_strings = array("TestList" => array ("A" => "Option A", "B" => "Option B", "C" => "Option C"));

      require_once 'include/utils.php';

      file_put_contents($this->testLangFile, "<?php\n\$exemptDropdowns[] = 'TestList';\n\$app_list_strings['TestList']['D'] = 'Option D';\n?>");

      // Initially TestList should have 3 items
      $this->assertEquals(3, count($app_list_strings['TestList']));

      $app_list_strings = _mergeCustomAppListStrings($this->testLangFile, $app_list_strings);

      // After merge with custom language file, TestList should have 4 items (after-fix behaviour)
      $this->assertEquals(4, count($app_list_strings['TestList']));

      unlink($this->testLangFile);

      unset($GLOBALS['app_list_strings']);
    }

}

