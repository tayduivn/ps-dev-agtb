<!--
/*********************************************************************************
 * Call Assistant Screen page
 *
 * Author: Felix Nilam
 * Date: 20/08/2007
 ********************************************************************************/
-->
<script type='text/javascript' src='include/javascript/sugar_grp_overlib.js'></script>
<script type='text/javascript' src='fonality/include/InboundCall/overlib.js'></script>
{literal}
<script type='text/javascript'>
function selectOverlib(body){
{/literal}
	return overlib(body, CAPTION, '<div style=\'float:left\'>Additional Details</div><div style=\'float: right\'>', DELAY, 200, STICKY, MOUSEOFF, 1000, WIDTH, 300, CLOSETEXT, '<img border=0 src={$THEMEPATH}close.gif>', CLOSETITLE, 'Click to Close', CLOSECLICK, FGCLASS, 'olFgClass', CGCLASS, 'olCgClass', BGCLASS, 'olBgClass', TEXTFONTCLASS, 'olFontClass', CAPTIONFONTCLASS, 'olCapFontClass', CLOSEFONTCLASS, 'olCloseFontClass');
{literal}
}

function helpText(txt){
	return overlib(txt, FGCLASS, 'olFgClass', CGCLASS, 'olCgClass', BGCLASS, 'olBgClass', TEXTFONTCLASS, 'olFontClass', CAPTIONFONTCLASS, 'olCapFontClass', CLOSEFONTCLASS, 'olCloseFontClass', WIDTH, -1, NOFOLLOW, 'ol_nofollow' );
}

function enableAttachNote(){
	var button = document.getElementById('attach_note');
	var note = document.getElementById('call_notes');

	if(note.value != ''){
		button.disabled = false;
		button.style.color = "black";
	}
}

function showAttachNoteLinks(){
	var links = document.getElementsByClassName('attach_note_link');
	for (var i = 0; i < links.length; i++){
		links[i].style.display = 'block';
	}
	document.getElementById('show_attach_note_links').value = 1;
}

function hideAttachNoteLinks(){
	var links = document.getElementsByClassName('attach_note_link');
	for (var i = 0; i < links.length; i++){
		links[i].style.display = 'none';
	}
}
</script>
{/literal}

<form id="inboundcall" name="inboundcall" method="post" action="index.php">
<input type="hidden" name="new_call_id" value="{$NEW_CALL_ID}" />
<input type="hidden" name="module" value="Calls" />
<input type="hidden" name="action" value="CreateParent" />
<input type="hidden" name="direction" value="{$DIRECTION}" />
<input type="hidden" name="phone_no" value="{$PHONE_NO}" />
<input type="hidden" name="type" />
<input type="hidden" name="parent_type" />
<input type="hidden" name="parent_id" />
<input type="hidden" name="contact_id" />
<input type="hidden" name="call_start_time" value="{$START_TIME}" />
<input type="hidden" name="show_attach_note_links" id="show_attach_note_links" value="0" />
<h3>Notes 
<img border="0" src="themes/default/images/helpInline.gif" onmouseover="return helpText('Call Notes can be attached to new or existing records.<br/>New Records (in the &quot;Create New&quot; area)<br/>&nbsp;&nbsp;- Creates a Call Record<br/>&nbsp;&nbsp;- Associates the Call Record with the new Record<br/>Existing Records<br/>&nbsp;&nbsp;- Adds Notes to the record in a variety of ways<br/>&nbsp;&nbsp;(Mouse over <img border=0 style=vertical-align:bottom src=themes/default/images/helpInline.gif> next to &quot;Attach call Notes&quot; button to learn more)')" onmouseout="return nd();">
</h3>
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="edit view">
<tr><td>
	<table width="100%" cellpadding="0" cellspacing="0">
	<tr>
		<td style="border-bottom: 0px none" width="77%"><textarea name="call_notes" id="call_notes" rows="4" cols="80" onKeyUp='enableAttachNote()' onBlur='enableAttachNote()'></textarea></td>
		<td style="border-bottom: 0px none; vertical-align:bottom" align="right" width="23%" nowrap>
			<table cellpadding="0" cellspacgin="0" border="0">
			<tr>
				<td align="left" width="10%" style="border-bottom: 0px none;">
				<img src="fonality/include/images/arrow1.png" border="0" style="vertical-align: middle"> <input type="button" class="button" id="attach_note" name="attach_note" disabled="disabled" style="color:gray" value="Attach Notes" onClick="showAttachNoteLinks()"> 
				<img border="0" src="themes/default/images/helpInline.gif" onmouseover="return helpText('Once you have typed the Notes, click here to continue.<br/>Links below will appear, allowing you to...<br/>Call Record<br/> - Creates a new record<br/> - Attaches Notes to <i>Description</i><br/>New Records<br/> - Creates a Call Record (above)<br/> - Associates the Call Record with the new Record<br/>Planned Calls<br/> - Updates <i>Start Date & Time</i><br/> - Changes <i>Status</i> to &quot;Held&quot;<br/> - Appends Notes to <i>Description</i>')" onmouseout="return nd();">
				</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
