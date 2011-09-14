/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */
// $Id: User.js 54569 2010-02-17 19:28:11Z jmertic $

function clearInboundSettings() {
	var url = document.getElementById('server_url');
	var user = document.getElementById('email_user');
	var prot = document.getElementById('protocol');
	var pass = document.getElementById('email_password');
	var port = document.getElementById('port');
	var inbox = document.getElementById('mailbox');
	
	url.value = '';
	user.value ='';
	pass.value = '';
	port.value = '';
	inbox.value = '';
	
	for(i=0; i<prot.options.length; i++) {
		if(prot.options[i].value == '') {
			prot.options[i].selected = true;
		}
	}
}

function checkInboundEmailSettings() {
	var url = document.getElementById('server_url');
	var user = document.getElementById('email_user');
	var prot = document.getElementById('protocol');
	var pass = document.getElementById('email_password');
	var port = document.getElementById('port');
	var inbox = document.getElementById('mailbox');
	var doCheck = false;
	var IEAlert = SUGAR.language.get('Users', 'ERR_IE_MISSING_REQUIRED');
	
	if(url.value != '') {
		doCheck = true;
	} else if(user.value != '') {
		doCheck = true;
	} else if(prot.value != '') {
		doCheck = true;
	} else if(pass.value != '') {
		doCheck = true;
	} else if(port.value != '') {
		doCheck = true;
	}
	/* else if(inbox.value != '') {
		doCheck = true;
	}*/

	if(doCheck == true) {
		if(url.value == '' || url.value == 'undefined') {
			alert(IEAlert);
			return false;
		} else if(user.value == '' || user.value == 'undefined') {
			alert(IEAlert);
			return false;
		} else if(prot.value == '' || prot.value == 'undefined') {
			alert(IEAlert);
			return false;
		} else if(pass.value == '' || pass.value == 'undefined') {
			alert(IEAlert);
			return false;
		} else if(port.value == '' || port.value == 'undefined') {
			alert(IEAlert);
			return false;
		} else if(inbox.value == '' || inbox.value == 'undefined') {
			alert(IEAlert);
			return false;
		}
	}
	
	return true;
}


function show_main() {
	var basic = document.getElementById('basic'); basic.style.display = "";
	var settings = document.getElementById('settings'); settings.style.display = "";
	var info = document.getElementById('information'); info.style.display = "";
	var address = document.getElementById('address'); address.style.display = "";
	var calendar_options = document.getElementById('calendar_options'); calendar_options.style.display = "";
	var edit_tabs = document.getElementById('edit_tabs'); edit_tabs.style.display = "";
	
	var email_options = document.getElementById('email_options'); email_options.style.display = 'none';
	var email_inbound = document.getElementById('email_inbound'); email_inbound.style.display = 'none';
}

function show_email() {
	var basic = document.getElementById('basic'); basic.style.display = "none";
	var settings = document.getElementById('settings'); settings.style.display = "none";
	var info = document.getElementById('information'); info.style.display = "none";
	var address = document.getElementById('address'); address.style.display = "none";
	var calendar_options = document.getElementById('calendar_options'); calendar_options.style.display = "none";
	var edit_tabs = document.getElementById('edit_tabs'); edit_tabs.style.display = "none";
	
	var email_options = document.getElementById('email_options'); email_options.style.display = "";
	var email_inbound = document.getElementById('email_inbound'); email_inbound.style.display = "";
}


function enable_change_password_button() {
	var butt = document.getElementById('change_password_button');
	if(document.EditView.record.value != "" && document.EditView.record.value != 'undefined') {
		butt.style.display = '';
	}
}


function toggleAdv() {
	var adv = document.getElementById("ie_adv");
	if(adv.style.display == 'none') {
		adv.style.display = "";
	} else {
		adv.style.display = 'none';
	}
}



function refresh_signature_list(signature_id, signature_name) {
	var field=document.getElementById('signature_id');
	var bfound=0;
	for (var i=0; i < field.options.length; i++) {
			if (field.options[i].value == signature_id) {
				if (field.options[i].selected==false) {
					field.options[i].selected=true;
				}
				bfound=1;
			}
	}
	//add item to selection list.
	if (bfound == 0) {
		var newElement=document.createElement('option');
		newElement.text=signature_name;
		newElement.value=signature_id;
		field.options.add(newElement);
		newElement.selected=true;
	}	

	//enable the edit button.
	var field1=document.getElementById('edit_sig');
	field1.style.visibility="visible";
}

function setSigEditButtonVisibility() {
	var field = document.getElementById('signature_id');
	var editButt = document.getElementById('edit_sig');
	if(field.value != '') {
		editButt.style.visibility = "visible";
	} else {
		editButt.style.visibility = "hidden";
	} 
}

