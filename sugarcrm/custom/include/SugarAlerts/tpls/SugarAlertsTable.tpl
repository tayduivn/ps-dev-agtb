<table class="list view" cellspacing="0" cellpadding="0" border="0" width="100%">
<tr height="20">
<th width="1%" nowrap="nowrap" scope="col">
&nbsp;
</th>
<th width="84%" nowrap="nowrap" scope="col">
Alert Text
</th>
<th width="15%" nowrap="nowrap" scope="col">
Alert Date
</th>
<th width="1%" nowrap="nowrap" scope="col">
</th>
</tr>
{foreach from=$data key=k item=dataRow name=s}
<tr id="row_{$dataRow.id}" class="{if $smarty.foreach.s.iteration % 2 == 0}even{else}odd{/if}ListRowS1" height="20">
<td align="left" valign="top" scope="row">{if $dataRow.is_read}&nbsp;{else}<img src="{sugar_getimagepath file='new_alert.gif'}" height=20 width=20>{/if}</td>
<td align="left" valign="top" scope="row">{$dataRow.alert_text}</td>
<td align="left" valign="top" scope="row">{$dataRow.date_entered}</td>
<td aligh="right" valign="top" scope="row"><input type="image" id="{$dataRow.id}" name="{$dataRow.id}" src="{sugar_getimagepath file='delete_inline.png'}" onclick="SUGAR.SugarAlerts.removeAlert(this.id, '{$alert_type}'); return false;" border="0" style="border:0px"></td>
</tr>
{/foreach}
<tr class="oddListRowS1" height="20">
<td colspan=4>
<a href="index.php?module=Users&action=EditView&record={$current_user_id}&selected_tab=alerts">Manage alert preferences</a>
</td>
</tr>
</table>
