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
 
require_once('include/SugarFields/Fields/Phone/SugarFieldPhone.php');

class Bug43942Test extends Sugar_PHPUnit_Framework_TestCase
{
	public function test_phone_without_formatting()
	{
	    $sugarField = new SugarFieldPhone('Phone');
	    $bean = new Contact();
	    
	    $params = array('phone_work'=>'+1(800)999-2222');
	    $field = 'phone_work';
	    $properties = array('name' => 'phone_work', 
	                        'vname' => 'LBL_OFFICE_PHONE', 
	                        'type' => 'phone',
						    'dbType' => 'varchar',
						    'len' => 100,
						    'audited'=> 1,
						    'unified_search'=> 1,
						    'comment' => 'Work phone number of the contact',
						    'merge_filter' => 'enabled',
						    'calculated' => '',
	    			  );
	    
	    $sugarField->save($bean, $params, $field, $properties);
	    $this->assertEquals($bean->phone_work, '+1(800)999-2222', "Assert that '+1(800)999-2222' is not formatted");
	    
	    $params = array('phone_work'=>'');
	    $sugarField->save($bean, $params, $field, $properties);
	    $this->assertEquals($bean->phone_work, '', "Assert that saving empty value works as expected");
	}	
	
}

?>