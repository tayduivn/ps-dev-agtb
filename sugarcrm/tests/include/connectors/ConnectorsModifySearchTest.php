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

class ConnectorsModifySearchTest extends Sugar_Connectors_TestCase {
    /*
     * test_hoovers_search
     * This test defines the mapping for connectors ( hoovers).
     *
     */
    function test_hoovers_search() {
    	require_once('modules/Connectors/controller.php');
    	require_once('include/MVC/Controller/SugarController.php');

    	//First enable all the connectors
    	$_REQUEST['reset_to_default'] = '';
    	$_REQUEST['action'] = 'SaveModifyDisplay';
    	$_REQUEST['module'] = 'Connectors';
    	$_REQUEST['from_unit_test'] = true;
    	$_REQUEST['source1'] = 'ext_soap_hoovers';
        $_REQUEST['search_values'] = 'ext_soap_hoovers:Leads:recname,ext_soap_hoovers:Leads:addrcity,ext_soap_hoovers:Accounts:recname,ext_soap_hoovers:Accounts:addrcity,ext_soap_hoovers:Contacts:recname,ext_soap_hoovers:Contacts:addrcity';
        $_REQUEST['search_sources'] = 'ext_soap_hoovers';
    	$_REQUEST['action'] = 'SaveModifySearch';

    	$controller = new ConnectorsController();
    	$controller->action_SaveModifySearch();
	    $searchdefs = ConnectorUtils::getSearchDefs();

	    $this->assertTrue(count($searchdefs['ext_soap_hoovers']) == 3);

	    foreach($searchdefs['ext_soap_hoovers'] as $module=>$mapping) {
	    	$this->assertTrue($mapping[0] == 'recname' && $mapping[1] == 'addrcity');
	    }

    }



}
?>