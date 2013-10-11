<?php

require_once 'include/SugarSearchEngine/SugarSearchEngineSyncIndexer.php';

/**
 * class SugarSearchEngineIndexerTest
 *
 * Class for testing the SugarSearchEngineSyncIndexer class.
 */
class SugarSearchEngineSyncIndexerTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var db
     */
    private $db;

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
        if (empty($GLOBALS['db']) || !($GLOBALS['db'] instanceOf DBManager)) {
            $GLOBALS['db'] = DBManagerFactory::getInstance();
        }
        
        if (empty($this->db)) {
            $this->db = DBManagerFactory::getInstance();
        }

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->account = SugarTestAccountUtilities::createAccount();
        
        /*
        $this->prevMinCronInterval = isset($GLOBALS['sugar_config']['cron']['min_cron_interval']) ? 
            $GLOBALS['sugar_config']['cron']['min_cron_interval'] 
            : 
            0;
        */
        
        if (isset($GLOBALS['sugar_config']['cron']['min_cron_interval'])) {
            $this->prevMinCronInterval = $GLOBALS['sugar_config']['cron']['min_cron_interval'];
        } else {
            $this->prevMinCronInterval = 0;
        }
        
        $GLOBALS['sugar_config']['cron']['min_cron_interval'] = 0;
        
        $this->engine = SugarSearchEngineFactory::getInstance('Elastic');
        $this->indexer = new TestSugarSearchEngineSyncIndexer($this->engine);
    }
    
    
    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        $GLOBALS['sugar_config']['cron']['min_cron_interval'] = $this->prevMinCronInterval;
        $GLOBALS['db'] = DBManagerFactory::getInstance();
        unset($GLOBALS['current_user']);
        $this->db->query("DELETE FROM {$this->indexer->getTableName()}");
        $jobQueue = BeanFactory::getBean('SchedulersJobs', null);
        $this->db->query("DELETE FROM {$jobQueue->table_name} WHERE name like 'FTSConsumer%' ");
    }
    
    
    public function deleteSyncConsumers()
    {
        $jobBean = BeanFactory::getBean('SchedulersJobs');
        $this->db->query("delete from {$jobBean->table_name} where name = 'FTSSyncConsumer'");
    }
    
    public function testIsJobQueueConsumerPresent()
    {
        // delete all sync consumers.
        $this->deleteSyncConsumers();
        
        // test for presence of sync consumer - should be false.
        $present = $this->indexer->isJobQueueConsumerPresent();
        $this->assertFalse($present, "Expected to find no Sync consumers, but found '$present'");
        
        // create a sync consumer.
        $this->indexer->createJobQueueConsumer();
        
        // test for presence of sync consumer = should be string id.
        $present = $this->indexer->isJobQueueConsumerPresent();
        $this->assertFalse(empty($present), "Expected to find a Sync consumer, but none are in the db.");
        
        // delete all sync consumers.
        $this->deleteSyncConsumers();
    }
    
    
    public function testCreateJobQueueConsumer()
    {
        $jobID = $this->indexer->createJobQueueConsumer();
        $this->assertFalse(empty($jobID), "Sync consumer create failed.");
    }
    
    
    public function testIndexRecords()
    {
        $module = 'Accounts';
        $fieldDefs = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsPerModule($module);
        $this->engine->indexBean($this->account, false);
        $indexedCount = $this->indexer->indexRecords($module, $fieldDefs);
        $this->assertTrue(($indexedCount > -1), 'Bulk indexing failed!');
    }
}


/**
 * class TestSugarSearchEngineSyncIndexer
 *
 * Wrapper class to access protected methods.
 */
class TestSugarSearchEngineSyncIndexer extends SugarSearchEngineSyncIndexer
{
    public function __construct()
    {
        return parent::__construct();
    }
    
    public function isJobQueueConsumerPresent()
    {
        return parent::isJobQueueConsumerPresent();
    }
    
    public function getTableName()
    {
        return $this->table_name;
    }
}
