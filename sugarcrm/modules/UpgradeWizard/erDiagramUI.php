<?php
//FILE SUGARCRM flav=int ONLY
///////////////////////////////////////////////////////////////////////////////
////	STANDARD REQUIRED SUGAR INCLUDES AND PRESETS
global $sugar_config;
$site_url =  $sugar_config['site_url'];

//$cwd = getcwd();
//echo $cwd;

function erFilesExist(){
	$exists = '0';
	if(file_exists($GLOBALS['sugar_config']['cache_dir'].'erschema/schema.sql')){
		$exists = '1';
	}
	return $exists;
}
$erfiles = '0';
$erfiles = erFilesExist();
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
<td class='dataField' width='20%' <b>ER Diagram Schema</b>
	<div id='chkSc' name='chkSc'>
	<table>
		<tr>
			<span sugar='slot9b'>&nbsp;<input type='button' onclick ='erDiagramLaunch();' class='button' value='Create ER Diagram Schema'>
			</span sugar='slot'>
   			</tr>
   		</table>
   		</div>
	</td>
</tr>
</table>
<div id='er_schema' name='er_schema' style='display:none'>
<table cellpadding='3' cellspacing='0' border='0'>

	<tr>
		<th colspan='2' align='left'>
			<h1><span class='error'><b>************************************************************************</b></span></h1>
			<span class='error'><b>DDL files for ER Diagram Schema and FK schema have been generated</b></span>

			<h1><span class='error'><b><a href=cache/erschema/schema.sql>Download Complete ER Diagram Schema DDL File</a></b></span></h1>
			</br>
			<h1><span class='error'><b><a href=cache/erschema/fkschema.sql>Download Foreign Keys Schema DDL File</a></b></span></h1>
			<h1><span class='error'><b>************************************************************************</b></span></h1>
			<h1><span class='error'><b><a href=cache/erschema/comments.sql>Download Schema Comments DDL File</a></b></span></h1>
			<h1><span class='error'><b>************************************************************************</b></span></h1>
		</th>
	</tr>
</table></div>
</form>
<script>function erDiagramLaunch(){
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

	currMsg = 'Preparing DDL for ER Diagram Schema ';

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
		   document.getElementById('er_schema').style.display='';
	     }
	}
	document.getElementById('er_schema').style.display='none';
	msgPanel.show;
	postData = '&module=UpgradeWizard&action=erDiagramTool&to_pdf=1';
	YAHOO.util.Connect.asyncRequest('POST', 'index.php', callback, postData);
	}
function erFiles(){
		if($erfiles=='1'){
			document.getElementById('er_schema').style.display = '';
		}
	}  erFiles();</script>
";
?>