<?php
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
        $cookie = 'debug_stop=1; debug_host=127.0.0.1; debug_port=10137; start_debug=1; send_debug_header=1; no_remote=1; send_sess_end=1; debug_jit=1; ZDEDebuggerPresent=php,phtml,php3; debug_session_id=1234567';
        $cookies = explode('; ', $cookie);
        foreach($cookies as $c) {
          $ca = explode('=', $c);
          $this->_soapClient->setCookie($ca[0], $ca[1]);
        }

        $result = $this->_soapClient->call(
            'get_entry_list',
            array(
                'session' => $this->_sessionId,
                "module_name" => 'Leads',
                "query" => "leads.id = '{$lead->id}'",
                '',
                0,
                array(),
                array(array('name' =>  'email_addresses', 'value' => array('id', 'email_address', 'opt_out', 'primary_address'))),
                )
            );

        $this->assertEquals('primary_address', $result['relationship_list'][0][0]['records'][0][3]['name']);

    }
}
