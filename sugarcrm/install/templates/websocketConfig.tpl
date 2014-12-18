<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html {$langHeader}>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <title>{$MOD.LBL_WEB_SOCKET_CONFIGURATION}</title>
    <link REL="SHORTCUT ICON" HREF="{$icon}">
    <link rel="stylesheet" href="{$css}" type="text/css" />
    <script type="text/javascript" src="install/installCommon.js"></script>
    <script src="cache/include/javascript/sugar_grp1_yui.js?v={$versionToken}"></script>
    <script src="cache/include/javascript/sugar_grp1_jquery.js?v={$versionToken}"></script>
    <script type="text/javascript">
        <!--
        if (YAHOO.env.ua)
            UA = YAHOO.env.ua;
        -->
    </script>
    <link rel='stylesheet' type='text/css' href='include/javascript/yui/build/container/assets/container.css'/>
</head>
<body onload="document.getElementById('button_next').focus();">
<form action="install.php" method="post" name="wsConfig" id="form">
    <table cellspacing="0" cellpadding="0" border="0" align="center" class="shell">
        <tr>
            <td colspan="2" id="help"><a href="{$help_url}" target='_blank'>{$MOD.LBL_HELP} </a></td>
        </tr>
        <tr>
            <th width="500">
                <p>
                    <img src="{$sugar_md}" alt="SugarCRM" border="0">
                </p>
                {$MOD.LBL_WEB_SOCKET_CONFIGURATION}
            </th>
            <th width="200" height="30" style="text-align: right;"><a href="http://www.sugarcrm.com" target="_blank">
                    <IMG src="{$loginImage}" alt="SugarCRM" border="0"></a>
            </th>
        </tr>
        <tr>
            <td colspan="2">
                <div id="errorMsgs" style="display:none"></div>
                <div class="required">* Required field</div>
                <table width="100%" cellpadding="0" border="0" class="StyleDottedHr">
                    <tr>
                        <td colspan="3" align="left">{$MOD.LBL_WEB_SOCKET_CONFIGURATION_DESC}</td>
                    </tr>
                    <tr><th colspan="3" align="left"></th></tr>
                    <tr>
                        <td><span class="required">*</span></td>
                        <td nowrap=""><strong>{$MOD.LBL_WEB_SOCKET_CONFIGURATION_CLIENT} {$MOD.LBL_WEB_SOCKET_CONFIGURATION_URL}:</strong></td>
                        <td align="left"><input type="text" name="websocket[client][url]" id="websocket_client_url"
                                                value="{$smarty.session.websockets.client.url}" size="30"></td>
                    </tr>
                    <tr>
                        <td><span class="required">*</span></td>
                        <td nowrap=""><strong>{$MOD.LBL_WEB_SOCKET_CONFIGURATION_SERVER} {$MOD.LBL_WEB_SOCKET_CONFIGURATION_URL}:</strong></td>
                        <td align="left"><input type="" name="websocket[server][url]" id="websocket_server_url"
                                                value="{$smarty.session.websockets.server.url}" size="30"></td>
                    </tr>
                    <tr>
                        <td><span class="required">*</span></td>
                        <td nowrap=""><strong>{$MOD.LBL_WEB_SOCKET_CONFIGURATION_SECRET}</strong></td>
                        <td align="left"><input type="" name="websocket[public_secret]" id="websocket_public_secret"
                                                value="{$smarty.session.websockets.public_secret}" size="30"></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td align="right" colspan="2">
                <hr>
                <input type="hidden" name="current_step" value="{$next_step}">
                <table cellspacing="0" cellpadding="0" border="0" class="stdTable">
                    <tr>
                        <td>
                            <input class="acceptButton" type="button" name="goto" value="{$MOD.LBL_BACK}"
                                   id="button_back_license" onclick="document.getElementById('form').submit();"/>
                        </td>
                        <td>
                            <input class="acceptButton" type="button" name="goto" value="{$MOD.LBL_NEXT}"
                                   id="button_next" onclick="callWebsocketCheck({$next_step});"/>
                            <input type="hidden" name="goto" id='hidden_goto' value="{$MOD.LBL_BACK}"/>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</form>
