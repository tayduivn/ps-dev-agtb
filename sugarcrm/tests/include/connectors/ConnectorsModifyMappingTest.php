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
require_once('include/connectors/ConnectorsTestCase.php');
require_once('include/MVC/Controller/SugarController.php');

class ConnectorsModifyMappingTest extends Sugar_Connectors_TestCase
{
    function test_modify_mapping_hoovers() {
    	$controller = new ConnectorsController();
    	//Enable and Hoovers for Leads
    	$_REQUEST['display_values'] = "ext_soap_hoovers:Leads";
    	$_REQUEST['display_sources'] =  'ext_soap_hoovers,ext_rest_linkedin';
    	$_REQUEST['action'] = 'SaveModifyDisplay';
    	$_REQUEST['module'] = 'Connectors';
    	$_REQUEST['from_unit_test'] = true;
    	$controller->action_SaveModifyDisplay();

        $_REQUEST['mapping_values'] = 'ext_soap_hoovers:Leads:addrstreet=primary_address_street,ext_soap_hoovers:Leads:addrcountry=primary_address_country,ext_soap_hoovers:Leads:finsales=opportunity_amount,ext_soap_hoovers:Leads:id=id,ext_soap_hoovers:Leads:addrzip=primary_address_postalcode,ext_soap_hoovers:Leads:recname=account_name,ext_soap_hoovers:Leads:addrstateprov=primary_address_state';
        $_REQUEST['mapping_sources'] = 'ext_soap_hoovers,ext_rest_linkedin';
    	$_REQUEST['action'] = 'SaveModifyMapping';
        $controller->action_SaveModifyMapping();

    	$viewdefs_sources = array('ext_soap_hoovers'=>true);
    	$mergeview_defs = ConnectorUtils::getViewDefs($viewdefs_sources);
    	$this->assertTrue(!empty($mergeview_defs['Connector']['MergeView']['Leads']));
    	$leads_mapped_fields_results = array_values($mergeview_defs['Connector']['MergeView']['Leads']);
    	$leads_mapped_fields_expected = array('primary_address_city', 'primary_address_country', 'account_name', 'primary_address_state', 'id', 'primary_address_street', 'opportunity_amount', 'primary_address_postalcode');
    	$differences = array_diff($leads_mapped_fields_results, $leads_mapped_fields_expected);
    	$this->assertTrue(empty($differences));
    }

    function test_modify_mapping_hoovers_with_disabled_linkedin() {
    	$controller = new ConnectorsController();
    	//Enable Hoovers for Leads
    	$_REQUEST['display_values'] = "ext_soap_hoovers:Leads";
    	$_REQUEST['display_sources'] =  'ext_soap_hoovers,ext_rest_linkedin';
    	$_REQUEST['action'] = 'SaveModifyDisplay';
    	$_REQUEST['module'] = 'Connectors';
    	$_REQUEST['from_unit_test'] = true;
    	$controller->action_SaveModifyDisplay();

    	//Force it to pass in mapping for linked in anyways to prove that disabled checks are enforced
        $_REQUEST['mapping_values'] = 'ext_soap_hoovers:Leads:addrstreet=primary_address_street,ext_soap_hoovers:Leads:addrcountry=primary_address_country,ext_soap_hoovers:Leads:finsales=opportunity_amount,ext_soap_hoovers:Leads:id=id,ext_soap_hoovers:Leads:addrzip=primary_address_postalcode,ext_soap_hoovers:Leads:recname=account_name,ext_soap_hoovers:Leads:addrstateprov=primary_address_state,ext_rest_linkedin:Leads:name=refered_by';
        $_REQUEST['mapping_sources'] = 'ext_soap_hoovers,ext_rest_linkedin';
    	$_REQUEST['action'] = 'SaveModifyMapping';
        $controller->action_SaveModifyMapping();

    	$mergeview_defs = ConnectorUtils::getMergeViewDefs(true);
    	$this->assertTrue(!empty($mergeview_defs['Connector']['MergeView']));
    	$this->assertTrue(count($mergeview_defs['Connector']['MergeView']) == 1);
    	$leads_mapped_fields_results = array_values($mergeview_defs['Connector']['MergeView']['Leads']);
    	$leads_mapped_fields_expected = array('primary_address_city', 'primary_address_country', 'account_name', 'primary_address_state', 'id', 'primary_address_street', 'opportunity_amount', 'primary_address_postalcode');
    	$differences = array_diff($leads_mapped_fields_results, $leads_mapped_fields_expected);
    	$this->assertTrue(empty($differences));
    }
}
?>