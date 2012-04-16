<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('include/nusoap/nusoap.php');
require_once 'tests/service/SOAPTestCase.php';

/**
 * Bug 51723
 *  SOAP::get_entries() call fails to export portal_name field
 * @ticket 51723
 * @author arymarchik@sugarcrm.com
 */
class Bug51723Test extends SOAPTestCase
{
    private $_contact;
    private $_opt = null;

    public function setUp()
    {
        $this->markTestIncomplete("Test breaking on CI, working with dev to fix");
        $administration = new Administration();
        $administration->retrieveSettings();
        if(isset($administration->settings['portal_on']))
        {
            $this->_opt = $administration->settings['portal_on'];
        }
        $administration->saveSetting('portal', 'on',  1);

        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php?wsdl';
        parent::setUp();
        $this->_login();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

        $this->_contact = new Contact();
        $this->_contact->last_name = "Contact #bug51723";
        $this->_contact->id = create_guid();
        $this->_contact->new_with_id = true;
        $this->_contact->team_id = 1;
        $this->_contact->save();
    }

    public function tearDown()
    {
        //$this->_contact->mark_deleted($this->_contact->id);
        parent::tearDown();

        $administration = new Administration();
        $administration->retrieveSettings();
        if($this->_opt === null)
        {
            if(isset($administration->settings['portal_on']))
            {
                $administration->saveSetting('portal', 'on', 0);
            }
        }
        else
        {
            $administration->saveSetting('portal', 'on',  $this->_opt);
        }
    }

    /**
     * Testing SOAP method get_entries for existing "portal_name" field
     * @group 51723
     */
    public function testPortalNameInGetEntries()
    {
        $fields = array('portal_name', 'first_name', 'last_name');
        $result = $this->_soapClient->call(
            'get_entries',
            array('session' => $this->_sessionId,
                'module_name' => 'Contacts',
                'ids' => array($this->_contact->id),
                'select_fields' => $fields
            )
        );
        // replacement of $this->assertCount()
        if(count($result['entry_list']) != 1)
        {
            $this->fail('Can\'t get entry list');
        }

        foreach ($result['entry_list'][0]['name_value_list'] as $key => &$value)
        {
            if(($index = array_search($value['name'], $fields, true)) !== false)
            {
                unset($fields[$index]);
            }
            else
            {
                $this->fail('Wrong field in selected fields:' . $value['name']);
            }
        }
        if(count($fields) != 0)
        {
            $this->fail('Can\'t get expected values:' . implode(',', $fields));
        }
    }

}