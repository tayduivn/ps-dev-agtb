<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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

/**
 * Bug #49772
 *
 * [IBM RTC 3001] XSS - Administration, Rename Modules, Singular Label
 * @ticket 49772
 * @author arymarchik@sugarcrm.com
 */
class Bug49772Test extends Sugar_PHPUnit_Framework_TestCase
{


    private $_old_label = '';
    private $_test_label = 'LBL_ACCOUNT_NAME';
    private $_test_module = 'Contacts';
    private $_lang = 'en_us';


    /**
     * Generating new label with HTML tags
     * @group 43069
     */
    public function testLabelSaving()
    {
        $mod_strings = return_module_language($this->_lang, $this->_test_module);
        $this->_old_label = $mod_strings[$this->_test_label];
        $pref = '<img alt="<script>" src="www.test.com/img.png" ="alert(7001)" width="1" height="1"/>';
        $prepared_pref = to_html(strip_tags(from_html($pref)));
        //$prepared_pref = to_html(remove_xss(from_html($pref)));
        $new_label = $prepared_pref . ' ' . $this->_old_label;

        // save the new label to the language file
        ParserLabel::addLabels($this->_lang, array($this->_test_label => $new_label), $this->_test_module);

        // read the language file to get the new value
        include("custom/modules/{$this->_test_module}/language/{$this->_lang}.lang.php");

        $this->assertEquals($new_label, $mod_strings[$this->_test_label]);
        $this->assertNotEquals($pref . ' ' . $this->_old_label, $mod_strings[$this->_test_label]);

    }

    public function tearDown()
    {
        ParserLabel::addLabels($this->_lang, array($this->_test_label=>$this->_old_label), $this->_test_module);
    }
}
