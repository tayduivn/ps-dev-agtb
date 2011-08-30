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

//FILE SUGARCRM flav=pro ONLY

require_once 'include/Smarty/plugins/function.sugarvar_teamset.php';
require_once 'include/Sugar_Smarty.php';

class Bug45969 extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
    
        sugar_mkdir("custom/include/SugarFields/Fields/Teamset/",null,true);
        if (file_exists("custom/include/SugarFields/Fields/Teamset/Teamset.php")) { 
            unlink("custom/include/SugarFields/Fields/Teamset/SugarFieldTeamset.php");
        }

        if( $fh = @fopen("custom/include/SugarFields/Fields/Teamset/SugarFieldTeamset.php", 'w+') ) 
        {
$string = <<<EOQ
<?php
require_once 'include/SugarFields/Fields/Teamset/SugarFieldTeamset.php';

class CustomSugarFieldTeamset extends SugarFieldTeamset {
    function render(\$params, &\$smarty) {
        return 'CustomRender';
    }

}
EOQ;
            fputs( $fh, $string);
            fclose( $fh );
        }
        
        $this->_smarty = new Sugar_Smarty;
        $this->_isset_request_module = array_key_exists('module', $_REQUEST);
        if (!$this->_isset_request_module) {
            $_REQUEST['module'] = 'foo';
        }
        $this->_smarty_function_params = array(
            'displayType' => 'renderDetailView', 
            'formName' => 'foo', 
            'module' => 'Foo'
        );
        
    }
    
    public function tearDown()
    {
        if (file_exists("custom/include/SugarFields/Fields/Teamset/Teamset.php")) {
            unlink("custom/include/SugarFields/Fields/Teamset/SugarFieldTeamset.php");
        }
        
        unset($this->_smarty);
        if (!$this->_isset_request_module) {
            unset($_REQUEST['module']);
        }
    }

    /**
     * @bug 45969
     */
    public function testCustomCalling() 
    {
        $smarty = new Sugar_Smarty;
        
        $this->assertEquals('CustomRender', smarty_function_sugarvar_teamset($this->_smarty_function_params,$this->_smarty));
    }
}
