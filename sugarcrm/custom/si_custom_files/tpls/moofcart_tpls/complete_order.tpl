<!-- BEGIN: main -->
<script language='javascript'>
function toggleDisplay(div) {
	if(document.getElementById(div).style.display=='none') {
		document.getElementById(div).style.display='';
		document.getElementById(div+'_link').innerHTML = 'Hide Email';
	}
	else {
		document.getElementById(div).style.display='none';
		document.getElementById(div+'_link').innerHTML = 'Show Email';	
	}
}
</script>
<h3>The following will items will be done when the Order is completed</h3><br/>
<ul>
	<!-- BEGIN: message -->
	<li>
		<hr />
		<strong>{msg}</strong>
		<!-- BEGIN: custom -->
		<br /><br />
		<table border='0' cellpadding='0' cellspacing='0' width='60%'>
			<tr>
				<td width='33%'><strong>Field</strong></td>
				<td width='33%'><strong>Before</strong></td>
				<td width='33%'><strong>After</strong></td>
			</tr>
			<!-- BEGIN: custom_row -->
			<tr>
				<td width='33%'>{c.field}</td>
				<td width='33%'>{c.before}</td>
				<td width='33%'>{c.after}</td>
			</tr>
			<!-- END: custom_row -->
		</table>
		<!-- END: custom -->
	</li>
	<!-- END: message -->
	<!-- BEGIN: email -->
	<li>
		<ul>
			<li>To: {e.to}</li>
			<li>Subject: {e.subject}</li>
			<li>
				<a href='javascript:toggleDisplay("{e.name}");'><div id='{e.name_link}'>Show Email</div></a>
				<div id='{e.name}' style='display:none;'>
					{e.email}
				</div>
			</li>
		</ul>
	</li>
	<!-- END: email -->
</ul>
<!-- BEGIN: show_submit -->
<br />
<form method='POST' action='/index.php?module=Orders&action=CompleteOrder&record={record_id}'>
<input type='button' value='Cancel' name='cancel' onclick='window.location="/index.php?module=Orders&action=DetailView&record={record_id}";' />&nbsp;&nbsp;<input type='submit' name='submit' value='Complete Order' />
</form>
<!-- END: show_submit -->
<!-- END: main -->