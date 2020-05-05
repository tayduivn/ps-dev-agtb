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


/**
 * Bug #43339
 * get_entries_count doesn't work with custom fields
 *
 * @ticket 43339
 */
class GetEntriesCustomTest extends SOAPTestCase
{
    private $_module = null;
    private $_moduleName = 'Contacts';
    private $_customFieldName = 'test_custom_c';
    private $_field = null;
    private $_df = null;

    protected $session = [];

    protected function setUp() : void
    {
        parent::setUp();

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        $_SERVER['REMOTE_ADDR'] = '0.0.0.0';

        require_once 'modules/DynamicFields/FieldCases.php';
        $this->_field = get_widget('varchar');
        $this->_field->id = $this->_moduleName . $this->_customFieldName;
        $this->_field->name = $this->_customFieldName;
        $this->_field->vanme = 'LBL_' . strtoupper($this->_customFieldName);
        $this->_field->comments = null;
        $this->_field->help = null;
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
        $this->_field->ext1 = null;
        $this->_field->ext2 = null;
        $this->_field->ext3 = null;
        $this->_field->ext4 = null;

        global $beanList, $beanFiles;

        $className = $beanList[$this->_moduleName];
        require_once $beanFiles[$className];
        $this->_module = new $className();

        $this->_df = new DynamicField($this->_moduleName);

        $this->_df->setup($this->_module);
        $this->_df->addFieldObject($this->_field);
    }

    /**
     * Test for soap/SoapSugarUsers.php::get_entries_count()
     *
     * @group 43339
     */
    public function testGetEntriesCountFromBasicSoap()
    {
        $this->_login();
        $params = [
            'session' => $this->_sessionId,
            'module_name' => $this->_moduleName,
            'query' => $this->_customFieldName . ' LIKE \'\'',
            'deleted' => 0,
        ];
        $actual = $this->_soapClient->call('get_entries_count', $params);

        $this->assertNotSame(null, $actual['result_count'], 'Null value returned by get_entries_count.');
    }

    protected function tearDown() : void
    {
        $this->_df->deleteField($this->_field);

        SugarTestHelper::tearDown();
        unset($_SERVER['REMOTE_ADDR']);

        $this->_tearDownTestUser();
    }
}
