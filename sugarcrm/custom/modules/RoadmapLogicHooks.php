<?php

class RoadmapLogicHooks {
	function setAccount(&$focus, $event, $arguments) {
		if($event == 'before_save'){
			if(!empty($focus->opportunity_id_c)){
				$ores = $GLOBALS['db']->query("SELECT account_id FROM accounts_opportunities WHERE opportunity_id = '{$focus->opportunity_id_c}' AND deleted = 0");
				$orow = $GLOBALS['db']->fetchByAssoc($ores);
				$focus->account_id_c = empty($orow['account_id']) ? "" : $orow['account_id'];
			}
		}
	}
}

