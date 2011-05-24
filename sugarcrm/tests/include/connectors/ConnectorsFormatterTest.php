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
require_once('include/connectors/formatters/FormatterFactory.php');
require_once('include/MVC/Controller/SugarController.php');
require_once('include/connectors/ConnectorsTestCase.php');

class ConnectorsFormatterTest extends Sugar_Connectors_TestCase
{
	var $parentFieldArray, $vardef, $displayParams, $tabindex, $ss;

	function setUp() {
    	//Store original files
	    if(!file_exists(CONNECTOR_DISPLAY_CONFIG_FILE))
    	{
$the_string = <<<EOQ
<?php
\$modules_sources = array (
  'Accounts' =>
  array (
    'ext_rest_linkedin' => 'ext_rest_linkedin',
    'ext_soap_hoovers' => 'ext_soap_hoovers',
  ),
  'Opportunities' =>
  array (
    'ext_rest_linkedin' => 'ext_rest_linkedin',
    'ext_soap_hoovers' => 'ext_soap_hoovers',
  ),
  'Contacts' =>
  array (
    'ext_soap_hoovers' => 'ext_soap_hoovers',
  ),
);
?>

EOQ;

$fp = sugar_fopen('custom/modules/Connectors/metadata/display_config.php', "w" );
fwrite( $fp, $the_string );
fclose( $fp );
    	}
        parent::setUp();

   		if(file_exists('custom/modules/Connectors/connectors/sources/ext/rest/twitter/twitter.php')) {
   		   copy_recursive('custom/modules/Connectors/connectors/sources/ext/rest/twitter', 'custom/modules/Connectors/backup/connectors/sources/ext/rest/twitter_backup');
    	   ConnectorsTestUtility::rmdirr('custom/modules/Connectors/backup/sources/ext/rest/twitter');
   		}

   		if(file_exists('custom/modules/Connectors/connectors/sources/ext/rest/linkedin/linkedin.php')) {
   		   copy_recursive('custom/modules/Connectors/connectors/sources/ext/rest/linkedin', 'custom/modules/Connectors/backup/connectors/sources/ext/rest/linkedin_backup');
    	   ConnectorsTestUtility::rmdirr('custom/modules/Connectors/backup/sources/ext/rest/linkedin');
   		}

   		if(file_exists('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/hoovers.php')) {
    	   copy_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers', 'custom/modules/Connectors/backup/connectors/sources/ext/soap/hoovers_backup');
   		   ConnectorsTestUtility::rmdirr('custom/modules/Connectors/backup/sources/ext/soap/hoovers');
   		}

   		//Setup the neccessary Smarty configurations
    	$this->parentFieldArray = 'fields';
    	require_once('include/SugarObjects/VardefManager.php');
        VardefManager::loadVardef('Accounts', 'Account', true);
        require_once('cache/modules/Accounts/Accountvardefs.php');
        $this->vardef = $GLOBALS['dictionary']['Account']['fields']['name'];
    	$this->displayParams = array('sources'=>array('ext_rest_linkedin','ext_rest_twitter'));
    	$this->tabindex = 0;
    	require_once('include/Sugar_Smarty.php');
    	$this->ss = new Sugar_Smarty();
    	$this->ss->assign('parentFieldArray', $this->parentFieldArray);
    	$this->ss->assign('vardef', $this->vardef);
    	$this->ss->assign('displayParams', $this->displayParams);
        $this->ss->left_delimiter = '{{';
        $this->ss->right_delimiter = '}}';

    	//Setup the mapping to guarantee that we have hover fields for the Accounts module
    	$_REQUEST['module'] = 'Connectors';
    	$_REQUEST['from_unit_test'] = true;
    	$_REQUEST['modify'] = true;
    	$_REQUEST['action'] = 'SaveModifyMapping';
    	$_REQUEST['mapping_values'] = 'ext_soap_hoovers:Accounts:addrcountry=billing_address_country,ext_soap_hoovers:Accounts:id=id,ext_soap_hoovers:Accounts:addrcity=billing_address_city,ext_soap_hoovers:Accounts:addrzip=billing_address_postalcode,ext_soap_hoovers:Accounts:recname=name,ext_soap_hoovers:Accounts:addrstateprov=billing_address_state';
    	$_REQUEST['mapping_sources'] = 'ext_soap_hoovers,ext_rest_linkedin,ext_rest_twitter';

    	$controller = new ConnectorsController();
    	$controller->action_SaveModifyMapping();

    	FormatterFactory::$formatter_map = array();
    	ConnectorFactory::$source_map = array();
    }

    function tearDown() {
        parent::tearDown();
        if(file_exists('custom/modules/Connectors/connectors/sources/ext/rest/twitter_backup/twitter.php')) {
    	   copy_recursive('custom/modules/Connectors/backup/connectors/sources/ext/rest/twitter_backup', 'custom/modules/Connectors/connectors/sources/ext/rest/twitter');
    	   ConnectorsTestUtility::rmdirr('custom/modules/Connectors/backup/sources/ext/rest/twitter_backup');
        }

        if(file_exists('custom/modules/Connectors/connectors/sources/ext/rest/linkedin_backup/linkedin.php')) {
    	   copy_recursive('custom/modules/Connectors/backup/connectors/sources/ext/rest/linkedin_backup', 'custom/modules/Connectors/connectors/sources/ext/rest/linkedin');
    	   ConnectorsTestUtility::rmdirr('custom/modules/Connectors/backup/sources/ext/rest/linkedin_backup');
        }

        if(file_exists('custom/modules/Connectors/connectors/sources/ext/soap/hoovers_backup/hoovers.php')) {
    	   copy_recursive('custom/modules/Connectors/backup/connectors/sources/ext/soap/hoovers_backup', 'custom/modules/Connectors/connectors/sources/ext/rest/hoovers');
    	   ConnectorsTestUtility::rmdirr('custom/modules/Connectors/backup/connectors/sources/ext/soap/hoovers_backup');
        }
    }

