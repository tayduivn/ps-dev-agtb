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
{overlib_includes}
<form name="manualRescore" method="POST">
<input type="hidden" name="action" value="ProcessRescore">
<input type="hidden" name="module" value="Score">
<table id="picker" class="listView">
  <tr>
	<th>
	  {$mod.LBL_RESCORE_MODULE}
	</td>
	<th>
	  {$mod.LBL_RESCORE_TOTAL} {sugar_help text=$mod.LBL_RESCORE_HELP_TOTAL WIDTH=500}
	</td>
  </tr>
{foreach name=moduleCountLoop from=$moduleCounts key=module item=currModule}
  <tr>
	<td>
	  {$currModule.label}
	</td>
	<td align="center">
	  {$currModule.total}<input type="checkbox"
	  name="{$module}" value="{$currModule.total}">
	</td>
  </tr>
{/foreach}
</table><br>
	<input type="button" class="button"
	onclick="startRescore(document.manualRescore); return false;" value="{$mod.LBL_RESCORE_SUBMIT}">
	<input type="button" onclick="document.location='index.php?module=Score&action=AdminSettings'; return false;" class="button" value="{$app.LBL_CANCEL_BUTTON_LABEL}">
</form>

<iframe id="scoreFrame" width="400px" height="128px" style="display:none;">
{$mod.LBL_RESCORE_STARTING}
</iframe>
<script type="text/javascript" src="include/JSON.js"></script>
{literal}
<script type="text/javascript">
function startRescore( form ) {
  var idx;
  var scoreThis = new Object();
  var scoreTotal = 0;
  var tmpArray;
  
  for ( idx in form ) {
	if ( form[idx] != null && form[idx].value != null && form[idx].checked ) {
	  if ( scoreThis[form[idx].name] == null ) {
		  scoreThis[form[idx].name] = parseInt(form[idx].value,10);
	  }
     scoreTotal = scoreTotal + parseInt(form[idx].value);
	}
  }

  document.getElementById('picker').style.display='none';

  var frame = document.getElementById('scoreFrame');
  frame.style.display = 'block';
  frame.src = 'index.php?module=Score&action=ProcessRescore&offset=-1&total='+scoreTotal+'&to_pdf=1&scoreThis='+JSON.stringifyNoSecurity(scoreThis).replace(/'/g,'"');
}
</script>
{/literal}
