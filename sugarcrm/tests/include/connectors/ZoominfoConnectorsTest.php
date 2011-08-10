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

require_once('include/connectors/ConnectorFactory.php');
require_once('include/connectors/sources/SourceFactory.php');
require_once('include/connectors/utils/ConnectorUtils.php');
require_once('modules/Connectors/controller.php');
require_once('include/connectors/ConnectorsTestCase.php');
require_once('tests/include/connectors/ZoominfoHelper.php');

class ZoominfoConnectorsTest extends Sugar_Connectors_TestCase
{
	protected $qual_module;
    protected $mock;

    public function setUp()
    {
        parent::setUp();
        $this->mock = new ZoominfoTestHelper();

    	ConnectorFactory::$source_map = array();

    	if(file_exists('custom/modules/Connectors/connectors/sources/ext/rest/zoominfocompany/mapping.php')) {
           unlink('custom/modules/Connectors/connectors/sources/ext/rest/zoominfocompany/mapping.php');
        }


    	ConnectorFactory::$source_map = array();

    	$_REQUEST['module'] = 'Connectors';
    	$_REQUEST['from_unit_test'] = true;
    	$_REQUEST['modify'] = true;
    	$_REQUEST['action'] = 'SaveModifyDisplay';
    	$_REQUEST['display_values'] = 'ext_rest_zoominfoperson:Leads,ext_rest_zoominfocompany:Leads';
    	$_REQUEST['display_sources'] = 'ext_soap_hoovers,ext_rest_linkedin,ext_rest_zoominfocompany,ext_rest_zoominfoperson';

    	$controller = new ConnectorsController();
    	$controller->action_SaveModifyDisplay();

    	$_REQUEST['action'] = 'SaveModifyMapping';
    	$_REQUEST['mapping_values'] = 'ext_rest_zoominfoperson:Leads:firstname=first_name,ext_rest_zoominfoperson:Leads:lastname=last_name,ext_rest_zoominfoperson:Leads:jobtitle=title,ext_rest_zoominfoperson:Leads:companyname=account_name,ext_rest_zoominfocompany:Leads:companyname=account_name,ext_rest_zoominfocompany:Leads:companydescription=description';
    	$_REQUEST['mapping_sources'] = 'ext_rest_zoominfoperson,ext_rest_zoominfocompany';
    	$controller->action_SaveModifyMapping();

    	$this->qual_module = 'Leads';
    	$this->company_source = ConnectorFactory::getInstance('ext_rest_zoominfocompany')->getSource();
    	$this->company_props = $this->company_source->getProperties();
    	$this->person_source = ConnectorFactory::getInstance('ext_rest_zoominfoperson')->getSource();
    	$this->person_props = $this->person_source->getProperties();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->company_source->setProperties($this->company_props);
        $this->person_source->setProperties($this->person_props);
        $this->mock = null;
    }

    public function testZoominfoCompanyFillBeans()
    {
    	require_once('modules/Leads/Lead.php');
    	$source_instance = ConnectorFactory::getInstance('ext_rest_zoominfocompany');
    	$source_instance->getSource()->loadMapping();
    	$props = $source_instance->getSource()->getProperties();
    	$props['company_search_url'] = $this->mock->url('company_search_query');
    	$source_instance->getSource()->setProperties($props);
    	$leads = array();
    	$leads = $source_instance->fillBeans(array('companyname'=>'Cisco Systems, Inc'), $this->qual_module, $leads);
        foreach($leads as $count=>$lead) {
    		$this->assertContains('Cisco', $lead->account_name, "Assert fillBeans set account name to Cisco");
    		break;
    	}
    }

    public function testZoominfoCompanyFillBean()
    {
    	require_once('modules/Leads/Lead.php');
    	$source_instance = ConnectorFactory::getInstance('ext_rest_zoominfocompany');
    	$source_instance->getSource()->loadMapping();
    	$props = $source_instance->getSource()->getProperties();
    	$props['company_detail_url'] = $this->mock->url('company_detail');
    	$source_instance->getSource()->setProperties($props);
    	$lead = new Lead();
    	$lead = $source_instance->fillBean(array('id'=>'18579882'), $this->qual_module, $lead);
    	$this->assertContains('International Business Machines Corporation', $lead->account_name);
    }

    public function testZoominfoPersonFillBeans()
    {
    	require_once('modules/Leads/Lead.php');
    	$source_instance = SourceFactory::getSource('ext_rest_zoominfoperson');
    	$props = $source_instance->getProperties();
    	$props['person_search_url'] = $this->mock->url('people_search_query');
    	$source_instance->setProperties($props);

    	$args = array('firstname'=>'John', 'lastname'=>'Roberts');
    	$data = $source_instance->getList($args, $this->qual_module);
    	$this->assertNotEmpty($data);

    	$leads = array();
    	$source_instance = ConnectorFactory::getInstance('ext_rest_zoominfoperson');
    	$props = $source_instance->getSource()->getProperties();
    	$props['person_search_url'] = $this->mock->url('people_search_query2');
    	$source_instance->getSource()->setProperties($props);
    	$leads = $source_instance->fillBeans($args, $this->qual_module, $leads);
        foreach($leads as $count=>$lead) {
    		$this->assertEquals($data[$count]['firstname'], $lead->first_name);
    		$this->assertEquals($data[$count]['lastname'], $lead->last_name);
    		break;
    	}
    }
}