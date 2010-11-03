<script type='text/javascript' src='include/javascript/sugar_grp_overlib.js'></script>
<script type='text/javascript' src='fonality/include/InboundCall/overlib.js'></script>
{literal}
<script type='text/javascript'>
function selectOverlib(body){
{/literal}
	return overlib(body, CAPTION, '<div style=\'float:left\'>Additional Details</div><div style=\'float: right\'>', DELAY, 200, STICKY, MOUSEOFF, 1000, WIDTH, 300, CLOSETEXT, '<img border=0 src={$THEMEPATH}close.gif>', CLOSETITLE, 'Click to Close', CLOSECLICK, FGCLASS, 'olFgClass', CGCLASS, 'olCgClass', BGCLASS, 'olBgClass', TEXTFONTCLASS, 'olFontClass', CAPTIONFONTCLASS, 'olCapFontClass', CLOSEFONTCLASS, 'olCloseFontClass');
{literal}
}
</script>
{/literal}

{if $NO_RESULTS}
<br>
<p>No records found</p>
{/if}

{if !empty($CONTACTS_CALLS) || !empty($LEADS_CALLS) || !empty($ACCOUNTS_CALLS)}
<h3>Planned Calls</h3>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr><td>
	<table width="100%" cellpadding="0" cellspacing="0" class="list view">
	
	{if !empty($CONTACTS_CALLS)}
	{section name=ccall loop=$CONTACTS_CALLS}
	<tr class="{cycle values="oddListRowS1,evenListRowS1"}" height="20">
		<td scope="row" width="2%"><img src="{$DEFAULTPATH}Calls.gif"></td>
		<td scope="row" width="20%">{$CONTACTS_CALLS[ccall]->name}</td>
		<td scope="row" width="30%" nowrap>{$CONTACTS_CALLS[ccall]->parent_type} {$CONTACTS_CALLS[ccall]->parent_name}</td>
		<td scope="row" width="24%" nowrap>{$CONTACTS_CALLS[ccall]->date_start} {$CONTACTS_CALLS[ccall]->time_start}</td>
		<td scope="row" align="right" width="24%" nowrap><span class="attach_note_link">{$CONTACTS_CALLS[ccall]->new_call_link}</span></td>
	</tr>
	{/section}
	{/if}
	
	{if !empty($LEADS_CALLS)}
	{section name=lcall loop=$LEADS_CALLS}
	<tr class="{cycle values="oddListRowS1,evenListRowS1"}" height="20">
		<td scope="row" width="2%"><img src="{$DEFAULTPATH}Calls.gif"></td>
		<td scope="row" width="20%">{$LEADS_CALLS[lcall]->name}</td>
		<td scope="row" width="30%" nowrap>{$LEADS_CALLS[lcall]->parent_type} {$LEADS_CALLS[lcall]->parent_name}</td>
		<td scope="row" width="24%" nowrap>{$LEADS_CALLS[lcall]->date_start} {$LEADS_CALLS[lcall]->time_start}</td>
		<td scope="row" align="right" width="24%" nowrap><span class="attach_note_link">{$LEADS_CALLS[lcall]->new_call_link}</span></td>
	</tr>
	{/section}
	{/if}

	{if !empty($ACCOUNTS_CALLS)}
	{section name=acall loop=$ACCOUNTS_CALLS}
	<tr class="{cycle values="oddListRowS1,evenListRowS1"}" height="20">
		<td scope="row" width="2%"><img src="{$DEFAULTPATH}Calls.gif"></td>
		<td scope="row" width="20%">{$ACCOUNTS_CALLS[acall]->name}</td>
		<td scope="row" width="30%" nowrap>{$ACCOUNTS_CALLS[acall]->parent_type} {$ACCOUNTS_CALLS[acall]->parent_name}</td>
		<td scope="row" width="24%" nowrap>{$ACCOUNTS_CALLS[acall]->date_start} {$ACCOUNTS_CALLS[acall]->time_start}</td>
		<td scope="row" align="right" width="24%" nowrap><span class="attach_note_link">{$ACCOUNTS_CALLS[acall]->new_call_link}</span></td>
	</tr>
	{/section}
	{/if}
	
	</table>
</td></tr>
</table>
{/if}

<br/>

