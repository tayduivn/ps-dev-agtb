/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

// $Id$
// Javascript for editviewdefs.
var formElement=document.getElementById('form');
function onClickInit(){
    formElement.return_module.value=formElement.module.value;
    formElement.return_action.value='DetailView';
    formElement.return_id.value=formElement.record.value;
}
function onClickInitSubmit(actionType){
    onClickInit();
    switch(actionType){
        case 'delete':
            formElement.actionType.value='delete';
            break;
        case 'convert':
            formElement.actionType.value='convert';
            break;
        case 'archive':
            formElement.actionType.value='archive';
            break;
        case 'recover':
            formElement.actionType.value='recover';
            break;
        case 'toggle_on':
            formElement.actionType.value='toggle_on';
            break;
        case 'deploy':
            formElement.actionType.value='create';
            break;
        default:
            alert('JS Error');
    }
    formElement.action.value='CreateAction';
    ajaxStatus.showStatus(SUGAR.language.get('DCEInstances', 'LBL_ACTION_QUEUED'));
    setTimeout('document.getElementById("form").submit();',2000);
}
function onClickUpgrade(){
    onClickInit();
	formElement.action.value='DCEUpgradeStep2';
}
function onClickDelete(){
    var r=confirm(SUGAR.language.get('DCEInstances', 'LBL_CONFIRM_DELETE'));
    if (r==true){
        onClickInitSubmit('delete');
    }
    else{
        return false;
    }
}
function onClickDeploy(){
    record=document.getElementById('form').record.value;
    var callback = {
        success:function(o){
			switch(o.responseText){
                case '0':
                    var r=true;
                    break;
                case '1':
                    var r=confirm(SUGAR.language.get('DCEInstances', 'LBL_CONFIRM_USER_DEPLOY'));
                    break;
                case '2':
                    var r=confirm(SUGAR.language.get('DCEInstances', 'LBL_CONFIRM_CONTACT_DEPLOY'));
                    break;
                case '3':
                    var r=confirm(SUGAR.language.get('DCEInstances', 'LBL_CONFIRM_DEPLOY'));
                    break;
		        default:
		            alert('Ajax Error...');
                    var r=confirm(SUGAR.language.get('DCEInstances', 'LBL_CONFIRM_DEPLOY'));
		    }
		    if (r==true){
		        onClickInitSubmit('deploy');
		    }
		    else{
		        return false;
		    }
        },
        failure:function(o){
            alert(SUGAR.language.get('app_strings','LBL_AJAX_FAILURE'));
        }
    }
    YAHOO.util.Connect.asyncRequest('POST', 'index.php?module=DCEInstances&action=alert_on_deploy&to_pdf=1', callback, '&record=' + record);
}
function initDetailView(){
    document.getElementById('duplicate_button').value = SUGAR.language.get('DCEInstances', 'LBL_CLONE');
    document.getElementById('duplicate_button').style.display='none';
    if(document.getElementById('instance_status').value=='new'){
        document.getElementById('dcedeploy_button').style.display='inline';
    }
    if(document.getElementById('instance_status').value=='live'){
        document.getElementById('dcesupportuser_button').style.display='inline';
        document.getElementById('duplicate_button').style.display='inline';
        document.getElementById('dcearchive_button').style.display='inline';

        if(document.getElementById('instance_type').value=='evaluation'){
            document.getElementById('dceconvertinstance_button').style.display='inline';
        }
        if(document.getElementById('instance_type').value=='production'){
            document.getElementById('dceupgrade_button').style.display='inline';
        }
    }
    if(document.getElementById('instance_status').value=='archived'){
        document.getElementById('dcerecover_button').style.display='inline';
    }
    
}
initDetailView();