<?php
$viewdefs ['Opportunities'] = 
array (
  'EditView' => 
  array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'buttons' => 
        array (
          0 => 'SAVE',
          1 => 'CANCEL',
        ),
      ),
      'maxColumns' => '2',
      'widths' => 
      array (
        0 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
        1 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
      ),
      'useTabs' => true,
    ),
    'panels' => 
    array (
      'LBL_PANEL_OVERVIEW' => 
      array (
        array (
          0 => 
          array (
            'name' => 'description',
            'nl2br' => true,
          ),
          1 => 
          array (
            'name' => 'account_name',
            'label' => 'LBL_ACCOUNT_NAME',
          ),
        ),
        array (
          0 => '',
          1 => 
          array (
            'name' => 'contact_c',
            'studio' => 'visible',
            'label' => 'LBL_CONTACT',
			'customCode' => '<input type="text" name="{$fields.contact_c.name}" class="sqsEnabled" tabindex="103" id="{$fields.contact_c.name}" size="" value="{$fields.contact_c.value}" title=\'\' autocomplete="off"  >
<input type="hidden" name="{$fields.contact_c.id_name}" 
id="{$fields.contact_c.id_name}" 
value="{$fields.contact_id_c.value}">
<span class="id-ff multiple">
<button type="button" name="btn_{$fields.contact_c.name}" id="btn_{$fields.contact_c.name}" tabindex="103" title="{$APP.LBL_SELECT_BUTTON_TITLE}" accessKey="{$APP.LBL_SELECT_BUTTON_KEY}" class="button firstChild" value="{$APP.LBL_SELECT_BUTTON_LABEL}" 
onclick=\'open_popup(
"{$fields.contact_c.module}", 
600, 
400, 
"&account_id={$fields.account_id.value}", 
true, 
false, 
{literal}{"call_back_function":"set_return","form_name":"EditView","field_to_name_array":{"id":"contact_id_c","name":"contact_c"}}{/literal}, 
"single", 
true
);\' ><img src="{sugar_getimagepath file="id-ff-select.png"}"></button><button type="button" name="btn_clr_{$fields.contact_c.name}" id="btn_clr_{$fields.contact_c.name}" tabindex="103" title="{$APP.LBL_CLEAR_BUTTON_TITLE}" accessKey="{$APP.LBL_CLEAR_BUTTON_KEY}" class="button lastChild" 
onclick="document.forms[\'{$form_name}\'].{$fields.contact_c.name}.value = \'\'; document.forms[\'{$form_name}\'].{$fields.contact_c.id_name}.value = \'\'; var tempEvent = document.createEvent(\'HTMLEvents\'); tempEvent.initEvent(\'change\', true, true); document.forms[\'{$form_name}\'].{$fields.contact_c.name}.dispatchEvent(tempEvent); var tempEvent = document.createEvent(\'HTMLEvents\'); tempEvent.initEvent(\'change\', true, true); document.forms[\'{$form_name}\'].{$fields.contact_c.id_name}.dispatchEvent(tempEvent);"
value="{$APP.LBL_CLEAR_BUTTON_LABEL}" ><img src="{sugar_getimagepath file="id-ff-clear.png"}"></button>
</span>
<script type="text/javascript">
<!--
if(typeof QSProcessedFieldsArray != "undefined") 
    QSProcessedFieldsArray["{$form_name}_{$fields.contact_c.name}"] = false;
    

enableQS(false);
--> 
</script>',
          ),
        ),
        array (
          0 => 
          array (
            'name' => 'sales_stage',
            'comment' => 'Indication of progression towards closure',
            'label' => 'LBL_SALES_STAGE',
          ),
          1 =>  
          array (
            'name' => 'reason_won_c',
            'label' => 'LBL_REASON_WON',
          ),
        ),
        array (
          0 => '',
          1 =>
          array (
            'name' => 'solution_codes_c',
            'studio' => 'visible',
            'label' => 'LBL_SOLUTION_CODES',
          ),
        ),
        array (
          0 => 
          array (
            'name' => 'key_deal_c',
            'label' => 'LBL_KEY_DEAL',
          ),
          1 => 
          array (
            'name' => 'amount',
            'label' => 'LBL_AMOUNT',
            'customCode' => '
				{if strlen($fields.amount.value) <= 0}
				{assign var="value" value=$fields.amount.default_value }
				{else}
				{assign var="value" value=$fields.amount.value }
				{/if}
				<input type="text" name="{$fields.amount.name}" id="{$fields.amount.name}" size="30"  value="{sugar_number_format var=$value}">
				&nbsp;in Currency&nbsp;
				<span id="currency_id">
				{$fields.currency_id.value}
				</span>',
          ),
        ),
        array (
          0 => array(
            'name' => 'related_opportunity_c',
            'label' => 'LBL_RELATED_OPPORTUNITY',
          ),
          1 => 
          array (
            'name' => 'key_deal_comments_c',
            'label' => 'LBL_KEY_DEAL_COMMENTS',
          ),
        ),
		array(
		  0 => '',
          1 =>
          array (
            'name' => 'tags',
          ),
		),
		array(
		  0 => 'date_closed',
          1 => 'probability',
			2 => array(
	            'name' => 'NEW_PANEL',
	            'label' => 'LBL_DETAILVIEW_PANEL1',
	            'default' => 'true',
			),
		),
        array (
          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO',
          ),
          1 => array (
			'name' => 'additional_team_members_c',
            'label' => 'LBL_ADDITIONAL_TEAM_MEMBERS', 
		  ),

			2 => array(
	            'name' => 'NEW_PANEL',
	            'label' => 'LBL_PANEL_ASSIGNMENT',
	            'default' => 'false',
			),

        ),
        array (
          0 => 
          array (
            'name' => 'lead_source',
            'comment' => 'Source of the opportunity',
            'label' => 'LBL_LEAD_SOURCE',
          ),
          1 => 
          array (
            'name' => 'business_partner_c',
            'label' => 'LBL_BUSINESS_PARTNER',
          ),
        ),
        array (
          0 => 
          array (
            'name' => 'competitor_c',
            'label' => 'LBL_COMPETITOR',
          ),
          1 => 
          array (
            'name' => 'financing_sales_stage_c',
            'studio' => 'visible',
            'label' => 'LBL_FINANCING_SALES_STAGE',
          ),
        ),
        array (
          0 => 
          array (
            'name' => 'buying_behavior_c',
            'studio' => 'visible',
            'label' => 'LBL_BUYING_BEHAVIOR',
          ),
          1 => '',
        ),
        array (
          0 => 
          array (
            'name' => 'conditions_of_satisfaction_c',
            'studio' => 'visible',
            'label' => 'LBL_CONDITIONS_OF_SATISFACTION',
          ),
          1 => 
          array (
            'name' => 'reason_lost_c',
            'label' => 'LBL_REASON_LOST',
          ),
        ),
        array (
          0 => 
          array (
            'name' => 'is_restricted_c',
            'label' => 'LBL_IS_RESTRICTED',
          ),
          1 => 
          array (
            'name' => 'international_c',
            'label' => 'LBL_INTERNATIONAL',
          ),
        ),
        array (
          0 => 
          array (
            'name' => 'business_transaction_type_c',
            'label' => 'LBL_BUSINESS_TRANSACTION_TYPE',
          	'customCode' => '
{if $fields.business_transaction_type_c.acl > 1}
{counter name="panelFieldCount"}

{if strlen($fields.business_transaction_type_c.value) <= 0}
{assign var="value" value=$fields.business_transaction_type_c.default_value }
{else}
{assign var="value" value=$fields.business_transaction_type_c.value }
{/if}
<input type="text" name="{$fields.business_transaction_type_c.name}" id="{$fields.business_transaction_type_c.name}" size="3" maxlength="1" value="{sugar_number_format precision=0 var=$value}" title="" readonly="readonly">&nbsp;<img id="btt_calc_open" border=0 width=16 src="{sugar_getimagepath file="btt_calc.gif"}"><BR>
<div id=btt_panel_display class="yui-panel-container yui-dialog" style="background-color:white">
	<div class="hr"><h3>{$MOD.LBL_BTT_CALC_HEADER}</h3></div>
	<div class="bd">{$MOD.LBL_BTT_QUESTIONS_HEADER}<BR>
	<table>
	{section name=question_loop start=1 loop=8}
	{assign var="itr" value=$smarty.section.question_loop.iteration}
	{assign var="label_string" value="LBL_BTT_QUESTION_$itr"}
	{assign var="yes_checked_var" value="btt_options_y_$itr"}
	{assign var="no_checked_var" value="btt_options_n_$itr"}
	<tr>
		<td>{$MOD.$label_string}</td><td><input type=radio id=btt_options_{$itr} name=btt_options_{$itr} value="1" {$smarty.request.$yes_checked_var}>Yes&nbsp;&nbsp;<input type=radio id=btt_options_{$itr}_n name=btt_options_{$itr} value="0" {$smarty.request.$no_checked_var}>No</td>
	</tr>
	{/section}
	</table>
	</div>
	<div class="ft"><button type=button onclick="document.getElementById(\'business_transaction_type_c\').value = calcBTTValue();" id="btt_panel_close_calc">Calculate</button>&nbsp;<button type=button id="btt_panel_close_cancel" onclick="resetBTTOptions();">Cancel</button></div>
</div>
{literal}
<script type="text/javascript">
{/literal}
var original_btt_arr = JSON.parse(\'{$fields.btt_options.value}\');
{literal}
var btt_panel_obj = new YAHOO.widget.Panel("btt_panel_display", { width:"800px", visible:false, close:false } ); 
btt_panel_obj.render();
YAHOO.util.Event.addListener("btt_calc_open", "click", btt_panel_obj.show, btt_panel_obj, true);
YAHOO.util.Event.addListener("btt_panel_close_calc", "click", btt_panel_obj.hide, btt_panel_obj, true);
YAHOO.util.Event.addListener("btt_panel_close_cancel", "click", btt_panel_obj.hide, btt_panel_obj, true);
function calcBTTValue(){
	var total = 0;
	for(var i = 1; i < 8; i++){
		if(document.getElementById("btt_options_" + i).checked == true){
			original_btt_arr[i] = "1";
			if(total < 5){
				total++;
			}
		}
		else if(document.getElementById("btt_options_" + i + "_n").checked == true){
			original_btt_arr[i] = "0";
		}
		else{
			if(typeof(original_btt_arr[i]) != "undefined"){
				original_btt_arr.splice(i, 1);
			}
		}
	}
	return total;
}
function resetBTTOptions(){
	for(var i = 1; i < 8; i++){
		if(typeof(original_btt_arr[i]) != "undefined"){
			if(original_btt_arr[i] == "1"){
				document.getElementById("btt_options_" + i).checked = true;
				document.getElementById("btt_options_" + i + "_n").checked = false;
			}
			else{
				document.getElementById("btt_options_" + i).checked = false;
				document.getElementById("btt_options_" + i + "_n").checked = true;
			}
		}
		else{
			document.getElementById("btt_options_" + i).checked = false;
			document.getElementById("btt_options_" + i + "_n").checked = false;
		}
	}
}
</script>
{/literal}
{else}
{counter name="panelFieldCount"}
<span id="business_transaction_type_c">
<span id="{$fields.business_transaction_type_c.name}">
{sugar_number_format precision=0 var=$fields.business_transaction_type_c.value}
</span>
</span>
</td>
{/if}
',
          ),
          1 => 
          array (
            'name' => 'itar_compliance_c',
            'studio' => 'visible',
            'label' => 'LBL_ITAR_COMPLIANCE',
            'customCode' => '
{if strval($fields.itar_compliance_c.value) == "1" || strval($fields.itar_compliance_c.value) == "yes" || strval($fields.itar_compliance_c.value) == "on"}
{assign var="checked" value="CHECKED"}
{else}
{assign var="checked" value=""}
{/if}
<input type="hidden" name="{$fields.itar_compliance_c.name}" value="0">
<input type="checkbox" id="{$fields.itar_compliance_c.name}"
name="{$fields.itar_compliance_c.name}"
value="1" tabindex="125" {$checked}>&nbsp;&nbsp;<img id=compliance_name_tip src="{sugar_getimagepath file="help-dashlet.png"}" alt="Help" border=0>
<script type="text/javascript">new YAHOO.widget.Tooltip("compliance_tooltip", {literal}{{/literal} context:"compliance_name_tip", width:"300px", text:"{$MOD.LBL_ITAR_COMPLIANCE_HELP}" {literal}}{/literal});</script>
',
          ),
        ),
      ),
    ),
  ),
);
?>
