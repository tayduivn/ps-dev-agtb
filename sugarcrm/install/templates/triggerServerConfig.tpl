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
<br>
<table width="100%" cellpadding="0" border="0" class="StyleDottedHr">
    <tr><th colspan="3" align="left" >{$MOD.LBL_TRIGGER_SERVER_CONFIGURATION_LABEL}</td></tr>
    <tr><td colspan="3">{$MOD.LBL_TRIGGER_SERVER_CONFIGURATION_DESC}</td></tr>
    <tr>
        <td width="1%"></td>
        <td nowrap width='60%'><strong>{$MOD.LBL_TRIGGER_SERVER_CONFIGURATION_PROTOCOL}</strong></td>
        <td width='35%'nowrap align="left"><input type="radio" name="trigger_server_protocol" value="http" checked>Http
            <input type="radio" name="trigger_server_protocol" value="https">Https</td>
    </tr>
    <tr>
        <td width="1%"></td>
        <td nowlfdrap width='60%'><strong>{$MOD.LBL_TRIGGER_SERVER_CONFIGURATION_HOST}</strong></td>
        <td width='35%'nowrap align="left"><input type="text" name="trigger_server_host" id="trigger_server_host"
                                                  value="{$smarty.session.trigger_server_host}" size="30"></td>
    </tr>
    <tr>
        <td width="1%"></td>
        <td nowrap width='60%'><strong>{$MOD.LBL_TRIGGER_SERVER_CONFIGURATION_PORT}</strong></td>
        <td width='35%'nowrap align="left"><input type="text" name="trigger_server_port" id="trigger_server_port"
                                                  value="{$smarty.session.trigger_server_port}" size="30"></td>
    </tr>
</table>
<br>
