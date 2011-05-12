<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
*The contents of this file are subject to the SugarCRM Professional End User License Agreement
*("License") which can be viewed at http://www.sugarcrm.com/EULA.
*By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
*not use this file except in compliance with the License. Under the terms of the license, You
*shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
*otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
*service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
*of a third party.  Use of the Software may be subject to applicable fees and any use of the
*Software without first paying applicable fees is strictly prohibited.  You do not have the
*right to remove SugarCRM copyrights from the source code or user interface.
* All copies of the Covered Code must include on each user interface screen:
* (i) the "Powered by SugarCRM" logo and
* (ii) the SugarCRM copyright notice
* in the same form as they appear in the distribution.  See full license for requirements.
*Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
*to the License for the specific language governing these rights and limitations under the License.
*Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
********************************************************************************/

// $Id: dbConfig.php 15268 2006-08-01 01:12:01 +0000 (Tue, 01 Aug 2006) eddy $
global $sugar_version, $js_custom_version;


if(empty($_SESSION['setup_db_host_name'])){
      $_SESSION['setup_db_host_name'] = (isset($sugar_config['db_host_name']))  ? $sugar_config['db_host_name'] :  $_SERVER['SERVER_NAME'];
}

if( !isset( $install_script ) || !$install_script ){
	die($mod_strings['ERR_NO_DIRECT_SCRIPT']);
}


// DB split 
$oci8sid = '';
$createDbCheckbox = '';
$createDb = (isset($_SESSION['setup_db_create_database']) && !empty($_SESSION['setup_db_create_database'])) ? 'checked="checked"' : '';
$dropCreate = (isset($_SESSION['setup_db_drop_tables']) && !empty($_SESSION['setup_db_drop_tables'])) ? 'checked="checked"' : ''; 
$instanceName = '';
if (isset($_SESSION['setup_db_host_instance']) && !empty($_SESSION['setup_db_host_instance'])){
	$instanceName = $_SESSION['setup_db_host_instance'];
}

if($_SESSION['setup_db_type'] == 'oci8') {
//BEGIN SUGARCRM flav=ent ONLY
	$dbSplit1 = '<input type="hidden" name="setup_db_host_name" value="'.$_SESSION['setup_db_host_name'].'" /></td>';
    $dbSplit1 .= '<tr><td colspan="3" align="left">'.$mod_strings['LBL_DBCONFIG_MSG1'].'</td></tr>';    
	$oci8sid = "(SID from tnsnames.ora)";
	$dbUser = '<input type=hidden name="setup_db_create_sugarsales_user" value="no" />';
//END SUGARCRM flav=ent ONLY
}else {
    
    $host_lbl = $mod_strings['LBL_DBCONF_HOST_NAME'];
    if($_SESSION['setup_db_type'] == 'mssql') {
        $host_lbl = $mod_strings['LBL_DBCONF_HOST_NAME_MSSQL'];    
    }
    
	$dbSplit1 = '<tr><td colspan="3" align="left">'.$mod_strings['LBL_DBCONFIG_MSG2'].' </td></tr>
        <tr>
		 <td><span class="required">*</span></td>
		 <td nowrap><b>'.$host_lbl.'</b></td>
		 <td align="left">
			<input type="text" name="setup_db_host_name" id="setup_db_host_name" value="'.$_SESSION['setup_db_host_name'].'" />';
			if (isset($_SESSION['setup_db_type']) && $_SESSION['setup_db_type'] =='mssql'){
				$dbSplit1 .= '&nbsp;\&nbsp;<input type="text" name="setup_db_host_instance" id="setup_db_host_instance" value="'.$instanceName.'" />';
			}
		$dbSplit1 .= '</td>
	</tr>';
	

}




///////////////////////////////////////////////////////////////////////////////
////	BEGIN PAGE OUTPUT

