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
require_once('modules/MySettings/StoreQuery.php');

class Bug44908Test extends Sugar_PHPUnit_Framework_TestCase 
{
	
    public function testAdvancedSearchWithCommaSeparatedBugNumbers()
    {
    	$_REQUEST = array();
    	$storeQuery = new StoreQuery();
	    $query['action'] = 'index';
	    $query['module'] = 'Bugs';
	    $query['orderBy'] = 'BUG_NUMBER';
	    $query['sortOrder'] = 'ASC';
	    $query['query'] = 'true';
	    $query['searchFormTab'] = 'advanced_search';
	    $query['showSSDIV'] = 'no';
	    $query['bug_number_advanced'] = '1,2,3,4,5';
	    $query['name_advanced'] = '';
	    $query['status_advanced'][] = 'Assigned';
	    $query['favorites_only_advanced'] = '0';
	    $query['search_module'] = 'Bug';
	    $query['saved_search_action'] = 'save';
	    $query['displayColumns'] = 'BUG_NUMBER|NAME|STATUS|TYPE|PRIORITY|FIXED_IN_RELEASE_NAME|ASSIGNED_USER_NAME';
    	$storeQuery->query = $query;
    	$storeQuery->populateRequest();
    	$this->assertEquals('1,2,3,4,5', $_REQUEST['bug_number_advanced'], "Assert that bug search string 1,2,3,4,5 was not formatted");
    }
    
}