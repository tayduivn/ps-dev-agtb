/**
 * Edit functions for EAPM
 */
function EAPMChange() {
    var apiName = '';

    if ( EAPMFormName == 'EditView' ) {
        apiName = document.getElementById('application').value;
    } else {
        apiName = document.getElementById('application_raw').value;
    }
    if(SUGAR.eapm[apiName]){
        var apiOpts = SUGAR.eapm[apiName];

        var urlObj = new SUGAR.forms.VisibilityAction('url',(apiOpts.needsUrl==true), EAPMFormName);
        if ( EAPMFormName == 'EditView' ) {
            EAPMSetFieldRequired('url',(apiOpts.needsUrl == true));
        }

        var userObj = new SUGAR.forms.VisibilityAction('name',(apiOpts.authMethod=='password'), EAPMFormName);
        if ( EAPMFormName == 'EditView' ) {
            EAPMSetFieldRequired('name',(apiOpts.authMethod == 'password'));
        }

        var passObj = new SUGAR.forms.VisibilityAction('password',(apiOpts.authMethod=='password'), EAPMFormName);
        if ( EAPMFormName == 'EditView' ) {
            EAPMSetFieldRequired('password',(apiOpts.authMethod == 'password'));
        }

        urlObj.exec();
        userObj.exec();
        passObj.exec();

        //hide/show new window notice
        if(apiOpts.authMethod){
            var messageDiv = document.getElementById('eapm_notice_div');
            if(apiOpts.authMethod == "oauth"){
                messageDiv.innerHTML = EAPMOAuthNotice;
            }else{
                 messageDiv.innerHTML = EAPMBAsicAuthNotice;
            }
        }else{
            var messageDiv = document.getElementById('eapm_notice_div');
            messageDiv.innerHTML = EAPMBAsicAuthNotice;
        }
    }
}

function EAPMSetFieldRequired(fieldName, isRequired) {
    var formname = 'EditView';
    for(var i = 0; i < validate[formname].length; i++){
		if(validate[formname][i][0] == fieldName){
            validate[formname][i][2] = isRequired;
		}
    }
}

function EAPMEditStart(userIsAdmin) {
    var apiElem = document.getElementById('application');

    EAPM_url_validate = null;
    EAPM_name_validate = null;
    EAPM_password_validate = null;

    apiElem.onchange = EAPMChange;

    setTimeout(EAPMChange,100);
    
    if ( !userIsAdmin ) {
        // Disable the assigned user picker for non-admins
        document.getElementById('assigned_user_name').parentNode.innerHTML = document.getElementById('assigned_user_name').value;
    }
}