</td></tr>
</table>
<br>

{if $UNKNOWN}
<p>The phone number is unknown</p>
{elseif $NO_RESULTS}
<p>There are no records with this phone number</p>
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

<br/>
<h3>Find Existing Contacts, Accounts or Leads</h3>
<input type="text" name="search_name" id="search_name"> <input type="button" style="vertical-align: top" class="button" name="search" value="Search" onClick='getSearch();'> <div id="ajax_loader" style="display: none"><img src="fonality/include/images/ajax-loader.gif"></div>
<br/>
(Searches names and phone numbers only)
<br/><br/>
<div id='existing'></div>

{if $create_new_call}
<br>
<h3>Create New</h3>
<div style="font-size: 12px">
<img src="{$DEFAULTPATH}CreateCalls.gif" title="New Call" align="absmiddle" border="0"> <a href="javascript:void(1)" onClick="document.inboundcall.type.value='New';document.inboundcall.submit();" style="cursor: pointer">Call</a>&nbsp;|&nbsp;
<img src="{$DEFAULTPATH}CreateLeads.gif" title="New Lead" align="absmiddle" border="0"> <a href="javascript:void(1)" onClick="document.inboundcall.type.value='NewLead';document.inboundcall.submit();" style="cursor: pointer">Lead</a>&nbsp;|&nbsp;
<img src="{$DEFAULTPATH}CreateContacts.gif" title="New Contact" align="absmiddle" border="0"> <a href="javascript:void(1)" onClick="document.inboundcall.type.value='NewContact';document.inboundcall.submit();" style="cursor: pointer">Contact</a>&nbsp;|&nbsp;
<img src="{$DEFAULTPATH}CreateAccounts.gif" title="New Account" align="absmiddle" border="0"> <a href="javascript:void(1)" onClick="document.inboundcall.type.value='NewAccount';document.inboundcall.submit();" style="cursor: pointer">Account</a>&nbsp;|&nbsp;
<img src="{$DEFAULTPATH}CreateCases.gif" title="New Case" align="absmiddle" border="0"> <a href="javascript:void(1)" onClick="document.inboundcall.type.value='NewCase';document.inboundcall.submit();" style="cursor: pointer">Case</a>&nbsp;|&nbsp;
<img src="{$DEFAULTPATH}CreateOpportunities.gif" title="New Opportunity" align="absmiddle" border="0"> <a href="javascript:void(1)" onClick="document.inboundcall.type.value='NewOpp';document.inboundcall.submit();" style="cursor: pointer">Opportunity</a>
</div>
{/if}
</form>

{literal}
<script type="text/javascript">
document.inboundcall.call_notes.focus();
hideAttachNoteLinks();
{/literal}

