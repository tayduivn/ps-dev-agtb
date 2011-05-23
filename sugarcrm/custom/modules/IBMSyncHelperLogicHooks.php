<?php 

class IBMSyncHelperLogicHooks {
	
	
	// START jvink - call IBMSyncHelper 
	function IBMSyncHelper(&$focus, $event, $arguments) {
		require_once('custom/IBMSyncHelper.php');
		$sync = IBMSyncHelper::getInstance();
		if(!$sync->isInit) {
			$sync->init($focus);
			$sync->execute();
		}
	}
	// END jvink
}

?>