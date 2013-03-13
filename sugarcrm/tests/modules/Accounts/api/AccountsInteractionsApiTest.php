<?php

//FILE SUGARCRM flav=pro ONLY
/* * *******************************************************************************
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
 * ****************************************************************************** */

require_once('tests/rest/RestTestBase.php');

/**
 * Used to test interactions in account module
 * 
 * @group accountapi
 * @group accounts
 */
class AccountsInteractionsApiTest extends RestTestBase
{

    /**
     * Account
     * 
     * @var Account
     */
    protected $account;

    /**
     * Calls.
     * 
     * @var array
     */
    protected $calls = array();

    /**
     * Meetings.
     * 
     * @var array
     */
    protected $meetings = array();

    /**
     * Emails which sent.
     * 
     * @var array
     */
    protected $emailsSent = array();

    /**
     * Emails which recv.
     * 
     * @var array
     */
    protected $emailsRecv = array();

    /**
     * Root data keys.
     * 
     * @var array
     */
    protected $dataKeys = array('calls', 'meetings', 'emailsSent', 'emailsRecv');

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->account = SugarTestAccountUtilities::createAccount();

        // creating calls
        for ($i = 0; $i < 5; $i++) {
            $call = SugarTestCallUtilities::createCall();
            $call->status = 'Held';
            $call->parent_id = $this->account->id;
            $call->parent_type = 'Accounts';
            $call->save();

            $this->calls[] = $call;
        }

        // creating meetings
        for ($i = 0; $i < 4; $i++) {
            $meeting = SugarTestMeetingUtilities::createMeeting();
            $meeting->status = 'Held';
            $meeting->parent_id = $this->account->id;
            $meeting->parent_type = 'Accounts';
            $meeting->save();

            $this->meetings[] = $meeting;
        }

        // creating emails which was sent.
        for ($i = 0; $i < 3; $i++) {
            $email = SugarTestEmailUtilities::createEmail('', array(
                        'parent_id' => $this->account->id,
                        'parent_type' => 'Accounts',
                        'type' => 'out',
            ));
            $this->emailsSent[] = $email;
        }

        // creating emails which was recv.
        for ($i = 0; $i < 3; $i++) {
            $email = SugarTestEmailUtilities::createEmail('', array(
                        'parent_id' => $this->account->id,
                        'parent_type' => 'Accounts',
                        'type' => 'inbound',
            ));
            $this->emailsRecv[] = $email;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        parent::tearDown();

        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    /**
     * Build REST uri.
     * 
     * @return string
     */
    protected function buildRestUri()
    {
        return "Accounts/{$this->account->id}/interactions";
    }

    /**
     * Validate response.
     * 
     * @param mixed $response
     */
    protected function validateResponse($response)
    {
        $this->assertNotEmpty($response["reply"]);
        $this->assertInternalType('array', $response['reply']);

        foreach ($this->dataKeys as $dataKey) {
            $this->assertArrayHasKey($dataKey, $response['reply']);
            $this->assertInternalType('array', $response['reply'][$dataKey]);

            /**
             * Check structure for given data key.
             */
            $this->assertArrayHasKey('count', $response['reply'][$dataKey]);
            $this->assertArrayHasKey('data', $response['reply'][$dataKey]);

            /**
             * Verify types of given data key.
             */
            $this->assertInternalType('integer', $response['reply'][$dataKey]['count']);
            $this->assertInternalType('array', $response['reply'][$dataKey]['data']);
            /**
             * Count & count of data must be equals.
             */
            $this->assertEquals($response['reply'][$dataKey]['count'], count($response['reply'][$dataKey]['data']));
        }
    }

    /**
     * Test account interactions service is avaliable & access.
     * 
     * @group accountapi
     * @group accounts
     */
    public function testInterations()
    {
        $response = $this->_restCall($this->buildRestUri());

        $this->validateResponse($response);

        $this->assertEquals(count($this->calls), $response['reply']['calls']['count']);
        $this->assertEquals(count($this->meetings), $response['reply']['meetings']['count']);
        $this->assertEquals(count($this->emailsRecv), $response['reply']['emailsRecv']['count']);
        $this->assertEquals(count($this->emailsSent), $response['reply']['emailsSent']['count']);
    }

    /**
     * Test limit
     * 
     * @group accountapi
     * @group accounts
     */
    public function testLimit()
    {
        foreach (array(5, 10) as $limit) {
            foreach ($this->dataKeys as $dataKey) {
                $response = $this->_restCall($this->buildRestUri(), array(
                    'limit' => $limit,
                    'view' => $dataKey,
                        ), 'GET');

                $this->validateResponse($response);

                /**
                 * Count & data must be equals or less then limit.
                 */
                $this->assertLessThanOrEqual($limit, $response['reply'][$dataKey]['count'], $dataKey);
                $this->assertLessThanOrEqual($limit, count($response['reply'][$dataKey]['data']), $dataKey);
                /**
                 * Count & count of data must be equals.
                 */
                $this->assertEquals($response['reply'][$dataKey]['count'], count($response['reply'][$dataKey]['data']));
            }
        }
    }

    /**
     * Test limit if limit is zero.
     * 
     * @group accountapi
     * @group accounts
     */
    public function testLimitIfZero()
    {
        foreach ($this->dataKeys as $dataKey) {
            $response = $this->_restCall($this->buildRestUri(), array(
                'limit' => 0,
                'view' => $dataKey,
                    ), 'GET');

            $this->validateResponse($response);
        }
    }

    /**
     * Test limit
     * 
     * @group accountapi
     * @group accounts
     */
    public function testLimitIfLessThenZero()
    {
        foreach ($this->dataKeys as $dataKey) {
            $response = $this->_restCall($this->buildRestUri(), array(
                'limit' => -10,
                'view' => $dataKey,
                    ), 'GET');

            $this->validateResponse($response);
        }
    }

    /**
     * Test list type filter
     * 
     * @group accountapi
     * @group accounts
     */
    public function testListTypeFilter()
    {
        foreach (array('me', 'all') as $listType) {
            $response = $this->_restCall($this->buildRestUri(), array(
                'list' => $listType,
                    ), 'GET');

            $this->validateResponse($response);
        }
    }

    /**
     * Test base filter as last date
     * 
     * @group accountapi
     * @group accounts
     */
    public function testBaseFilterAsLastDate()
    {
        foreach (array(7, 14, 30, 180) as $filter) {
            $response = $this->_restCall($this->buildRestUri(), array(
                'filter' => $filter,
                    ), 'GET');

            $this->validateResponse($response);
        }
    }

    /**
     * Test base filter as favorites
     * 
     * @group accountapi
     * @group accounts
     */
    public function testBaseFilterAsFavorites()
    {
        $response = $this->_restCall($this->buildRestUri(), array(
            'filter' => 'favorites',
                ), 'GET');

        $this->validateResponse($response);
    }

}

