{*

/**
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 **/
*}
<style type="text/css">
{literal}
fieldset.scoreConfig {
	border: none;
}
fieldset.scoreConfig legend {
	font-weight: bold;
	font-size: 110%;
	padding-right: 5px;
}
.listView {
    margin-left: 2.4em;
}
{/literal}
</style>
{overlib_includes}
<form name="adminSettings" method="POST">
<input type="hidden" name="action" value="AdminSettings">
<input type="hidden" name="module" value="Score">
<input type="hidden" name="saveScoreConfigs" value="">
<input type="hidden" name="deleteConfig" value="">
<input type="hidden" name="deleteRowPrefix" value="">
<input type="hidden" name="deleteRow" value="">
<input type="hidden" id="active_tab" name="active_tab" value="{$active_tab}">

<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
  <td class="datalabel" width="100%" colspan="2">
	<input type="button" onclick="document.adminSettings.saveScoreConfigs.value='true'; if(check_form('adminSettings')) {ldelim} document.adminSettings.submit(); {rdelim}" class="button" value="{$app.LBL_SAVE_BUTTON_LABEL}">
	<input type="button" onclick="document.location.href='index.php?module=Administration&action=index'" class="button" value="{$app.LBL_CANCEL_BUTTON_LABEL}">
	<input type="button" onclick="document.location='index.php?module=Score&action=ManualRescore'; return false;" class="button" value="{$mod.LBL_MANUAL_RESCORE}">
  </td>
</tr>
</table>

<ul id="scoreAdminModuleList" class="tablist">
  {foreach from=$adminData key=module item=moduleData}
  <li id="{$module}_tab" {if $active_tab==$module}class="active"{/if}><a id="{$module}_link" href="javascript:scoreShowHide('admin|{$module}',showHideGroup.tabs); setActiveTab('{$module}')"
  {if $active_tab==$module}class="current"{/if}>{$moduleData.label}</a></li>
  {/foreach}
</ul>

{foreach name=moduleDataLoop from=$adminData key=module item=moduleData}
<table id="admin|{$module}" class="tabForm" width="80%" cellspacing=0 cellpadding=0 border=0 style="border-top: 0px none; margin-bottom: 4px; {if $module != $active_tab}display: none;{/if}">
  <tr>
	<td>
	  <table cellspacing=0 cellpadding=2>
		<tr>
		  <td class="dataLabel">
			{$mod.LBL_ENABLE_SCORE_FOR}
			{$moduleData.label}
		  </td>
		  <td>
			<input type="checkbox" value="true" name="{$module}_enabled"{if $moduleData.configs.enabled}checked="checked" {/if}>
		  </td>
		</tr>
        {if isset($moduleData.parentLabel) }
		<tr>
		  <td class="dataLabel">
			{$mod.LBL_APPLY_MULT_TO}: {sugar_help text=$mod.LBL_HELP_BOOST_APPLY WIDTH=500}
		  </td>
		  <td>
			<select name="{$module}_apply_mult">
			  <option value="record" {if $moduleData.configs.apply_mult=="record"}selected{/if}>{$moduleData.label}</option>
			  <option value="parent" {if $moduleData.configs.apply_mult=="parent"}selected{/if}>{$moduleData.parentLabel}</option>
			</select>
		  </td>
		</tr>
        {/if}
		<tr>
		  <td class="dataLabel" valign="top">
			{$mod.LBL_ADD_NEW_RULE}: {sugar_help text=$mod.LBL_HELP_NEW_RULE WIDTH=500}
		  </td>
		  <td>
			<select id="add|{$module}" name="add[{$module}]" onChange="scoreShowHide('add|{$module}|'+this[this.selectedIndex].getAttribute('ruleName'),showHideGroup.addExtra.{$module})">
			  <option value='' ruleName=''>{$mod.LBL_ADD_NEW_NONE}</option>
				{foreach from=$moduleData.addFields key=fieldName item=fieldData}
			  <option value="{$fieldName}" ruleName="{$fieldData.ruleName}">{$fieldData.label}</option>
				{/foreach}
			</select>
			<input type="button" onclick="document.adminSettings.saveScoreConfigs.value='true'; if(check_form('adminSettings')) {ldelim} document.adminSettings.submit(); {rdelim}" class="button" value="{$mod.LBL_ADD_VALUE}">
		    <div id="add|{$module}|">
			</div>
			{foreach from=$moduleData.addHTML key=ruleName item=addChunk}
			<div id="add|{$module}|{$ruleName}" style="display: none;">
			  {$addChunk}
			</div>
			{/foreach}
			<br>&nbsp;<br>
		  </td>
		</tr>
	  </table>
	</td>
  </tr>
  {foreach name=configHTMLinner from=$moduleData.configHTML key=prefix item=config}
  <tr>
	<td>
	  <fieldset class="scoreConfig" width="100%">
	      <legend>
	          <input type="checkbox" name="{$prefix}_enabled" value="true" {if $config.enabled}checked{/if}>
              <span id="show_link_{$module}_{$prefix}" style="display: none;">
                <a href="#" class="utilsLink" onclick="document.getElementById('{$module}_{$prefix}_table').style.display='';document.getElementById('show_link_{$module}_{$prefix}').style.display='none';document.getElementById('hide_link_{$module}_{$prefix}').style.display='';return false;">
                    <img src="{$image_path}advanced_search.gif" alt="Show" absmiddle="" border="0" width="8" height="8"></a>
              </span>
              <span id="hide_link_{$module}_{$prefix}">
                <a href="#" class="utilsLink" onclick="document.getElementById('{$module}_{$prefix}_table').style.display='none';document.getElementById('hide_link_{$module}_{$prefix}').style.display='none';document.getElementById('show_link_{$module}_{$prefix}').style.display='';return false;">
                    <img src="{$image_path}basic_search.gif" alt="Hide" align="absmiddle" border="0" width="8" height="8"></a>
              </span>&nbsp;
              {$config.label}
	          <input type="image" src="{$image_path}delete_inline.gif" onclick="if(check_form('adminSettings')&&confirm('{$mod.LBL_DELETE_RULE}')) {ldelim} document.adminSettings.saveScoreConfigs.value='true'; document.adminSettings.deleteConfig.value='{$prefix}'; document.adminSettings.submit(); {rdelim}">
	      </legend>
          <span id="{$module}_{$prefix}_table">
	      {$config.html}
          </span>
	  </fieldset>
	</td>
  </tr>
  {/foreach}
