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

require_once 'include/Smarty/plugins/function.sugar_button.php';
require_once 'include/Smarty/plugins/function.sugar_menu.php';
require_once 'include/Sugar_Smarty.php';

class FunctionSugarButtonTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_smarty = new Sugar_Smarty;

    }

    public function providerCustomCode($func, $onclick = 'this.form.module.value=\'Contacts\';') {
        return array(
            /*
            //simple input custom code
            array(
                '<input type="submit" value="{$APP.BUTTON_LABEL}" onclick="'.$onclick.'">',
                array(
                    'tag' => 'input',
                    'type' => 'submit',
                    'self_closing' => true,
                    'onclick' => $onclick,
                    'value' => '{$APP.BUTTON_LABEL}'
                ),
                '<input type="submit" value="{$APP.BUTTON_LABEL}" onclick="'.$onclick.'">',
            ),
            //custom code contains smarty conditional statement
            array(
                '<input type="submit" value="{$APP.BUTTON_LABEL}" onclick="{if $bean->access(\'edit\')}'.$onclick.'{/if}">',
                array(
                    'tag' => 'input',
                    'type' => 'submit',
                    'self_closing' => true,
                    'onclick' => '{if $bean->access(\'edit\')}'.$onclick.'{/if}',
                    'value' => '{$APP.BUTTON_LABEL}'
                ),
                '<input type="submit" value="{$APP.BUTTON_LABEL}" onclick="'.$onclick.'">',
            ),
            */
            //attributes wrapped with smarty
            array(
                '<input type="submit" value="{$APP.BUTTON_LABEL}" {if $bean->access(\'edit\')}onclick="'.$onclick.'"{else}onclick=""{/if} id="button_submit">',
                array(
                    'tag' => 'input',
                    'type' => 'submit',
                    'self_closing' => true,
                    'onclick' => '{if $bean->access(\'edit\')}'.$onclick.'{/if}',
                    'value' => '{$APP.BUTTON_LABEL}'
                ),
                '<input type="submit" value="{$APP.BUTTON_LABEL}" onclick="'.$onclick.'">',
            ),

            /*
            //custom code encapsulated smarty conditional statement, and contains hidden fields
            array(
                '{if $fields.status.value != "Held"} <input type="hidden" name="isSaveAndNew" value="false">  <input type="hidden" name="status" value="">  <input type="hidden" name="isSaveFromDetailView" value="true">  <input title="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}"   class="button"  onclick="this.form.status.value=\'Held\'; this.form.action.value=\'Save\';this.form.return_module.value=\'Calls\';this.form.isDuplicate.value=true;this.form.isSaveAndNew.value=true;this.form.return_action.value=\'EditView\'; this.form.return_id.value=\'{$fields.id.value}\'"  name="button"  value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}"  type="submit">{/if}',
                array()
            ),
            */
        );
    }
    /**
     * @dataProvider providerCustomCode
     */
    public function testCustomCode($customCode, $expected_parsed_array, $expected_customCode)
    {
        $output = parse_html_tag($customCode);
        var_dump($output);
        $this->assertEquals($expected_parsed_array, $output);
        /*
        $params = array(
            'module' => 'Accounts',
            'view' => 'DetailView',
            'id' => array(
                'customCode' => $customCode
            )
        );
        $output = smarty_function_sugar_button($params, $this->_smarty);
        */
    }
}