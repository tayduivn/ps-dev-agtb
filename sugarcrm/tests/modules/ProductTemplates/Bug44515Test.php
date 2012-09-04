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

class Bug44515Test extends Sugar_PHPUnit_Framework_TestCase
{
   
    /**
     * @group Bug44515
     */
    var $customDir = "custom/modules/ProductTemplates/formulas";

    public function setUp()
    {
        
        if (!is_dir($this->customDir))
          mkdir($this->customDir, 0700, TRUE); // Creating nested directories at a glance

        file_put_contents($this->customDir . "/customformula1.php", "<?php\nclass Customformula1 {\n}\n?>");
        file_put_contents($this->customDir . "/customformula2.php", "<?php\nclass Customformula2 {\n}\n?>");
    }


    public function tearDown()
    {
        unset($GLOBALS['price_formulas']['Customformula1']);
        unset($GLOBALS['price_formulas']['Customformula2']);
        unlink($this->customDir . "/customformula1.php");
        unlink($this->customDir . "/customformula2.php");
        rmdir($this->customDir);
    }

    public function testLoadCustomFormulas()
    {
      require_once "modules/ProductTemplates/Formulas.php";

      // At this point I expect to have 7 formulas (5 standard and 2 custom).
      $expectedIndexes = 7;
      $this->assertEquals($expectedIndexes, count($GLOBALS['price_formulas']));

      // Check if standard formulas are still in the array
      $this->assertArrayHasKey("Fixed", $GLOBALS['price_formulas']);
      $this->assertArrayHasKey("ProfitMargin", $GLOBALS['price_formulas']);
      $this->assertArrayHasKey("PercentageMarkup", $GLOBALS['price_formulas']);
      $this->assertArrayHasKey("PercentageDiscount", $GLOBALS['price_formulas']);
      $this->assertArrayHasKey("IsList", $GLOBALS['price_formulas']);
      // Check if custom formulas are in the array
      $this->assertArrayHasKey("Customformula1", $GLOBALS['price_formulas']);
      $this->assertArrayHasKey("Customformula2", $GLOBALS['price_formulas']);

      // Check if CustomFormula1 point to the right file (/custom/modules/ProductTemplates/formulas/customformula1.php)
      $_customFormula1FileName = "custom/modules/ProductTemplates/formulas/customformula1.php";
      $this->assertEquals($_customFormula1FileName, $GLOBALS['price_formulas']['Customformula1']);
    }
}

