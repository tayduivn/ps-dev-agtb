<?php

require_once('custom/include/SugarAlerts/SugarAlerts.php');

class SugarAlertsOppKeyDealFlagChanged extends SugarAlerts {

	private $formatted_amount = null;

	public static function getAlertTitle() {
		return 'Opportunity Key Deal changed';
	}
	
	private function getFormattedAmount(){
		if(!is_null($this->formatted_amount)){
			return $this->formatted_amount;
		}
		
		$curr_params = array(
			'currency_symbol' => true,
			'currency_id' => $this->bean->currency_id,
		);
		require_once('modules/Currencies/Currency.php');
		$this->formatted_amount = format_number($this->bean->amount, 0, 0, $curr_params);
		return $this->formatted_amount;
	}
	
	protected function generateDashletContent($links = true) {
		$amount = $this->getFormattedAmount();
		$now_no = ($this->bean->key_deal_c ? 'now' : 'no longer');

		$opp_name = $this->bean->name;
		if($links){
			$opp_name = "<a href=index.php?module=Opportunities&action=DetailView&record={$this->bean->id}>{$opp_name}</a>";
		}
		
		$content = "Opportunity {$opp_name} - {$this->bean->description} with a value of {$amount} is {$now_no} a key deal";
		
		return $content;
	}
	
	protected function generateCubeContent() {
		return $this->generateDashletContent();
	}

	protected function generateEmailContent() {
		$subject = $this->generateDashletContent(false);
		
		$key_deal = ($this->bean->key_deal_c ? 'Yes' : 'No');
		$amount = $this->getFormattedAmount();
		
		$body_lines[] = "Opportunity Number: {$this->bean->name}";
		$body_lines[] = "Opportunity Description: {$this->bean->description}";
		$body_lines[] = "Opportunity Total Revenue: {$amount}";
		$body_lines[] = "Key Deal: {$key_deal}";
		$body_lines[] = "Updated by: {$GLOBALS['current_user']->first_name} {$GLOBALS['current_user']->last_name}";
		$body_lines[] = "";
		$body_lines[] = "{$GLOBALS['sugar_config']['site_url']}/index.php?module=Opportunities&action=DetailView&record={$this->bean->id}";
		
		$body = implode("\n", $body_lines);
		
		return array('subject' => $subject, 'body' => $body);
	}
}