function open_email_signature_form(record, the_user_id) {
	URL="index.php?module=Users&action=Popup";
	if(record != "") {
		URL += "&record="+record;
	}
	if(the_user_id != "") {
		URL += "&the_user_id="+the_user_id;
	}
	windowName = 'email_signature';
	windowFeatures = 'width=800' + ',height=600' + ',resizable=1,scrollbars=1';

	win = window.open(URL, windowName, windowFeatures);
	if(window.focus) {
		// put the focus on the popup if the browser supports the focus() method
		win.focus();
	}
}

function setDefaultSigId(id) {
	var checkbox = document.getElementById("signature_default");
	var default_sig = document.getElementById("signatureDefault");
	
	if(checkbox.checked) {
		default_sig.value = id;
	} else {
		default_sig.value = "";
	}
}

function setSymbolValue(id) {
    document.getElementById('symbol').value = currencies[id];
}

function user_status_display(field){
		switch (field.value){
		case 'Administrator':
		    document.getElementById('UserTypeDesc').innerHTML=SUGAR.language.get('Users',"LBL_ADMIN_DESC");
		document.getElementById('is_admin').value='1';
		break;
		case 'RegularUser':
			document.getElementById('is_admin').value='0';
			document.getElementById('UserTypeDesc').innerHTML=SUGAR.language.get('Users',"LBL_REGULAR_DESC");
		break;
		case 'UserAdministrator':
			document.getElementById('is_admin').value='0';
			document.getElementById('UserTypeDesc').innerHTML=SUGAR.language.get('Users',"LBL_USER_ADMIN_DESC");
		break;
	}
}


function startOutBoundEmailSettingsTest()
{
    var loader = new YAHOO.util.YUILoader({
    require : ["element","sugarwidgets"],
    loadOptional: true,
    //BEGIN SUGARCRM flav=int ONLY
	filter: 'debug',
	//END SUGARCRM flav=int ONLY
    skin: { base: 'blank', defaultSkin: '' },
    onSuccess: testOutboundSettings,
    allowRollup: true,
    base: "include/javascript/yui/build/"
    });
    loader.addModule({
        name :"sugarwidgets",
        type : "js",
        fullpath: "include/javascript/sugarwidgets/SugarYUIWidgets.js",
        varName: "YAHOO.SUGAR",
        requires: ["datatable", "dragdrop", "treeview", "tabview"]
    });
    loader.insert();

}

function testOutboundSettings()
{
	var errorMessage = '';
	var isError = false;
	var fromAddress = document.getElementById("outboundtest_from_address").value;
    var errorMessage = '';
    var isError = false;
    var smtpServer = document.getElementById('mail_smtpserver').value;

    var mailsmtpauthreq = document.getElementById('mail_smtpauth_req');
    if(trim(smtpServer) == '' || trim(mail_smtpport) == '')
    {
        isError = true;
        errorMessage += SUGAR.language.get('Users',"LBL_MISSING_DEFAULT_OUTBOUND_SMTP_SETTINGS") + "<br/>";
        overlay(SUGAR.language.get('app_strings',"ERR_MISSING_REQUIRED_FIELDS"), errorMessage, 'alert');
        return false;
    }


    if(document.getElementById('mail_smtpuser') && trim(document.getElementById('mail_smtpuser').value) == '')
    {
        isError = true;
        errorMessage += SUGAR.language.get('app_strings',"LBL_EMAIL_ACCOUNTS_SMTPUSER") + "<br/>";
    }


    if(isError) {
        overlay(SUGAR.language.get('app_strings',"ERR_MISSING_REQUIRED_FIELDS"), errorMessage, 'alert');
        return false;
    }

    testOutboundSettingsDialog();
}

