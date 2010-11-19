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
    
    var apiOpts = SUGAR.eapm[apiName];

    var urlObj = new SUGAR.forms.VisibilityAction('url',(apiOpts.needsUrl==true), EAPMFormName);
    var userObj = new SUGAR.forms.VisibilityAction('name',(apiOpts.authMethod=='password'), EAPMFormName);
    var passObj = new SUGAR.forms.VisibilityAction('password',(apiOpts.authMethod=='password'), EAPMFormName);

    urlObj.exec();
    userObj.exec();
    passObj.exec();
}

function EAPMEditStart(userIsAdmin) {
    var apiElem = document.getElementById('application');
    
    apiElem.onchange = EAPMChange;

    EAPMChange();
    
    if ( !userIsAdmin ) {
        // Disable the assigned user picker for non-admins
        document.getElementById('assigned_user_name').parentNode.innerHTML = document.getElementById('assigned_user_name').value;
    }
}