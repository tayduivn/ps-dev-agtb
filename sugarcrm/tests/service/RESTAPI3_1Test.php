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
 
require_once('service/v3/SugarWebServiceUtilv3.php');
require_once('tests/service/APIv3Helper.php');


class RESTAPI3_1Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_user;

    protected $_lastRawResponse;

    private static $helperObject;

    public function setUp()
    {
        //Reload langauge strings
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Accounts');
        //Create an anonymous user for login purposes/
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $this->_user->status = 'Active';
        $this->_user->is_admin = 1;
        $this->_user->save();
        $GLOBALS['current_user'] = $this->_user;

        self::$helperObject = new APIv3Helper();
    }

    public function tearDown()
	{
	    if(isset($GLOBALS['listViewDefs'])) unset($GLOBALS['listViewDefs']);
	    if(isset($GLOBALS['viewdefs'])) unset($GLOBALS['viewdefs']);
	    unset($GLOBALS['app_list_strings']);
	    unset($GLOBALS['app_strings']);
	    unset($GLOBALS['mod_strings']);
        SugarTestHelper::tearDown();
    }

    protected function _makeRESTCall($method,$parameters)
    {
        // specify the REST web service to interact with
        $url = $GLOBALS['sugar_config']['site_url'].'/service/v3_1/rest.php';
        // Open a curl session for making the call
        $curl = curl_init($url);
        // set URL and other appropriate options
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
        // build the request URL
        $json = json_encode($parameters);
        $postArgs = "method=$method&input_type=JSON&response_type=JSON&rest_data=$json";
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs);
        // Make the REST call, returning the result
        $response = curl_exec($curl);
        // Close the connection
        curl_close($curl);

        $this->_lastRawResponse = $response;

        // Convert the result from JSON format to a PHP array
        return json_decode($response,true);
    }

    protected function _returnLastRawResponse()
    {
        return "Error in web services call. Response was: {$this->_lastRawResponse}";
    }

    protected function _login()
    {
        $GLOBALS['db']->commit(); // Making sure we commit any changes before logging in
        return $this->_makeRESTCall('login',
            array(
                'user_auth' =>
                    array(
                        'user_name' => $this->_user->user_name,
                        'password' => $this->_user->user_hash,
                        'version' => '.01',
                        ),
                'application_name' => 'mobile',
                'name_value_list' => array(),
                )
            );
    }

    public function testLogin()
    {
        $result = $this->_login();
        $this->assertTrue( isset($result['name_value_list']['available_modules']) );
        $this->assertTrue( isset($result['name_value_list']['vardefs_md5']) );
        $this->assertTrue(!empty($result['id']) && $result['id'] != -1,$this->_returnLastRawResponse());
    }

    /**
     * Test the available modules returned from the login call to make sure they are correct.
     *
     */
    public function testLoginAvailableModulesResults()
    {
        $this->markTestIncomplete('modInvisList becomes corrupted, need to investigate.');
        $result = $this->_login();
        $this->assertTrue( isset($result['name_value_list']['available_modules']) );

        $actualModuleList= $result['name_value_list']['available_modules'];
        $sh = new SugarWebServiceUtilv3();
        $availModules = array_keys($sh->get_user_module_list($this->_user));
        $expectedModuleList = $sh->get_visible_mobile_modules($availModules);

        $this->assertEquals(count($actualModuleList), count($expectedModuleList), "Could not get available modules during login" );
    }

    public function testGetSingleModuleLanguage()
    {
        $result = $this->_login();
        $session = $result['id'];

        $results = $this->_makeRESTCall('get_language_definition',
                        array(
                            'session' => $session,
                            'modules'  => 'Accounts',
                            'md5'   => false,
                        ));
        $this->assertTrue( isset($results['Accounts']['LBL_NAME']) );
    }

     public function testGetSingleModuleLanguageMD5()
    {
        $result = $this->_login();
        $session = $result['id'];

        $results = $this->_makeRESTCall('get_language_definition',
                        array(
                            'session' => $session,
                            'modules'  => 'Accounts',
                            'md5'   => true,
                        ));

        $this->assertTrue( isset($results['Accounts']) );
        $this->assertTrue( !empty($results['Accounts']) );
    }

    public function testGetMultipleModuleLanguage()
    {
        $result = $this->_login();
        $session = $result['id'];

        $results = $this->_makeRESTCall('get_language_definition',
                        array(
                            'session' => $session,
                            'modules'  => array('Accounts','Contacts','Leads'),
                            'md5'   => false,
                        ));
        $this->assertTrue( isset($results['Accounts']['LBL_NAME']), "Unable to get multiple module language for Accounts, result: " . var_export($results['Accounts'],true) );
        $this->assertTrue( isset($results['Contacts']['LBL_NAME']), "Unable to get multiple module language for Contacts, result: " . var_export($results['Contacts'],true) );
        $this->assertTrue( isset($results['Leads']['LBL_LEAD_SOURCE']), "Unable to get multiple module language for Leads, result: " . var_export($results['Leads'],true) );
    }

    public function testGetMultipleModuleLanguageAndAppStrings()
    {
        $result = $this->_login();
        $session = $result['id'];

        $results = $this->_makeRESTCall('get_language_definition',
                        array(
                            'session' => $session,
                            'modules'  => array('Accounts','Contacts','Leads','app_strings','app_list_strings'),
                            'md5'   => false,
                        ));

        $this->assertTrue( isset($results['app_strings']['LBL_NO_ACTION']) );
        $this->assertTrue( isset($results['app_strings']['LBL_EMAIL_YES']) );
        $this->assertTrue( isset($results['app_list_strings']['account_type_dom']) );
        $this->assertTrue( isset($results['app_list_strings']['moduleList']) );
        $this->assertTrue( isset($results['Contacts']['LBL_NAME']) );
        $this->assertTrue( isset($results['Leads']['LBL_LEAD_SOURCE']) );
    }
    //BEGIN SUGARCRM flav=pro ONLY
    public function testGetQuotesPDFContents()
    {
        $quote = new Quote();
        $quote->name = "Test " . uniqid();
        $quote->save(FALSE);

        $result = $this->_login(); // Logging in just before the REST call as this will also commit any pending DB changes
        $session = $result['id'];

        $results = $this->_makeRESTCall('get_quotes_pdf',
                        array(
                            'session' => $session,
                            'quote_id' => $quote->id,
                            'pdf_format'   => 'Standard',
                        ));

        $this->assertTrue( !empty($results['file_contents']) );
    }
    //END SUGARCRM flav=pro ONLY
     /**
     * Test the available modules returned from the login call to make sure they are correct.
     *
     */
    public function testLoginVardefsMD5Results()
    {
        $this->markTestIncomplete('Vardef results are still dirty even with reload global set, need to investigate further.');

        $GLOBALS['reload_vardefs'] = TRUE;
        global  $beanList, $beanFiles;
        $result = $this->_login();
        $this->assertTrue( isset($result['name_value_list']['vardefs_md5']) );

        $a_actualMD5= $result['name_value_list']['vardefs_md5'];

        $sh = new SugarWebServiceUtilv3();
        $availModules = array_keys($sh->get_user_module_list($this->_user));
        $expectedModuleList = $sh->get_visible_mobile_modules($availModules);
        $soapHelper = new SugarWebServiceUtilv3();
        foreach ($expectedModuleList as $mod)
        {
            $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], $mod);
            $actualMD5 = $a_actualMD5[$mod];

            $class_name = $beanList[$mod];
            require_once($beanFiles[$class_name]);
            $seed = new $class_name();
            $actualVardef = $soapHelper->get_return_module_fields($seed,$mod,'');
            $expectedMD5 = md5(serialize($actualVardef));
            $this->assertEquals($expectedMD5, $actualMD5);
        }
        $this->assertEquals(count($actualModuleList), count($expectedModuleList), "Could not get available modules during login" );
    }


    function _aclEditViewFieldProvider()
    {
        return array(
            array('Accounts','wireless','edit', 'name', 99),
            array('Accounts','wireless','edit', 'phone_office', 99),
            array('Accounts','wireless','edit', 'email1', 99),
            array('Accounts','wireless','edit', 'nofield', null),
            );
    }


    function _aclListViewFieldProvider()
    {
        return array(
            array('Accounts','wireless','list', 'NAME', 99),
            array('Accounts','wireless','list', 'WEBSITE', 99),
            array('Accounts','wireless','list', 'FAKEFIELD', null),
        );
    }

    /**
     * @dataProvider _aclListViewFieldProvider
     */
    public function testMetadataListViewFieldLevelACLS($module, $view_type, $view, $field_name, $expeced_acl)
    {
        $this->markTestIncomplete('Should be enabled for 611 patch.');

        $result = $this->_login();
        $session = $result['id'];

        $results = $this->_makeRESTCall('get_module_layout',
            array(
                'session' => $session,
                'module' => array($module),
                'type' => array($view_type),
                'view' => array($view))
        );
        $this->assertEquals($expeced_acl, $results[$module][$view_type][$view][$field_name]['acl'] );

    }

    /**
     * @dataProvider _aclEditViewFieldProvider
     */
    public function testMetadataEditViewFieldLevelACLS($module, $view_type, $view, $field_name, $expeced_acl)
    {
        $this->markTestIncomplete('Should be enabled for 611 patch.');
        $result = $this->_login();
        $session = $result['id'];

        $results = $this->_makeRESTCall('get_module_layout',
        array(
            'session' => $session,
            'module' => array($module),
            'type' => array($view_type),
            'view' => array($view))
        );

        $fields = $results[$module][$view_type][$view]['panels'];
        foreach ($fields as $field_row)
        {
            foreach ($field_row as $field_def)
            {
                if($field_def['name'] == $field_name)
                {
                    $this->assertEquals($expeced_acl, $field_def['acl'] );
                    break;
                }
            }
        }
    }
}