function sendTestEmail()
{
    var toAddress = document.getElementById("outboundtest_from_address").value;
    var fromAddress = document.getElementById("outboundtest_from_address").value;

    if (trim(fromAddress) == "")
    {
        overlay(SUGAR.language.get('app_strings',"ERR_MISSING_REQUIRED_FIELDS"), SUGAR.language.get('app_strings',"LBL_EMAIL_SETTINGS_FROM_TO_EMAIL_ADDR"), 'alert');
        return;
    }
    else if (!isValidEmail(fromAddress)) {
        overlay(SUGAR.language.get('app_strings',"ERR_INVALID_REQUIRED_FIELDS"), SUGAR.language.get('app_strings',"LBL_EMAIL_SETTINGS_FROM_TO_EMAIL_ADDR"), 'alert');
        return;
    }

    //Hide the email address window and show a message notifying the user that the test email is being sent.
    EmailMan.testOutboundDialog.hide();
    overlay(SUGAR.language.get('app_strings',"LBL_EMAIL_PERFORMING_TASK"), SUGAR.language.get('app_strings',"LBL_EMAIL_ONE_MOMENT"), 'alert');

    var callbackOutboundTest = {
    	success	: function(o) {
    		hideOverlay();
    		overlay(SUGAR.language.get('app_strings',"LBL_EMAIL_TEST_OUTBOUND_SETTINGS"), SUGAR.language.get('app_strings',"LBL_EMAIL_TEST_NOTIFICATION_SENT"), 'alert');
    	}
    };
    var smtpServer = document.getElementById('mail_smtpserver').value;

    if(document.getElementById('mail_smtpuser') && document.getElementById('mail_smtppass')){
    var postDataString = 'mail_sendtype=SMTP&mail_smtpserver=' + smtpServer + "&mail_smtpport=" + mail_smtpport + "&mail_smtpssl=" + mail_smtpssl + "&mail_smtpauth_req=true&mail_smtpuser=" + trim(document.getElementById('mail_smtpuser').value) + "&mail_smtppass=" + trim(document.getElementById('mail_smtppass').value) + "&outboundtest_from_address=" + fromAddress + "&outboundtest_to_address=" + toAddress;
    }
    else{
	var postDataString = 'mail_sendtype=SMTP&mail_smtpserver=' + smtpServer + "&mail_smtpport=" + mail_smtpport + "&mail_smtpssl=" + mail_smtpssl + "&outboundtest_from_address=" + fromAddress + "&outboundtest_to_address=" + toAddress;
    }
	YAHOO.util.Connect.asyncRequest("POST", "index.php?action=testOutboundEmail&mail_name=system&module=EmailMan&to_pdf=true&sugar_body_only=true", callbackOutboundTest, postDataString);
}
function testOutboundSettingsDialog() {
        // lazy load dialogue
        if(!EmailMan.testOutboundDialog) {
        	EmailMan.testOutboundDialog = new YAHOO.widget.Dialog("testOutboundDialog", {
                modal:true,
				visible:true,
            	fixedcenter:true,
            	constraintoviewport: true,
                width	: 600,
                shadow	: false
            });
            EmailMan.testOutboundDialog.setHeader(SUGAR.language.get('app_strings',"LBL_EMAIL_TEST_OUTBOUND_SETTINGS"));
            YAHOO.util.Dom.removeClass("testOutboundDialog", "yui-hidden");
        } // end lazy load

        EmailMan.testOutboundDialog.render();
        EmailMan.testOutboundDialog.show();
} // fn

function overlay(reqtitle, body, type) {
    var config = { };
    config.type = type;
    config.title = reqtitle;
    config.msg = body;
    YAHOO.SUGAR.MessageBox.show(config);
}

function hideOverlay() {
	YAHOO.SUGAR.MessageBox.hide();
}


<!--//BEGIN SUGARCRM flav=pro ONLY -->
function confirmReassignRecords() {
    var status = document.getElementsByName('status');
    if(verify_data(document.EditView)) {
        if(status[0] && status[0].value == 'Inactive'){
            var handleYes = function() {
                document.EditView.return_action.value = 'reassignUserRecords';
                document.EditView.return_module.value = 'Users';
                document.EditView.submit();
            };
            
            var handleNo = function() {
                document.EditView.submit();
            };
            YAHOO.namespace('example.container');
            YAHOO.example.container.simpledialog1 =
                new YAHOO.widget.SimpleDialog('simpledialog1',
                                              { width: '300px',
                                                fixedcenter: true,
                                                visible: true,
                                                draggable: false,
                                                close: true,
                                                text: SUGAR.language.get('Users','LBL_REASS_CONFIRM_REASSIGN'),
                                                constraintoviewport: true,
                                                buttons: [ { text:'Yes', handler:handleYes, isDefault:true },
                                                           { text:'No',  handler:handleNo } ]
                                              } );
            YAHOO.example.container.simpledialog1.setHeader('Re-Assign');
            YAHOO.example.container.simpledialog1.render('popup_window');
            YAHOO.util.Event.addListener('Save', 'click', YAHOO.example.container.simpledialog1.show, YAHOO.example.container.simpledialog1, true);
        }
        else{
            document.EditView.submit();
        }
    }
    else{
        return false;
    }
}
<!--//END SUGARCRM flav=pro ONLY -->

<!-- Autoruns -->
YAHOO.util.Event.onContentReady('user_theme_picker',function()
{
    document.getElementById('user_theme_picker').onchange = function()
    {
        document.getElementById('themePreview').src =
            "index.php?entryPoint=getImage&themeName=" + document.getElementById('user_theme_picker').value + "&imageName=themePreview.png";
        if (typeof themeGroupList[document.getElementById('user_theme_picker').value] != 'undefined' &&
            themeGroupList[document.getElementById('user_theme_picker').value] ) {
            document.getElementById('use_group_tabs_row').style.display = '';
        } else {
            document.getElementById('use_group_tabs_row').style.display = 'none';
        }
    }
});
