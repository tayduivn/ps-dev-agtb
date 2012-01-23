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


/**
 * Bug49898Test.php
 *
 * This test is for bug 49898.  Basically the plugin code is still dependent on the legacy versions of the SOAP api (soap.php).  As a result,
 * the search_by_module call is expecting a username and password combination.  However, the plugin code cannot supply a username and password, but
 * only has the session id information.  Therefore, as an alternative, it was proposed to have a workaround where if a username is empty, then the password
 * is assumed to be the session id.  This test replicates that check by searching on two modules (Accounts and Contacts) based on an email address
 * derived from the Contact.
 *
 * @author Collin Lee
 *
 */

require_once 'tests/service/SOAPTestCase.php';

class Bug49898Test extends SOAPTestCase
{
    var $contact;
    var $account;
    var $lead;

    /**
     * setUp
     * Override the setup from SoapTestCase to also create the seed search data for Accounts and Contacts.
     */
    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
   		parent::setUp();
        $this->_login(); // Logging in just before the SOAP call as this will also commit any pending DB changes
        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->contacts_users_id = $GLOBALS['current_user']->id;
        $this->contact->save();
        $this->account = SugarTestAccountUtilities::createAccount();
        $this->account->email1 = $this->contact->email1;
        $this->account->save();
        $this->lead = SugarTestLeadUtilities::createLead();
        $this->lead->email1 = $this->contact->email1;
        $this->lead->save();
        $GLOBALS['db']->commit(); // Making sure these changes are committed to the database
    }

    public function testSearchByModuleWithSessionIdHack()
    {
        //Assert that the plugin fix to use a blank user_name and session id as password works
        $modules = array('Contacts', 'Accounts', 'Leads');
        $result = $this->_soapClient->call('search_by_module', array('user_name' => '', 'password' => $this->_sessionId, 'search_string' => $this->contact->email1, 'modules' => $modules, 'offset' => 0, 'max_results' => 10));
        $this->assertTrue(!empty($result) && count($result['entry_list']) == 3, 'Incorrect number of results returned. HTTP Response: '.$this->_soapClient->response);

        //Assert that the traditional method of using user_name and password also works
        $result = $this->_soapClient->call('search_by_module', array('user_name' => $GLOBALS['current_user']->user_name, 'password' => $GLOBALS['current_user']->user_hash, 'search_string' => $this->contact->email1, 'modules' => $modules, 'offset' => 0, 'max_results' => 10));
        $this->assertTrue(!empty($result) && count($result['entry_list']) == 3, 'Incorrect number of results returned. HTTP Response: '.$this->_soapClient->response);
    }


}

