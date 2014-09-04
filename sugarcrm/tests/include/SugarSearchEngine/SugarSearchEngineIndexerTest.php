<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */


require_once 'include/SugarSearchEngine/SugarSearchEngineFullIndexer.php';

class SugarSearchEngineIndexerTest extends Sugar_PHPUnit_Framework_TestCase
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
        SugarTestHelper::setUp('app_list_strings');
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
        $GLOBALS['db']->query("DELETE FROM {$this->indexer->table_name}");
        $jobQueue = BeanFactory::getBean('SchedulersJobs', null);
        $GLOBALS['db']->query("DELETE FROM {$jobQueue->table_name} WHERE name like 'FTSConsumer%' ");

        unset($GLOBALS['current_user']);
    }

    /**
     * Ensure a record is added to the queue
     *
     */
    public function testFTSPopulateFullQueue()
    {
        $this->indexer->initiateFTSIndexer(array('Accounts'));
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


    /**
     * testPopulateIndexQueueForModule()
     *
     * Tests to see if the correct number of records are added to fts_queue table.
     */
    public function testPopulateIndexQueueForModule()
    {
        // select a module
        $module = 'Accounts';
        $beanName = BeanFactory::getBeanName($module);
        // get the number of records for this bean type currently in fts_queue.
        $countFTS_SQL = "select count(bean_module) as total from fts_queue where bean_module = '$beanName'";
        $ftsRowBefore = $this->_db->getOne($countFTS_SQL);

        // get the count of beans of this module.
        $countBean_SQL = "select count(id) as total from {$this->account->table_name} where deleted = 0";
        $beanCount = $this->_db->getOne($countBean_SQL);
        
        // queue the module
        $populateResult = $this->indexer->populateIndexQueueForModule($module);

        // assert that the populateIndexQueueForModule() call returns 1
        $msg = "Expected populateIndexQueueForModule('$module') to return 1, but returned ";
        $msg .= var_export($populateResult, true);
        $this->assertEquals($populateResult, 1, $msg);

        // get a new count of records in fts_queue for this module.
        $ftsRowAfter = $this->_db->getOne($countFTS_SQL);

        // subtract the old total from the new total.
        $diff = $ftsRowAfter - $ftsRowBefore;

        // assert that difference is equal to count of beans for this module.
        $msg = "Expected populateIndexQueueForModule('$module') to add {$beanCount} ";
        $msg .= "entries to fts_queue, but added $diff.";
        $this->assertEquals($beanCount, $diff, $msg);
    }

}


class TestSugarSearchEngineFullIndexer extends SugarSearchEngineFullIndexer
{
    const POSTPONE_JOB_TIME = 0;

    private $shouldIndexViaBean;

    public $table_name;

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

    public function setDB($db)
    {
        $this->db = $db;
    }

    public function clearFTSIndexQueueStub()
    {
        // this should call $this->clearFTSIndexQueue but
        // it fails on db2 as not all versions of db2
        // support TRUNCATE TABLE command
        //$this->clearFTSIndexQueue();
        $GLOBALS['db']->query('DELETE FROM fts_queue');
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
