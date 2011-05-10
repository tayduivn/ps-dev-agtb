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
if (typeof(SUGAR.DCEInstance) == "undefined") {
    SUGAR.DCEInstance = function(){
        this.license_fields = new Array('license_key', 'license_duration', 'type', 'license_start_date', 'licensed_users');
    };
    SUGAR.DCEInstance.prototype = {
        update_license_fields: function(){
            var license_duration_select = document.getElementById('license_duration_select');
            var license_duration_extended = document.getElementById('license_duration_extended');
            license_duration_select.options.length=0;
            license_duration_extended.options.length=0;
            if(!checkValidate('EditView', 'license_start_date')){
                addToValidate('EditView', 'license_start_date', 'date', true, SUGAR.language.get('DCEInstances', 'LBL_LICENSE_START_DATE') );
                addToValidate('EditView', 'license_duration', 'enum', true, SUGAR.language.get('DCEInstances', 'LBL_LICENSE_DURATION') );
            }
            if(document.getElementById('type').value == 'evaluation'){
                var def = eval_list_default;
                var def_ext = eval_ext_list_default;
                var list = eval_list;
                var list_ext = eval_ext_list;
            }else{
                var def = prod_list_default;
                var def_ext = prod_ext_list_default;
                var list = prod_list;
                var list_ext = prod_ext_list;
            }
            var i=0;
            for(var key in list){
                if(key==def){
                    license_duration_select.options[i] = new Option(list[key],key,true,true);
                }else{
                    license_duration_select.options[i] = new Option(list[key],key);
                }
                i++;
            }
            i=0;
            for(var key in list_ext){
                if(key==def_ext){
                    license_duration_extended.options[i] = new Option(list_ext[key],key,true,true);
                }else{
                    license_duration_extended.options[i] = new Option(list_ext[key],key);
                }
                i++;
            }
        },
        /*
         * for duplicate
         */
        disable_license_fields: function(){
            document.getElementById('license_start_date').readOnly=true;
            document.getElementById('license_start_date_trigger').style.display="none";
            
            var newPageDivElem = document.createElement('div');
            var newPageDivElem2 = document.createElement('div');
            newPageDivElem.innerHTML=document.getElementById('license_duration').value;
            newPageDivElem2.innerHTML=document.getElementById('type').options[document.getElementById('type').selectedIndex].text;
            document.getElementById('license_duration').parentNode.appendChild(newPageDivElem);
            document.getElementById('type').parentNode.appendChild(newPageDivElem2);
            document.getElementById('license_duration_select').style.display="none";
            document.getElementById('extend_term').style.display="none";
            document.getElementById('type').style.display="none";
            
            document.getElementById('licensed_users').readOnly=true;
            document.getElementById('license_key').readOnly=true;
            
            document.getElementById('disable_key_btn').style.display="none";
            document.getElementById('get_key_btn').style.display="none";
        },
        /*
         * Revert license fields values to old values if get key fail or update the "old_value_XX" fields on success.
         */
        revert_license_field: function(failure){
            var prefix1='old_value_';
            var prefix2='';
            var update_license_duration = false;
            if(failure){
                prefix1='';
                prefix2='old_value_';
            }
            if(document.getElementById('old_value_type').value != document.getElementById('type').value){
                var update_license_duration = true;
            }
            if(document.getElementById('EditView').record.value != '' || document.getElementById('license_key').value != '' || !failure){
                for(var key in this.license_fields){
                    if (isInteger(key)) {
                        document.getElementById(prefix1 + this.license_fields[key]).value = document.getElementById(prefix2 + this.license_fields[key]).value;
                    }
                }
                this.update_license_fields();
            }
            if(failure && update_license_duration){
                this.update_license_duration();
            }
            
        },
        /*
         * if a licensing field is change, need to update the key so addToValidate
         */
        onchange_license_field: function(){
            var change = false;
            while(checkValidate('EditView', 'license_key')){
                removeFromValidate('EditView', 'license_key');
            }
            for (var key in this.license_fields) {
                if (isInteger(key)){
                    if(this.license_fields[key] != 'license_key'){
                        if(document.getElementById('old_value_'+this.license_fields[key]).value != document.getElementById(this.license_fields[key]).value){
                            change = true;
                        }
                    }
                }
            }
            if(change){
                addToValidate('EditView', 'license_key', 'error', true, SUGAR.language.get('DCEInstances','ERR_NEED_UPDATE_OR_GET_KEY') );
            }
        },
        update_license_key_status: function(){
            if(document.getElementById('license_key_status').value == '1'){
                document.getElementById('get_key_btn').style.display = 'inline';
                document.getElementById('disable_key_btn').value = SUGAR.language.get('DCEInstances','LBL_DISABLE_KEY');
            }else{
                document.getElementById('get_key_btn').style.display = 'none';
                document.getElementById('disable_key_btn').value = SUGAR.language.get('DCEInstances','LBL_ENABLE_KEY');
            }
        },
        update_expiration_date: function(){
            if(document.getElementById('license_start_date').value != ''){
                var callback = {
                    success: function(o){
                        document.getElementById('license_expire_date').value=o.responseText;
                    },
                    failure: function(o){
                        alert(SUGAR.language.get('app_strings','LBL_AJAX_FAILURE'));
                    }
                }
                var postData = '&start_date='+document.getElementById('license_start_date').value+'&duration='+document.getElementById('license_duration').value;
                YAHOO.util.Connect.asyncRequest('POST', 'index.php?module=DCEInstances&action=returnExpirationDate&to_pdf=1', callback, postData);
            }else{
                document.getElementById('license_expire_date').value='';
            }
        },
        update_license_duration: function(){
            // if license duration drop down state
            if (document.getElementById('license_duration_select').style.display != 'none') {
                if (document.getElementById('license_key').value && document.getElementById('type').value == 'evaluation') {
                    document.getElementById('license_duration').value = document.getElementById('duration_disabled').value;
                    this.show_license_duration(true);
                }
                else {
                    document.getElementById('license_duration').value = document.getElementById('license_duration_select').value;
                }
            // if "extend term by" state
            }else{
                document.getElementById('duration_disabled').value = document.getElementById('license_duration').value;
                document.getElementById('license_duration').value = document.getElementById('license_duration_select').value;
                this.show_license_duration(false);
            }
        },
        show_license_duration: function(show){
            if(show){
                document.getElementById('extend_term').style.display='';
                document.getElementById('license_duration_select').style.display='none';
                document.getElementById('duration_disabled').style.display='';
                document.getElementById('duration_disabled').value = document.getElementById('license_duration').value +' '+ SUGAR.language.get('DCEInstances', 'LBL_DAYS');
            }else{
                document.getElementById('extend_term').style.display='none';
                document.getElementById('license_duration_select').style.display='';
                document.getElementById('duration_disabled').style.display='none';
            }
        },
        disable_type_field: function(){
            if (document.getElementById('type').value == 'production') {
                document.getElementById('type').id = 'type1';
                document.getElementById('type1').name = 'type1';
                document.getElementById('type1').disabled = true;
                try {
                    var element = document.createElement("<input name='type' type='hidden' id='type' value='production'>");
                } catch (e) {
                    var element = document.createElement("input");
                    element.setAttribute("name", "type");
                    element.setAttribute("id", "type");
                    element.setAttribute("type", "hidden");
                    element.setAttribute("value", "production");
                }
                document.getElementById('type1').parentNode.appendChild(element);
            }
        },
        getKey: function(action){
            while(checkValidate('EditView', 'license_key')){
                removeFromValidate('EditView', 'license_key');
                removeFromValidate('EditView', 'get_key_user_id');
            }
            if(!check_form('EditView')){
                return false;
            }
            if(!checkValidate('EditView', 'license_key')){
                addToValidate('EditView', 'license_key', 'varchar', true, SUGAR.language.get('DCEInstances', 'LBL_LICENSE_KEY') );
            }
            if(!checkValidate('EditView', 'get_key_user_id')){
                addToValidate('EditView', 'get_key_user_id', 'id', true, SUGAR.language.get('DCEInstances', 'LBL_GET_KEY_USER_ID') );
            }
            var license_key=document.getElementById('license_key');
            switch(action){
                case 'get':
                    this.key_call('get');
                    break;
                case 'disable':
                    if(license_key.value){
                        this.key_call('disable');
                    }
                    break;
                default:
                    alert('JS ERROR');
                    break;
             }
        },
        key_call: function(action){
            var license_key=document.getElementById('license_key');
            var fields_array=new Array('name', 'account_name', 'account_id', 'sugar_version', 'sugar_edition', 'parent_dceinstance_name', 'status', 'type', 'license_start_date', 'license_duration', 'license_expire_date', 'licensed_users', 'license_key', 'license_key_status', 'current_user_id');
            var callback = {
                success: function(o){
                    document.getElementById('loading_img').style.display="none";
                    eval('var response = '+ o.responseText);
                    if(typeof(response)=='object'){
                       if(typeof(response['error'])!='undefined' && response['error']['number']==0 && response['get_key_result']['license_key']!=null && response['get_key_result']['license_key']!=""){
                           if(license_key.value){
                               document.getElementById('update_key_user_id').value=document.getElementById('current_user_id').value;
                           }else{
                               document.getElementById('get_key_user_id').value=document.getElementById('current_user_id').value;
                           }
                           license_key.value = response['get_key_result']['license_key'];

  						   //process if result has enable license flag set
                           if(typeof(response['get_key_result']['enable_license'])!='undefined' && response['get_key_result']['enable_license']!=null){
                           		//if enable license is set to true, then change key_status to 1
	                           if(typeof(response['get_key_result']['enable_license'])=='1' || response['get_key_result']['enable_license']==1){
    	                           document.getElementById('license_key_status').value= 1;
	                           }else{
                           		//if enable license is not set to true, then change key_status to 0
		                           	document.getElementById('license_key_status').value= 0;
	                           }
                           }
  						   //process if result has disable license flag is set
                           if(typeof(response['get_key_result']['disable_license'])!='undefined' && response['get_key_result']['disable_license']!=null){
                           		//if disable license is set to true, then change key_status to 0
	                           if(typeof(response['get_key_result']['disable_license'])=='1' || response['get_key_result']['disable_license']==1){
    	                           document.getElementById('license_key_status').value= 0;
	                           }else{
	                           		//if disable license is not set to true, then change key_status to 1
		                           	document.getElementById('license_key_status').value= 1;
	                           }
                           }                           
                           
                           document.getElementById('license_field_change').value = true;
                           this.update_license_fields();
                           this.update_license_key_status();
                           this.revert_license_field(false);
                           this.show_license_duration(true);
                           this.update_expiration_date();
                           this.disable_type_field();
                           document.getElementById('get_key_btn').value=SUGAR.language.get('DCEInstances','LBL_UPDATE_KEY');
                           document.getElementById('disable_key_btn').style.display='inline';
                           if(typeof(response['get_key_result']['message'])!='undefined'){
                               alert(response['get_key_result']['message']);
                           }else{
                               alert(SUGAR.language.get('DCEInstances','LBL_GET_KEY_SUCCESS'));
                           }
                        }
                        else if(typeof(response['error'])!='undefined' && response['error']['number']!=0){
                           alert("ERROR : "+response['error']['number']+" - "+response['error']['name']+" - "+response['error']['description']);
                           this.revert_license_field(true);
                        }
                    }else{
                        alert(SUGAR.language.get('DCEInstances','LBL_GET_KEY_SUCCESS'));
                        this.revert_license_field(true);
                    }
                },
                failure: function(o){
                    alert(SUGAR.language.get('app_strings','LBL_AJAX_FAILURE'));
                    this.revert_license_field(true);
                },
                scope: this
            }
            document.getElementById('license_duration').value = parseInt(document.getElementById('license_duration').value) + parseInt(document.getElementById('license_duration_extended').value);
            document.getElementById('loading_img').style.display="inline";
            var postData = '&license_action=' + action;
            for (var key in fields_array){
               if(document.getElementById(fields_array[key])){
                   if(document.getElementById(fields_array[key]).value){
                       postData += "&"+fields_array[key]+"="+document.getElementById(fields_array[key]).value;
                   }
               }
            }
            YAHOO.util.Connect.asyncRequest('POST', 'index.php?module=DCEInstances&action=License_Key&to_pdf=1', callback, postData);
        },
        //create the Url from the name of the instance and the url of the cluster
        create_url1: function(){
            // wait for population of the cluster_url field
            setTimeout(function(thisObj) { thisObj.create_url(); },1000, this);
        },
        // population of the url field with name and cluster_url
        create_url: function(){
            var urlpath='';
            document.getElementById('url').disabled=false;
            if(document.getElementById('dcecluster_name').value){
                if(document.getElementById('cluster_url').value && document.getElementById('cluster_url_format').value){
                    if(document.getElementById('name').value!=''){
                        urlpath=document.getElementById('cluster_url').value;
                        if(document.getElementById('cluster_url_format').value == SUGAR.language.get('app_list_strings', 'url_format_list')['URL/Instance_Name']){
                            urlpath+="/"+document.getElementById('name').value;
                            urlpath=urlpath.replace("//","/");
                            urlpath=urlpath.replace(":/","://");
                        }else if(document.getElementById('cluster_url_format').value == SUGAR.language.get('app_list_strings', 'url_format_list')['Instance_Name.URL']){
                            urlpath=urlpath.replace(/http:/i,"");
                            urlpath=urlpath.replace("//","");
                            urlpath=urlpath.replace(/www./i,"");
                            urlpath=document.getElementById('name').value+"."+urlpath;
                        }else{
                            urlpath=SUGAR.language.get('DCEInstances','LBL_POPULATED_ON_SAVE');
                        }
                    }
                }else{
                //come from cluster instance subpanel => impossible to retrieve the cluster URL
                    document.getElementById('url').disabled=true;
                    urlpath=SUGAR.language.get('DCEInstances','LBL_POPULATED_ON_SAVE');
                    setTimeout(function(thisObj) { thisObj.create_url(); },5000, this);
                }
            }else{
                document.getElementById('cluster_url').value='';
                document.getElementById('cluster_url_format').value='';
            }
            document.getElementById('url').value=urlpath;
        
        },
        init: function(){
            this.update_license_fields();
            var cluster=document.getElementById('dcecluster_name');
            var template=document.getElementById('dcetemplate_name');
            document.getElementById('status').disabled=true;
            //if we edit an existing instance template and cluster need to be readonly.
            if(document.forms.EditView.record.value){
                if(document.getElementById('license_key_status').value == ''){
                    document.getElementById('license_key_status').value = 1;
                }
                cluster.readOnly=true;
                cluster.className='';
                //remove the buttons of the cluster field
                cluster.parentNode.removeChild(cluster.parentNode.childNodes[5]);
                cluster.parentNode.removeChild(cluster.parentNode.childNodes[5]);
                cluster.parentNode.removeChild(cluster.parentNode.childNodes[5]);
                template.readOnly=true;
                template.className='';
                //remove the buttons of the template field
                template.parentNode.removeChild(template.parentNode.childNodes[5]);
                template.parentNode.removeChild(template.parentNode.childNodes[5]);
                template.parentNode.removeChild(template.parentNode.childNodes[5]);
                if (document.getElementById('type').value == 'production') {
                    document.getElementById('type').disabled = true;
                }
                document.getElementById('name').readOnly=true;
                document.getElementById('get_key_btn').value=SUGAR.language.get('DCEInstances','LBL_UPDATE_KEY');
                this.show_license_duration(true);
                this.update_license_key_status();
            }else{
                document.getElementById('license_key_status').value=1;
                document.getElementById('disable_key_btn').style.display='none';
                this.show_license_duration(false);
            }
            if(is_admin){
                document.getElementById('license_key').readOnly=false;
            }
            //if is a duplicate hide the licensing fields
            if(document.getElementById('parent_dceinstance_id').value){
                this.disable_license_fields();
            }
        }
    };
}
DCEEditView = new SUGAR.DCEInstance();
DCEEditView.init();
