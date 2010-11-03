{literal}
<script type="text/javascript">
function verifyCredentials(){
	document.getElementById('ajax_loader').style.display = "inline";

	var script_file = 'index.php?module=fonuae_PBXSettings&action=verify&to_pdf=1&username=' + document.getElementById('username').value + '&pass=' + encodeURIComponent(document.getElementById('password').value);
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
				var responseText = global_xmlhttp.responseText;
				document.getElementById('ajax_loader').style.display = "none";
				if(responseText != 0){
					document.getElementById('server_id').value = responseText;
					document.EditView.submit();
				} else {
					alert("Incorrect credentials. Please check and try again");
				}
			} else {
				document.getElementById('ajax_loader').style.display = "none";
				alert("Internal Error");
			}	
		}
	}
	global_xmlhttp.send(null);
}
</script>
{/literal}