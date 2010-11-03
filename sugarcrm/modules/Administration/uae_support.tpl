<table class="moduleTitle" border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>
<tr><td valign="top"><h2>Support</h2></td>
</tr>
</tbody>
</table>
<br>
<p>Please click the &quot;Send Log&quot; button below to send log files to Fonality.<br/>
These files will help troubleshoot integration issues between SugarCRM and Fonality.<br/>
<br/>
Please contact Fonality support at 310-861-4300 after you have sent the log files.
</p>
<br/>
Server ID: <input type="text" id="server_id" name="server_id" size="10"> <input type="button" class="button" style="vertical-align:top" name="sendlog" id="sendlog" value="Send Log" onClick="sendLog()"><br/><br/>
<div id="ajax_loader" style="display:none"><img src="fonality/include/images/ajax-loader.gif"></div>
<div id="msg" style="display:none"></div>
{literal}
<script type="text/javascript">
function sendLog(){
	if(document.getElementById('server_id').value == '')
	{
		alert("Please enter your Server ID");
		return false;
	}
	document.getElementById('sendlog').disabled = true;
	document.getElementById('msg').style.display = "none";
	document.getElementById('ajax_loader').style.display = "block";

	var script_file = 'uae_send_log.php?server_id=' + document.getElementById('server_id').value;

	my_http_fetch_async(script_file);
}

function my_http_fetch_async(url){
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
				document.getElementById('msg').style.display = "block";
				var responseText = global_xmlhttp.responseText;
				if(responseText != ''){
					document.getElementById('msg').innerHTML = responseText;
				}
			} else {
				alert("Internal Error");
			}
			document.getElementById('ajax_loader').style.display = "none";
			document.getElementById('sendlog').disabled = false;
		}
	}
	global_xmlhttp.send(null);
}
</script>
{/literal}