$out =<<<EOQ
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <title>{$mod_strings['LBL_WIZARD_TITLE']} {$mod_strings['LBL_DBCONF_TITLE']}</title>
    <link rel="stylesheet" href="install/install.css" type="text/css" />
    <script type="text/javascript" src="install/installCommon.js"></script>
    <script type="text/javascript" src="install/dbConfig.js"></script>
    <link REL="SHORTCUT ICON" HREF="include/images/sugar_icon.ico">
    <script src="include/javascript/sugar_grp1_yui.js?s={$sugar_version}&c={$js_custom_version}"></script>
    <script type="text/javascript">
    <!--
    if ( YAHOO.env.ua )
        UA = YAHOO.env.ua;
    -->
    </script>
    <link rel='stylesheet' type='text/css' href='include/javascript/yui/build/container/assets/container.css' />     

</head>
EOQ;
$out .= '<body onload="document.getElementById(\'defaultFocus\').focus();">';

$out2 =<<<EOQ2
<form action="install.php" method="post" name="setConfig" id="form">
<input type='hidden' name='setup_db_drop_tables' id='setup_db_drop_tables' value='false'>
<input type="hidden" id="hidden_goto" name="goto" value="{$mod_strings['LBL_BACK']}" />
<table cellspacing="0" cellpadding="0" border="0" align="center" class="shell">

      <tr><td colspan="2" id="help"><a href="{$help_url}" target='_blank'>{$mod_strings['LBL_HELP']} </a></td></tr>
    <tr>
      <th width="500">
		<p>
		<img src="{$sugar_md}" alt="SugarCRM" border="0">
		</p>
		{$mod_strings['LBL_DBCONF_TITLE']}
	</th>
	<th width="200" height="30" style="text-align: right;"><a href="http://www.sugarcrm.com" target="_blank">
		<IMG src="include/images/sugarcrm_login.png" width="145" height="30" alt="SugarCRM" border="0"></a>
        </th>
</tr>
<tr>
	<td colspan="2">
<div id="errorMsgs" style="display:none"></div>
<div class="required">{$mod_strings['LBL_REQUIRED']}</div>
<table width="100%" cellpadding="0" cellpadding="0" border="0" class="StyleDottedHr">
<tr><th colspan="3" align="left" >{$mod_strings['LBL_DBCONF_TITLE_NAME']} </td></tr>

<tr><td colspan="3" align="left">&nbsp;{$mod_strings['LBL_DBCONFIG_MSG3']}</td></tr>
<tr><td width='1%'><span class="required">*</span></td>
    <td width='60%' nowrap><b>{$mod_strings['LBL_DBCONF_DB_NAME']} {$oci8sid}</b></td>
    <td width='35%' nowrap align="left">
         <input type="text" name="setup_db_database_name"  value="{$_SESSION['setup_db_database_name']}"><br>&nbsp;
    </td>
</tr>



{$dbSplit1}


<tr><th colspan="3" align="left">{$mod_strings['LBL_DBCONF_TITLE_USER_INFO']} </td></tr>
<tr><td colspan="3" align="left">{$mod_strings['LBL_DBCONFIG_B_MSG1']}</td></tr>
<tr>
    <td><span class="required">*</span></td>
    <td nowrap><b>{$mod_strings['LBL_DBCONF_DB_ADMIN_USER']}</b></td>
    <td nowrap align="left">
         <input type="text" name="setup_db_admin_user_name" maxlength="30" value="{$_SESSION['setup_db_admin_user_name']}" autocomplete="off"/>
    </td>
</tr>
<tr>
    <td></td>
    <td nowrap><b>{$mod_strings['LBL_DBCONF_DB_ADMIN_PASSWORD']}</b></td>
    <td nowrap align="left"><input type="password" name="setup_db_admin_password" value="{$_SESSION['setup_db_admin_password']}" autocomplete="off" /></td></tr>
    </table>
EOQ2;

