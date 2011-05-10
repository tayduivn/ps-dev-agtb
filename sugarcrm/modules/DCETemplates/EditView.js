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

function uploadCheck(){
    //AJAX call for checking the zip file and retrieving the edition and the version.
    var callback = {
        upload:function(r){
	        var file_type = r.responseText;
	        switch(file_type){
	           case 'empty':
	              empty_field();
	              break;
	           case 'error':
	              alert(SUGAR.language.get('DCETemplates', 'ERR_UPLOAD_ERROR'));
	              empty_field();
	              break;
	           case 'other':
	              alert(SUGAR.language.get('DCETemplates', 'ERR_UPLOAD_OTHER'));
	              empty_field();
	              break;
	           case 'size':
	              alert(SUGAR.language.get('DCETemplates', 'ERR_UPLOAD_SIZE'));
	              empty_field();
	              break;
	           case 'none':
	              alert(SUGAR.language.get('DCETemplates', 'ERR_UPLOAD_NONE'));
	              empty_field();
	              break;
               case 'duplicate':
                  alert(SUGAR.language.get('DCETemplates', 'ERR_UPLOAD_DUPLICATE'));
                  empty_field();
                  break;
	           default:
	               values=file_type.split("^,^");
	               if(values[0]=='SUGARDCE'){
	                   document.getElementById('sugar_edition').value=values[1];
	                   document.getElementById('sugar_version').value=values[2];
	                   document.getElementById('template_name').value=values[3];
	                   document.getElementById('uploadTmpDir').value=values[4];
	                   document.getElementById('template_file').value=values[5];
	                   if(values[6]=='UPGRADE_EDITION'){
	                       edt=values[7].split(" | ");
	                       document.getElementById('upgrade_acceptable_edition').value=values[7];
	                       update_acc_edition(true);
	                       
	                   }
	                   if(values[8]=='UPGRADE_VERSION'){
	                       document.getElementById('upgrade_acceptable_version').value=values[9];
	                   }
	               }else{
	                   alert(SUGAR.language.get('DCETemplates', 'ERR_UPLOAD_NOT_RECOGNIZE'));
	                   empty_field();
	               }
	        }
	        document.getElementById('loading_img').style.display="none";
        },
        failure:function(r){
        alert(SUGAR.language.get('app_strings','LBL_AJAX_FAILURE'));
        }
    }
    if(document.getElementById('my_file').value){
        document.getElementById('loading_img').style.display="inline";
        YAHOO.util.Connect.setForm(document.getElementById('upload_form'), true,true);
        YAHOO.util.Connect.asyncRequest('POST', 'index.php?module=DCETemplates&action=UploadFileCheck&to_pdf=1', callback,null);
    }else{
        empty_field();
    }
}
function empty_field(){
    document.getElementById('my_file').value='';
    document.getElementById('sugar_version').value='';
    document.getElementById('sugar_edition').value='';
    document.getElementById('template_name').value='';
    document.getElementById('uploadTmpDir').value='';
    document.getElementById('ce_ckbox').checked=false;
    document.getElementById('pro_ckbox').checked=false;
    document.getElementById('upgrade_acceptable_edition').value='';
    document.getElementById('upgrade_acceptable_version').value='';
}

function update_acc_edition(from_acc_edition){
    if(from_acc_edition){
        var acc_arr=new Array();
        var acc=document.getElementById('upgrade_acceptable_edition').value;
        document.getElementById('ce_ckbox').checked=false;
        document.getElementById('pro_ckbox').checked=false;
        if(acc != ''){
            acc_arr=acc.split(" | ");
            for(x in acc_arr){
                if(isInteger(x)){
                    if(acc_arr[x]=='PRO'){
                        document.getElementById('pro_ckbox').checked=true;
                    }else if(acc_arr[x]=='CE'){
                        document.getElementById('ce_ckbox').checked=true;
                    }else{
                        alert(acc_arr[x] + " " + SUGAR.language.get('DCETemplates', 'ERR_ACC_EDITION_NOT_RECOGNIZE'));
                        //empty_field();
                    }
                }
            }
	    }
    }else{
	    var acc_arr=new Array();
	    if(document.getElementById('ce_ckbox').checked){
	        acc_arr.push('CE');
	    }
	    if(document.getElementById('pro_ckbox').checked){
	        acc_arr.push('PRO');
	    }
	    acc=acc_arr.join(' | ');
	    document.getElementById('upgrade_acceptable_edition').value=acc;
	}
}
update_acc_edition(true);
if(document.forms.EditView.record.value){
    document.getElementById('template_name').style.display="inline";
}else{
    if(document.getElementById('my_file').value==""){
        empty_field();
    }
    YAHOO.util.Event.onDOMReady(function(){
	    document.getElementById('upload_panel').style.display="inline";
	    YAHOO.util.Dom.setXY('upload_panel', YAHOO.util.Dom.getXY("container_upload"));
	});
}