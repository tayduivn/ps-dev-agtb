<?php
//FILE SUGARCRM flav=pro ONLY
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
 
require_once('modules/Teams/Team.php');
require_once('modules/Teams/TeamSet.php');
require_once('include/nusoap/nusoap.php');

class AdvancedSearchWidgetTest extends Sugar_PHPUnit_Framework_OutputTestCase
{
    private $_sugarField;
    private $_smarty;
    private $_params;

	public function setUp()
	{
	    require_once('include/SugarFields/SugarFieldHandler.php');
		$sfh = new SugarFieldHandler();
		$this->_sugarField = $sfh->getSugarField('Teamset', true);
		require_once('include/Sugar_Smarty.php');

		$this->_params = array();
		$this->_params['parentFieldArray'] = 'fields';
		$this->_params['tabindex'] = true;
		$this->_params['displayType'] = 'renderSearchView';
    	$this->_params['display'] = '';
    	$this->_params['labelSpan'] = '';
    	$this->_params['fieldSpan'] = '';
    	$this->_params['formName'] = 'search_form';
    	$this->_params['displayParams'] = array('formName'=>'');
		$team = SugarModule::get('Accounts')->loadBean();
		$fieldDefs = $team->field_defs;
		$fieldDefs['team_name_advanced'] = $fieldDefs['team_name'];
		$fieldDefs['team_name_advanced']['name'] = 'team_name_advanced';
		$this->_smarty = new Sugar_Smarty();
		$this->_smarty->assign('fields', $fieldDefs);
		$this->_smarty->assign('displayParams', array());
		$_REQUEST['module'] = 'Accounts';
		$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    protected function checkSearchValues($html)
    {
		$matches = array();
        preg_match_all("'(<script[^>]*?>)(.*?)(</script[^>]*?>)'si", $html, $matches, PREG_PATTERN_ORDER);
	    $this->assertTrue(isset($matches[0][5]), "Check that the script tags are rendered for advanced teams widget");
		if(isset($matches[0][5])) {
	       $js = $matches[0][5];
	       $valueMatches = array();
	       if(preg_match_all('/\.value = \"([^\"]+)\"/', $js, $valueMatches, PREG_PATTERN_ORDER)) {
	       	  $this->assertEquals($valueMatches[1][0], 'West', "Check that team 'West' is the first team in widget as specified by arguments");
	       	  $this->assertEquals($valueMatches[1][1], 'West', "Check that team 'West' is the first team in widget as specified by arguments");
	       }
	    }
	    return true;
    }

    public function testSearchValuesFromRequest()
    {
    	$_REQUEST['form_name'] = '';
	    $_REQUEST['update_fields_team_name_advanced_collection'] = '';
	    $_REQUEST['team_name_advanced_new_on_update'] = false;
	    $_REQUEST['team_name_advanced_allow_update'] = '';
	    $_REQUEST['team_name_advanced_allowed_to_check'] = false;
	    $_REQUEST['team_name_advanced_field'] = 'team_name_advanced_table';
	    $_REQUEST['team_name_advanced_collection_0'] = 'West';
	    $_REQUEST['id_team_name_advanced_collection_0'] = 'West';
	    $_REQUEST['primary_team_name_advanced_collection'] = 0;
	    $_REQUEST['team_name_advanced_type'] = 'all';
		$this->_sugarField->render($this->_params, $this->_smarty);
		$this->setOutputCheck(array($this, "checkSearchValues"));
    }
}