//if we are installing in custom mode, include the following html 
if($_SESSION['setup_db_type'] != 'oci8' ){

// create / set db user dropdown
$auto_select = '';$provide_select ='';$create_select = '';$same_select = '';
if(isset($_SESSION['dbUSRData'])){
//    if($_SESSION['dbUSRData']=='auto')    {$auto_select ='selected';}
    if($_SESSION['dbUSRData']=='provide') {$provide_select ='selected';}
if(isset($_SESSION['install_type'])  && !empty($_SESSION['install_type'])  && strtolower($_SESSION['install_type'])=='custom'){
    if($_SESSION['dbUSRData']=='create')  {$create_select ='selected';}
}
    if($_SESSION['dbUSRData']=='same')  {$same_select ='selected';}
}else{
    $same_select ='selected';
}
$dbUSRDD   = "<select name='dbUSRData' id='dbUSRData' onchange='toggleDBUser();'>";
$dbUSRDD  .= "<option value='provide' $provide_select>".$mod_strings['LBL_DBCONFIG_PROVIDE_DD']."</option>";
$dbUSRDD  .= "<option value='create' $create_select>".$mod_strings['LBL_DBCONFIG_CREATE_DD']."</option>";
$dbUSRDD  .= "<option value='same' $same_select>".$mod_strings['LBL_DBCONFIG_SAME_DD']."</option>";
$dbUSRDD  .= "</select><br>&nbsp;";




$out2 .=<<<EOQ2

<table width="100%" cellpadding="0" cellpadding="0" border="0" class="StyleDottedHr">
<tr><td colspan="3" align="left"><br>{$mod_strings['LBL_DBCONFIG_SECURITY']}</td></tr>
<tr><td width='1%'>&nbsp;</td><td width='60%'><div id='sugarDBUser'><b>{$mod_strings['LBL_DBCONF_SUGAR_DB_USER']}</b></div>&nbsp;</td><td width='35%'>$dbUSRDD</td></tr>
</table>

<span id='connection_user_div' style="display:none">
<table width="100%" cellpadding="0" cellpadding="0" border="0" class="StyleDottedHr">
    <tr>
        <td width='1%'><span class="required">*</span></td>
        <td nowrap width='60%'><b>{$mod_strings['LBL_DBCONF_SUGAR_DB_USER']}</b></td>
        <td  width='35%'nowrap align="left">
         <input type="text" name="setup_db_sugarsales_user" maxlength="16" value="{$_SESSION['setup_db_sugarsales_user']}" />
        </td>
</tr>
<tr>
    <td>&nbsp;</td>
    <td nowrap><b>{$mod_strings['LBL_DBCONF_DB_PASSWORD']}</b></td>
    <td nowrap align="left"><input type="password" name="setup_db_sugarsales_password" value="{$_SESSION['setup_db_sugarsales_password']}" /></td>
</tr>
<tr>
    <td>&nbsp;</td>
    <td nowrap><b>{$mod_strings['LBL_DBCONF_DB_PASSWORD2']}</b></td>
    <td nowrap align="left"><input type="password" name="setup_db_sugarsales_password_retype" value="{$_SESSION['setup_db_sugarsales_password_retype']}" /></td>
</tr></table>
</span>

EOQ2;
}

//set demo dropdown
//$supported_demodata = array(
//	'en_us' => 'English (US)',
//	'zh_cn' => '简体中文',
//	'ja_jp' => 'Japanese - 日本語',
//);
$demoDD = "<select name='demoData' id='demoData'><option value='no' >".$mod_strings['LBL_NO']."</option><option value='yes'>".$mod_strings['LBL_YES']."</option>";
//foreach($supported_demodata as $key => $v){
//	// mssql is broken for mbcs
//	if( ($_SESSION['setup_db_type'] == 'mssql') && ($key != 'en_us'))
//		continue;
//	$selected = '';
//	if($_SESSION['demoData'] == $key)
//		$selected = "selected"; 
//	$demoDD .="<option value='$key' $selected>".$v."</option>";
//}
$demoDD .= "</select><br>&nbsp;";

   
$out3 =<<<EOQ3
<table width="100%" cellpadding="0" cellpadding="0" border="0" class="StyleDottedHr">
<tr><th colspan="3" align="left">{$mod_strings['LBL_DBCONF_DEMO_DATA_TITLE']}</th></tr>
<tr>
    <td width='1%'>&nbsp;</td>
    <td  width='60%'nowrap><b>{$mod_strings['LBL_DBCONF_DEMO_DATA']}</b></td>
    <td  width='35%'nowrap align="left">
        {$demoDD}
    </td>