    function test_hover_link_for_accounts() {
    	$enabled_sources = ConnectorUtils::getModuleConnectors('Accounts');
    	$hover_sources = array();
    	$displayParams = array();
    	$displayParams['module'] = 'Accounts';
    	$displayParams['enableConnectors'] = true;

    	foreach($enabled_sources as $id=>$mapping) {
    		$source = SourceFactory::getSource($id);
    		if($source->isEnabledInHover()) {
    		   $parts = preg_split('/_/', $id);
    		   $hover_sources[$parts[count($parts) - 1]] = $id;
    		   $displayParams['connectors'][] = $id;
    		}
    	}

    	if(!empty($hover_sources)) {
    	   $output = ConnectorUtils::getConnectorButtonScript($displayParams, $this->ss);
    	   preg_match_all('/<div[^\>]*?>/', $output, $matches);
    	   $this->assertTrue(isset($matches[0]));
    	}
    }

    /*
    function test_hover_link_for_linkedin_only() {
    	require(CONNECTOR_DISPLAY_CONFIG_FILE);
    	$modules_sources['Accounts'] = array('ext_rest_linkedin'=>'ext_rest_linkedin');
    	$displayParams = array();
    	$displayParams['module'] = 'Accounts';
    	$displayParams['enableConnectors'] = true;
    	$displayParams['connectors'][] = 'ext_rest_linkedin';

    	if(write_array_to_file('modules_sources', $modules_sources, CONNECTOR_DISPLAY_CONFIG_FILE)) {
           $output = ConnectorUtils::getConnectorButtonScript($displayParams, $this->ss);
           preg_match_all('/<div.*?id=[\'\"](.*?)[\'\"][^\>]*?>/', $output, $matches);
    	   $this->assertTrue(!empty($matches[0][0]) && preg_match('/linkedin/', $matches[0][0]));
        }
    }
    */
    function test_remove_hover_links_in_viewdefs() {

    	$module = 'Accounts';

    	if(file_exists("custom/modules/{$module}/metadata/detailviewdefs.php")) {
    	  require("custom/modules/{$module}/metadata/detailviewdefs.php");
    	} else if(file_exists("modules/{$module}/metadata/detailviewdefs.php")) {
    	  require("modules/{$module}/metadata/detailviewdefs.php");
    	}

    	$this->assertTrue(!empty($viewdefs));

    	//Remove hover fields
    	ConnectorUtils::removeHoverField($viewdefs, $module);
    	$foundHover = false;
		foreach($viewdefs[$module]['DetailView']['panels'] as $panel_id=>$panel) {
	  	   foreach($panel as $row_id=>$row) {
	  	  	  foreach($row as $field_id=>$field) {
		  	  	  if(is_array($field) && !empty($field['displayParams']['enableConnectors'])) {
                     $foundHover = true;
		  	  	  }
	  	  	  } //foreach
	  	   } //foreach
		} //foreach

		//There should have been no hover fields found
		$this->assertTrue(!$foundHover);
    }

    function test_modify_display_changes() {

    	$module = 'Accounts';

    	//Now call the code that will add the mapping fields
    	$_REQUEST['display_values'] = "ext_soap_hoovers:Accounts,ext_rest_linkedin:Accounts";
    	$_REQUEST['display_sources'] = "ext_soap_hoovers,ext_rest_linkedin,ext_rest_twitter";
    	$_REQUEST['action'] = 'SaveModifyDisplay';
    	$_REQUEST['module'] = 'Connectors';
    	$_REQUEST['from_unit_test'] = true;

       	$controller = new ConnectorsController();
    	$controller->action_SaveModifyDisplay();

		if(file_exists("custom/modules/{$module}/metadata/detailviewdefs.php")) {
    	  require("custom/modules/{$module}/metadata/detailviewdefs.php");
		  foreach($viewdefs[$module]['DetailView']['panels'] as $panel_id=>$panel) {
		  	   foreach($panel as $row_id=>$row) {
		  	  	  foreach($row as $field_id=>$field) {
		  	  	  	  $name = is_array($field) ? $field['name'] : $field;
		  	  	  	  switch(strtolower($name)) {
		  	  	  	  	case "account_name":
							 $this->assertTrue(!empty($field['displayParams']['enableConnectors']));
							 $this->assertTrue(in_array('ext_rest_linkedin', $field['displayParams']['connectors']));
							 $this->assertTrue(in_array('ext_rest_twitter', $field['displayParams']['connectors']));
		  	  	  	  	break;
		  	  	  	  }
		  	  	  } //foreach
		  	   } //foreach
		  } //foreach

		  $this->test_remove_hover_links_in_viewdefs(); //Call remove again b/c we know for sure there are now fields
    	} else {
    	  $this->assertTrue(false); //Failed because we couldn't create the custom file
    	}
    }
}
?>