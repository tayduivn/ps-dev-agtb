<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// updates an Opportunity's probability based on sales stage
// sales stage -> probability mapping stored in custom/si_custom_files/meta/OpportunitiesSalesStageConfig.php

class updateProbability  {
	function update(&$bean, $event, $arguments) {
		require_once("custom/si_custom_files/meta/OpportunitiesSalesStageConfig.php");
		if (isset($sales_stage_map[$bean->sales_stage])) {
			$bean->probability = $sales_stage_map[$bean->sales_stage];
		}
	}
}
