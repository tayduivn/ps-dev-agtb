<?php

require_once('custom/include/SugarAlerts/SugarAlerts.php');

class SugarAlertsNewOpportunity extends SugarAlerts {

	private $account_info = null;

	public static function getAlertTitle() {
		return 'New Opportunity created';
	}
	
	private function getAccountName(){
		if(!is_null($this->account_info)){
			return $this->account_info;
		}
		
		$q_q = "SELECT accounts.id, accounts.name FROM accounts_opportunities acc_opp ".
				"INNER JOIN accounts ON acc_opp.account_id = accounts.id AND acc_opp.deleted = 0 ".
				"WHERE acc_opp.opportunity_id = '{$this->bean->id}' AND accounts.deleted = 0";
		$res = $GLOBALS['db']->query($q_q);
		$row = $GLOBALS['db']->fetchByAssoc($res);
		$account_info = array('id' => '', 'name' => '');
		if(!empty($row['name'])){
			$account_info = array('id' => $row['id'], 'name' => $row['name']);
		}
		
		$this->account_info = $account_info;
		return $account_info;
	}
	
	
	protected function generateDashletContent($links = true) {
		$account_info = $this->getAccountName();
		
		$account_name = $account_info['name'];
		if($links){
			$account_name = "<a href=index.php?module=Accounts&action=DetailView&record={$account_info['id']}>{$account_info['name']}</a>";
		}
		
		$opp_name = $this->bean->name;
		if($links){
			$opp_name = "<a href=index.php?module=Opportunities&action=DetailView&record={$this->bean->id}>{$this->bean->name}</a>";
		}
		
		$user = "{$GLOBALS['current_user']->first_name} {$GLOBALS['current_user']->last_name}";
		if($links){
			$user = "<a href=index.php?module=Employees&action=DetailView&record={$GLOBALS['current_user']->id}>{$user}</a>";
		}

		$ret = "{$user} has created an Opportunity {$opp_name} for {$account_name}";
		return $ret;
	}
	
	protected function generateCubeContent() {
		return $this->generateDashletContent();
	}

	protected function generateEmailContent() {
		$subject = $this->generateDashletContent(false);
		
		$account_info = $this->getAccountName();
		
		$body_lines[] = "Created By: {$GLOBALS['current_user']->first_name} {$GLOBALS['current_user']->last_name}";
		$body_lines[] = "Opportunity Number: {$this->bean->name}";
		$body_lines[] = "Opportunity Description: {$this->bean->description}";
		$body_lines[] = "Opportunity Source: {$this->bean->lead_source}";
		$body_lines[] = "Competitor: {$this->bean->competitor_c}";
		$body_lines[] = "Account Name: {$account_info['name']}";
		$body_lines[] = "";
		$body_lines[] = "{$GLOBALS['sugar_config']['site_url']}/index.php?module=Opportunities&action=DetailView&record={$this->bean->id}";
		
		$body = implode("\n", $body_lines);
		
		return array('subject' => $subject, 'body' => $body);
	}
	
	// supported by the parent SugarAlerts class. If defined, it returns an array with a list of user ids to filter
	protected function customUserFilter(){
		$q_q = "SELECT acc_users.user_id FROM accounts_opportunities acc_opp ".
				"INNER JOIN accounts ON acc_opp.account_id = accounts.id AND acc_opp.deleted = 0 ".
				"INNER JOIN accounts_users acc_users ON accounts.id = acc_users.account_id and acc_users.deleted = 0 ".
				"WHERE acc_opp.opportunity_id = '{$this->bean->id}' AND accounts.deleted = 0";
		$res = $GLOBALS['db']->query($q_q);
		$users_arr = array();
		while($row = $GLOBALS['db']->fetchByAssoc($res)){
			$users_arr[$row['user_id']] = $row['user_id'];
		}
		
		return $users_arr;
	}
}
