<table class="moduleTitle" border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>
<tr><td valign="top"><h2>Click to Dial Settings:</h2></td>
</tr>
</tbody>
</table>
<br>
<form name="EditView" action="index.php" method="post" action="index.php">
<input type="hidden" name="module" value="Administration" />
<input type="hidden" name="action" value="ConfigureDialSettings" />
<input type="hidden" name="save" value="1" />
<input type="hidden" id="prepend" name="prepend" value="{$PREPEND}" />
<input type="hidden" id="dial_out_no" name="dial_out_no" value="{$DIAL_OUT_NO}" />
<input type="hidden" id="strip_out" name="strip_out" value="{$STRIP_OUT}" />
<input type="hidden" id="old_country" value="{$COUNTRY_CODE}" />
<input type="hidden" id="old_itl" value="{$ITL_CODE}" />
<input type="hidden" id="old_area" value="{$AREA_CODE}" />
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td style="padding-bottom: 2px;">
	<input id="save" type="button" class="button"
		title="{$APP.LBL_SAVE_BUTTON_TITLE}"
		accesskey="{$APP.LBL_SAVE_BUTTON_KEY}"
		{literal}
		onclick="if(check_form('EditView')){runJob('normalize');}"
		{/literal}
		value="{$APP.LBL_SAVE_BUTTON_LABEL}"
	/>
	<input type="submit" name="button" class="button"
		title="{$APP.LBL_CANCEL_BUTTON_TITLE}"
		accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}"
		onclick="this.form.action.value='index'; this.form.module.value='Administration';"
		value="{$APP.LBL_CANCEL_BUTTON_LABEL}"
	/>
</td>
<td align="right" nowrap="nowrap"><span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span> {$APP.NTC_REQUIRED}</td>
</tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="edit view">
<tr><td>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<th colspan="4" align="left" scope="row"><h4>General</h4></th>
		</tr>
		<tr>
		<td width="15%" scope="row" >{$MOD.LBL_TAPIDIAL_ON} <img border="0" src="themes/default/images/helpInline.gif" onmouseover="return helpText('{$MOD.LBL_TAPIDIAL_ON_DESC}')" onmouseout="return nd();"></td>
		<td width="35%" align="left" scope="row"><input type='hidden' name='system_tapidial_on' value='0'><input id="click_to_dial_on" name="system_tapidial_on" value="1" class="checkbox" tabindex='1' type="checkbox" {$system_tapidial_on_checked} ></td>
		<td width="15%" scope="row">&nbsp;</td>
		<td width="35%" scope="row">&nbsp;</td>
		</tr><tr>
		<td scope="row">{$MOD.LBL_DIAL_CREATE_CALL} <img border="0" src="themes/default/images/helpInline.gif" onmouseover="return helpText('{$MOD.LBL_DIAL_CREATE_CALL_DESC}')" onmouseout="return nd();"></td>
		<td scope="row"><input type='hidden' name='system_create_call_on_dial' value='0'><input id="create_call" name="system_create_call_on_dial" value="1" class="checkbox" tabindex='1' type="checkbox" {$system_create_call_on_dial_checked} ></td>
		<td scope="row">&nbsp;</td>
		<td scope="row">&nbsp;</td>
		</tr>
	</table>
</td></tr></table>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="edit view">
<tr><td>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<th colspan="4" align="left" scope="row"><h4>Location Information</h4></th>
	</tr>
	<tr>
	<td width="15%" scope="row">{$MOD.LBL_DEFAULT_COUNTRY_CODE} <span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span> <img border="0" src="themes/default/images/helpInline.gif" onmouseover="return helpText('Example &quot;1&quot; for USA, or &quot;61&quot; for Australia.')" onmouseout="return nd();"></td>
	<td width="35%" scope="row"><input id="country_code" type="text" name="country_code" value="{$COUNTRY_CODE}"></td>
	<td width="15%" scope="row">&nbsp;</td>
	<td width="35%" scope="row">&nbsp;</td>
	</tr>
	<tr>
	<td scope="row">{$MOD.LBL_DEFAULT_ITL_CODE} <span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span> <img border="0" src="themes/default/images/helpInline.gif" onmouseover="return helpText('This is the number you dial to call internationally.<br/>Example: &quot;011&quot; for USA, or &quot;0011&quot; for Australia.')" onmouseout="return nd();"></td>
	<td scope="row"><input id="itl_code" type="text" name="itl_code" value="{$ITL_CODE}"></td>
	<td scope="row">&nbsp;</td>
	<td scope="row">&nbsp;</td>
	</tr>
	<tr>
	<td scope="row">{$MOD.LBL_DEFAULT_AREA_CODE} <img border="0" src="themes/default/images/helpInline.gif" onmouseover="return helpText('Enter your local area code.<br/>This is not required within the United States.')" onmouseout="return nd();"></td>
	<td scope="row"><input id="area_code" type="text" name="area_code" value="{$AREA_CODE}"></td>
	<td scope="row">&nbsp;</td>
	<td scope="row">&nbsp;</td>
	</tr>
	</table>
