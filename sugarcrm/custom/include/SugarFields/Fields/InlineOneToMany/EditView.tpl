{{capture name=idname assign=idname}}{{sugarvar key='name'}}{{/capture}}
{capture name=form_field_id assign=form_field_id}{{sugarvar key='name'}}{/capture}
{capture name=inline_module assign=inline_module}{{sugarvar key='inline_module'}}{/capture}
{capture name=inline_link_table assign=inline_link_table}{{sugarvar key='inline_link_table'}}{/capture}
{capture name=inline_parent_link_field assign=inline_parent_link_field}{{sugarvar key='inline_parent_link_field'}}{/capture}
{capture name=inline_child_link_field assign=inline_child_link_field}{{sugarvar key='inline_child_link_field'}}{/capture}
{ibm_inline_one_to_many 
parent_id=$smarty.request.record 
link_table=$smarty.capture.inline_link_table 
parent_link_field=$smarty.capture.inline_parent_link_field 
child_link_field=$smarty.capture.inline_child_link_field
form_field_id=$smarty.capture.form_field_id
child_module=$smarty.capture.inline_module
}


<style type="text/css">
</style>

<button type="button" id="{{$idname}}_btn_select" onclick='{literal}javascript:open_popup("{/literal}{$inline_module}{literal}", 600, 400, "", true, false, {"call_back_function":"YUI.{/literal}{{$idname}}{literal}.set_return_for_editview","form_name":"EditView","field_to_name_array":{"id":"id","name":"name"}});{/literal}'><img src="{sugar_getimagepath file="id-ff-select.png"}"></button>
<button type="button" id="{{$idname}}_btn_add" onclick="YUI.{{$idname}}.addRow('{{$idname}}')"><img src="{sugar_getimagepath file="id-ff-add.png"}"></button>
<input type="hidden" id="{{$idname}}_max_rows" name="{{$idname}}_max_rows" value="0" />

<div id="{{$idname}}_template" style="display:none">
	<div id="{{$idname}}_row_template">
	<input type="text" id="{{$idname}}_name_template" name="{{$idname}}_name_template" class="sqsEnabled" autocomplete="off" /> 
	<input type="hidden" id="{{$idname}}_id_template" name="{{$idname}}_id_template" />
	<button type="button" id="{{$idname}}_btn_remove_template">
		<img src="{sugar_getimagepath file="id-ff-remove.png"}" class="id-ff-remove">
	</button>
	</div>
</div>



<div id="{{$idname}}_main"></div>

<script type="text/javascript">
{literal}

// prepare sugar quick search array
if(typeof sqs_objects == 'undefined'){var sqs_objects = []; }

YUI.namespace('{/literal}{{$idname}}{literal}');
YUI().use('node',
	function(Y) {
	
		// initialize
		var cntRows = 0;
		var divMain = Y.one("#{/literal}{{$idname}}{literal}_main");
		var divTemplate = Y.one("#{/literal}{{$idname}}{literal}_template");
		var cntMax = Y.one("#{/literal}{{$idname}}{literal}_max_rows");
	
		// add empty row
		YUI.{/literal}{{$idname}}{literal}.addRow = function ( idName ) {
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
            YAHOO.util.Event.onContentReady(idName+"_row_"+currRow,(function(){sqs_objects["EditView_"+idName+"_name_"+currRow]={"form":"EditView","method":"query","modules":["{/literal}{$inline_module}{literal}"],"group":"or","field_list":["name","id"],"populate_list":["EditView_"+idName+"_name_"+currRow,idName+"_id_"+currRow],"conditions":[{"name":"name","op":"like_custom","end":"%","value":""}],"required_list":[idName+"_name_"+currRow],"order":"name","limit":"30","no_match_text":"No Match"};QSProcessedFieldsArray["EditView_"+idName+"_name_"+currRow]=false;enableQS(true);}));

			// setup remove button action
			Y.one("#{/literal}{{$idname}}{literal}_btn_remove_"+cntRows).on('click', 
					function(e) { YUI.{/literal}{{$idname}}{literal}.delRow(e);});

		};

		// remove row
		YUI.{/literal}{{$idname}}{literal}.delRow = function(e) {
			rowId = e._currentTarget.id.replace(/_btn_remove_/gi,"_row_");
			rowNode = Y.one("#"+rowId);
			rowNode.get('parentNode').removeChild(rowNode);
		};
		
		// parse return data from popup
		YUI.{/literal}{{$idname}}{literal}.set_return_for_editview = function(popup_reply_data) {
			var form_name = popup_reply_data.form_name;
			var user = popup_reply_data.name_to_value_array;
			
			// add row
			YUI.{/literal}{{$idname}}{literal}.addRow("{/literal}{{$idname}}{literal}");
			Y.one("#{/literal}{{$idname}}{literal}_name_"+cntRows).set('value',user.name);
			Y.one("#{/literal}{{$idname}}{literal}_id_"+cntRows).set('value',user.id);

		};
		
		{/literal}

		{* populate the present users/roles relationships dynamically using Smarty plugin *}

		
		{foreach from=$inline_one_to_many_populate item=record}
			// add present relationship
			YUI.{{$idname}}.addRow("{{$idname}}");
			Y.one("#{{$idname}}_name_"+cntRows).set('value','{$record.name}');
			Y.one("#{{$idname}}_id_"+cntRows).set('value','{$record.id}');
		{/foreach}
		
		{literal}
			
	}
);

{/literal}
</script>