</tr>
</table>
EOQ3;
   
   
$out4 =<<<EOQ4
</td>
</tr>
<tr>
<td align="right" colspan="2">
<hr>
     <input type="hidden" name="current_step" value="{$next_step}">
     <table cellspacing="0" cellpadding="0" border="0" class="stdTable">
        <tr>
            <td>
                <input class="button" type="button" name="goto" value="{$mod_strings['LBL_BACK']}" onclick="document.getElementById('form').submit();" />
            </td>
            <td>
                <input class="button" type="button" name="goto" id="defaultFocus" value="{$mod_strings['LBL_NEXT']}" onClick="callDBCheck();"/>
            </td>
        </tr>
     </table>
</td>
</tr>
</table>
</form>
<br>

<script>
function toggleDBUser(){
     if(typeof(document.getElementById('dbUSRData')) !='undefined'
     && document.getElementById('dbUSRData') != null){

        ouv = document.getElementById('dbUSRData').value;
        if(ouv == 'provide' || ouv == 'create'){
            document.getElementById('connection_user_div').style.display = '';
            document.getElementById('sugarDBUser').style.display = 'none';
        }else{
            document.getElementById('connection_user_div').style.display = 'none';
            document.getElementById('sugarDBUser').style.display = '';
        }
    }
}
    toggleDBUser();
    
var msgPanel;
function callDBCheck(){

            //begin main function that will be called
            ajaxCall = function(msg_panel){
                //create success function for callback

                getPanel = function() {        
                var args = {    width:"300px", 
                                modal:true,
                                fixedcenter: true,
                                constraintoviewport: false,  
                                underlay:"shadow",  
                                close:false, 
                                draggable:true, 
                                
                                effect:{effect:YAHOO.widget.ContainerEffect.FADE, duration:.5}
                               } ; 
                        msg_panel = new YAHOO.widget.Panel('p_msg', args);

                        msg_panel.setHeader("{$mod_strings['LBL_LICENSE_CHKDB_HEADER']}"); 
                        msg_panel.setBody(document.getElementById("checkingDiv").innerHTML);
                        msg_panel.render(document.body);
                        msgPanel = msg_panel;
                } 
                
                
                passed = function(url){
                    document.setConfig.goto.value="{$mod_strings['LBL_NEXT']}";
                    document.getElementById('hidden_goto').value="{$mod_strings['LBL_NEXT']}";
                    document.setConfig.current_step.value="{$next_step}";
                    document.setConfig.submit();
                }
                success = function(o) {
                    
                    //condition for just the preexisting database
                    if (o.responseText.indexOf('preexeest')>=0){

                        //  throw confirmation message
                        msg_panel.setBody(document.getElementById("sysCheckMsg").innerHTML);
                        msg_panel.render(document.body);
                        msgPanel = msg_panel;
                        document.getElementById('accept_btn').focus();
                    //condition for no errors
                    }else if (o.responseText.indexOf('dbCheckPassed')>=0){
                        //make navigation
                        passed("install.php?goto={$mod_strings['LBL_NEXT']}");

                    //condition for other errors    
                    }else{
                        //turn off loading message
                        msgPanel.hide();
                        document.getElementById("errorMsgs").innerHTML = o.responseText;
                        document.getElementById("errorMsgs").style.display = '';
                        return false;
                    }

                    
                }//end success
        
                //set loading message and create url

                postData = "checkDBSettings=true&to_pdf=1&sugar_body_only=1";
                postData += "&setup_db_database_name="+document.setConfig.setup_db_database_name.value;
                if(typeof(document.setConfig.setup_db_host_instance) != 'undefined'){
                    postData += "&setup_db_host_instance="+document.setConfig.setup_db_host_instance.value;
                }
                postData += "&setup_db_host_name="+document.setConfig.setup_db_host_name.value;
                postData += "&setup_db_admin_user_name="+document.setConfig.setup_db_admin_user_name.value;
                postData += "&setup_db_admin_password="+document.setConfig.setup_db_admin_password.value;
                if(typeof(document.setConfig.setup_db_sugarsales_user) != 'undefined'){
                    postData += "&setup_db_sugarsales_user="+document.setConfig.setup_db_sugarsales_user.value;
                }
                if(typeof(document.setConfig.setup_db_sugarsales_password) != 'undefined'){
                    postData += "&setup_db_sugarsales_password="+document.setConfig.setup_db_sugarsales_password.value;
                }
                if(typeof(document.setConfig.setup_db_sugarsales_password_retype) != 'undefined'){
                    postData += "&setup_db_sugarsales_password_retype="+document.setConfig.setup_db_sugarsales_password_retype.value;
                }
                if(typeof(document.setConfig.dbUSRData) != 'undefined'){
                    postData += "&dbUSRData="+document.getElementById('dbUSRData').value;
                }

EOQ4;

$out_dd = 'postData += "&demoData="+document.setConfig.demoData.value;';
$out5 =<<<EOQ5
                postData += "&to_pdf=1&sugar_body_only=1";                                                
                 
                //if this is a call already in progress, then just return               
                    if(typeof ajxProgress != 'undefined'){ 
                        return;                            
                    }

                getPanel();
                msgPanel.show;
                var ajxProgress = YAHOO.util.Connect.asyncRequest('POST','install.php', {success: success, failure: success}, postData);                
                        
    
            };//end ajaxCall method
              ajaxCall();
            return;   
}

