<br>
<div id="errorMsgs" style="display:none"></div>
<table width="100%" cellpadding="0" border="0" class="StyleDottedHr">
    <tr><th colspan="3" align="left" >{$MOD.LBL_WEB_SOCKET_CONFIGURATION_DESC}</td></tr>
    <tr>
        <td width="1%"></td>
        <td nowrap width='60%'><strong>{$MOD.LBL_WEB_SOCKET_CONFIGURATION_CLIENT} {$MOD.LBL_WEB_SOCKET_CONFIGURATION_URL}:</strong></td>
        <td width='35%'nowrap align="left"><input type="text" name="websockets_client_url" id="websockets_client_url"
                                                  value="{$smarty.session.websockets_client_url}" size="30"></td>
    </tr>
    <tr>
        <td width="1%"></td>
        <td nowrap width='60%'><strong>{$MOD.LBL_WEB_SOCKET_CONFIGURATION_SERVER} {$MOD.LBL_WEB_SOCKET_CONFIGURATION_URL}:</strong></td>
        <td width='35%'nowrap align="left"><input type="" name="websockets_server_url" id="websockets_server_url"
                                                  value="{$smarty.session.websockets_server_url}" size="30"></td>
    </tr>
</table>
<br>

