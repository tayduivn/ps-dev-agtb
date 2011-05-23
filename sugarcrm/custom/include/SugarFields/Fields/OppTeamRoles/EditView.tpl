{{capture name=idname assign=idname}}{{sugarvar key='name'}}{{/capture}}
{{capture name=role_default assign=role_default}}{{sugarvar key='default'}}{{/capture}}
{{capture name=dropdown_list assign=dropdown_list}}{{sugarvar key='dropdown_list' string=true}}{{/capture}}
{ibm_oppteam_members opp_id=$smarty.request.record roles={{$dropdown_list}}}

<style type="text/css">
</style>

<button type="button" id="{{$idname}}_btn_select" onclick='{literal}javascript:open_popup("Users", 600, 400, "", true, false, {"call_back_function":"YUI.OppTeamRoles.set_return_for_editview","form_name":"EditView","field_to_name_array":{"id":"user_id","name":"user_name"}});{/literal}'><img src="{sugar_getimagepath file="id-ff-select.png"}"></button>
<button type="button" id="{{$idname}}_btn_add" onclick="YUI.OppTeamRoles.addRow('{{$idname}}')"><img src="{sugar_getimagepath file="id-ff-add.png"}"></button>
<input type="hidden" id="{{$idname}}_max_rows" name="{{$idname}}_max_rows" value="0" />

<div id="{{$idname}}_template" style="display:none">
	<div id="{{$idname}}_row_template">
	<input type="text" id="{{$idname}}_user_name_template" name="{{$idname}}_user_name_template" class="sqsEnabled" autocomplete="off" /> 
	<input type="hidden" id="{{$idname}}_user_id_template" name="{{$idname}}_user_id_template" />
	as
	<select id="{{$idname}}_role_id_template" name="{{$idname}}_role_id_template">
		{{foreach from=$opportunity_team_roles key=drop_value item=drop_label}}
			{{if $drop_value == $vardef.default}}
				<option value="{{$drop_value}}" selected="selected">{{$drop_label}}</option>
			{{else}}
				<option value="{{$drop_value}}">{{$drop_label}}</option>
			{{/if}}
		{{/foreach}}
	</select> 
	<img id="{{$idname}}_btn_remove_template" src="{sugar_getimagepath file="id-ff-remove.png"}" style="cursor:pointer;vertical-align:bottom">
	</div>
</div>



<div id="{{$idname}}_main"></div>

<script type="text/javascript">
{literal}

// prepare sugar quick search array
if(typeof sqs_objects == 'undefined'){var sqs_objects = []; }

