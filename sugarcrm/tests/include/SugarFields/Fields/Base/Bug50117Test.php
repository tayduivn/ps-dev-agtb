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
 
require_once('include/SugarFields/SugarFieldHandler.php');

class Bug50117Test extends Sugar_PHPUnit_Framework_TestCase
{
   		
	private $_listViewSmartyOutput1;
	private $_listViewSmartyOutput2;
	
	public function setUp()
    {
        $enumField = SugarFieldHandler::getSugarField('enum');
   		$parentFieldArray = array(
		    					'ACCEPT_STATUS_NAME' => 'Accepted',		
							);
		$vardef = array(
					    'name' => 'accept_status_name',
					    'type' => 'enum',
					    'source' => 'non-db',
					    'vname' => 'LBL_LIST_ACCEPT_STATUS',
					    'options' => 'dom_meeting_accept_status',
					    'massupdate' => false,
					    'studio' => Array
					        (
					            'listview' => false,
					            'searchview' => false,
					        )
					);
		$displayParams = array(
							'vname' => 'LBL_LIST_ACCEPT_STATUS',
						    'width' => '11%',
						    'sortable' => false,
						    'linked_field' => 'users',
						    'linked_field_set' => 'users',
						    'name' => 'accept_status_name',
							'module' => 'Users',
						);
		$col = 1;
		
		$this->_listViewSmartyOutput1 = trim($enumField->getListViewSmarty($parentFieldArray, $vardef, $displayParams, $col));
		
		$vardef['name'] = 'just_another_name';
		$parentFieldArray['JUST_ANOTHER_NAME'] = 'None';
		
		$this->_listViewSmartyOutput2 = trim($enumField->getListViewSmarty($parentFieldArray, $vardef, $displayParams, $col));
	}
    
     /**
     * @bug 50117
     */
	public function testListViewSmarty()
	{	
		$this->assertEquals("Accepted", $this->_listViewSmartyOutput1);
		$this->assertEquals("None", $this->_listViewSmartyOutput2);
    }
}
