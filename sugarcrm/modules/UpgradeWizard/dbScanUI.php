<?php
//FILE SUGARCRM flav=int ONLY
///////////////////////////////////////////////////////////////////////////////
////	STANDARD REQUIRED SUGAR INCLUDES AND PRESETS
function dbFilesExist(){
	$exists = '0';
	if(file_exists(sugar_cached('dbscan/schema_inconsistencies.txt'))){
		$exists = '1';
	}
	return $exists;
}
$dbfile = '0';
$dbfile = dbFilesExist();
echo "
<form><link rel='stylesheet' type='text/css' href='include/javascript/yui-old/assets/container.css' />
<div  id='checkingDiv' style='display:none''>
   <table cellspacing='0' cellpadding='0' border='0'>
   <tr><td>
        <p><!--not_in_theme!--><img src='modules/UpgradeWizard/processing.gif' alt='Processing'> <br>May take sometime</p>

    </td></tr>
</table>
</div>
<table cellpadding='3' cellspacing='0' border='0'><tr>
<td class='dataField' width='20%' <b>Perform a full DB Scan</b>
	<div id='chkSc' name='chkSc'>
	<table>
		<tr>
			<span sugar='slot9b'>&nbsp;<input type='button' onclick ='dbScanLaunch();' class='button' value='Launch DB Scan'>
			</span sugar='slot'>
   			</tr>
   		</table>
   		</div>
	</td>
</tr>
</table>
<div id='dbScan' name='dbScan' style='display:none'>
<table cellpadding='3' cellspacing='0' border='0'>
	<tr>
		<th colspan='2' align='center'>
			<h1><span class='error'><b>************************************************************************</b></span></h1>
			<h1><span class='error'><b>Schema_Inconsistentcies.txt file is generated after the DB Scan </b></span></h1>

			<h1><span class='error'><b><a href=cache/dbscan/schema_inconsistencies.txt>Download DB Scan Results File</a></b></span></h1>
			<h1><span class='error'><b>************************************************************************</b></span></h1>
		</th>
	</tr>
</table></div>
</form>
<script>function dbScanLaunch(){
	//launch the check and ajax call etc...
	var args = {    width:'300px',
	                        modal:true,
	                        fixedcenter: true,
	                        constraintoviewport: false,
	                        underlay:'shadow',
	                        close:false,
	                        draggable:true,

	                        effect:{effect:YAHOO.widget.ContainerEffect.FADE, duration:.5}
	                       } ;
	msg_panel = new YAHOO.widget.Panel('p_msg', args);
	var s = 0;
	//If we haven't built our panel using existing markup,
	//we can set its content via script:
	//msg_panel.setHeader('Schema Check going');

	msg_panel.setBody(document.getElementById('checkingDiv').innerHTML);

	currMsg = 'DB Scan is in progress ';

	//timedCount(currMsg);
	//timedCount();
	msg_panel.setHeader(currMsg);
	msg_panel.render(document.body);
	msgPanel = msg_panel;
	 var callback = {
	     success:function(r) {
	       //alert(r.responseText);
	       msgPanel.hide();
		   //SUGAR.util.evalScript(document.getElementById('relation_id').innerHTML=r.responseText);
		   document.getElementById('dbScan').style.display='';
	     }
	}
	document.getElementById('dbScan').style.display='none';
	msgPanel.show;
	postData = '&module=UpgradeWizard&action=dbScan&to_pdf=1';
	YAHOO.util.Connect.asyncRequest('POST', 'index.php', callback, postData);
	}
function dbFiles(){
		if($dbfile=='1'){
			document.getElementById('dbScan').style.display = '';
		}
	}  dbFiles(); </script>		"

?>