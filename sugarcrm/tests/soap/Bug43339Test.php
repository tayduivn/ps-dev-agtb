<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License. Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party. Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited. You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution. See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License. Please refer to the License for the specific language
 * governing these rights and limitations under the License. Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 * ****************************************************************************** */

require_once("service/v4_1/SugarWebServiceImplv4_1.php");
require_once('tests/service/SOAPTestCase.php');
require_once('soap/SoapError.php');

/**
 * Bug #43339
 * get_entries_count doesn't work with custom fields
 *
 * @ticket 43339
 */
class Bug43339Test extends SOAPTestCase
{

    private $_module = NULL;
    private $_moduleName = 'Contacts';
    private $_customFieldName = 'test_custom_c';
    private $_field = null;
    private $_df = null;

    protected $session = array();

    public function setUp()
    {
        $this->session['use_cookies'] = ini_get('session.use_cookies');
        $this->session['cache_limiter'] = ini_get('session.session.cache_limiter');
        ini_set('session.use_cookies', false);
        ini_set('session.cache_limiter', false);

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        $_SERVER['REMOTE_ADDR'] = '0.0.0.0';

        $this->_setupTestUser();

        $this->_field = get_widget('varchar');
        $this->_field->id = $this->_moduleName . $this->_customFieldName;
        $this->_field->name = $this->_customFieldName;
        $this->_field->vanme = 'LBL_' . strtoupper($this->_customFieldName);
        $this->_field->comments = NULL;
        $this->_field->help = NULL;
        $this->_field->custom_module = $this->_moduleName;
        $this->_field->type = 'varchar';
        $this->_field->label = 'LBL_' . strtoupper($this->_customFieldName);
        $this->_field->len = 255;
        $this->_field->required = 0;
        $this->_field->default_value = '';
        $this->_field->date_modified = '2012-03-14 02:23:23';
        $this->_field->deleted = 0;
        $this->_field->audited = 0;
        $this->_field->massupdate = 0;
        $this->_field->duplicate_merge = 0;
        $this->_field->reportable = 1;
        $this->_field->importable = 'true';
        $this->_field->ext1 = NULL;
        $this->_field->ext2 = NULL;
        $this->_field->ext3 = NULL;
        $this->_field->ext4 = NULL;

        $className = $beanList[$this->_moduleName];
        require_once($beanFiles[$className]);
        $this->_module = new $className();

        $this->_df = new DynamicField($this->_moduleName);

        $this->_df->setup($this->_module);
        $this->_df->addFieldObject($this->_field);
    }

    /**
     * get_entries_count doesn't work with custom fields
     *
     * @group 43339
     */
    public function testGetEntriesCountForCustomField()
    {
        $api = new SugarWebServiceImplv4_1();
        $auth = $api->login(array('user_name' => self::$_user->user_name, 'password' => self::$_user->user_hash, 'version' => '.01'), 'SoapTest', array());
        $assert = $api->get_entries_count($auth['id'], $this->_moduleName, $this->_customFieldName . ' LIKE \'\'', 0);
        $api->logout($auth['id']);

        $this->assertNotEquals(NULL, $assert['result_count']);
    }

    /**
     * Test for soap/SoapSugarUsers.php::get_entries_count()
     *
     * @group 43339
     */
    public function testGetEntriesCountFromBasicSoap()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
        parent::setUp();

        $auth = $this->_soapClient->call('login',
            array('user_auth' =>
            array(
                'user_name' => $this->_user->user_name,
                'password' => $this->_user->user_hash,
                'version' => '.01'),
                'application_name' => 'SoapTest', "name_value_list" => array()
            )
        );

        $params = array(
            'session' => $auth['id'],
            'module_name' => $this->_moduleName,
            'query' => $this->_customFieldName . ' LIKE \'\'',
            'deleted' => 0
        );
        $assert = $this->_soapClient->call('get_entries_count', $params);

        $this->assertNotEquals(NULL, $assert['result_count']);
    }

    public function tearDown()
    {
        $this->_df->deleteField($this->_field);

        SugarTestHelper::tearDown();
        unset($_SERVER['REMOTE_ADDR']);

        $this->_tearDownTestUser();

        ini_set('session.use_cookies', $this->session['use_cookies']);
        ini_set('session.cache_limiter', $this->session['cache_limiter']);
    }
}
