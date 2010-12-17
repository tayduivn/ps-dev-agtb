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
        
if ( typeof(SUGAR.field) == 'undefined' ) {
    SUGAR.field = new Object();
}

if ( typeof(SUGAR.field.file) == 'undefined' ) {
    SUGAR.field.file = {
        deleteAttachment: function(elemBaseName,docTypeName,elem) {
            ajaxStatus.showStatus(SUGAR.language.get("Notes", "LBL_REMOVING_ATTACHMENT"));
            elem.form.deleteAttachment.value=1;
            elem.form.action.value="EditView";
            SUGAR.dashlets.postForm(elem.form, SUGAR.field.file.deleteAttachmentCallbackGen(elemBaseName,docTypeName));
            elem.form.deleteAttachment.value=0;
            elem.form.action.value="";
        },
        deleteAttachmentCallbackGen: function(elemBaseName,docTypeName) {
            return function(text) {
	            if(text == 'true') {
		            document.getElementById(elemBaseName+'_new').style.display = '';
		            ajaxStatus.hideStatus();
		            document.getElementById(elemBaseName+'_old').innerHTML = '';
                    document.getElementById(docTypeName).disabled = false;
                    document.getElementById(elemBaseName).value = '';
	            } else {
		            document.getElementById(elemBaseName+'_new').style.display = 'none';
		            ajaxStatus.flashStatus(SUGAR.language.get('Notes', 'ERR_REMOVING_ATTACHMENT'), 2000); 
	            }
            }
        },
        setupEapiShowHide: function(elemBaseName,docTypeName,formName) {
            var showHideFunc = function() {
                var docShowHideElem = document.getElementById(elemBaseName + "_externalApiSelector");
                var dropdownValue = document.getElementById(docTypeName).value;
                if ( typeof(SUGAR.eapm) != 'undefined' 
                     && typeof(SUGAR.eapm[dropdownValue]) != 'undefined' 
                     && typeof(SUGAR.eapm[dropdownValue].docSearch) != 'undefined'
                     && SUGAR.eapm[dropdownValue].docSearch ) {
                    docShowHideElem.style.display = '';
                    
                    // Start a refresh of the document cache in the background. Thanks AJAX!
                    YAHOO.util.Connect.asyncRequest('GET', 'index.php?module=EAPM&action=flushFileCache&to_pdf=1&api='+dropdownValue,{});


                } else {
                    docShowHideElem.style.display = 'none';
                }
                // Update the quick search
                sqs_objects[formName+"_"+elemBaseName+"_remoteName"].api = dropdownValue;

                
                // Now time to see if we can select security options
                var secLevelBoxElem = document.getElementById(elemBaseName + '_securityLevelBox');
                var secLevelElem = document.getElementById(elemBaseName + '_securityLevel');
                
                secLevelElem.options.length = 0;
                
                if ( SUGAR.eapm[dropdownValue].sharingOptions ) {
                    var opts = SUGAR.eapm[dropdownValue].sharingOptions;
                    var i = 0;

                    for ( idx in opts ) {
                        secLevelElem.options[i] = new Option(SUGAR.language.get('app_strings',opts[idx]),idx,false,false);
                        i++;
                    }
                    
                    secLevelBoxElem.style.display='';
                } else {
                    secLevelBoxElem.style.display='none';
                }
            }
            document.getElementById(docTypeName).onchange = showHideFunc;

            showHideFunc();
        }
    }
}