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

<br/>

<script type="text/javascript" src="{sugar_getjspath file='cache/include/javascript/sugar_grp_yui_widgets.js'}"></script>
<link rel="stylesheet" type="text/css" href="{sugar_getjspath file='modules/Connectors/tpls/tabs.css'}"/>

<form name="TriggerServerConfiguration" method="POST">

    <input type="hidden" name="module" value="Administration">
    <input type="hidden" name="action" value="saveTriggerServerConfiguration">
    {sugar_csrf_form_token}

    <span class="error">{$error.main}</span>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="actionsContainer">
        <tr>
            <td>
                <input title="{$APP.LBL_SAVE_BUTTON_TITLE}"
                       accessKey="{$APP.LBL_SAVE_BUTTON_KEY}"
                       class="button primary"
                       onclick="SUGAR.saveTriggerServerConfiguration();"
                       type="button"
                       name="save"
                       value="{$APP.LBL_SAVE_BUTTON_LABEL}"/>
                &nbsp;
                <input title="{$APP.LBL_CANCEL_BUTTON_TITLE}"
                       onclick="document.location.href='index.php?module=Administration&action=index'"
                       class="button" type="button" name="cancel" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
            </td>
        </tr>
    </table>

    <table width="100%" border="0" cellspacing="1" cellpadding="0" class="edit view">
        <tr>
            <th align="left" scope="row"><h4>{$MOD.LBL_TRIGGER_SERVER_CONFIGURATION_URL}:</h4></th>
        </tr>
        <tr>
            <td scope="row">
                <input type="text" name="trigger_server[url]" id="trigger_server_url" size="50" value="{$config.url}">
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="actionsContainer">
        <tr>
            <td>
                <input title="{$APP.LBL_SAVE_BUTTON_TITLE}"
                       accessKey="{$APP.LBL_SAVE_BUTTON_KEY}"
                       class="button primary"
                       onclick="SUGAR.saveTriggerServerConfiguration();"
                       type="button"
                       name="save"
                       value="{$APP.LBL_SAVE_BUTTON_LABEL}"/>
                &nbsp;
                <input title="{$APP.LBL_CANCEL_BUTTON_TITLE}"
                       onclick="document.location.href='index.php?module=Administration&action=index'"
                       class="button" type="button" name="cancel" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
            </td>
        </tr>
    </table>
</form>

{literal}
    <script type="text/javascript">
        (function() {
            var Connect = YAHOO.util.Connect;
            Connect.url = 'index.php';
            Connect.method = 'POST';
            Connect.timeout = 300000;

            SUGAR.saveTriggerServerConfiguration = function() {
                var trigger_server_url = document.getElementById('trigger_server_url').value;

                ajaxStatus.showStatus(SUGAR.language.get('Administration', 'LBL_SAVING'));
                Connect.asyncRequest(
                        Connect.method,
                        Connect.url,
                        {success: SUGAR.saveCallBack},
                        SUGAR.util.paramsToUrl({
                            module: "Administration",
                            action: "savetriggerserverconfiguration",
                            trigger_server_url: trigger_server_url,
                            csrf_token: SUGAR.csrf.form_token
                        }) + "to_pdf=1"
                );

                return true;
            };

            SUGAR.saveCallBack = function(o) {
                ajaxStatus.flashStatus(SUGAR.language.get('Administration', 'LBL_DONE'));
                var response = YAHOO.lang.JSON.parse(o.responseText);

                if (response['status'] === true) {
                    window.location.assign('index.php?module=Administration&action=index');
                } else {
                    var errMsg = response.errMsg;
                    YAHOO.SUGAR.MessageBox.show({msg: errMsg});
                }
            };
        })();
    </script>
{/literal}
