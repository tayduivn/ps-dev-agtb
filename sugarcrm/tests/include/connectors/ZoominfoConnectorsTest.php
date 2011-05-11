<?php
//FILE SUGARCRM flav=pro ONLY
require_once('include/connectors/ConnectorFactory.php');
require_once('include/connectors/sources/SourceFactory.php');
require_once('include/connectors/utils/ConnectorUtils.php');
require_once('modules/Connectors/controller.php');
require_once('include/connectors/ConnectorsTestCase.php');

class ZoominfoConnectorsTest extends Sugar_Connectors_TestCase
{
	protected $qual_module;

    public function setUp()
    {
        parent::setUp();
		require('modules/Connectors/connectors/sources/ext/rest/zoominfocompany/config.php');
		$url = $config['properties']['company_search_url'] . $config['properties']['api_key'] . '&CompanyID=18579882';
		$contents = @file_get_contents($url);

	    if(empty($contents)) {
	       $this->markTestSkipped("Skipping Zoominfo test");
	    }

    	ConnectorFactory::$source_map = array();

    	if(file_exists('custom/modules/Connectors/connectors/sources/ext/rest/zoominfocompany/mapping.php')) {
           unlink('custom/modules/Connectors/connectors/sources/ext/rest/zoominfocompany/mapping.php');
        }


    	require('modules/Connectors/connectors/sources/ext/rest/zoominfocompany/config.php');
		$url = $config['properties']['company_search_url'] . $config['properties']['api_key'] . '&CompanyID=18579882';
		$contents = @file_get_contents($url);
		if(empty($contents)) {
		    $this->markTestSkipped("Skipping... Zoominfocompany service is unavailable.");
		} else {
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
		}
    }

    public function testZoominfoCompanyFillBeans()
    {
    	require_once('modules/Leads/Lead.php');
    	$source_instance = ConnectorFactory::getInstance('ext_rest_zoominfocompany');
    	$source_instance->getSource()->loadMapping();
    	$leads = array();
    	$leads = $source_instance->fillBeans(array('companyname'=>'Cisco Systems, Inc'), $this->qual_module, $leads);
        foreach($leads as $count=>$lead) {
    		$this->assertEquals(preg_match('/Cisco/', $lead->account_name), 1, "Assert fillBeans set account name to Cisco");
    		break;
    	}
    }

    public function testZoominfoCompanyFillBean()
    {
    	require_once('modules/Leads/Lead.php');
    	$source_instance = ConnectorFactory::getInstance('ext_rest_zoominfocompany');
    	$source_instance->getSource()->loadMapping();
    	$lead = new Lead();
    	$lead = $source_instance->fillBean(array('id'=>'172209392'), $this->qual_module, $lead);
    	if(!empty($lead->website)) {
    		$this->assertTrue(trim($lead->website) == 'www.ibm.com');
    	}
    }

    public function testZoominfoPersonFillBeans()
    {
    	require_once('modules/Leads/Lead.php');
    	$source_instance = SourceFactory::getSource('ext_rest_zoominfoperson');
    	$args = array('firstname'=>'John', 'lastname'=>'Roberts');
    	$data = $source_instance->getList($args, $this->qual_module);
    	if(!empty($data)) {
	    	$leads = array();
	    	$source_instance = ConnectorFactory::getInstance('ext_rest_zoominfoperson');
	    	$leads = $source_instance->fillBeans($args, $this->qual_module, $leads);
	        foreach($leads as $count=>$lead) {
	    		$this->assertTrue($lead->first_name == $data[$count]['firstname'] && $lead->last_name == $data[$count]['lastname']);
	    		break;
	    	}
    	}
    }
}