<?php

require_once('custom/include/SugarAlerts/SugarAlerts.php');

class SugarAlertsNewRevLineItem extends SugarAlerts {

	private $level = null;
	private $opp_info = null;

	public static function getAlertTitle() {
		return 'New Revenue Line Item created';
	}
	
	private function getLevel(){
		if(!is_null($this->level)){
			return $this->level;
		}
		
		$prod_arr = array($this->bean->sub_brand_c);
		$result = IBMHelper::getProductValuesFromKeys($prod_arr);
		$level = $result[$this->bean->sub_brand_c];
		
		$this->level = $level;
		return $level;
	}
	
	private function getOppInfo(){
		if(!is_null($this->opp_info)){
			return $this->opp_info;
		}
		
		$q_q = "SELECT opp.id, opp.name, opp.description FROM ibm_revenuepportunities_c rli_join ".
				"INNER JOIN opportunities opp ON rli_join.ibm_revenud375unities_ida = opp.id ".
				"WHERE rli_join.ibm_revenu04e3neitems_idb = '{$this->bean->id}' AND opp.deleted = 0 AND rli_join.deleted = 0";
		$res = $GLOBALS['db']->query($q_q);
		$row = $GLOBALS['db']->fetchByAssoc($res);
		$opp_info = array('id' => '', 'name' => '', 'description' => '');
		if(!empty($row['id'])){
			$opp_info = array('id' => $row['id'], 'name' => $row['name'], 'description' => $row['description']);
		}
		
		$this->opp_info = $opp_info;
		return $opp_info;
	}
	
	protected function generateDashletContent($links = true) {
		$level = $this->getLevel();
		if($links){
			$level = "<a href=index.php?module=ibm_revenueLineItems&action=EditView&record={$this->bean->id}>{$level}</a>";
		}
		
		$opp_info = $this->getOppInfo();
		$opp_id = $opp_info['id'];
		$opp_description = $opp_info['description'];
		$opp_name = $opp_info['name'];
		if($links){
			$opp_name = "<a href=index.php?module=Opportunities&action=DetailView&record={$opp_id}>{$opp_name}</a>";
		}
		
		$user = "{$GLOBALS['current_user']->first_name} {$GLOBALS['current_user']->last_name}";
		if($links){
			$user = "<a href=index.php?module=Employees&action=DetailView&record={$GLOBALS['current_user']->id}>{$user}</a>";
		}
		
		$ret = "{$user} has added a(n) {$level} line item to Opportunity {$opp_name} - {$opp_description}";
		return $ret;
	}
	
	protected function generateCubeContent() {
		return $this->generateDashletContent();
	}

	protected function generateEmailContent() {
		$subject = $this->generateDashletContent(false);
		
		$level = $this->getLevel();
		$opp_info = $this->getOppInfo();
		$opp_id = $opp_info['id'];
		$opp_name = $opp_info['name'];
		$opp_description = $opp_info['description'];
		
		$body_lines[] = "Created By: {$GLOBALS['current_user']->first_name} {$GLOBALS['current_user']->last_name}";
		$body_lines[] = "Sub Brand: {$level}";
		$body_lines[] = "Opportunity Number: {$opp_name}";
		$body_lines[] = "Opportunity Description: {$opp_description}";
		$body_lines[] = "";
		$body_lines[] = "{$GLOBALS['sugar_config']['site_url']}/index.php?module=Opportunities&action=DetailView&record={$opp_id}";
		
		$body = implode("\n", $body_lines);
		
		return array('subject' => $subject, 'body' => $body);
	}
	
	// supported by the parent SugarAlerts class. If defined, it returns an array with a list of user ids to filter
	protected function customUserFilter(){
		$q_q = "SELECT opp_users.user_id FROM ibm_revenuepportunities_c rli_join ".
				"INNER JOIN opportunities opp ON rli_join.ibm_revenud375unities_ida = opp.id ".
				"INNER JOIN opportunities_users opp_users ON opp.id = opp_users.opportunity_id and opp_users.deleted = 0 ".
				"WHERE rli_join.ibm_revenu04e3neitems_idb = '{$this->bean->id}' AND opp.deleted = 0 AND rli_join.deleted = 0".
				"  AND opp_users.user_role = '4'";
		$res = $GLOBALS['db']->query($q_q);
		$users_arr = array();
		while($row = $GLOBALS['db']->fetchByAssoc($res)){
			$users_arr[$row['user_id']] = $row['user_id'];
		}
		
		return $users_arr;
	}
}
