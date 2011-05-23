{{capture name=dropdown_list assign=dropdown_list}}{{sugarvar key='dropdown_list' string=true}}{{/capture}}
{ibm_oppteam_members opp_id=$smarty.request.record roles={{$dropdown_list}}}


{foreach from=$related_users item=user}
{* // BEGIN sadek - AUTOMATICALLY ADD THE ASSIGNED USER TO THE OPP TEAM ROLE AS AN ASSIGNED USER *}
{if $user.user_role_id != 20}
{* // END sadek - AUTOMATICALLY ADD THE ASSIGNED USER TO THE OPP TEAM ROLE AS AN ASSIGNED USER *}


		{literal}
	    <script type="text/javascript">
		id = '{/literal}{$user.user_id}{literal}';

		// hard-coding these for now...
		if (id == 'seed_sarah_id') {
			name = 'dcdrake@us.ibm.com';
		}
		else if (id == 'seed_sally_id') {
			name = 'mraven@us.ibm.com';
			//name = 'jostrow@us.ibm.com';
		}
		else if (id == '79a87128-0e7f-fa5f-72b6-4d88533ac4e5') {
			name = 'goodwincp@us.ibm.com';
		}
		else if (id == 'seed_jim_id') {
			name = 'jostrow@us.ibm.com';
		}
		else {
			name = 'goodwincp@us.ibm.com';
		}

	    if (document.getElementById("businesscard_{/literal}{$user.user_id}{literal}")==null){
		    document.write('<span id="businesscard_{/literal}{$user.user_id}{literal}" class="vcard"><a href="index.php?module=Employees&action=DetailView&record={/literal}{$user.user_id}{literal}" class="fn url">{/literal}{$user.user_name}{literal}</a><span class="email" style="display:none;">'+name+'</span></span>');
	    }
	    </script>
		{/literal}
	({$user.user_role_name})<br />

{* // BEGIN sadek - AUTOMATICALLY ADD THE ASSIGNED USER TO THE OPP TEAM ROLE AS AN ASSIGNED USER *}
{/if}
{* // END sadek - AUTOMATICALLY ADD THE ASSIGNED USER TO THE OPP TEAM ROLE AS AN ASSIGNED USER *}
{/foreach}
