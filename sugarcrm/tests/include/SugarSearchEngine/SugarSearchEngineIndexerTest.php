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

require_once 'include/SugarSearchEngine/SugarSearchEngineFullIndexer.php';


class SugarSearchIndexerTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var db
     */
    private $_db;

    /**
     * @var engine
     */
    private $engine;

    /**
     * @var indexer
     */
    private $indexer;

    public function setUp()
    {
        SugarTestAccountUtilities::createAccount();
        SugarTestContactUtilities::createContact();

        if(empty($this->_db))
            $this->_db = DBManagerFactory::getInstance();

        $this->engine = SugarSearchEngineFactory::getInstance('elastic');
        $this->indexer = new SugarSearchEngineFullIndexer($this->engine);
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        $this->_db->query("TRUNCATE table {$this->indexer->table_name}");
        $jobQueue = BeanFactory::getBean('SchedulersJobs', null);
        $this->_db->query("DELETE FROM {$jobQueue->table_name} WHERE name like 'FTSConsumer%' ");

    }

    /**
     * Ensure a record is added to the queue
     *
     */
    public function testFTSPopulateFullQueue()
    {
        $this->indexer->populateIndexQueue();

        $ids = SugarTestAccountUtilities::getCreatedAccountIds();
        $accountID = $ids[0];
        $actualID = $this->recordExistInQueue($accountID);
        $this->assertEquals($accountID, $actualID);
    }


    /**
     * Ensure a record is added to the queue
     *
     */
    public function testEnsureFTSConsumerCreated()
    {
        $this->indexer->populateIndexQueue(array('Accounts','Contacts'));
        $jobQueue = BeanFactory::getBean('SchedulersJobs', null);
        $jobName = "FTSConsumer Accounts";
        $loadedJobs = $jobQueue->retrieve_by_string_fields(array('name'=>'FTSConsumer Accounts'));
        $this->assertEquals($jobName, $loadedJobs->name);
    }


    /**
     * Ensure a record is not added to the queue
     *
     */
    public function testFTSDoNotPopulateQueue()
    {
        $this->indexer->populateIndexQueue(array('Contacts'));

        $ids = SugarTestAccountUtilities::getCreatedAccountIds();
        $accountID = $ids[0];
        $actualID = $this->recordExistInQueue($accountID);
        $this->assertNotEquals($accountID, $actualID);
    }

    /**
     * Helper function to see if a record is in the queue
     *
     * @param $record_id
     * @return mixed
     */
    protected function recordExistInQueue($record_id)
    {
        $query = "SELECT bean_id FROM {$this->indexer->table_name} WHERE bean_id='$record_id'";
        return $this->_db->getOne($query);
    }
}