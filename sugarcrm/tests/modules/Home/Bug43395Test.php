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

class Bug43395Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
	private static $quickSearch;
	private static $contact;

	static function setUpBeforeClass()
    {
    	global $app_strings, $app_list_strings;
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $app_strings = return_application_language($GLOBALS['current_language']);
        $app_list_strings = return_app_list_strings_language($GLOBALS['current_language']);
    	$user = new User();
    	$user->retrieve('1');
        $GLOBALS['current_user'] = $user;
        self::$contact = SugarTestContactUtilities::createContact();
        self::$contact->first_name = 'Bug43395';
        self::$contact->last_name = 'Test';
        self::$contact->salutation = 'Mr.';
        self::$contact->save();
    }

    public static function tearDownAfterClass()
    {
        unset($_REQUEST['data']);
        unset($_REQUEST['query']);
        SugarTestContactUtilities::removeAllCreatedContacts();
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
    }

    public function testFormatResults()
    {
    	$_REQUEST = array();
    	$_REQUEST['data'] = '{"form":"search_form","method":"query","modules":["Contacts"],"group":"or","field_list":["name","id"],"populate_list":["contact_c_basic","contact_id_c_basic"],"required_list":["parent_id"],"conditions":[{"name":"name","op":"like_custom","end":"%","value":""}],"order":"name","limit":"30","no_match_text":"No Match"}';
        $_REQUEST['query'] = self::$contact->first_name;
        require_once 'modules/Home/quicksearchQuery.php';

        $json = getJSONobj();
		$data = $json->decode(html_entity_decode($_REQUEST['data']));
		if(isset($_REQUEST['query']) && !empty($_REQUEST['query'])){
    		foreach($data['conditions'] as $k=>$v){
    			if(empty($data['conditions'][$k]['value'])){
       				$data['conditions'][$k]['value']=$_REQUEST['query'];
    			}
    		}
		}
 		self::$quickSearch = new quicksearchQuery();
		$result = self::$quickSearch->query($data);
		$resultBean = $json->decodeReal($result);
	    $this->assertEquals(self::$contact->first_name . ' ' . self::$contact->last_name, $resultBean['fields'][0]['name'],  'Assert that the quicksearch returns a contact name without salutation');
    }

    public function testPersonLocaleNameFormattting()
    {
        $GLOBALS['current_user']->setPreference('default_locale_name_format', 's f l');

    	self::$contact->createLocaleFormattedName = true;
    	self::$contact->_create_proper_name_field();
    	$this->assertContains('Mr.',self::$contact->name, 'Assert that _create_proper_name_field with createLocaleFormattedName set to true returns salutation');

    	self::$contact->createLocaleFormattedName = false;
    	self::$contact->_create_proper_name_field();
    	$this->assertNotContains('Mr.',self::$contact->name, 'Assert that _create_proper_name_field with createLocaleFormattedName set to false does not return salutation');
    }

}
?>