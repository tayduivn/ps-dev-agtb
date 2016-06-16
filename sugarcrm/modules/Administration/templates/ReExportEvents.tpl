{*
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
*}

<div id="msgDiv"></div>
<script type="text/javascript">
{literal}
var ajxProgress;

function callReExportEvents() {

    var ajaxCall = function () {
        var success = function (data) {
            ajaxStatus.hideStatus();
            document.getElementById('msgDiv').innerHTML = data.responseText;
        };

        ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_PROCESSING_REQUEST'));
        var postData = 'module=Administration&action=callReExportEvents' + '&csrf_token=' + SUGAR.csrf.form_token;

        if (typeof ajxProgress != 'undefined') {
            return;
        }

        ajxProgress = YAHOO.util.Connect.asyncRequest(
                'POST',
                'index.php',
                {success: success, failure: success},
                postData);
    };

    window.setTimeout(ajaxCall, 2000);

}

callReExportEvents();
{/literal}
</script>
