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
 
require_once 'tests/service/SOAPTestCase.php';

/**
 * @group bug43696
 */
class Bug31003Test extends SOAPTestCase
{
	private $prospect;
    private $contact;

	public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
        parent::setUp();
        $this->contact = SugarTestContactUtilities::createContact();
        $this->prospect = new Prospect();
        $this->prospect->email1 = $this->contact->email1;
        $this->prospect->save();
    }

    public function testContactByEmail()
    {
    	$result = $this->_soapClient->call('contact_by_email', array('user_name' => $this->_user->user_name, 'password' => $this->_user->user_hash, 'email_address' => $this->contact->email1));
        $this->assertTrue(!empty($result) && count($result) > 0, 'Incorrect number of results returned. HTTP Response: '.$this->_soapClient->response);
    	$this->assertEquals($result[0]['name1'], $this->contact->first_name, 'Incorrect result found');
    }

    public function tearDown()
    {
        parent::tearDown();
        $GLOBALS['db']->query(sprintf('DELETE FROM prospects WHERE id = %d', $this->contact->id));
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

}