{if !empty($CONTACTS) || !empty($LEADS)}
<h3>Matching Contacts &amp; Leads</h3>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr><td>
	<table width="100%" cellpadding="0" cellspacing="0" class="list view">
	
	{if !empty($CONTACTS)}
	{section name=con loop=$CONTACTS}
	{assign var='cid' value=$CONTACTS[con]->id}
	<tr class="{cycle values="oddListRowS1,evenListRowS1"}" height="20">
		<td scope="row" width="2%"><img src="{$DEFAULTPATH}Contacts.gif"></td>
		<td scope="row" width="20%">{$CONTACTS[con]->name}</td>
		<td scope="row" width="54%">{$CONTACTS[con]->account_name} {$CONTACTS[con]->title}</td>
		<td scope="row" width="24%" align="right" nowrap><span class="attach_note_link">{$CONTACTS[con]->new_call_link}</span></td>
	</tr>

	{if !empty($CONTACTS_CASES[$cid]) || !empty($CONTACTS_OPPORTUNITIES[$cid])}
	
	{if !empty($CONTACTS_CASES[$cid])}
	{section name=ccase loop=$CONTACTS_CASES[$cid]}
	<tr class="{cycle values="oddListRowS1,evenListRowS1"}" height="20">
		<td scope="row" width="2%">&nbsp;</td>
		<td scope="row" colspan="2">
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr height="20">
					<td style="border-bottom: 0px none" width="4%" scope="row">&nbsp;</td>
					<td style="border-bottom: 0px none" width="2%" scope="row"><img src="{$DEFAULTPATH}Cases.gif"></td>
					<td style="border-bottom: 0px none" scope="row" width="25%">{$CONTACTS_CASES[$cid][ccase]->case_num} {$CONTACTS_CASES[$cid][ccase]->name}</td>
					<td style="border-bottom: 0px none" scope="row" width="20%" nowrap>{$CONTACTS_CASES[$cid][ccase]->status}</td>
					<td style="border-bottom: 0px none" scope="row" width="15%" nowrap>{$CONTACTS_CASES[$cid][ccase]->date_entered}</td>
					<td style="border-bottom: 0px none" scope="row" width="15%" nowrap>{$CONTACTS_CASES[$cid][ccase]->assigned_user_name}</td>
					<td style="border-bottom: 0px none" scope="row" width="14%">&nbsp;</td>
				</tr>
			</table>
		</td>
		<td scope="row" align="right" width="24%" nowrap><span class="attach_note_link">{$CONTACTS_CASES[$cid][ccase]->new_call_link}</span></td>
	</tr>
	{/section}
	{/if}
	
	{if !empty($CONTACTS_OPPORTUNITIES[$cid])}
	{section name=copp loop=$CONTACTS_OPPORTUNITIES[$cid]}
	<tr class="{cycle values="oddListRowS1,evenListRowS1"}" height="20">
		<td scope="row" width="2%">&nbsp;</td>
		<td scope="row" colspan="2">
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr height="20">
					<td style="border-bottom: 0px none" width="4%" scope="row">&nbsp;</td>
					<td style="border-bottom: 0px none" scope="row" width="2%"><img src="{$DEFAULTPATH}Opportunities.gif"></td>
					<td style="border-bottom: 0px none" scope="row" width="25%">{$CONTACTS_OPPORTUNITIES[$cid][copp]->name}</td>
					<td style="border-bottom: 0px none" scope="row" width="20%" nowrap>{$CONTACTS_OPPORTUNITIES[$cid][copp]->sales_stage}</td>
					<td style="border-bottom: 0px none" scope="row" width="15%" nowrap>{$CONTACTS_OPPORTUNITIES[$cid][copp]->date_closed}</td>
					<td style="border-bottom: 0px none" scope="row" width="15%" nowrap>{$CONTACTS_OPPORTUNITIES[$cid][copp]->assigned_user_name}</td>
					<td style="border-bottom: 0px none" scope="row" width="14%" nowrap>{$CONTACTS_OPPORTUNITIES[$cid][copp]->amount}</td>
				</tr>
			</table>
		</td>
		<td scope="row" align="right" width="24%" nowrap><span class="attach_note_link">{$CONTACTS_OPPORTUNITIES[$cid][copp]->new_call_link}</span></td>
	</tr>
	{/section}
	{/if}

	{/if}

	{/section}
	{/if}

	{if !empty($LEADS)}
	{section name=lea loop=$LEADS}
	<tr class="{cycle values="oddListRowS1,evenListRowS1"}" height="20">
		<td scope="row" width="2%"><img src="{$DEFAULTPATH}Leads.gif"></td>
		<td scope="row" width="20%">{$LEADS[lea]->name}</td>
		<td width="54%">{$LEADS[lea]->account_name} {$LEADS[lea]->title}</td>
		<td width="24%" scope="row" align="right" nowrap><span class="attach_note_link">{$LEADS[lea]->new_call_link}</span></td>
	</tr>
	{/section}
	{/if}
	</table>
</td></tr>
</table>
{/if}

<br/>

