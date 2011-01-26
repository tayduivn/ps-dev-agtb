<?php

require_once('service/v3/SugarWebServiceUtilv3.php');
require_once('tests/service/APIv3Helper.php');


class RESTAPI4Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_user;

    protected $_lastRawResponse;

    private static $helperObject;

    public function setUp()
    {
        //Reload langauge strings
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
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
	    unset($GLOBALS['disable_date_format']);
	}

    protected function _makeRESTCall($method,$parameters)
    {
        // specify the REST web service to interact with
        $url = $GLOBALS['sugar_config']['site_url'].'/service/v4/rest.php';
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
        return $this->_makeRESTCall('login',
            array(
                'user_auth' =>
                    array(
                        'user_name' => $this->_user->user_name,
                        'password' => $this->_user->user_hash,
                        'version' => '.01',
                        ),
                'application_name' => 'SugarTestRunner',
                'name_value_list' => array(),
                )
            );
    }

    /**
     * Ensure the ability to retrieve a module list of recrods that are favorites.
     *
     */
    public function testGetModuleFavoriteList()
    {
        $result = $this->_login();
        $session = $result['id'];

        $account = new Account();
        $account->id = uniqid();
        $account->new_with_id = TRUE;
        $account->name = "Test " . $account->id;
        $account->save();

        $this->_markBeanAsFavorite($session, "Accounts", $account->id);
        
        $whereClause = "accounts.name='{$account->name}'";
        $module = 'Accounts';
        $orderBy = 'name';
        $offset = 0;
        $returnFields = array('name');
        $linkNameFields = "";
        $maxResults = 50;
        $deleted = FALSE;
        $favorites = TRUE;
        $result = $this->_makeRESTCall('get_entry_list', array($session, $module, $whereClause, $orderBy,$offset, $returnFields,$linkNameFields, $maxResults, $deleted, $favorites));

        $this->assertEquals($account->id, $result['entry_list'][0]['id'],'Unable to retrieve account favorite list.');

        $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$account->id}'");
        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE record_id = '{$account->id}'");
    }
    
    /**
     * Test search by module with favorites flag enabled.
     *
     */
    public function testSearchByModuleWithFavorites()
    {
        $result = $this->_login();
        $session = $result['id'];

        $account = new Account();
        $account->id = uniqid();
        $account->new_with_id = TRUE;
        $account->name = "Unit Test Fav " . $account->id;
        $account->save();
        $this->_markBeanAsFavorite($session, "Accounts", $account->id);
        
        //Negative test.
        $account2 = new Account();
        $account2->id = uniqid();
        $account2->new_with_id = TRUE;
        $account2->name = "Unit Test Fav " . $account->id;
        $account2->save();
        
        $searchModules = array('Accounts');
        $searchString = "Unit Test Fav ";
        $offSet = 0;
        $maxResults = 10;

        $results = $this->_makeRESTCall('search_by_module',
                        array(
                            'session' => $session,
                            'search'  => $searchString,
                            'modules' => $searchModules,
                            'offset'  => $offSet,
                            'max'     => $maxResults,
                            'user'    => '',
                            'select_field' => array(),
                            'unified_only' => true,
                            'favorites' => true,                            
                            )
                        );
        $this->assertTrue( self::$helperObject->findBeanIdFromEntryList($results['entry_list'],$account->id,'Accounts'), "Unable to find {$account->id} id in favorites search.");
        $this->assertFalse( self::$helperObject->findBeanIdFromEntryList($results['entry_list'],$account2->id,'Accounts'), "Account {$account2->id} id in favorites search should not be there.");
        $GLOBALS['db']->query("DELETE FROM accounts WHERE name like 'Unit Test %' ");
        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE record_id = '{$account->id}'");
        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE record_id = '{$account2->id}'");
    }
    
    
    /**
     * Private helper function to mark a bean as a favorite item.
     *
     * @param string $session
     * @param string $moduleName
     * @param string $recordID
     */
    private function _markBeanAsFavorite($session, $moduleName, $recordID)
    {
        $result = $this->_makeRESTCall('set_entry',
            array(
                'session' => $session,
                'module' => 'SugarFavorites',
                'name_value_list' => array(
                    array('name' => 'record_id', 'value' => $recordID),
                    array('name' => 'module', 'value' => $moduleName),
                    ),
                )
            );
    }
}