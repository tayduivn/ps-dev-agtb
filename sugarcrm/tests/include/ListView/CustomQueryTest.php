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

require_once 'include/ListView/ListView.php';

class CustomQueryTest extends Sugar_PHPUnit_Framework_TestCase
{
    static public function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }

    static public function tearDownAfterClass()
    {
    	SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    	unset($GLOBALS['current_user']);
    	unset($GLOBALS['app_strings']);
    }

    public function setUp()
    {
        $contact = new Contact();
        $this->defs = $contact->field_defs;
    }

	public static function query_func($ret_array, $fielddef)
	{
	    $ret_array['select'] .= ", 2+2 four /* for {$fielddef['name']} */";
	    return $ret_array;
	}

    public function testCustomQuery()
    {
        $bean = new SugarBean();
        $bean->field_defs = $this->defs;
        $bean->field_defs['testquery'] = array(
          "name" => "testquery",
          "source" => "non-db",
          'type' => "custom_query",
          "query_function" => array(
                        'function_name'=>'query_func',
                        'function_class'=>get_class($this),
          ),
          'reportable'=>false,
          'duplicate_merge'=>'disabled',
      );
          $result = $bean->create_new_list_query('', '');
          $this->assertContains("2+2 four /* for testquery */", $result);
    }

    public function testCustomQueryForced()
    {
        $bean = new SugarBean();
        $bean->field_defs = $this->defs;
        $bean->field_defs['testquery'] = array(
          "name" => "testquery",
          "source" => "non-db",
          'type' => "custom_query",
          "query_function" => array(
                        'function_name'=>'query_func',
                        'function_class'=>get_class($this),
          ),
          'reportable'=>false,
          'duplicate_merge'=>'disabled',
          );
        $result = $bean->create_new_list_query('', '', array('id', 'name'));
        $this->assertNotContains("2+2 four /* for testquery */", $result);

        $bean->field_defs['testquery']['force_exists'] = true;
        $result = $bean->create_new_list_query('', '', array('id', 'name'));
        $this->assertContains("2+2 four /* for testquery */", $result);
    }
}