{if !empty($ACCOUNTS)}
<h3>Matching Accounts</h3>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr><td>
	<table width="100%" cellpadding="0" cellspacing="0" class="list view">
	
	{section name=act loop=$ACCOUNTS}
	{assign var='aid' value=$ACCOUNTS[act]->id}
	<tr class="{cycle values="oddListRowS1,evenListRowS1"}" height="20">
		<td scope="row" width="2%"><img src="{$DEFAULTPATH}Accounts.gif"></td>
		<td scope="row" width="20%">{$ACCOUNTS[act]->name}</td>
		<td width="54%">&nbsp;</td>
		<td scope="row" width="24%" align="right" nowrap><span class="attach_note_link">{$ACCOUNTS[act]->new_call_link}</span></td>
	</tr>
	
	{if !empty($ACCOUNTS_CASES[$aid]) || !empty($ACCOUNTS_OPPORTUNITIES[$aid]) || !empty($ACCOUNTS_CONTACTS[$aid])}
	
	{if !empty($ACCOUNTS_CONTACTS[$aid])}
	{section name=acon loop=$ACCOUNTS_CONTACTS[$aid]}
	<tr class="{cycle values="oddListRowS1,evenListRowS1"}" height="20">
		<td scope="row" width="2%">&nbsp;</td>
		<td scope="row" colspan="2">
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr height="20">
					<td style="border-bottom: 0px none" width="4%" scope="row">&nbsp;</td>
					<td style="border-bottom: 0px none" scope="row" width="2%"><img src="{$DEFAULTPATH}Contacts.gif"></td>
					<td style="border-bottom: 0px none" scope="row" width="25%">{$ACCOUNTS_CONTACTS[$aid][acon]->name}</td> 
					<td style="border-bottom: 0px none" scope="row" width="20%" nowrap>{$ACCOUNTS_CONTACTS[$aid][acon]->title|default:'&nbsp;'}</td> 
					<td style="border-bottom: 0px none" scope="row" width="15%">&nbsp;</td> 
					<td style="border-bottom: 0px none" scope="row" width="15%">&nbsp;</td> 
					<td style="border-bottom: 0px none" scope="row" width="14%">&nbsp;</td> 
				</tr>
			</table>
		</td>
		<td scope="row" width="24%" align="right" nowrap><span class="attach_note_link">{$ACCOUNTS_CONTACTS[$aid][acon]->new_call_link}</span></td>
	</tr>
	{/section}
	{/if}

	{if !empty($ACCOUNTS_CASES[$aid])}
	{section name=acase loop=$ACCOUNTS_CASES[$aid]}
	<tr class="{cycle values="oddListRowS1,evenListRowS1"}" height="20">
		<td scope="row" width="2%">&nbsp;</td>
		<td scope="row" colspan="2">
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr height="20">
					<td style="border-bottom: 0px none" width="4%" scope="row">&nbsp;</td>
					<td style="border-bottom: 0px none" scope="row" width="2%"><img src="{$DEFAULTPATH}Cases.gif"></td>
					<td style="border-bottom: 0px none" scope="row" width="25%">{$ACCOUNTS_CASES[$aid][acase]->case_num} {$ACCOUNTS_CASES[$aid][acase]->name}</td>
					<td style="border-bottom: 0px none" scope="row" width="20%" nowrap>{$ACCOUNTS_CASES[$aid][acase]->status}</td>
					<td style="border-bottom: 0px none" scope="row" width="15%" nowrap>{$ACCOUNTS_CASES[$aid][acase]->date_entered}</td>
					<td style="border-bottom: 0px none" scope="row" width="15%" nowrap>{$ACCOUNTS_CASES[$aid][acase]->assigned_user_name}</td>
					<td style="border-bottom: 0px none" scope="row" width="14%">&nbsp;</td>
				</tr>
			</table>
		</td>
		<td scope="row" width="24%" align="right" nowrap><span class="attach_note_link">{$ACCOUNTS_CASES[$aid][acase]->new_call_link}</span></td>
	</tr>
	{/section}
	{/if}

	{if !empty($ACCOUNTS_OPPORTUNITIES[$aid])}
	{section name=aopp loop=$ACCOUNTS_OPPORTUNITIES[$aid]}
	<tr class="{cycle values="oddListRowS1,evenListRowS1"}" height="20">
		<td scope="row" width="2%">&nbsp;</td>
		<td scope="row" colspan="2">
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr height="20">
					<td style="border-bottom: 0px none" width="4%" scope="row">&nbsp;</td>
					<td style="border-bottom: 0px none" scope="row" width="2%"><img src="{$DEFAULTPATH}Opportunities.gif"></td>
					<td style="border-bottom: 0px none" scope="row" width="25%">{$ACCOUNTS_OPPORTUNITIES[$aid][aopp]->name}</td>
					<td style="border-bottom: 0px none" scope="row" width="20%" nowrap>{$ACCOUNTS_OPPORTUNITIES[$aid][aopp]->sales_stage}</td>
					<td style="border-bottom: 0px none" scope="row" width="15%" nowrap>{$ACCOUNTS_OPPORTUNITIES[$aid][aopp]->date_closed}</td>
					<td style="border-bottom: 0px none" scope="row" width="15%" nowrap>{$ACCOUNTS_OPPORTUNITIES[$aid][aopp]->assigned_user_name}</td>
					<td style="border-bottom: 0px none" scope="row" width="14%" nowrap>{$ACCOUNTS_OPPORTUNITIES[$aid][aopp]->amount}</td>
				</tr>
			</table>
		</td>
		<td scope="row" width="24%" align="right" nowrap><span class="attach_note_link">{$ACCOUNTS_OPPORTUNITIES[$aid][aopp]->new_call_link}</span></td>
	</tr>
	{/section}
	{/if}

	{/if}

	{/section}
	</table>
</td></tr>
</table>
{/if}