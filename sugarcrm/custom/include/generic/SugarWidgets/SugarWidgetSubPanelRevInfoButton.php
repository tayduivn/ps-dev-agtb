<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/generic/SugarWidgets/SugarWidgetField.php');

class SugarWidgetSubPanelRevInfoButton extends SugarWidgetField
{
	function displayHeaderCell(&$layout_def)
	{
		return "&nbsp;";
	}
	
	function displayList(&$layout_def)
	{
		global $app_list_strings;
		
		$irli_mod_strings = return_module_language($GLOBALS['current_language'], 'ibm_revenueLineItems');
		
		if(empty($layout_def['fields']['PROBABILITY'])){
			$layout_def['fields']['PROBABILITY'] = '0%';
		}
		
		if(empty($layout_def['fields']['IGF_ODDS'])){
			$layout_def['fields']['IGF_ODDS'] = '0%';
		}
		
		$do_finance_fields = false;
		if($layout_def['fields']['OFFERING_TYPE'] == 'B2000'){ // Global Finance
			$do_finance_fields = true;
		}
		
		require_once('modules/Currencies/Currency.php');
		$financed_revenue_display = currency_format_number($layout_def['fields']['FINANCED_REVENUE_AMOUNT'] , array('currency_id' => $layout_def['fields']['FRA_CURRENCY_ID'], 'symbol_space' => true));
		
		$tt_text = 	"<b>{$irli_mod_strings['LBL_PROBABILITY']}:</b> {$layout_def['fields']['PROBABILITY']}<BR>".
					"<b>{$irli_mod_strings['LBL_PLATFORM']}:</b> {$layout_def['fields']['PLATFORM']}<BR>".
					"<b>{$irli_mod_strings['LBL_FLOW_CODE']}:</b> {$layout_def['fields']['FLOW_CODE']}<BR>".
					(($do_finance_fields) ? "<b>{$irli_mod_strings['LBL_IGF_ODDS']}:</b> {$layout_def['fields']['IGF_ODDS']}<BR>" : "").
					"<b>{$irli_mod_strings['LBL_REFURB']}:</b> ".(($layout_def['fields']['REFURB'] == "1") ? "Yes" : "No")."<BR>".
					(($do_finance_fields) ? "<b>{$irli_mod_strings['LBL_FINANCED_REVENUE_AMOUNT']}:</b> {$financed_revenue_display}<BR>" : "").
					"<b>{$irli_mod_strings['LBL_PROJECT_START_DATE']}:</b> {$layout_def['fields']['PROJECT_START_DATE']}<BR>".
					"<b>{$irli_mod_strings['LBL_PROJECT_END_DATE']}:</b> {$layout_def['fields']['PROJECT_END_DATE']}<BR>"
		;
		
		$return = "<img id='{$layout_def['fields']['ID']}_tt' align='absmiddle' width='18' height='18' class='info' border='0' src='custom/themes/default/images/info_inline.png'><script type='text/javascript'>new YAHOO.widget.Tooltip('comp_{$layout_def['fields']['ID']}_tooltip', { context:'{$layout_def['fields']['ID']}_tt', width:'300px', text:'{$tt_text}' });</script>";

		return $return;
	}
}

?>