<br>

<div id="checkingDiv" style="display:none">
    <table cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td>
                <p><img alt="{$MOD.LBL_WEB_SOCKET_CHECK_HEADER}" src='install/processing.gif'>
                    <br>{$MOD.LBL_WEB_SOCKET_CHECK_HEADER}</p>
            </td>
        </tr>
    </table>
</div>
</body>
<script>
    var msgPanel;
    function callWebsocketCheck() {ldelim}
        //begin main function that will be called
        ajaxCall = function(msg_panel) {ldelim}
            //create success function for callback
            getPanel = function() {ldelim}
                var args = {ldelim}
                    width: "300px",
                    modal: true,
                    fixedcenter: true,
                    constraintoviewport: false,
                    underlay: "shadow",
                    close: false,
                    draggable: true,
                    effect: {ldelim}effect: YAHOO.widget.ContainerEffect.FADE, duration: .5{rdelim}
                {rdelim};
                msg_panel = new YAHOO.widget.Panel('p_msg', args);
                //If we haven't built our panel using existing markup,
                //we can set its content via script:
                msg_panel.setHeader("{$MOD.LBL_LICENSE_CHKENV_HEADER}");
                msg_panel.setBody(document.getElementById("checkingDiv").innerHTML);
                msg_panel.render(document.body);
                msgPanel = msg_panel;
            {rdelim};

            passed = function(url) {ldelim}
                document.wsConfig.goto.value = "{$MOD.LBL_NEXT}";
                document.getElementById('hidden_goto').value = "{$MOD.LBL_NEXT}";
                document.wsConfig.current_step.value = "{$next_step}";
                document.wsConfig.submit();
                window.focus();
            {rdelim};

            success = function(o) {ldelim}
                //condition for errors
                if (o.responseText.indexOf('wsCheckPassed') >= 0) {ldelim}
                    //make navigation
                    passed("install.php?goto={$MOD.LBL_NEXT}");
                    //condition for other errors
                {rdelim} else {ldelim}
                    //turn off loading message
                    msgPanel.hide();
                    document.getElementById("errorMsgs").innerHTML = o.responseText;
                    document.getElementById("errorMsgs").style.display = '';
                    return false;
                {rdelim}
            {rdelim};

            //set loading message and create url
            postData = "checkWSConfiguration=true&to_pdf=1&sugar_body_only=1";

            postData += "&websockets[client][url]=" + document.wsConfig.websocket_client_url.value;
            postData += "&websockets[server][url]=" + document.wsConfig.websocket_server_url.value;
            postData += "&websockets[public_secret]=" + document.wsConfig.websocket_public_secret.value;

            //if this is a call already in progress, then just return
            if (typeof ajxProgress != 'undefined') {ldelim}
                return;
            {rdelim}

            getPanel();
            msgPanel.show;
            var ajxProgress = YAHOO.util.Connect.asyncRequest('POST', 'install.php', {ldelim}
                success: success,
                failure: success
            {rdelim}, postData);

        {rdelim};
        ajaxCall();
        return;
    {rdelim}

    function countdown(num) {ldelim}
        scsbody = "<table cellspacing='0' cellpadding='0' border='0' align='center'><tr><td>";
        scsbody += "<p>{$MOD.LBL_LICENSE_CHECK_PASSED}</p>";
        scsbody += "<div id='cntDown'>{$MOD.LBL_LICENSE_REDIRECT}" + num + "</div>";
        scsbody += "</td></tr></table>";
        msgPanel.setBody(scsbody);
        msgPanel.render();
        if (num > 0) {ldelim}
            num = num - 1;
            setTimeout("countdown(" + num + ")", 1000);
        {rdelim}
    {rdelim}
</script>
</html>