</td></tr></table>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td style="padding-bottom: 2px;">
	<input id="save2" type="button" class="button"
		title="{$APP.LBL_SAVE_BUTTON_TITLE}"
		accesskey="{$APP.LBL_SAVE_BUTTON_KEY}"
		{literal}
		onclick="if(check_form('EditView')){runJob('normalize');}"
		{/literal}
		value="{$APP.LBL_SAVE_BUTTON_LABEL}"
	/>
	<input type="submit" name="button" class="button"
		title="{$APP.LBL_CANCEL_BUTTON_TITLE}"
		accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}"
		onclick="this.form.action.value='index'; this.form.module.value='Administration';"
		value="{$APP.LBL_CANCEL_BUTTON_LABEL}"
	/>
	<div id="loading_normalize" style="display:none; color: orange">Updating phone numbers <img src="fonality/include/images/ajax-loader.gif"></div><div id="completed_normalize" style="display:none; color: green">phone numbers updated.</div>
</td>
<td align="right" nowrap="nowrap">&nbsp;</td>
</tr>
</table>
</form>
<script type='text/javascript' src='include/javascript/sugar_grp_overlib.js'></script>
<script type="text/javascript">
{literal}
function runJob(idname){
	if(document.getElementById('old_country').value != document.getElementById('country_code').value ||
	   document.getElementById('old_itl').value != document.getElementById('itl_code').value ||
	   document.getElementById('old_area').value != document.getElementById('area_code').value
	){
		document.getElementById('save').disabled = true;
		document.getElementById('save2').disabled = true;
		document.getElementById('completed_'+idname).style.display = "none";
		document.getElementById('loading_'+idname).style.display = "block";
		document.getElementById('old_country').value = document.getElementById('country_code').value;
		document.getElementById('old_itl').value = document.getElementById('itl_code').value;
		document.getElementById('old_area').value = document.getElementById('area_code').value;
	
		var create_call = 0;
		if(document.getElementById('create_call').checked) {
			create_call = 1;
		}
		var click_to_dial_on = 0;
		if(document.getElementById('click_to_dial_on').checked) {
			click_to_dial_on = 1;
		}
		var script_file1 = 'index.php?module=Administration&action=ConfigureDialSettings&ajax=1&area_code=' + 
							document.getElementById('area_code').value + 
							'&itl_code=' + document.getElementById('itl_code').value +
							'&country_code=' + document.getElementById('country_code').value +
							'&system_create_call_on_dial=' + create_call +
							'&system_tapidial_on=' + click_to_dial_on +
							'&prepend=' + document.getElementById('prepend').value +
							'&dial_out_no=' + document.getElementById('dial_out_no').value +
							'&strip_out=' + document.getElementById('strip_out').value;
		var script_file2 = 'uae_initial_normalize.php';
	
		my_http_fetch_async(script_file1, script_file2, idname);
	} else {
		document.EditView.submit();
	}
}

function helpText(txt){
	return overlib(txt, FGCLASS, 'olFgClass', CGCLASS, 'olCgClass', BGCLASS, 'olBgClass', TEXTFONTCLASS, 'olFontClass', CAPTIONFONTCLASS, 'olCapFontClass', CLOSEFONTCLASS, 'olCloseFontClass', WIDTH, -1, NOFOLLOW, 'ol_nofollow' );
}

function my_http_fetch_async(url,url2,thediv){
	global_xmlhttp = getXMLHTTPinstance();
	var method = 'POST';
	try {
		global_xmlhttp.open(method, url,true);
	}
	catch(e){
		alert('message:'+e.message+":url:"+url);
	}
	global_xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	global_xmlhttp.onreadystatechange = function() {
		if(global_xmlhttp.readyState==4) {
			if(global_xmlhttp.status == 200) {
				if(url2 != ''){
					my_http_fetch_async(url2,'',thediv);
				}
				else {
					document.getElementById('completed_'+thediv).style.display = "block";
					var responseText = global_xmlhttp.responseText;
					if(responseText != ''){
						document.getElementById('completed_'+thediv).innerHTML = responseText;
					}
					document.getElementById('loading_'+thediv).style.display = "none";
					document.getElementById('save').disabled = false;
					document.getElementById('save2').disabled = false;
				}
			} else {
				alert("Internal Error");
			}
		}
	}
	global_xmlhttp.send(null);
}
{/literal}
</script>