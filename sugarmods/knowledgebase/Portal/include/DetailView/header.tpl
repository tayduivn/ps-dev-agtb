<form id="{$formId}" name="{$formName}" method="POST" action="index.php">
<input type="hidden" name="module" value="{$module}">
<input type="hidden" name="id" value="{$data.id}">
<input type="hidden" name="action" value="EditView">
<input type="hidden" name="returnmodule" value="{$returnModule}">
<input type="hidden" name="returnaction" value="{$returnAction}">
<input type="hidden" name="returnid" value="{$returnId}">
{foreach name=rowIteration from=$def.templateMeta.hiddenInputs key=name item=value}
<input type="hidden" name="{$name}" value="{$value}">
{/foreach}
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
	<td style="padding-bottom: 2px;">
		{if $editable}
		<input title="{$app.LBL_EDIT_BUTTON_TITLE}" class="button" type="submit" name="Edit" value="  {$app.LBL_EDIT_BUTTON_LABEL}  ">
		{/if}
		{if $returnModule && $returnAction && $returnId}
			<input title="{$app.LBL_BACK}" class="button" onclick="document.location = 'index.php?module={$returnModule}&action={$returnAction}{if $returnId}&id={$returnId}{/if}'; return false" type="submit" name="Back" value="  {$app.LBL_BACK}  ">
		{/if}
	</td>
	<td align='right'></td>
	</tr>
</table>
</form>