<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use PHPUnit\Framework\TestCase;

class RESTAPI3Test extends TestCase
{
    protected $_user;

    protected $_lastRawResponse;

    private static $helperObject;

    private $_unified_search_modules_content;

    public static function setUpBeforeClass() : void
    {
        if(isset($_SESSION['ACL'])) {
            unset($_SESSION['ACL']);
        }
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('Accounts'));
    }

    protected function setUp() : void
    {
        //Create an anonymous user for login purposes/
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;

        self::$helperObject = new APIv3Helper();

        if(file_exists(sugar_cached('modules/unified_search_modules.php')))
        {
            $this->unified_search_modules_content = file_get_contents(sugar_cached('modules/unified_search_modules.php'));
            unlink(sugar_cached('modules/unified_search_modules.php'));
        }

        $unifiedSearchAdvanced = new UnifiedSearchAdvanced();
        $_REQUEST['enabled_modules'] = 'Accounts,Contacts,Opportunities';
        $unifiedSearchAdvanced->saveGlobalSearchSettings();

        $GLOBALS['db']->query("DELETE FROM accounts WHERE name like 'UNIT TEST%' ");
        $GLOBALS['db']->query("DELETE FROM opportunities WHERE name like 'UNIT TEST%' ");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE first_name like 'UNIT TEST%' ");
        $GLOBALS['db']->query("DELETE FROM calls WHERE name like 'UNIT TEST%' ");
        $GLOBALS['db']->query("DELETE FROM tasks WHERE name like 'UNIT TEST%' ");
        $GLOBALS['db']->query("DELETE FROM meetings WHERE name like 'UNIT TEST%' ");
        //$this->useOutputBuffering = false;
    }

    protected function tearDown() : void
    {
        if(isset($GLOBALS['listViewDefs'])) unset($GLOBALS['listViewDefs']);
        if(isset($GLOBALS['viewdefs'])) unset($GLOBALS['viewdefs']);

        if(!empty($this->unified_search_modules_content))
        {
            file_put_contents(sugar_cached('modules/unified_search_modules.php'), $this->unified_search_modules_content);
        }

        $GLOBALS['db']->query("DELETE FROM accounts WHERE name like 'UNIT TEST%' ");
        $GLOBALS['db']->query("DELETE FROM opportunities WHERE name like 'UNIT TEST%' ");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE first_name like 'UNIT TEST%' ");
        $GLOBALS['db']->query("DELETE FROM calls WHERE name like 'UNIT TEST%' ");
        $GLOBALS['db']->query("DELETE FROM tasks WHERE name like 'UNIT TEST%' ");
        $GLOBALS['db']->query("DELETE FROM meetings WHERE name like 'UNIT TEST%' ");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['reload_vardefs']);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    protected function _makeRESTCall($method,$parameters)
    {
        // specify the REST web service to interact with
        $url = $GLOBALS['sugar_config']['site_url'].'/service/v3/rest.php';
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
                        'user_name' => 'admin',
                        'password' => md5('asdf'),
                        'version' => '.01',
                    ),
                'application_name' => 'SugarTestRunner',
                'name_value_list' => array(),
            )
        );
    }

    public function testSearchByModule()
    {
        $seedData = self::$helperObject->populateSeedDataForSearchTest($this->_user->id);

        $searchModules = array('Accounts','Contacts','Opportunities');
        $searchString = "UNIT TEST";
        $offSet = 0;
        $maxResults = 10;

        $result = $this->_login(); // Logging in just before the REST call as this will also commit any pending DB changes
        $session = $result['id'];
        $results = $this->_makeRESTCall('search_by_module',
            array(
                'session' => $session,
                'search'  => $searchString,
                'modules' => $searchModules,
                'offset'  => $offSet,
                'max'     => $maxResults,
                'user'    => $this->_user->id)
        );

        $this->assertTrue( self::$helperObject->findBeanIdFromEntryList($results['entry_list'],$seedData[0]['id'],'Accounts') );
        $this->assertFalse( self::$helperObject->findBeanIdFromEntryList($results['entry_list'],$seedData[1]['id'],'Accounts') );
        $this->assertTrue( self::$helperObject->findBeanIdFromEntryList($results['entry_list'],$seedData[2]['id'],'Contacts') );
        $this->assertTrue( self::$helperObject->findBeanIdFromEntryList($results['entry_list'],$seedData[3]['id'],'Opportunities') );
        $this->assertFalse( self::$helperObject->findBeanIdFromEntryList($results['entry_list'],$seedData[4]['id'],'Opportunities') );
    }

    public function testSearchByModuleWithReturnFields()
    {
        $seedData = self::$helperObject->populateSeedDataForSearchTest($this->_user->id);

        $returnFields = array('name','id','deleted');
        $searchModules = array('Accounts','Contacts','Opportunities');
        $searchString = "UNIT TEST";
        $offSet = 0;
        $maxResults = 10;

        $result = $this->_login(); // Logging in just before the REST call as this will also commit any pending DB changes
        $session = $result['id'];
        $results = $this->_makeRESTCall('search_by_module',
            array(
                'session' => $session,
                'search'  => $searchString,
                'modules' => $searchModules,
                'offset'  => $offSet,
                'max'     => $maxResults,
                'user'    => $this->_user->id,
                'selectFields' => $returnFields)
        );


        $this->assertEquals($seedData[0]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[0]['id'],'Accounts', $seedData[0]['fieldName']));
        $this->assertFalse(self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[1]['id'],'Accounts', $seedData[1]['fieldName']));
        $this->assertEquals($seedData[2]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[2]['id'],'Contacts', $seedData[2]['fieldName']));
        $this->assertEquals($seedData[3]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[3]['id'],'Opportunities', $seedData[3]['fieldName']));
        $this->assertFalse(self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[4]['id'],'Opportunities', $seedData[4]['fieldName']));
    }

    public function testGetServerInformation()
    {
        require('sugar_version.php');

        $result = $this->_login();
        $session = $result['id'];

        $result = $this->_makeRESTCall('get_server_info',array());

        $this->assertEquals($sugar_version, $result['version'],'Unable to get server information');
        $this->assertEquals($sugar_flavor, $result['flavor'],'Unable to get server information');
    }

    public function testGetModuleList()
    {
        $account = new Account();
        $account->id = uniqid();
        $account->new_with_id = TRUE;
        $account->name = "Test " . $account->id;
        $account->save();

        $whereClause = "accounts.name='{$account->name}'";
        $module = 'Accounts';
        $orderBy = 'name';
        $offset = 0;
        $returnFields = array('name');

        $result = $this->_login(); // Logging in just before the REST call as this will also commit any pending DB changes
        $session = $result['id'];
        $result = $this->_makeRESTCall('get_entry_list', array($session, $module, $whereClause, $orderBy,$offset, $returnFields));

        $this->assertEquals($account->id, $result['entry_list'][0]['id'],'Unable to retrieve account list during search.');

        $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$account->id}'");
    }

    public function testLogin()
    {
        $result = $this->_login();
        $this->assertTrue(!empty($result['id']) && $result['id'] != -1,$this->_returnLastRawResponse());
    }

    public static function _multipleModuleLayoutProvider()
    {
        return array(
            array(
                'module' => array('Accounts','Contacts'),
                'type' => array('default'),
                'view' => array('list'),
                'expected_file' => array(
                    'Accounts' => array( 'default' => array('list' => 'modules/Accounts/metadata/listviewdefs.php')),
                    'Contacts' => array( 'default' => array('list' => 'modules/Contacts/metadata/listviewdefs.php')))
            ),
            array(
                'module' => array('Accounts','Contacts'),
                'type' => array('default'),
                'view' => array('list','detail'),
                'expected_file' => array(
                    'Accounts' => array(
                        'default' => array(
                            'list' => 'modules/Accounts/metadata/listviewdefs.php',
                            'detail' => 'modules/Accounts/metadata/detailviewdefs.php')),
                    'Contacts' => array(
                        'default' => array(
                            'list' => 'modules/Contacts/metadata/listviewdefs.php',
                            'detail' => 'modules/Contacts/metadata/detailviewdefs.php'))
                ))
        );
    }

    /**
     * @dataProvider _multipleModuleLayoutProvider
     */
    public function testGetMultipleModuleLayout($a_module, $a_type, $a_view, $a_expected_file)
    {
        $result = $this->_login();
        $session = $result['id'];

        $results = $this->_makeRESTCall('get_module_layout',
            array(
                'session' => $session,
                'module' => $a_module,
                'type' => $a_type,
                'view' => $a_view)
        );

        foreach ($results as $module => $moduleResults )
        {
            foreach ($moduleResults as $type => $viewResults)
            {
                foreach ($viewResults as $view => $result)
                {
                    $expected_file = $a_expected_file[$module][$type][$view];
                    if ( is_file('custom'  . DIRECTORY_SEPARATOR . $expected_file) )
                        require('custom'  . DIRECTORY_SEPARATOR . $expected_file);
                    else
                        require($expected_file);

                    if($view == 'list')
                        $expectedResults = $listViewDefs[$module];
                    else
                        $expectedResults = $viewdefs[$module][ucfirst($view) .'View' ];

                    $this->assertEquals(md5(serialize($expectedResults)), md5(serialize($result)), "Unable to retrieve module layout: module {$module}, type $type, view $view");
                }
            }
        }
    }

    public static function _moduleLayoutProvider()
    {
        return array(
            array('module' => 'Accounts','type' => 'default', 'view' => 'list','expected_file' => 'modules/Accounts/metadata/listviewdefs.php' ),
            array('module' => 'Accounts','type' => 'default', 'view' => 'edit','expected_file' => 'modules/Accounts/metadata/editviewdefs.php' ),
            array('module' => 'Accounts','type' => 'default', 'view' => 'detail','expected_file' => 'modules/Accounts/metadata/detailviewdefs.php' ),
        );
    }

    /**
     * @dataProvider _moduleLayoutProvider
     */
    public function testGetModuleLayout($module, $type, $view, $expected_file)
    {
        $result = $this->_login();
        $session = $result['id'];

        $result = $this->_makeRESTCall('get_module_layout',
            array(
                'session' => $session,
                'module' => array($module),
                'type' => array($type),
                'view' => array($view))
        );

        if ( is_file('custom'  . DIRECTORY_SEPARATOR . $expected_file) )
            require('custom'  . DIRECTORY_SEPARATOR . $expected_file);
        else
            require($expected_file);

        if($view == 'list') {
            $expectedResults = $listViewDefs[$module];
        } else {
            if ($type == 'wireless') {
                $expectedResults = $viewdefs[$module]['mobile']['view'][$view];
            } else {
                $expectedResults = $viewdefs[$module][ucfirst($view) .'View' ];
            }
        }

        $a_expectedResults = array();
        $a_expectedResults[$module][$type][$view] = $expectedResults;

        $this->assertEquals(md5(serialize($a_expectedResults)), md5(serialize($result)), "Unable to retrieve module layout: module {$module}, type $type, view $view");
    }

    /**
     * @dataProvider _moduleLayoutProvider
     */
    public function testGetModuleLayoutMD5($module, $type, $view, $expected_file)
    {
        $result = $this->_login();
        $session = $result['id'];

        $fullResult = $this->_makeRESTCall('get_module_layout_md5',
            array(
                'session' => $session,
                'module' => array($module),
                'type' => array($type),
                'view' => array($view) )
        );
        $result = $fullResult['md5'];
        if ( is_file('custom'  . DIRECTORY_SEPARATOR . $expected_file) )
            require('custom'  . DIRECTORY_SEPARATOR . $expected_file);
        else
            require($expected_file);

        if($view == 'list') {
            $expectedResults = $listViewDefs[$module];
        } else {
            if ($type == 'wireless') {
                $expectedResults = $viewdefs[$module]['mobile']['view'][$view];
            } else {
                $expectedResults = $viewdefs[$module][ucfirst($view) .'View' ];
            }
        }

        $a_expectedResults = array();
        $a_expectedResults[$module][$type][$view] = $expectedResults;

        $this->assertEquals(md5(serialize($expectedResults)), $result[$module][$type][$view], "Unable to retrieve module layout md5: module {$module}, type $type, view $view");
    }

    public static function _wirelessGridModuleLayoutProvider()
    {
        return array(
            array('module' => 'Accounts', 'view' => 'edit', 'metadatafile' => 'modules/Accounts/clients/mobile/views/edit/edit.php',),
            array('module' => 'Accounts', 'view' => 'detail', 'metadatafile' => 'modules/Accounts/clients/mobile/views/detail/detail.php',),
        );

    }

    /**
     * Leaving as a provider in the event we need to extend it in the future
     *
     * @static
     * @return array
     */
    public static function _wirelessListModuleLayoutProvider()
    {
        return array(
            array('module' => 'Cases'),
        );

    }

    /**
     * @dataProvider _wirelessGridModuleLayoutProvider
     */
    public function testGetWirelessGridModuleLayout($module, $view, $metadatafile)
    {
        $result = $this->_login();
        $session = $result['id'];

        $type = 'wireless';
        $result = $this->_makeRESTCall('get_module_layout',
            array(
                'session' => $session,
                'module' => array($module),
                'type' => array($type),
                'view' => array($view))
        );

        $this->assertArrayHasKey('panels', $result[$module][$type][$view], 'REST call result does not have a panels array');
    }

    public function testAddNewAccountAndThenDeleteIt()
    {
        $result = $this->_login();
        $this->assertTrue(!empty($result['id']) && $result['id'] != -1,$this->_returnLastRawResponse());
        $session = $result['id'];

        $result = $this->_makeRESTCall('set_entry',
            array(
                'session' => $session,
                'module' => 'Accounts',
                'name_value_list' => array(
                    array('name' => 'name', 'value' => 'New Account'),
                    array('name' => 'description', 'value' => 'This is an account created from a REST web services call'),
                ),
            )
        );

        $this->assertTrue(!empty($result['id']) && $result['id'] != -1,$this->_returnLastRawResponse());

        $accountId = $result['id'];

        // verify record was created
        $result = $this->_makeRESTCall('get_entry',
            array(
                'session' => $session,
                'module' => 'Accounts',
                'id' => $accountId,
            )
        );

        $this->assertEquals($result['entry_list'][0]['id'],$accountId,$this->_returnLastRawResponse());

        // delete the record
        $result = $this->_makeRESTCall('set_entry',
            array(
                'session' => $session,
                'module' => 'Accounts',
                'name_value_list' => array(
                    array('name' => 'id', 'value' => $accountId),
                    array('name' => 'deleted', 'value' => '1'),
                ),
            )
        );

        $this->assertTrue(!empty($result['id']) && $result['id'] != -1,$this->_returnLastRawResponse());

        // try to retrieve again to validate it is deleted
        $result = $this->_makeRESTCall('get_entry',
            array(
                'session' => $session,
                'module' => 'Accounts',
                'id' => $accountId,
            )
        );

        $GLOBALS['db']->query("DELETE FROM accounts WHERE id= '{$accountId}'");

        $this->assertTrue(!empty($result['entry_list'][0]['id']) && $result['entry_list'][0]['id'] != -1,$this->_returnLastRawResponse());
        $this->assertEquals($result['entry_list'][0]['name_value_list'][0]['name'],'warning',$this->_returnLastRawResponse());
        $this->assertEquals($result['entry_list'][0]['name_value_list'][0]['value'],"Access to this object is denied since it has been deleted or does not exist",$this->_returnLastRawResponse());
        $this->assertEquals($result['entry_list'][0]['name_value_list'][1]['name'],'deleted',$this->_returnLastRawResponse());
        $this->assertEquals($result['entry_list'][0]['name_value_list'][1]['value'],1,$this->_returnLastRawResponse());
    }

    public function testRelateAccountToTwoContacts()
    {
        $result = $this->_login();
        $this->assertTrue(!empty($result['id']) && $result['id'] != -1,$this->_returnLastRawResponse());
        $session = $result['id'];

        $result = $this->_makeRESTCall('set_entry',
            array(
                'session' => $session,
                'module' => 'Accounts',
                'name_value_list' => array(
                    array('name' => 'name', 'value' => 'New Account'),
                    array('name' => 'description', 'value' => 'This is an account created from a REST web services call'),
                ),
            )
        );

        $this->assertTrue(!empty($result['id']) && $result['id'] != -1,$this->_returnLastRawResponse());

        $accountId = $result['id'];

        $result = $this->_makeRESTCall('set_entry',
            array(
                'session' => $session,
                'module' => 'Contacts',
                'name_value_list' => array(
                    array('name' => 'last_name', 'value' => 'New Contact 1'),
                    array('name' => 'description', 'value' => 'This is a contact created from a REST web services call'),
                ),
            )
        );

        $this->assertTrue(!empty($result['id']) && $result['id'] != -1,$this->_returnLastRawResponse());

        $contactId1 = $result['id'];

        $result = $this->_makeRESTCall('set_entry',
            array(
                'session' => $session,
                'module' => 'Contacts',
                'name_value_list' => array(
                    array('name' => 'last_name', 'value' => 'New Contact 2'),
                    array('name' => 'description', 'value' => 'This is a contact created from a REST web services call'),
                ),
            )
        );

        $this->assertTrue(!empty($result['id']) && $result['id'] != -1,$this->_returnLastRawResponse());

        $contactId2 = $result['id'];

        // now relate them together
        $result = $this->_makeRESTCall('set_relationship',
            array(
                'session' => $session,
                'module' => 'Accounts',
                'module_id' => $accountId,
                'link_field_name' => 'contacts',
                'related_ids' => array($contactId1,$contactId2),
            )
        );

        $this->assertEquals($result['created'],1,$this->_returnLastRawResponse());

        // check the relationship
        $result = $this->_makeRESTCall('get_relationships',
            array(
                'session' => $session,
                'module' => 'Accounts',
                'module_id' => $accountId,
                'link_field_name' => 'contacts',
                'related_module_query' => '',
                'related_fields' => array('last_name','description'),
                'related_module_link_name_to_fields_array' => array(),
                'deleted' => false,
            )
        );

        $returnedValues = array();
        $returnedValues[] = $result['entry_list'][0]['name_value_list']['last_name']['value'];
        $returnedValues[] = $result['entry_list'][1]['name_value_list']['last_name']['value'];

        $GLOBALS['db']->query("DELETE FROM accounts WHERE id= '{$accountId}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$contactId1}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$contactId2}'");
        $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE account_id= '{$accountId}'");

        $this->assertContains('New Contact 1',$returnedValues,$this->_returnLastRawResponse());
        $this->assertContains('New Contact 2',$returnedValues,$this->_returnLastRawResponse());
    }

    /**
     * @ticket 36658
     */
    public function testOrderByClauseOfGetRelationship()
    {
        $result = $this->_login();
        $this->assertTrue(!empty($result['id']) && $result['id'] != -1,$this->_returnLastRawResponse());
        $session = $result['id'];

        $result = $this->_makeRESTCall('set_entry',
            array(
                'session' => $session,
                'module' => 'Accounts',
                'name_value_list' => array(
                    array('name' => 'name', 'value' => 'New Account'),
                    array('name' => 'description', 'value' => 'This is an account created from a REST web services call'),
                ),
            )
        );

        $this->assertTrue(!empty($result['id']) && $result['id'] != -1,$this->_returnLastRawResponse());

        $accountId = $result['id'];

        $result = $this->_makeRESTCall('set_entry',
            array(
                'session' => $session,
                'module' => 'Contacts',
                'name_value_list' => array(
                    array('name' => 'last_name', 'value' => 'New Contact 1'),
                    array('name' => 'description', 'value' => 'This is a contact created from a REST web services call'),
                ),
            )
        );

        $this->assertTrue(!empty($result['id']) && $result['id'] != -1,$this->_returnLastRawResponse());

        $contactId1 = $result['id'];

        $result = $this->_makeRESTCall('set_entry',
            array(
                'session' => $session,
                'module' => 'Contacts',
                'name_value_list' => array(
                    array('name' => 'last_name', 'value' => 'New Contact 3'),
                    array('name' => 'description', 'value' => 'This is a contact created from a REST web services call'),
                ),
            )
        );

        $this->assertTrue(!empty($result['id']) && $result['id'] != -1,$this->_returnLastRawResponse());
        $contactId3 = $result['id'];

        $result = $this->_makeRESTCall('set_entry',
            array(
                'session' => $session,
                'module' => 'Contacts',
                'name_value_list' => array(
                    array('name' => 'last_name', 'value' => 'New Contact 2'),
                    array('name' => 'description', 'value' => 'This is a contact created from a REST web services call'),
                ),
            )
        );

        $this->assertTrue(!empty($result['id']) && $result['id'] != -1,$this->_returnLastRawResponse());

        $contactId2 = $result['id'];

        // now relate them together
        $result = $this->_makeRESTCall('set_relationship',
            array(
                'session' => $session,
                'module' => 'Accounts',
                'module_id' => $accountId,
                'link_field_name' => 'contacts',
                'related_ids' => array($contactId1,$contactId3,$contactId2),
            )
        );

        $this->assertEquals($result['created'],1,$this->_returnLastRawResponse());

        // check the relationship
        $result = $this->_makeRESTCall('get_relationships',
            array(
                'session' => $session,
                'module' => 'Accounts',
                'module_id' => $accountId,
                'link_field_name' => 'contacts',
                'related_module_query' => '',
                'related_fields' => array('last_name','description'),
                'related_module_link_name_to_fields_array' => array(),
                'deleted' => false,
                'order_by' => 'last_name',
            )
        );

        $GLOBALS['db']->query("DELETE FROM accounts WHERE id= '{$accountId}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$contactId1}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$contactId2}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$contactId3}'");
        $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE account_id= '{$accountId}'");

        $this->assertEquals($result['entry_list'][0]['name_value_list']['last_name']['value'],'New Contact 1',$this->_returnLastRawResponse());
        $this->assertEquals($result['entry_list'][1]['name_value_list']['last_name']['value'],'New Contact 2',$this->_returnLastRawResponse());
        $this->assertEquals($result['entry_list'][2]['name_value_list']['last_name']['value'],'New Contact 3',$this->_returnLastRawResponse());
    }

    public static function _subpanelLayoutProvider()
    {
        return array(
            array(
                'module' => 'Contacts',
                'type' => 'default',
                'view' => 'subpanel',
            ),
            array(
                'module' => 'Leads',
                'type' => 'wireless',
                'view' => 'subpanel',
            ),
        );
    }

    /**
     * @dataProvider _subpanelLayoutProvider
     */
    public function testGetSubpanelLayout($module, $type, $view)
    {
        $result = $this->_login();
        $session = $result['id'];

        $results = $this->_makeRESTCall('get_module_layout',
            array(
                'session' => $session,
                'module' => array($module),
                'type' => array($type),
                'view' => array($view))
        );

        $this->assertTrue(isset($results[$module][$type][$view]), "Unable to get subpanel defs");
    }
    /**
     * @depends SOAPAPI3Test::testSetEntriesForAccount
     */
    public function testGetLastViewed()
    {
        $testModule = 'Accounts';
        $testModuleID = create_guid();

        $this->_createTrackerEntry($testModule,$testModuleID);

        $result = $this->_login();
        $this->assertTrue(!empty($result['id']) && $result['id'] != -1,$this->_returnLastRawResponse());
        $session = $result['id'];

        $results = $this->_makeRESTCall('get_last_viewed',
            array(
                'session' => $session,
                'modules' => array($testModule),
            )
        );

        $found = FALSE;
        foreach ($results as $entry)
        {
            if($entry['item_id'] == $testModuleID)
            {
                $found = TRUE;
                break;
            }
        }

        $this->assertTrue($found, "Unable to get last viewed modules");
    }

    private function _createTrackerEntry($module, $id,$summaryText = "UNIT TEST SUMMARY")
    {
        $trackerManager = TrackerManager::getInstance();
        $trackerManager->unPause();

        $timeStamp = TimeDate::getInstance()->nowDb();
        $monitor = $trackerManager->getMonitor('tracker');

        $monitor->setValue('team_id', $this->_user->getPrivateTeamID());
        $monitor->setValue('action', 'detail');
        $monitor->setValue('user_id', $this->_user->id);
        $monitor->setValue('module_name', $module);
        $monitor->setValue('date_modified', $timeStamp);
        $monitor->setValue('visible', true);
        $monitor->setValue('item_id', $id);
        $monitor->setValue('item_summary', $summaryText);
        $trackerManager->saveMonitor($monitor, true, true);
    }
}
