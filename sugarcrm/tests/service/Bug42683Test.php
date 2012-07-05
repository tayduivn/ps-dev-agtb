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
 * @ticket 42683
 */
class Bug42683Test extends SOAPTestCase
{
    public function setUp()
    {
    	$this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v2/soap.php';
		parent::setUp();
    }

    public function tearDown()
    {
        SugarTestLeadUtilities::removeAllCreatedLeads();
        parent::tearDown();
    }

    public function testBadQuery()
    {
        $lead = SugarTestLeadUtilities::createLead();

        $this->_login();
        $result = $this->_soapClient->call(
            'get_entry_list',
            array(
                'session' => $this->_sessionId,
                "module_name" => 'Leads',
                "query" => "leads.id = '{$lead->id}'",
                'order_by' => '',
                'offset' => 0,
                'select_fields' => array(
                    'name'
                ),
                'link_name_to_fields_array' => array(
                    array(
                        'name' => 'email_addresses',
                        'value' => array(
                            'id',
                            'email_address',
                            'opt_out',
                            'primary_address'
                        )
                    )
                ),
                'max_results' => 1,
                'deleted' => 0
            )
        );

        $this->assertEquals('primary_address', $result['relationship_list'][0][0]['records'][0][3]['name']);

    }
}