YUI.namespace('OppTeamRoles');
YUI().use('node',
	function(Y) {
	
		// initialize
		var cntRows = 0;
		var divMain = Y.one("#{/literal}{{$idname}}{literal}_main");
		var divTemplate = Y.one("#{/literal}{{$idname}}{literal}_template");
		var cntMax = Y.one("#{/literal}{{$idname}}{literal}_max_rows");
	
		// add empty row
		YUI.OppTeamRoles.addRow = function ( idName ) {
			cntRows++;
			cntMax.set('value',cntRows);
            if ( document.getElementById(idName+"_row_"+cntRows) != null ) {
                // We already have a row for this, somehow we were reset
                return;
            }

			// replace template id with current row count
			newRow = divTemplate.get('innerHTML');
			newRow = newRow.replace(/_template/gi,"_"+cntRows);

			// add the new row to our main table
			divMain.appendChild(newRow);

			// add quick search
            var currRow = cntRows;
            YAHOO.util.Event.onContentReady(idName+"_row_"+currRow,(function(){sqs_objects["EditView_"+idName+"_user_name_"+currRow]={"form":"EditView","method":"query","modules":["Users"],"group":"or","field_list":["name","id"],"populate_list":["EditView_"+idName+"_user_name_"+currRow,idName+"_user_id_"+currRow],"conditions":[{"name":"name","op":"like_custom","end":"%","value":""}],"required_list":[idName+"_user_name_"+currRow],"order":"name","limit":"30","no_match_text":"No Match"};QSProcessedFieldsArray["EditView_"+idName+"_user_name_"+currRow]=false;enableQS(true);}));

			// setup remove button action
			Y.one("#{/literal}{{$idname}}{literal}_btn_remove_"+cntRows).on('click', 
					function(e) { YUI.OppTeamRoles.delRow(e);});

		};

		// remove row
		YUI.OppTeamRoles.delRow = function(e) {
			rowId = e._currentTarget.id.replace(/_btn_remove_/gi,"_row_");
			rowNode = Y.one("#"+rowId);
			rowNode.get('parentNode').removeChild(rowNode);
		};
		
		// parse return data from popup
		YUI.OppTeamRoles.set_return_for_editview = function(popup_reply_data) {
			var form_name = popup_reply_data.form_name;
			var user = popup_reply_data.name_to_value_array;
			
			// add row
			YUI.OppTeamRoles.addRow("{/literal}{{$idname}}{literal}");
			Y.one("#{/literal}{{$idname}}{literal}_user_name_"+cntRows).set('value',user.user_name);
			Y.one("#{/literal}{{$idname}}{literal}_user_id_"+cntRows).set('value',user.user_id);

		};
		
		{/literal}

		{* populate the present users/roles relationships dynamically using Smarty plugin *}

		
		{foreach from=$related_users item=user}
			{* // BEGIN sadek - AUTOMATICALLY ADD THE ASSIGNED USER TO THE OPP TEAM ROLE AS AN ASSIGNED USER *}
			{if $user.user_role_id != 20}
			{* // END sadek - AUTOMATICALLY ADD THE ASSIGNED USER TO THE OPP TEAM ROLE AS AN ASSIGNED USER *}
			// add present relationship
			YUI.OppTeamRoles.addRow("{{$idname}}");
			Y.one("#{{$idname}}_user_name_"+cntRows).set('value','{$user.user_name}');
			Y.one("#{{$idname}}_user_id_"+cntRows).set('value','{$user.user_id}');
			Y.one("#{{$idname}}_role_id_"+cntRows).set('value','{$user.user_role}');
			{* // BEGIN sadek - AUTOMATICALLY ADD THE ASSIGNED USER TO THE OPP TEAM ROLE AS AN ASSIGNED USER *}
			{/if}
			{* // END sadek - AUTOMATICALLY ADD THE ASSIGNED USER TO THE OPP TEAM ROLE AS AN ASSIGNED USER *}
		{/foreach}
		
		{literal}
			
	}
);

YUI.OppTeamRoles.ensureAccountClientRep = function(e) {
		var new_account_id = document.getElementById('account_id').value;
		var url = "index.php?" + SUGAR.util.paramsToUrl({
			module:"Accounts",
			action:"ajaxgetfieldvalue",
			record: new_account_id,
			smodule: "Accounts",
			field: "assigned_user_id"
		});
		
		var results = eval("(" + http_fetch_sync(url).responseText + ")");
		if(typeof(results['error']) != 'undefined'){
			return;
		}
		if(typeof(results['value']) == 'undefined'){
			return;
		}
		
		var url = "index.php?" + SUGAR.util.paramsToUrl({
			module:"Accounts",
			action:"ajaxgetfieldvalue",
			record: new_account_id,
			smodule: "Accounts",
			field: "assigned_user_name",
			type: "relate"
		});
		
		var results_name = eval("(" + http_fetch_sync(url).responseText + ")");
		if(typeof(results_name['error']) != 'undefined'){
			return;
		}
		if(typeof(results_name['value']) == 'undefined'){
			return;
		}
		
		var found = false;
		for(var i = 1; i < 30; i++){
			var user_id_el = document.getElementById('additional_team_members_c_user_id_' + i);
			var user_role_el = document.getElementById('additional_team_members_c_role_id_' + i);
			if(user_id_el != null){
				if(user_id_el.value == results['value'] && user_role_el.value == "4"){
					found = true;
				}
			}
		}
		
		if(!found){
			YUI.OppTeamRoles.addRow('additional_team_members_c');
			last_el_num = document.getElementById('additional_team_members_c_max_rows').value;
			document.getElementById('additional_team_members_c_user_id_' + last_el_num).value = results['value'];
			document.getElementById('additional_team_members_c_user_name_' + last_el_num).value = results_name['value'];
			document.getElementById('additional_team_members_c_role_id_' + last_el_num).value = "4";
		}
	};


YUI().use('node-base', function(Y) {
	// the function we'll use to handle the event:
	
	Y.on("change", YUI.OppTeamRoles.ensureAccountClientRep, "#account_name");
});
{/literal}
{{if empty($smarty.request.record) && $smarty.request.relate_to eq 'account_member_records' && !empty($smarty.request.relate_id)}}
{literal}
YUI().use('node-base', function(Y) {
    Y.on("domready", YUI.OppTeamRoles.ensureAccountClientRep);
});
{/literal}
{{elseif empty($smarty.request.record) && $smarty.request.parent_type eq 'Accounts' && !empty($smarty.request.parent_id)}}
{literal}
YUI().use('node-base', function(Y) {
    Y.on("domready", YUI.OppTeamRoles.ensureAccountClientRep);
});
{/literal}
{{/if}}
</script>
