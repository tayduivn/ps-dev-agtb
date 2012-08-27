<?php
//FILE SUGARCRM flav=pro ONLY
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
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

    private $prevMinCronInterval;

    private $account;

    public function setUp()
    {
        if(empty($GLOBALS['db']) || !($GLOBALS['db'] instanceOf DBManager))
        {
            $GLOBALS['db'] = DBManagerFactory::getInstance();
        }

        //This is a bit extreme to do here so we may need to revisit and just index test accounts/contacts
        $GLOBALS['db']->query("DELETE FROM accounts");
        $GLOBALS['db']->query("DELETE FROM contacts");

        $beanList = array();
		$beanFiles = array();
		require('include/modules.php');
		$GLOBALS['beanList'] = $beanList;
		$GLOBALS['beanFiles'] = $beanFiles;

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->account = SugarTestAccountUtilities::createAccount();
        SugarTestContactUtilities::createContact();

        if(empty($this->_db))
            $this->_db = DBManagerFactory::getInstance();

        $this->prevMinCronInterval = isset($GLOBALS['sugar_config']['cron']['min_cron_interval']) ? $GLOBALS['sugar_config']['cron']['min_cron_interval'] : 0;
        $GLOBALS['sugar_config']['cron']['min_cron_interval'] = 0;
        $this->engine = SugarSearchEngineFactory::getInstance('Elastic');
        $this->indexer = new TestSugarSearchEngineFullIndexer($this->engine);
    }

    public function tearDown()
    {
        $GLOBALS['sugar_config']['cron']['min_cron_interval'] = $this->prevMinCronInterval;
        $GLOBALS['db'] = DBManagerFactory::getInstance();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        $this->_db->query("DELETE FROM {$this->indexer->table_name}");
        $jobQueue = BeanFactory::getBean('SchedulersJobs', null);
        $this->_db->query("DELETE FROM {$jobQueue->table_name} WHERE name like 'FTSConsumer%' ");

        unset($GLOBALS['current_user']);
    }

    /**
     * Ensure a record is added to the queue
     *
     */
    public function testFTSPopulateFullQueue()
    {
        $this->indexer->initiateFTSIndexer();
        $accountID = $this->account->id;
        $actualID = $this->recordExistInQueue($accountID);
        $this->assertEquals($accountID, $actualID);
    }


    /**
     * Ensure a record is added to the queue
     *
     */
    public function testEnsureFTSConsumerCreated()
    {
        $this->indexer->initiateFTSIndexer(array('Accounts','Contacts'));
        $jobQueue = BeanFactory::getBean('SchedulersJobs', null);
        $jobName = "FTSConsumer Accounts";
        $loadedJobs = $jobQueue->retrieve_by_string_fields(array('name'=>'FTSConsumer Accounts'));
        $this->assertEquals($jobName, $loadedJobs->name);
    }


    /**
     * Ensure the queue is cleared
     *
     */
    public function testClearFTSQueue()
    {
        $this->indexer->initiateFTSIndexer(array('Accounts'));
        $this->indexer->clearFTSIndexQueueStub();
        $query = "SELECT bean_id FROM {$this->indexer->table_name}";
        $recordExists = $this->_db->getOne($query);
        $this->assertFalse($recordExists, "Unable to clean fts queue");
    }

    /**
     * Test create consumers for a module
     *
     */
    public function testCreateFTSConsumers()
    {
        $moduleName = 'Leads';
        $jobID = $this->indexer->createJobQueueConsumerForModule($moduleName);
        $jobBean = BeanFactory::getBean('SchedulersJobs',$jobID);
        $this->assertEquals("class::SugarSearchEngineFullIndexer", $jobBean->target);
        $this->assertEquals($moduleName, $jobBean->data);
    }

    /**
     * Ensure consumers are cleared out
     */
    public function testRemoveExistinFTSConsumers()
    {
        $this->indexer->initiateFTSIndexer(array('Accounts'));
        $this->indexer->removeExistingFTSConsumersStub();

        $jobBean = BeanFactory::getBean('SchedulersJobs');
        $query = "SELECT id FROM {$jobBean->table_name} WHERE name like 'FTSConsumer%' AND deleted = 0";
        $recordExists = $this->_db->getOne($query);
        $this->assertFalse($recordExists, "Unable to clean fts consumers");
    }


    /**
     * Ensure a record is not added to the queue
     *
     */
    public function testFTSDoNotPopulateQueue()
    {
        $this->indexer->initiateFTSIndexer(array('Contacts'));

        $ids = SugarTestAccountUtilities::getCreatedAccountIds();
        $accountID = $ids[0];
        $actualID = $this->recordExistInQueue($accountID);
        $this->assertNotEquals($accountID, $actualID);
    }

    public function testConsumerRuns()
    {
        $this->markTestIncomplete('Marking this skipped.');
        $jobBean = BeanFactory::getBean('SchedulersJobs');
        //Mock object for SSEngine
        $SSEngine = $this->getMock('SugarSearchEngineElastic');
        $SSEngine->expects($this->once())->method('createIndexDocument');
        $SSEngine->expects($this->once())->method('bulkInsert');

        $indexer = new TestSugarSearchEngineFullIndexer($SSEngine);
        $indexer->setJob($jobBean);
        $indexer->initiateFTSIndexer(array('Accounts'));
        $indexer->run('Accounts');
    }

    public function testConsumerRunsIndexByBean()
    {
        $this->markTestIncomplete('Marking this skipped.');
        $jobBean = BeanFactory::getBean('SchedulersJobs');
        //Mock object for SSEngine
        $SSEngine = $this->getMock('SugarSearchEngineElastic');
        $SSEngine->expects($this->once())->method('createIndexDocument');
        $SSEngine->expects($this->once())->method('bulkInsert');

        $indexer = new TestSugarSearchEngineFullIndexer($SSEngine);
        $indexer->setJob($jobBean);
        $indexer->setShouldIndexViaBean(TRUE);
        $indexer->initiateFTSIndexer(array('Accounts'));
        $indexer->run('Accounts');
    }

    public function testIsFTSIndexScheduleCompleted()
    {
        $this->markTestIncomplete();
        $this->assertFalse($this->indexer->isFTSIndexScheduleCompleted());
        $this->indexer->performFullSystemIndex();
        $this->assertTrue($this->indexer->isFTSIndexScheduleCompleted());
    }

    public function testGetStatistics()
    {
        $this->markTestIncomplete('Marking this skipped.');
        $this->indexer->performFullSystemIndex();
        $stats = $this->indexer->getStatistics();
        $this->assertEquals(1, $stats['Accounts']['count'], "Failed to retrieve account statistic");
        $this->assertEquals(1, $stats['Contacts']['count'], "Failed to retrieve contact statistic");
        $this->assertArrayHasKey('count', $stats);
        $this->assertArrayHasKey('time', $stats);
    }

    public function markBeansProvider()
    {
        return array(
            array(range(0,2999), 1),
            array(range(0,3002), 2),
            array(range(0,1), 1),
            array(array(), 0),
            array(range(0,101), 1)
        );
    }
    /**
    * @dataProvider markBeansProvider
    */
    public function testMarkBeansProcessed($ids, $expected)
    {
        $GLOBALS['db'] = DBManagerFactory::getInstance();
        $DBManagerClass = get_class($GLOBALS['db']);
        $db = $this->getMock($DBManagerClass);
        $db->expects($this->exactly($expected))->method('query');
        $GLOBALS['db'] = $db;
        $indexer = new TestSugarSearchEngineFullIndexer();
        $indexer->markBeansProcessedStub($ids);
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


class TestSugarSearchEngineFullIndexer extends SugarSearchEngineFullIndexer
{
    const POSTPONE_JOB_TIME = 0;

    private $shouldIndexViaBean;

    public function markBeansProcessedStub($ids)
    {
        $this->markBeansProcessed($ids);
    }

    public function setEngine($engine)
    {
        $this->SSEngine = $engine;
    }

    public function getEngine()
    {
        return $this->SSEngine;
    }

    public function clearFTSIndexQueueStub()
    {
        // this should call $this->clearFTSIndexQueue but
        // it fails on db2 as not all versions of db2
        // support TRUNCATE TABLE command
        //$this->clearFTSIndexQueue();
        $GLOBALS['db']->query('DELETE FROM fts_queue');
    }

    public function removeExistingFTSConsumersStub()
    {
        $this->removeExistingFTSConsumers();
    }

    public function setShouldIndexViaBean($should)
    {
        $this->shouldIndexViaBean = $should;
    }

    public function shouldIndexViaBean($module)
    {
        if(isset($this->shouldIndexViaBean))
            return $this->shouldIndexViaBean;
        else
            return parent::shouldIndexViaBean($module);
    }
}