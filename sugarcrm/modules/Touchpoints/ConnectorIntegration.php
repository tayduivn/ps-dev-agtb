<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************

 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

class ConnectorIntegration {

var $bean;
var $module;
var $show_merge_panel = false;
var $results;

public function ConnectorIntegration() {

}

public function setModule($module) {
	$this->module = $module;
}

public function setBean($focus) {
	$this->bean = $focus;
}

public function process() {
    //Now search the connectors for matching results
 	require_once('include/connectors/ConnectorFactory.php');
 	require_once('include/connectors/filters/FilterFactory.php');
    require_once('include/connectors/utils/ConnectorUtils.php');
    
 	//If we don't have an account name should we even bother since so much logic depends on searching this field?
 	
 	$display_config = ConnectorUtils::getDisplayConfig();
print_r($this->bean);
 	if(!empty($display_config['Touchpoints'])) {
 		$this->results = array();
 		
 		
	 	$lookup_mapping = array();
	 	
	 	if(isset($display_config['Touchpoints']['ext_soap_hoovers']) && !empty($this->bean->company_name)) {
		 	//Hoovers search
		 	if(file_exists('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/lookup_mapping.php')) {
		 	   require('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/lookup_mapping.php');
		 	}
		 
		 	$args = array();
		 	//Set account name search
		 	$args['recname'] = $this->bean->company_name;  
		 	
		 	//Set state search if there is a matching code
		 	if(!empty($this->bean->primary_address_state)) {
		 	   $args['addrstateprov'] = strtoupper($this->bean->primary_address_state); 
		 	}
		 	
		 	//Set country code if there is a matching code
		 	if(!empty($this->bean->primary_address_country)) {
		 	   $args['addrcountry'] = strtoupper($this->bean->primary_address_country);
		 	}	
	
		 	
		 	$source = ConnectorFactory::getInstance('ext_soap_hoovers');
		 	$hoovers_list = $source->fillBeans($args, 'Touchpoints'); 		 	
		 	$this->results['ext_soap_hoovers'] = $hoovers_list;
	 	}
	 	
	 	if(isset($display_config['Touchpoints']['ext_rest_zoominfocompany']) && !empty($this->bean->company_name)) {
	        //Zoominfocompany search
			$args = array('companyname'=>$this->bean->company_name);
			$source = ConnectorFactory::getInstance('ext_rest_zoominfocompany');
			$zoominfocompany_list = $source->fillBeans($args, 'Touchpoints');
			$this->results['ext_rest_zoominfocompany'] = $zoominfocompany_list;
	 	}
        
	 	
	 	if(isset($display_config['Touchpoints']['ext_rest_zoominfoperson'])) {
	        //Zoominfoperson search
	        $args = array('lastname'=>$this->bean->last_name);
	        if(isset($this->bean->first_name)) {
	           $args['firstname'] = $this->bean->first_name;
	        }
	        if(isset($this->bean->email1)) {
	           $args['email'] = $this->bean->email1;
	        }     
	        $source = ConnectorFactory::getInstance('ext_rest_zoominfoperson');
	        $zoominfoperson_list = $source->fillBeans($args, 'Touchpoints');        
			$this->results['ext_rest_zoominfoperson'] = $zoominfoperson_list;
	 	}
	 	
	 	
	 	if(isset($display_config['Touchpoints']['ext_rest_crunchbase']) && !empty($this->bean->company_name)) {
			//Crunchbase search
			$args = array('name'=>$this->bean->company_name);
			$source = ConnectorFactory::getInstance('ext_rest_crunchbase');
			$crunchbase_list = $source->fillBeans($args, 'Touchpoints');
			$this->results['ext_rest_crunchbase'] = $crunchbase_list;
	 	}
	 	
		//Jigsaw search
	 	if(isset($display_config['Touchpoints']['ext_soap_jigsaw']) && !empty($this->bean->company_name)) {
			$args = array('name'=>$this->bean->company_name);
	
			$source = ConnectorFactory::getInstance('ext_soap_jigsaw');
	        $jigsaw_list = $source->fillBeans($args, 'Touchpoints');
			$this->results['ext_soap_jigsaw'] = $jigsaw_list;
	 	}
	 	
	 	//Collin - Comment this out to not bother calculating whether or not to show merge panel
		//$this->show_merge_panel = $this->showMergePanel(); 
 	}		
}

public function display() {
		require_once('custom/modules/Connectors/views/view.step2.php');
		require_once('include/Sugar_Smarty.php');
		$view = new ViewStep2();
		$smarty = new Sugar_Smarty();
		$smarty->assign('show_merge_panel', $this->show_merge_panel);
		$view->ss = $smarty;
		
		$_SESSION['merge_module'] = 'Touchpoints';
		$_REQUEST['action'] = 'Step2';
		$_REQUEST['record'] = $this->bean->id;
		$GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Connectors');
		//$view->setDisplayTitle(false);
		$view->setTemplateFile('modules/Touchpoints/tpls/ConnectorMerge.tpl');
		ob_start();
		//_pp($this->results);
		$view->display();
		$merge_contents = ob_get_contents();
		ob_clean();
		$GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Touchpoints');
	    require_once('custom/modules/Connectors/views/view.step1.php');
		require_once('include/Sugar_Smarty.php');
		$_REQUEST['merge_module'] = 'Touchpoints';
		$_REQUEST['record'] = $this->bean->id;
		$view = new ViewStep1();
		$view->setDisplayTitle(false);
		$view->setTemplateFile('modules/Touchpoints/tpls/ConnectorSearch.tpl');
		$view->ss = $smarty;

		$view->ss->assign('APP', $GLOBALS['app_strings']);
		$view->ss->assign('MOD', $GLOBALS['mod_strings']);
		$view->process();
		ob_start();
		$view->display();
		$search_contents = ob_get_contents();
		ob_clean();
		$smarty->assign('MERGE_DIV', $merge_contents);
		$smarty->assign('SEARCH_DIV', $search_contents);
		return $smarty->fetch('modules/Touchpoints/tpls/ConnectorPanel.tpl');		

}

private function showMergePanel() {
	if(!is_array($this->results) || empty($this->results)) {
	   return false;	
	}

	$totalCount = 0;
	foreach($this->results as $key=>$list) {
		//$GLOBALS['log']->fatal($key . '=' . count($list));
	    if(count($list) > 1) {
           return false;
	    }
	    $totalCount += count($list);
	}
	return $totalCount == 0 ? false : true;
}

	
}


?>
