<?php
//File SUGARCRM flav=pro ONLY
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
     * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
     ********************************************************************************/

require_once 'tests/SugarTestDatabaseMock.php';
require_once 'modules/Audit/Audit.php';

class AuditTest extends Sugar_PHPUnit_Framework_TestCase
{

    protected $bean =null;

    static public $db;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        self::$db = new SugarTestDatabaseMock();
        self::$db->setUp();
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        self::$db->tearDown();
        SugarTestHelper::tearDown();
    }


    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');
        

        $this->bean = BeanFactory::getBean('Leads');
        $this->bean->name = 'Test';
        $this->bean->id = '1';
    }

    public function tearDown()
    {
        unset($this->bean);
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testGetAuditLog() 
    {
        global $timedate;
        $auditTable = $this->bean->get_audit_table_name();
        $dateCreated = date('Y-m-d H:i:s');
        self::$db->queries['auditQuery'] =  array(
                                    'match' => '/[' . $auditTable . ']/', 
                                    'rows' => array(
                                                    array(
                                                        'field_name' => 'name',
                                                        'date_created' => $dateCreated,
                                                        'before_value_string' => 'Test',
                                                        'after_value_string' => 'Awesome',
                                                        'before_value_text' => '',
                                                        'after_value_text' => '',
                                                        ),
                                                    ),
                                    );
        $audit = BeanFactory::getBean('Audit');
        $data = $audit->getAuditLog($this->bean);
        $dateCreated = $timedate->fromDbType($dateCreated, "datetime");
        $expectedDateCreated = $timedate->asIso($dateCreated);
        $expected = array(
                0 => array(
                    'field_name' => 'name',
                    'date_created' => $expectedDateCreated,
                    'after' => 'Awesome',
                    'before' => 'Test',
                ),
            );

        $this->assertEquals($expected, $data, "Expected Result was incorrect");
    }

}