</table>
{/foreach}
<!-- Add the show/hides to groups -->
<script type="text/javascript">
showHideGroup = new Object();
showHideGroup.tabs = new Array();
showHideGroup.addExtra = new Object();
{foreach from=$adminData key=module item=moduleData}
	showHideGroup.tabs[showHideGroup.tabs.length] = 'admin|{$module}';
	showHideGroup.addExtra.{$module} = new Array();
	{foreach from=$moduleData.addHTML key=className item=addChunk}
		showHideGroup.addExtra.{$module}[showHideGroup.addExtra.{$module}.length] = 'add|{$module}|{$className}';
	{/foreach}
{/foreach}

{literal}
function scoreShowHide( showThisId, hideGroup ) {
    var hideId;
	var i = 0;
	while ( i < hideGroup.length ) {
		hideId = hideGroup[i];
		if ( hideId != null && hideId != showThisId ) {
		   if ( document.getElementById(hideId) == null ) {
		   	  alert('HIDEID: '+hideId);
		   }
		   document.getElementById(hideId).style.display='none';
		}
		i++;		
	}
	if ( showThisId != null && document.getElementById(showThisId) != null) {
	   document.getElementById(showThisId).style.display='block';
	}
}

function setActiveTab ( module ) {
	var tabs = document.getElementById("scoreAdminModuleList");
	var tabList = tabs.getElementsByTagName('li');
	var linkList = tabs.getElementsByTagName('a');
	var idx;

    for ( idx in tabList ) {
		if ( tabList[idx].id == module + '_tab' ) {
		   tabList[idx].className = 'active';
		} else {
		tabList[idx].className = '';
		}
	}

    for ( idx in linkList ) {
		if ( linkList[idx].id == module + '_link' ) {
		   linkList[idx].className = 'current';
		} else {
		  linkList[idx].className = '';
		}
	}

    document.getElementById('active_tab').value=module;
}
function updateCalc ( elem, weightName ) {
	var calcId = elem.name.replace("[score]","[calc]");
	var calcElem = document.getElementById(calcId);
	var weightElem = document.getElementsByName(weightName)[0];
	calcElem.innerHTML = elem.value * weightElem.value;
}
function recalcAll ( prefix ) {
	var weightName = prefix + "_weight";
	var reg = new RegExp(prefix + "_rows\\[.*\\]\\[score\\]");
	var inputList = document.getElementsByTagName('input');
	var idx;
	for ( idx in inputList ) {
		if ( inputList[idx].name != null && reg.test(inputList[idx].name) ) {
		   updateCalc(inputList[idx],weightName);
		}
	}
}
{/literal}
</script>
