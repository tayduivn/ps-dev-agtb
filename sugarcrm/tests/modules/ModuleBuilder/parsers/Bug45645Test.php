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

require_once('modules/ModuleBuilder/parsers/parser.label.php');

class Bug45645Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $custLangFile;
    protected $lang = 'en_us';
    protected $testModule = 'Opportunities';
    protected $testLabel = 'LBL_ACCOUNT_NAME';
    protected $oldLabel;
    protected $newLabel;
    
    protected function setup()
    {
        $this->custLangFile = "custom/modules/{$this->testModule}/Ext/Language/en_us.lang.ext.php";
    }
    
    protected function tearDown()
    {
        // Set things back to what they were
        $params = array($this->testLabel => $this->oldLabel);
        ParserLabel::addLabels($this->lang, $params, $this->testModule);
    }

    public function testLabelSaving()
    {
        $mod_strings = return_module_language($this->lang, $this->testModule);
        $this->oldLabel = $mod_strings[$this->testLabel];
        $this->newLabel = 'test ' . $this->oldLabel;

        // save the new label to the language file
        $params = array($this->testLabel => $this->newLabel);
        ParserLabel::addLabels($this->lang, $params, $this->testModule);

        // read the language file to get the new value
        $this->assertFileExists($this->custLangFile, "Label extension file does not exist");
        include $this->custLangFile;
        $this->assertEquals($this->newLabel, $mod_strings[$this->testLabel], 'Label not changed.');
    }
}


?>