function confirm_drop_tables(yes_no){

        if(yes_no == true){
            document.getElementById('setup_db_drop_tables').value = true;
           //make navigation
                    document.setConfig.goto.value="{$mod_strings['LBL_NEXT']}";
                    document.getElementById('hidden_goto').value="{$mod_strings['LBL_NEXT']}";
                    document.setConfig.current_step.value="{$next_step}";
                    document.setConfig.submit();
        }else{
            //set drop tables to false
            document.getElementById('setup_db_drop_tables').value = false;
            msgPanel.hide();
        }
}
    
</script>



           <div id="checkingDiv" style="display:none">
           <table cellspacing="0" cellpadding="0" border="0">
               <tr><td>
                    <p><img alt="{$mod_strings['LBL_LICENSE_CHKDB_HEADER']}" src='install/processing.gif'> <br>{$mod_strings['LBL_LICENSE_CHKDB_HEADER']}</p>
                </td></tr>
            </table>
            </div>

          <div id='sysCheckMsg' style="display:none">
           <table cellspacing="0" cellpadding="0" border="0" >
               <tr><td>
                    <p>{$mod_strings['LBL_DROP_DB_CONFIRM']}</p>
               </td></tr>
               <tr><td align='center'>
                    <input id='accept_btn' type='button' class='button' onclick='confirm_drop_tables(true)' value="{$mod_strings['LBL_ACCEPT']}">
                    <input type='button' class='button' onclick='confirm_drop_tables(false)' value="{$mod_strings['LBL_CANCEL']}">
                </td></tr>
            </table>
                
          <div>

</body>
</html>




EOQ5;




////	END PAGE OUTPUT
///////////////////////////////////////////////////////////////////////////////



echo $out.$out2;
//BEGIN SUGARCRM flav=pro ONLY
if(!isset($_SESSION['oc_install']) || $_SESSION['oc_install'] == false){
//END SUGARCRM flav=pro ONLY
    echo $out3;
//BEGIN SUGARCRM flav=pro ONLY
}
//END SUGARCRM flav=pro ONLY
echo $out4;
//BEGIN SUGARCRM flav=pro ONLY
if(!isset($_SESSION['oc_install']) || $_SESSION['oc_install'] == false){
//END SUGARCRM flav=pro ONLY
    echo $out_dd;
//BEGIN SUGARCRM flav=pro ONLY
}
//END SUGARCRM flav=pro ONLY
echo $out5;

?>