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

require_once 'include/EditView/SubpanelQuickCreate.php';

class Bug39610Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $app_strings, $app_list_strings;
        $app_strings = return_application_language($GLOBALS['current_language']);
        $app_list_strings = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }
    
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }
    
    public function testUseCustomViewAndCustomClassName()
    {
        $target_module = 'Contacts';
        sugar_mkdir('custom/modules/'. $target_module . '/views/',null,true);
        if( $fh = @fopen('custom/modules/'. $target_module . '/views/view.edit.php', 'w') )
        {
$string = <<<EOQ
<?php
class CustomContactsViewEdit
{
     var \$useForSubpanel = false;

     public function CustomContactsViewEdit() 
     {
          \$GLOBALS['CustomContactsSubpanelQuickCreated'] = true;
     }
};
?>
EOQ;
            fputs( $fh, $string);
            fclose( $fh );
        }

        
        $subpanelMock = new SubpanelQuickCreateMockBug39610Test($target_module, 'SubpanelQuickCreate');
        $this->assertTrue(!empty($GLOBALS['CustomContactsSubpanelQuickCreated']), "Assert that CustomContactsEditView constructor was called");
        @unlink('custom/modules/'. $target_module . '/views/view.subpanelquickcreate.php');
    }

}


class SubpanelQuickCreateMockBug39610Test extends SubpanelQuickCreate
{
	public function SubpanelQuickCreateMockBug39610Test($module, $view='QuickCreate', $proccessOverride = false)
	{
		parent::SubpanelQuickCreate($module, $view, $proccessOverride);	
	}
	
	public function process()
	{
		//no-op
	}
}
