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

require_once 'vendor/nusoap//nusoap.php';


/**
 * @group bug40250
 */
class GetEntriesCountTest extends SOAPTestCase
{
    /**
     * Create test user
     */
    protected function setUp() : void
    {
        parent::setUp();
    }

    public function testRetrieveUsersList()
    {
        $this->_login();
        //First retrieve the users count (should be at least 1)
        // 20110707 Frank Steegmans: DB2 by default is case sensitive. Note http://www.db2ude.com/?q=node/79
        $countArr  = $this->_soapClient->call('get_entries_count', ['session'=>$this->_sessionId,'module_name'=>'Users','query'=>" users.status = 'Active' ",0]);
        $count = $countArr['result_count'];
        $this->assertGreaterThanOrEqual(1, $count, 'no users were retrieved so the test user was not set up correctly');

        //now retrieve the list of users
        $usersArr =   $this->_soapClient->call('get_entry_list', ['session'=>$this->_sessionId,'module_name'=>'Users','query'=>" users.status = 'Active' ", 'user_name','0'  ,'select_field'=>['user_name'], [], 10000,0]);
        $usersCount = $usersArr['result_count'];

        //the count from both functions should be the same
        $this->assertEquals($count, $usersCount, 'count is not the same which means that the 2 calls are generating different results.');
    }
}
