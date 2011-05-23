<span id="{{$vardef.tags_module}}_{{$vardef.name}}_value">{if !empty({{sugarvar key='value' string=true}}) && ({{sugarvar key='value' string=true}} != '^^')}{multienum_to_array string={{sugarvar key='value' string=true}} assign="vals"}{foreach from=$vals item=item name=tags}<a href="index.php?module={{$vardef.tags_module}}&action=index&tags_search={$item},&query=true">{$item}</a>{if !$smarty.foreach.tags.last}, {/if}{/foreach}{/if}</span>

&nbsp;<img border=0 width=16 id="{{$vardef.tags_module}}_{{$vardef.name}}_load" style="display:none" src="{sugar_getimagepath file="loading.gif"}">
<a href="javascript:void(0);" onclick="SUGAR.TagFields.{{$vardef.tags_module}}_{{$vardef.name}}.switchToEdit();"><img border=0 id="{{$vardef.tags_module}}_{{$vardef.name}}_edit" src="{sugar_getimagepath file="edit_inline.png"}"></a>

<script type="text/javascript">
{literal}
if(typeof(SUGAR.TagFields) == 'undefined') SUGAR.TagFields = {};

SUGAR.TagFields.{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal} = {};
SUGAR.TagFields.{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}.switchToEdit = function(){
	SUGAR.TagFields.{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}.current_value = document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_value').textContent;
	SUGAR.TagFields.{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}.current_html = document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_value').innerHTML;
	document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_load').style.display='';
	document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_edit').style.display='none';
	
	success = function(res) {
		var response_array = JSON.parse(res.responseText);
		
		if(!response_array || res.responseText == ''){
			// Unset loading icon
			document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_load').style.display='none';
			document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_edit').style.display='';
			return false;
		}
		
		var editfield = document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_value');
		editfield.innerHTML = response_array["html"];
		editfield.innerHTML = editfield.innerHTML + "&nbsp;<button type=button onclick='SUGAR.TagFields.{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}.saveValue();'>Save</button>";
		editfield.innerHTML = editfield.innerHTML + "&nbsp;<button type=button onclick=\"document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_value').innerHTML = SUGAR.TagFields.{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}.current_html; document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_load').style.display='none'; document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_edit').style.display='';\">Cancel</button>";
		eval(response_array["javascript"]);
		document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_load').style.display='none';
		document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_edit').style.display='none';
	}
	
	failure = function(){
		document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_load').style.display='none';
		document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_edit').style.display='';
		SUGAR.TagFields.{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}.current_value = null;
		SUGAR.TagFields.{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}.current_html = null;
	}
	
	{/literal}
	var postData = "module=Accounts&action=ajaxtaginline&to_pdf=1&tag_module={{$vardef.tags_module}}&field={{$vardef.name}}&record={$fields.id.value}&current_value=" + escape(SUGAR.TagFields.{{$vardef.tags_module}}_{{$vardef.name}}.current_value);
	{literal}
	// AJAX Call
	var result = YAHOO.util.Connect.asyncRequest('POST','index.php', {success: success, failure: failure}, postData);
}

SUGAR.TagFields.{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}.saveValue = function(){
	document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_load').style.display='';
	document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_edit').style.display='none';
	success = function(res) {
		var response_array = JSON.parse(res.responseText);
		
		if(!response_array || res.responseText == ''){
			document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_value').innerHTML = SUGAR.TagFields.{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}.current_html;
			document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_load').style.display='none';
			document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_edit').style.display='';
			return false;
		}
		
		var new_detail = '';
		var counter = 0;
		for(k in response_array['tags']){
			new_detail = new_detail + '<a href="index.php?module={{$vardef.tags_module}}&action=index&tags_search='+response_array['tags'][k]+',&query=true">'+response_array['tags'][k]+'</a>';
			if(response_array['tags'].length - 1 != counter){
				new_detail = new_detail + ', ';
			}
			
			counter++;
		}
		document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_value').innerHTML = new_detail;
		document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_load').style.display='none';
		document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_edit').style.display='';
	}
	
	failure = function(){
		document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_value').innerHTML = SUGAR.TagFields.{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}.current_html;
		document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_load').style.display='none';
		document.getElementById('{/literal}{{$vardef.tags_module}}_{{$vardef.name}}{literal}_edit').style.display='';
	}
	
	{/literal}
	var postData = "module=Accounts&action=ajaxtaginlinesave&to_pdf=1&tag_module={{$vardef.tags_module}}&field={{$vardef.name}}&record={$fields.id.value}&save_value="+document.getElementById('{{$vardef.name}}_inline').value;
	{literal}
	// AJAX Call
	var result = YAHOO.util.Connect.asyncRequest('POST','index.php', {success: success, failure: failure}, postData);
}
{/literal}
</script>
