{*

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

// $Id: EditView.tpl 31224 2008-01-23 01:46:52Z bsoufflet $

*}
<!--//FILE SUGARCRM flav=dce ONLY -->

<BR>
<form name="ConfigureDCESettings" enctype='multipart/form-data' method="POST" action="index.php?action=DCESettings&module=Configurator" onSubmit="return (check_form('ConfigureDCESettings'));">
<span class='error'>{$error.main}</span>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>

    <td>
        <input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="button"  type="submit"  name="save" value="  {$APP.LBL_SAVE_BUTTON_LABEL}  " >
        &nbsp;<input title="{$MOD.LBL_CANCEL_BUTTON_TITLE}"  onclick="document.location.href='index.php?module=Administration&action=index'" class="button"  type="button" name="cancel" value="  {$APP.LBL_CANCEL_BUTTON_LABEL}  " > </td>
    </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="edit view">
    <tr><th align="left" scope="row" colspan="4"><h4>{$MOD.DEFAULT_DCE_SETTINGS}</h4></th>
    </tr><tr>
<td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td  scope="row">{$MOD.DCE_TEMPLATES_DIR}: </td>
        <td  >
            <input type='text' size='40' name='dce_templates_dir' value='{$settings.dce_templates_dir|default:""}'>
        </td>
        <td  scope="row"> </td>
        <td  >

        </td>
    </tr>
    <tr>
        <td  scope="row">{$MOD.DCE_SUPPORT_USER_TIME_LIMIT}: </td>
        <td  >
            <input type='text' size='40' name='dce_support_user_time_limit' value='{$settings.dce_support_user_time_limit|default:"5"}'>
        </td>
        <td  scope="row"> </td>
        <td  >

        </td>
    </tr>
    <tr>
        <td  scope="row">{$MOD.DCE_UNIQUE_KEY}: </td>
        <td  >
            {$UNIQUE_KEY}
        </td>
        <td  scope="row"> </td>
        <td  >

        </td>
    </tr>
    <tr>
        <td  scope="row">{$MOD.DCE_LICENSING_USER}: </td>
        <td  >
            <input type='text' size='40' name='dce_licensing_user' value='{$settings.dce_licensing_user|default:""}'>
        </td>
        <td  scope="row"> </td>
        <td  >

        </td>
    </tr>
    <tr>
        <td  scope="row">{$MOD.DCE_LICENSING_PASSWORD}: </td>
        <td  >
            <input type='password' size='40' name='dce_licensing_password' value='{$settings.dce_licensing_password|default:""}'>
        </td>
        <td  scope="row"> </td>
        <td  >

        </td>
    </tr>
</table>
</td></tr>
</table>

<!-- Message Templates -->
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="edit view">
    <tr><th align="left" scope="row" colspan="4"><h4>{$MOD.DCE_MESSAGING}</h4></th>
    </tr><tr>
<td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td  scope="row">{$MOD.DCE_PRIMARY_IT_EMAIL}: </td>
        <td  >
            <input type='text' size='40' name='dce_primary_it_email' value='{$settings.dce_primary_it_email|default:""}'>
        </td>
        <td  scope="row"> </td>
        <td  ></td>
    </tr>
    <tr>
        <td  scope="row">{$MOD.CREATE_EVAL_SUCCESS_MSG}: </td>
        <td  >
        		<select name="dce_create_tmpl" id = "create_tmpl" >{$CREATE_DRPDWN}</select>
        </td>
        <td  scope="row"> </td>
        <td  >

        </td>
    </tr>
    <tr>
        <td  scope="row">{$MOD.CREATE_INSTANCE_SUCCESS_MSG}: </td>
        <td  >
        	<select name="dce_eval_tmpl" id = "eval_tmpl" >{$EVAL_DRPDWN}</select>
        </td>
        <td  scope="row"> </td>
        <td  ></td>
    </tr>
    <tr>
        <td  scope="row">{$MOD.ARCHIVE_INSTANCE_MSG}: </td>
        <td  >
        		<select name="dce_archive_tmpl" id = "archive_tmpl" >{$ARCHIVE_DRPDWN}</select>
        </td>
        <td  scope="row"> </td>
        <td  ></td>
    </tr>
    <tr>
        <td  scope="row">{$MOD.SUPPORT_USER_MSG}: </td>
        <td  >
        		<select name="dce_toggle_tmpl" id = "toggle_tmpl" >{$SUPPORT_USER_DRPDWN}</select>
        </td>
        <td  scope="row"> </td>
        <td  ></td>
    </tr>
    <tr>
        <td  scope="row">{$MOD.UPGRADE_LIVE_MSG}: </td>
        <td  >
        		<select name="dce_upgrade_live_tmpl" id = "upgrade_live_tmpl" >{$UPGRADE_LIVE_DRPDWN}</select>
        </td>
        <td  scope="row"> </td>
        <td  ></td>
    </tr>
    <tr>
        <td  scope="row">{$MOD.UPGRADE_TEST_MSG}: </td>
        <td  >
        		<select name="dce_upgrade_test_tmpl" id = "upgrade_test_tmpl" >{$UPGRADE_TEST_DRPDWN}</select>
        </td>
        <td  scope="row"> </td>
        <td  ></td>
    </tr>
    <tr>
        <td  scope="row">{$MOD.ERROR_MESSAGE_MSG}: </td>
        <td  >
        		<select name="dce_failed_tmpl" id = "failed_tmpl" >{$ERROR_DRPDWN}</select>
        </td>
        <td  scope="row"> </td>
        <td  ></td>
    </tr>

</table>
</td></tr>
</table>


<div style="padding-top: 2px;">
<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" class="button"  type="submit" name="save" value="  {$APP.LBL_SAVE_BUTTON_LABEL}  " />
&nbsp;<input title="{$MOD.LBL_CANCEL_BUTTON_TITLE}"  onclick="document.location.href='index.php?module=Administration&action=index'" class="button"  type="button" name="cancel" value="  {$APP.LBL_CANCEL_BUTTON_LABEL}  " />
</div>
{$JAVASCRIPT}
</form>
