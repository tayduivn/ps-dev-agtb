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
 
class Bug44324Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
	var $contact;

	public function setUp()
	{
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_list_strings'] = return_app_list_strings_language('en_us');
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $this->contact = SugarTestContactUtilities::createContact();	
        $this->contact->salutation = 'Ms.';
        $this->contact->first_name = 'Lady';
        $this->contact->last_name = 'Gaga';	
        //Save contact with salutation
        $this->contact->save();
	}
	
	public function tearDown()
	{
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        SugarTestContactUtilities::removeAllCreatedContacts();		
	}
	
    public function testSearchNamePopulatedCorrectly()
    {
    	require_once('include/Popups/PopupSmarty.php');
    	$popupSmarty = new PopupSmarty($this->contact, $this->contact->module_dir);
    	$this->contact->_create_proper_name_field();
    	$search_data = array();
    	$search_data[] = array('ID'=>$this->contact->id, 'NAME'=>$this->contact->name, 'FIRST_NAME'=>$this->contact->first_name, 'LAST_NAME'=>$this->contact->last_name);
    	
    	$data = array('data'=>$search_data);
    	$data['pageData']['offsets']['lastOffsetOnPage'] = 0;
    	$data['pageData']['offsets']['current'] = 0;
    	$popupSmarty->data = $data;
    	$popupSmarty->fieldDefs = array();
    	$popupSmarty->view= 'popup';
    	$popupSmarty->tpl = 'include/Popups/tpls/PopupGeneric.tpl';
    	$this->expectOutputRegex('/\"NAME\":\"Lady Gaga\"/', 'Assert that NAME value was set to "Lady Gaga"');
    	echo $popupSmarty->display();
    }

}

?>