{if $PROMPT_PBX_SETTINGS}
alert("You have not yet entered your username & password to setup the connection between Fonality and SugarCRM. Please take a moment to do so now.  Click OK to go to the setup page.");
window.open("index.php?module=fonuae_PBXSettings&action=EditView","PBX Settings");
{/if}

{literal}
function getSearch(){
	ajaxStatus.showStatus("Searching...");
	var search_str = document.getElementById('search_name').value;
	my_http_fetch_async('index.php','module=Calls&action=UAECallAssistant&ajax=1&sugar_body_only=1&search='+encodeURIComponent(search_str), document.getElementById('show_attach_note_links').value);
}

function my_http_fetch_async(url,post_data,show_attach_note_links) {
        global_xmlhttp = getXMLHTTPinstance();
        var method = 'GET';

        if(typeof(post_data) != 'undefined') method = 'POST';
        try {
                global_xmlhttp.open(method, url,true);
        }
        catch(e) {
                alert('message:'+e.message+":url:"+url);
        }
        if(method == 'POST') {
                global_xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        }
		global_xmlhttp.onreadystatechange = function() {
		if(global_xmlhttp.readyState==4) {
			if(global_xmlhttp.status == 200) {
        		var args = {"responseText" : global_xmlhttp.responseText};
				document.getElementById('existing').innerHTML = '';
				document.getElementById('existing').innerHTML = global_xmlhttp.responseText;
				ajaxStatus.hideStatus();
				if(show_attach_note_links == 1){
					showAttachNoteLinks();
				} else {
					hideAttachNoteLinks();
				}
            }
            else {
				alert("There was a problem retrieving the XML data:\n" + global_xmlhttp.statusText);
			}
		}
	}
        global_xmlhttp.send(post_data);
}

var frmRequest = document.getElementById('inboundcall');
if (frmRequest)
{
	if (window.event)
		frmRequest.onkeydown = frmRequest_KeyDown;
	else
		frmRequest.onkeypress = frmRequest_KeyPress;
}

function frmRequest_KeyDown( e )
   {

   var numCharCode;
   var elTarget;
   var strType;

   // get event if not passed
   if (!e) var e = window.event;

   // get character code of key pressed
   if (e.keyCode) numCharCode = e.keyCode;
   else if (e.which) numCharCode = e.which;

   // get target
   if (e.target) elTarget = e.target;
   else if (e.srcElement) elTarget = e.srcElement;
                                              
   // if form input field
   if ( elTarget.tagName.toLowerCase() == 'input' )
      {

      // get type
      strType = elTarget.getAttribute('type').toLowerCase();

      // based on type
      switch ( strType )
         {
         case 'checkbox' :
         case 'radio' :
         case 'text' :

            // if this is a return - change to tab
            if ( numCharCode == 13 )
               {
               if (e.keyCode) e.keyCode = 9;
               else if (e.which) e.which = 9;
		getSearch();
               }

            break;
            
         }

      }

   // process default action
   return true;

   }

function frmRequest_KeyPress( e )
   {

   var numCharCode;
   var elTarget;
   var strType;

   // get event if not passed
   if (!e) var e = window.event;

   // get character code of key pressed
   if (e.keyCode) numCharCode = e.keyCode;
   else if (e.which) numCharCode = e.which;

   // get target
   if (e.target) elTarget = e.target;
   else if (e.srcElement) elTarget = e.srcElement;
                                              
   // if form input field
   if ( elTarget.tagName.toLowerCase() == 'input' )
      {

      // get type
      strType = elTarget.getAttribute('type').toLowerCase();

      // based on type
      switch ( strType )
         {
         case 'checkbox' :
         case 'radio' :
         case 'text' :

            // if this is a return
            if ( numCharCode == 13 )
               {
               // cancel event to prevent form submission
		getSearch();
               return false;
               }

            break;
            
         }

      }

   // process default action
   return true;

   }
</script>
{/literal}
<!-- END: main -->
