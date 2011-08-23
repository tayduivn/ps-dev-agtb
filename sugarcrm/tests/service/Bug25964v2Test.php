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

require_once('include/nusoap/nusoap.php');
require_once 'tests/service/SOAPTestCase.php';


class Bug25964v2Test extends SOAPTestCase
{
    var $_resultId;
    var $c = null;
    var $c2 = null;

	public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v2_1/soap.php';
        parent::setUp();

		$unid = uniqid();
		$time = date('Y-m-d H:i:s');

        $contact = new Contact();
		$contact->id = 'c_'.$unid;
        $contact->first_name = 'testfirst';
        $contact->last_name = 'testlast';
        $contact->email1 = 'one@example.com';
        $contact->email2 = 'one_other@example.com';
        $contact->new_with_id = true;
        $contact->disable_custom_fields = true;
        $contact->save();
        $this->c = $contact;
        $this->_login();

    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->c->id}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->_resultId}'");
        unset($this->c);
        parent::tearDown();
    }

    public function testFindSameContact()
    {

        $contacts_list=array( 'session'=>$this->_sessionId, 'module_name' => 'Contacts',
				   'name_value_lists' => array(
                                        array(array('name'=>'assigned_user_id' , 'value'=>$GLOBALS['current_user']->id),array('name'=>'first_name' , 'value'=>'testfirst'),array('name'=>'last_name' , 'value'=>'testlast'),array('name'=>'email1' , 'value'=>'one_other@example.com'))
                                        ));

        $result = $this->_soapClient->call('set_entries',$contacts_list);
        $this->_resultId = $result['ids'][0];
        $this->assertEquals($this->c->id, $result['ids'][0], "did not match contacts");
    }

    public function testDoNotFindSameContact()
    {

        $contacts_list=array( 'session'=>$this->_sessionId, 'module_name' => 'Contacts',
				   'name_value_lists' => array(
                                        array(array('name'=>'assigned_user_id' , 'value'=>$GLOBALS['current_user']->id),array('name'=>'first_name' , 'value'=>'testfirst'),array('name'=>'last_name' , 'value'=>'testlast'),array('name'=>'email1' , 'value'=>'mytest1@example.com'))
                                        ));

        $result = $this->_soapClient->call('set_entries',$contacts_list);
        $this->_resultId = $result['ids'][0];
        $this->assertNotEquals($this->c->id, $result['ids'][0], "did not match contacts");
    }

}