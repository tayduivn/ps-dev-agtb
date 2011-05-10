<?php
//FILE SUGARCRM flav=pro ONLY
require_once('modules/Trackers/TrackerTestUtility.php');

class TrackerQueriesTest extends Sugar_PHPUnit_Framework_TestCase {

	var $dump_slow_queries;
	var $slow_query_time_msec;
	var $paused;
    var $query_hash = '7ada3a920b85e9d5718c273c5fd6c1b0';
    

    function setUp() {
        TrackerTestUtility::setUp(); 
		
    	$this->dump_slow_queries = isset($GLOBALS['sugar_config']['dump_slow_queries']) ? $GLOBALS['sugar_config']['dump_slow_queries'] : false;
    	$this->slow_query_time_msec = isset($GLOBALS['sugar_config']['slow_query_time_msec']) ? $GLOBALS['sugar_config']['slow_query_time_msec'] : 100;
    	
    	$trackerManager = TrackerManager::getInstance();
    	$this->paused = $trackerManager->isPaused();

    	$trackerManager->pause();
    	$query = "DELETE FROM tracker_queries WHERE query_hash = '{$this->query_hash}'";
    	$GLOBALS['db']->query($query);
    	$trackerManager->unPause();
    	
    	$GLOBALS['sugar_config']['dump_slow_queries'] = true;
        $GLOBALS['sugar_config']['slow_query_time_msec'] = 0; //force it to dump
    }
    
    function tearDown()
    {
    	TrackerTestUtility::tearDown(); 
    	
    	$GLOBALS['sugar_config']['dump_slow_queries'] = $this->dump_slow_queries;
    	$GLOBALS['sugar_config']['slow_query_time_msec'] = $this->slow_query_time_msec;
    	$trackerManager = TrackerManager::getInstance();
    	if($this->paused) {
    	   $trackerManager->pause();
    	}
    	
        $query = "DELETE FROM tracker_queries WHERE query_hash = '{$this->query_hash}'";
    	$GLOBALS['db']->query($query);    	
    }
    
    function test_track_slow_query() {
		
    	$trackerManager = TrackerManager::getInstance();
    	$disabledMonitors = $trackerManager->getDisabledMonitors();
    	$trackerManager->setDisabledMonitors(array());
    	$trackerManager->pause();
    	$result = $GLOBALS['db']->query("SELECT SUM(run_count) as total FROM tracker_queries");
    	$total = $GLOBALS['db']->fetchByAssoc($result);
    	$preRun = (int)$total['total'];    	
    	    	
    	$trackerManager->unPause();
		$result = $GLOBALS['db']->query("SELECT count(id) from tracker where action = 'save'");

		$mon = $trackerManager->getMonitor('tracker_queries');
        $trackerManager->saveMonitor($mon, true);

		$trackerManager->pause();
    	$result = $GLOBALS['db']->query("SELECT SUM(run_count) as total FROM tracker_queries");
    	$total = $GLOBALS['db']->fetchByAssoc($result);
    	$postRun = (int)$total['total'];
    	
    	//Check that count increased
    	$this->assertTrue($postRun > $preRun);

    	
    	$result = $GLOBALS['db']->query("SELECT id, run_count FROM tracker_queries WHERE query_hash = '{$this->query_hash}'");	
    	$stuff = $GLOBALS['db']->fetchByAssoc($result);
    	//Check that this query is in there
    	$this->assertTrue($stuff['run_count'] == 1);
    	
    	
    	$trackerManager->unPause();
		$result = $GLOBALS['db']->query("SELECT count(id) from tracker where action = 'save'"); 
		
		$mon = $trackerManager->getMonitor('tracker_queries');
		$trackerManager->saveMonitor($mon, true);
		        
		$trackerManager->pause();
		$result = $GLOBALS['db']->query("SELECT id, run_count FROM tracker_queries WHERE query_hash = '{$this->query_hash}'");	
    	$stuff = $GLOBALS['db']->fetchByAssoc($result);
    	//Check that this query is in there
    	$this->assertTrue($stuff['run_count'] == 2);	

    	$trackerManager->setDisabledMonitors($disabledMonitors);
    }

}  
?>