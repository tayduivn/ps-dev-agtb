{*
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
*}

<!DOCTYPE html PUBLIC "-//W3C//DTD html 4.01 Transitional//EN">
<html {$langHeader}>
<head>
<link REL="SHORTCUT ICON" HREF="include/images/sugar_icon.ico">

<title>SugarCRM - Commercial Open Source CRM</title>
{if $useCustomFile}
<style type="text/css">@import url("{sugar_getjspath file='custom/portal/custom/style.css'}");</style>
{else}
<style type="text/css">@import url("{sugar_getjspath file='portal/themes/Sugar/style.css'}");</style>
{/if}

<link href="{sugar_getjspath file='portal/themes/Sugar/navigation.css'}" rel="stylesheet" type="text/css" />


</head>

<body>

<div id='moduleLinks'>
				<ul id="tabRow"><div style="float: right;">
													<a href="javascript:void(0)" id="My AccountHandle">{$mod.LBL_MY_ACCOUNT}</a>
							 | 													<a href="javascript:void(0)" id="LogoutHandle">{$mod.LBL_LOGOUT}</a>

													
					</div>
																<li class=otherTab><a href="javascript:void(0)" class=otherTab>{$mod.LBL_HOME}</a></li>
																<li class=currentTab><a href="javascript:void(0)" class=currentTab>{$mod.LBL_CASES}</a></li>
																<li class=otherTab><a href="javascript:void(0)" class=otherTab>{$mod.LBL_NEWSLETTERS}</a></li>
																<li class=otherTab><a href="javascript:void(0)" class=otherTab>{$mod.LBL_BUG_TRACKER}</a></li>
				</ul>
					

</div>

<div id='shortCuts'>
			<a class='link' href='javascript:void(0)'>{$mod.LBL_CREATE_NEW}</a>
		 | 			<a class='link' href='javascript:void(0)'>{$mod.LBL_LIST}</a>
			</div>
<!-- crmprint --><p><table width='100%' cellpadding='0' cellspacing='0' border='0' class='moduleTitle'><tr><td valign='top'>
<h2>{$mod.LBL_CASES}</h2></td>
</tr></table>
</p><form id="CaseEditView" name="CaseEditView" method="POST" action="index.php" onsubmit='return false'>

<input type="hidden" name="module" value="Cases">
<input type="hidden" name="id" value="">
<input type="hidden" name="action" value="Save">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td>
			<input title="Save" class="button" type="submit" name="button" value="  {$mod.LBL_BTN_SAVE}  " > 
			<input title="Cancel"  class="button" type="submit" name="button" value="  {$mod.LBL_BTN_CANCEL}  ">
		</td>
		<td align="right" nowrap><span class="required"></span> </td>
		<td align='right'></td>

	</tr>
</table>
<table width='100%' border='0' cellspacing='1' cellpadding='0'  class='detail view'>
<tr>
					<td width='12.5%' scope='row'>
							{$mod.LBL_NUMBER} 					</td>
		<td width='37.5%' class='tabDetailViewDF' colspan='4'>
												


									</td>
	</tr>
<tr>

					<td width='12.5%' scope='row'>
							{$mod.LBL_PRIORITY} 					</td>
		<td width='37.5%' class='tabDetailViewDF' colspan='4'>
												
<select name='priority'>
	<option value='P1'>
		{$mod.LBL_HIGH}
	</option>
	<option value='P2'>
		{$mod.LBL_MEDIUM}
	</option>

	<option value='P3'>
		{$mod.LBL_LOW}
	</option>
</select>

									</td>
	</tr>
<tr>
					<td width='12.5%' scope='row'>
							{$mod.LBL_SUBJECT} <span class="required">*</span>					</td>

		<td width='37.5%' class='tabDetailViewDF' colspan='4'>
												
<input type='text' name='name' size='60' value=''>

									</td>
	</tr>
<tr>
					<td width='12.5%' scope='row'>
							{$mod.LBL_DESCRIPTION} 					</td>
		<td width='37.5%' class='tabDetailViewDF' colspan='4'>

												
<textarea name='description' rows='15' cols='100'></textarea>

									</td>
	</tr>
</table>
{literal}
</form><script type="text/javascript">
requiredTxt = 'Missing required field:';
invalidTxt = 'Invalid Value:';
</script><!-- crmprint --><div id='footer'><!--end body panes-->

	<table cellpadding='0' cellspacing='0' width='100%' border='0' class='underFooter'><tr><td align='center' class='copyRight'>{$app.LBL_SUGAR_COPYRIGHT}<br /><A href='http://www.sugarcrm.com' target='_blank'><img style='margin-top: 2px' border='0'  width='120' height='34' src='{sugar_getjspath file='include/images/poweredby_sugarcrm_65.png'}' alt=$mod_strings.LBL_POWERED_BY_SUGAR></a>

</td></tr></table></div>
</body></html>
{/literal}