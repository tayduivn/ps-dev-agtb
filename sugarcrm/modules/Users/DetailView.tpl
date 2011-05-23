<!--
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: DetailView.html 52049 2009-10-30 15:29:06Z jmertic $
 ********************************************************************************/
-->
<script type='text/javascript' src='{sugar_getjspath file='modules/Users/DetailView.js'}'></script>
<script type="text/javascript" src="{sugar_getjspath file='include/javascript/sugar_grp_yui_widgets.js'}"></script>
<script type='text/javascript'>
var LBL_NEW_USER_PASSWORD = '{$MOD.LBL_NEW_USER_PASSWORD_2}';
{if !empty($ERRORS)}
{literal}
YAHOO.SUGAR.MessageBox.show({title: '{/literal}{$ERROR_MESSAGE}{literal}', msg: '{/literal}{$ERRORS}{literal}'} );
{/literal}
{/if}
</script>

<script type="text/javascript">
var user_detailview_tabs = new YAHOO.widget.TabView("user_detailview_tabs");

{literal}
user_detailview_tabs.on('contentReady', function(e){
{/literal}
//BEGIN SUGARCRM flav!=com && flav!=sales ONLY
{if $EDIT_SELF && $SHOW_DOWNLOADS_TAB}
{literal}
    user_detailview_tabs.addTab( new YAHOO.widget.Tab({
        label: '{/literal}{$MOD.LBL_DOWNLOADS}{literal}',
        dataSrc: 'index.php?to_pdf=1&module=Home&action=pluginList',
        content: '<div style="text-align:center; width: 100%">{/literal}{sugar_image name="loading"}{literal}</div>',
        cacheData: true
    }));
    user_detailview_tabs.getTab(3).getElementsByTagName('a')[0].id = 'tab4';
{/literal}
{/if}
//END SUGARCRM flav!=com && flav!=sales ONLY
});
</script>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="actionsContainer">
<tr>
<td>

<form action="index.php" method="post" name="DetailView" id="form">
    <input type="hidden" name="module" value="Users">
    <input type="hidden" name="record" value="{$ID}">
    <input type="hidden" name="isDuplicate" value=false>
    <input type="hidden" name="action">
    <input type="hidden" name="user_name" value="{$USER_NAME}">
    <input type="hidden" name="password_generate">
    <input type="hidden" name="old_password">
    <input type="hidden" name="new_password">
    <input type="hidden" name="return_module">
    <input type="hidden" name="return_action">
    <input type="hidden" name="return_id">
<table width="100%" cellpadding="0" cellspacing="0" border="0">

    <tr><td colspan='2' width="100%" nowrap>{$BUTTONS}</td></tr>
</table>
</form>

</td>
<td width="100%">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
{$PAGINATION}
</table>
</td>
</tr>
</table>
<div id="user_detailview_tabs" class="yui-navset detailview_tabs">
    <ul class="yui-nav">
        <li class="selected"><a id="tab1" href="#tab1"><em>{$MOD.LBL_USER_INFORMATION}</em></a></li>
        <li {if $IS_GROUP_OR_PORTAL == 1}style="display: none;"{/if}><a id="tab2" href="#tab2"><em>{$MOD.LBL_ADVANCED}</em></a></li>
        {* //BEGIN SUGARCRM flav!=sales ONLY *}
        {if $SHOW_ROLES}
        <li><a id="tab3" href="#tab3"><em>{$MOD.LBL_USER_ACCESS}</em></a></li>
        {/if}
        {* //END SUGARCRM flav!=sales ONLY *}
    </ul>
    <div class="yui-content">
        <div>
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="detail view">
                <tr>
                    <td width="15%" valign="top" scope="row"><slot>{$MOD.LBL_NAME}:</slot></td>
                    <td width="35%" valign="top"><slot>{$FULL_NAME}&nbsp;</slot></td>
                    <td width="15%" valign="top" scope="row"><slot>{$MOD.LBL_USER_NAME}:</slot></td>
                    <td width="35%" valign="top"><slot>{$USER_NAME}&nbsp;</slot></td>
                </tr>
                <tr>
                    <td valign="top" scope="row"><slot>{$MOD.LBL_STATUS}:</slot></td>
                    <td valign="top"><slot>{$STATUS}&nbsp;</slot></td>
                    {* //BEGIN SUGARCRM flav=sales ONLY *}
                    {if $SYS_ADMIN}
                    {* //END SUGARCRM flav=sales ONLY *}
                    <td valign="top" scope="row"><slot>{$MOD.LBL_USER_TYPE}:</slot></td>
                    <td valign="top" ><slot>{$USER_TYPE_LABEL}&nbsp;</slot></td>
                    {* //BEGIN SUGARCRM flav=sales ONLY *}
                    {else}
                    <td valign="top" scope="row"><slot>&nbsp;</slot></td>
                    <td valign="top" ><slot>&nbsp;</slot></td>
                    {/if}
                    {* //END SUGARCRM flav=sales ONLY *}
                </tr>
                {* //BEGIN SUGARCRM flav!=com ONLY *}
                {if !$IS_GROUP_OR_PORTAL}
                <tr>
                    <td valign="top" scope="row"><slot>{$APP.LBL_PICTURE_FILE}:</slot></td>
                    <td valign="top"><slot>{$PICTURE_FILE_CODE}&nbsp;</slot></td>
                    <td valign="top" scope="row"><slot>&nbsp;</slot></td>
                    <td valign="top" ><slot>&nbsp;</slot></td>
                </tr>
                {/if}
                {* //END SUGARCRM flav!=com ONLY *}
            </table>

            <div id='information'>
                <table width="100%" border="0" cellspacing="0" cellpadding="0" class="detail view">
                <tr>
                <th colspan='4' align="left" width="100%" valign="top"><h4><slot>{$MOD.LBL_USER_INFORMATION}</slot></h4></th>
                </tr><tr>
                <td width="15%" valign="top" scope="row"><slot>{$MOD.LBL_EMPLOYEE_STATUS}:</slot></td>
                <td width="35%" valign="top"><slot>{$EMPLOYEE_STATUS}&nbsp;</slot></td>
                <td valign="top" scope="row"><slot>&nbsp;</slot></td>
                <td valign="top"><slot>&nbsp;</slot></td>
                </tr><tr>
                <td width="15%" valign="top" scope="row"><slot>{$MOD.LBL_TITLE}:</slot></td>
                <td width="35%" valign="top"><slot>{$TITLE}&nbsp;</slot></td>
                <td width="15%" valign="top" scope="row"><slot>{$MOD.LBL_OFFICE_PHONE}:</slot></td>
                <td width="35%" valign="top"><slot>{$PHONE_WORK}&nbsp;</slot></td>
                </tr><tr>
                <td valign="top" scope="row"><slot>{$MOD.LBL_DEPARTMENT}:</slot></td>
                <td valign="top"><slot>{$DEPARTMENT}&nbsp;</slot></td>
                <td valign="top" scope="row"><slot>{$MOD.LBL_MOBILE_PHONE}:</slot></td>
                <td valign="top"><slot>{$PHONE_MOBILE}&nbsp;</slot></td>
                </tr><tr>
                <td valign="top" scope="row"><slot>{$MOD.LBL_REPORTS_TO}:</slot></td>
                <td valign="top"><slot><a href="index.php?module=Users&action=DetailView&record={$REPORTS_TO_ID}">{$REPORTS_TO_NAME}</a>&nbsp;</slot></td>
                <td valign="top" scope="row"><slot>{$MOD.LBL_OTHER}:</slot></td>
                <td valign="top"><slot>{$PHONE_OTHER}&nbsp;</slot></td>
                </tr><tr>
                <td valign="top" scope="row"><slot>&nbsp;</slot></td>
                <td valign="top"><slot>&nbsp;</slot></td>
                <td valign="top" scope="row"><slot>{$MOD.LBL_FAX}:</slot></td>
                <td valign="top"><slot>{$PHONE_FAX}&nbsp;</slot></td>
                </tr><tr>

                <td valign="top" scope="row"><slot>{$MOD.LBL_HOME_PHONE}:</slot></td>
                <td valign="top"><slot>{$PHONE_HOME}&nbsp;</slot></td>
                <td valign="top" scope="row"><slot>&nbsp;</slot></td>
                <td valign="top"><slot>&nbsp;</slot></td>
                </tr><tr>
                <td valign="top" scope="row"><slot>{$MOD.LBL_MESSENGER_TYPE}:</slot></td>
                <td valign="top"><slot>{$MESSENGER_TYPE}&nbsp;</slot></td>
                <td valign="top" scope="row"><slot>&nbsp;</slot></td>
                <td valign="top"><slot>&nbsp;</slot></td>
                </tr><tr>
                <td valign="top" scope="row"><slot>{$MOD.LBL_MESSENGER_ID}:</slot></td>
                <td valign="top"><slot>{$MESSENGER_ID}&nbsp;</slot></td>
                <td valign="top" scope="row"><slot>&nbsp;</slot></td>
                <td valign="top"><slot>&nbsp;</slot></td>
                </tr><tr>
                <td valign="top" scope="row"><slot>{$MOD.LBL_ADDRESS}:</slot></td>
                <td valign="top" ><slot>{$ADDRESS_STREET}<br>
                {$ADDRESS_CITY} {$ADDRESS_STATE}&nbsp;&nbsp;{$ADDRESS_POSTALCODE}<br>
                {$ADDRESS_COUNTRY}</slot></td>
                <td scope="row"><slot>&nbsp;</slot></td>
                <td><slot>&nbsp;</slot></td>
                </tr><tr>
                <td valign="top" valign="top" scope="row"><slot>{$MOD.LBL_NOTES}:</slot></td>
                <td><slot>{$DESCRIPTION}&nbsp;</slot></td>
                <td width="15%" valign="top" scope="row"><slot>&nbsp;</slot></td>
                <td width="35%" valign="top"><slot>&nbsp;</slot></td>
            </tr></table>
            </div>

            <div id='email_options'>
                <table width="100%" border="0" cellspacing="0" cellpadding="0" class="detail view">
                    <tr>
                        <th align="left" scope="row" colspan="4">
                            <h4>{$MOD.LBL_MAIL_OPTIONS_TITLE}</h4>
                        </th>
                    </tr>
                    <tr>
                        <td align="top" scope="row" width="15%">
                            {$MOD.LBL_EMAIL}:
                        </td>
                        <td align="top" width="85%">
                            {$EMAIL_OPTIONS}
                        </td>
                    </tr>
                    <!--//BEGIN SUGARCRM flav!=sales ONLY -->
                    <tr id="email_options_link_type">
                        <td align="top"  scope="row">
                            {$MOD.LBL_EMAIL_LINK_TYPE}:
                        </td>
                        <td >
                            {$EMAIL_LINK_TYPE}
                        </td>
                    </tr>
                    {if $SHOW_SMTP_SETTINGS}
                    <tr>
                        <td scope="row" width="15%">
                            {$MOD.LBL_EMAIL_PROVIDER}:
                        </td>
                        <td width="35%">
                            {$MAIL_SMTPDISPLAY}
                        </td>
                    </tr>
                    <tr>
                        <td align="top"  scope="row">
                            {$MOD.LBL_MAIL_SMTPUSER}:
                        </td>
                        <td width="35%">
                            {$MAIL_SMTPUSER}
                        </td>
                    </tr>
                    {/if}
                    <!--//END SUGARCRM flav!=sales ONLY -->
                </table>
            </div>
        </div>
        <div>
        <div id="settings">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="detail view">
                <tr>
                <th colspan='4' align="left" width="100%" valign="top"><h4><slot>{$MOD.LBL_USER_SETTINGS}</slot></h4></th>
                </tr>
                <!--//BEGIN SUGARCRM flav!=sales ONLY -->
                <tr>
                <td scope="row"><slot>{$MOD.LBL_RECEIVE_NOTIFICATIONS}:</slot></td>
                <td><slot><input class="checkbox" type="checkbox" disabled {$RECEIVE_NOTIFICATIONS}></slot></td>
                <td><slot>{$MOD.LBL_RECEIVE_NOTIFICATIONS_TEXT}&nbsp;</slot></td>
                </tr>
                <!--//END SUGARCRM flav!=sales ONLY -->
                <!--//BEGIN SUGARCRM flav=pro ONLY -->
                <tr>
                <td width="15%" scope="row"><slot>{$MOD.LBL_DEFAULT_TEAM}:</slot></td>
                <td><slot>{$DEFAULT_TEAM}&nbsp;</slot></td>
                <td><slot>{$MOD.LBL_DEFAULT_TEAM_TEXT}&nbsp;</slot></td>
                </tr>
                <!--//END SUGARCRM flav=pro ONLY -->
                <!--//BEGIN SUGARCRM flav!=sales ONLY -->
                <tr>
                <td scope="row" valign="top"><slot>{$MOD.LBL_REMINDER}:</td>
                <td valign="top" nowrap><slot><input name='should_remind' tabindex='1' size='2' maxlength='2'  disabled type="checkbox" class="checkbox" value='1' {$REMINDER_CHECKED}>&nbsp;{$REMINDER_TIME}</slot></td>
                <td ><slot>{$MOD.LBL_REMINDER_TEXT}&nbsp;</slot></td>

                </tr>
                <tr>
                <td valign="top" scope="row"><slot>{$MOD.LBL_MAILMERGE}:</slot></td>
                <td valign="top" nowrap><slot><input tabindex='3' name='mailmerge_on' disabled class="checkbox" type="checkbox" {$MAILMERGE_ON}></slot></td>
                <td><slot>{$MOD.LBL_MAILMERGE_TEXT}&nbsp;</slot></td>
                </tr>
                <!--//END SUGARCRM flav!=sales ONLY -->
                <!--//BEGIN SUGARCRM flav!=dce ONLY -->
                <!--//BEGIN SUGARCRM flav=ent ONLY -->
                <tr>
                <td valign="top" scope="row"><slot>{$APP.LBL_OC_STATUS}:</slot></td>
                <td valign="top" nowrap><slot>{$OC_STATUS}&nbsp;</slot></td>
                <td><slot>{$APP.LBL_OC_STATUS_TEXT}&nbsp;</slot></td>
                </tr>
                <!--//END SUGARCRM flav=ent ONLY -->
                <!--//END SUGARCRM flav!=dce ONLY -->
                <tr>
                <td valign="top" scope="row"><slot>{$MOD.LBL_SETTINGS_URL}:</slot></td>
                <td valign="top" nowrap><slot>{$SETTINGS_URL}</slot></td>
                <td><slot>{$MOD.LBL_SETTINGS_URL_DESC}&nbsp;</slot></td>
                </tr>
                <tr>
                <td scope="row" valign="top"><slot>{$MOD.LBL_EXPORT_DELIMITER}:</slot></td>
                <td><slot>{$EXPORT_DELIMITER}</slot></td>
                <td><slot>{$MOD.LBL_EXPORT_DELIMITER_DESC}</slot></td>
                </tr>
                <tr>
                <td scope="row" valign="top"><slot>{$MOD.LBL_EXPORT_CHARSET}:</slot></td>
                <td><slot>{$EXPORT_CHARSET}</slot></td>
                <td><slot>{$MOD.LBL_EXPORT_CHARSET_DESC}</slot></td>
                </tr>
                <tr>
                <td scope="row" valign="top"><slot>{$MOD.LBL_USE_REAL_NAMES}:</slot></td>
                <td><slot>{$USE_REAL_NAMES}</slot></td>
                <td><slot>{$MOD.LBL_USE_REAL_NAMES_DESC}</slot></td>
                </tr>
                <!--//BEGIN SUGARCRM flav!=dce ONLY -->
                <!--//BEGIN SUGARCRM flav=pro ONLY -->
                <tr>
                <td scope="row" valign="top"><slot>{$MOD.LBL_OWN_OPPS}:</slot></td>
                <td valign="top" nowrap><slot><input name='no_opps' disabled class="checkbox" type="checkbox" {$NO_OPPS}></slot></td>
                <td><slot>{$MOD.LBL_OWN_OPPS_DESC}</slot></td>
                </tr>
                <!--//END SUGARCRM flav=pro ONLY -->
                <!--//END SUGARCRM flav!=dce ONLY -->
                {$EXTERNAL_AUTH}
            </table>
        </div>

        <div id='locale'>
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="detail view">
                <tr>
                    <th colspan='4' align="left" width="100%" valign="top">
                        <h4><slot>{$MOD.LBL_USER_LOCALE}</slot></h4></th>
                </tr>
                <tr>
                    <td width="15%" scope="row"><slot>{$MOD.LBL_DATE_FORMAT}:</slot></td>
                    <td><slot>{$DATEFORMAT}&nbsp;</slot></td>
                    <td><slot>{$MOD.LBL_DATE_FORMAT_TEXT}&nbsp;</slot></td>
                </tr>
                <tr>
                    <td width="15%" scope="row"><slot>{$MOD.LBL_TIME_FORMAT}:</slot></td>
                    <td><slot>{$TIMEFORMAT}&nbsp;</slot></td>
                    <td><slot>{$MOD.LBL_TIME_FORMAT_TEXT}&nbsp;</slot></td>
                </tr>
                <tr>
                    <td width="15%" scope="row"><slot>{$MOD.LBL_TIMEZONE}:</slot></td>
                    <td nowrap><slot>{$TIMEZONE}&nbsp;</slot></td>
                    <td><slot>{$MOD.LBL_ZONE_TEXT}&nbsp;</slot></td>
                </tr>
                <!--//BEGIN SUGARCRM flav!=dce ONLY -->
                <tr>
                    <td width="15%" scope="row"><slot>{$MOD.LBL_CURRENCY}:</slot></td>
                    <td><slot>{$CURRENCY}&nbsp;</slot></td>
                    <td><slot>{$MOD.LBL_CURRENCY_TEXT}&nbsp;</slot></td>
                </tr>
                <tr>
                    <td width="15%" scope="row"><slot>{$MOD.LBL_CURRENCY_SIG_DIGITS}:</slot></td>
                    <td><slot>{$CURRENCY_SIG_DIGITS}&nbsp;</slot></td>
                    <td><slot>{$MOD.LBL_CURRENCY_SIG_DIGITS_DESC}&nbsp;</slot></td>
                </tr>
                <!--//END SUGARCRM flav!=dce ONLY -->
                <tr>
                    <td width="15%" scope="row"><slot>{$MOD.LBL_NUMBER_GROUPING_SEP}:</slot></td>
                    <td><slot>{$NUM_GRP_SEP}&nbsp;</slot></td>
                    <td><slot>{$MOD.LBL_NUMBER_GROUPING_SEP_TEXT}&nbsp;</slot></td>
                </tr><tr>
                    <td width="15%" scope="row"><slot>{$MOD.LBL_DECIMAL_SEP}:</slot></td>
                    <td><slot>{$DEC_SEP}&nbsp;</slot></td>
                    <td><slot></slot>{$MOD.LBL_DECIMAL_SEP_TEXT}&nbsp;</td>
                </tr>
                </tr><tr>
                    <td width="15%" scope="row"><slot>{$MOD.LBL_LOCALE_DEFAULT_NAME_FORMAT}:</slot></td>
                    <td><slot>{$NAME_FORMAT}&nbsp;</slot></td>
                    <td><slot></slot>{$MOD.LBL_LOCALE_NAME_FORMAT_DESC}&nbsp;</td>
                </tr>
            </table>
        </div>

        <!--//BEGIN SUGARCRM flav=pro ONLY -->
        {if $SHOW_PDF_OPTIONS}
        <div id='pdf'>
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="detail view">
                <tr>
                    <th colspan='4' align="left"  width="100%" valign="top">
                        <h4><slot>{$MOD.LBL_PDF_SETTINGS}</slot></h4></th>
                </tr>
                <tr>
                    <td width="15%" scope="row"><slot>{$MOD.LBL_PDF_FONT_NAME_MAIN}:</slot></td>
                    <td width="35%"><slot>{$PDF_FONT_NAME_MAIN}&nbsp;</slot></td>
                    <td colspan="2"><slot>{$MOD.LBL_PDF_FONT_NAME_MAIN_TEXT}&nbsp;</slot></td>
                </tr>
                <tr>
                    <td width="15%" scope="row"><slot>{$MOD.LBL_PDF_FONT_SIZE_MAIN}:</slot></td>
                    <td width="35%"><slot>{$PDF_FONT_SIZE_MAIN}&nbsp;</slot></td>
                    <td colspan="2"><slot>{$MOD.LBL_PDF_FONT_SIZE_MAIN_TEXT}&nbsp;</slot></td>
                </tr>
                <tr>
                    <td width="15%" scope="row"><slot>{$MOD.LBL_PDF_FONT_NAME_DATA}:</slot></td>
                    <td width="35%"><slot>{$PDF_FONT_NAME_DATA}&nbsp;</slot></td>
                    <td colspan="2" class="tabDetailViewDF"><slot>{$MOD.LBL_PDF_FONT_NAME_DATA_TEXT}&nbsp;</slot></td>
                </tr>
                <tr>
                    <td width="15%"  scope="row"><slot>{$MOD.LBL_PDF_FONT_SIZE_DATA}:</slot></td>
                    <td width="35%" class="tabDetailViewDF"><slot>{$PDF_FONT_SIZE_DATA}&nbsp;</slot></td>
                    <td colspan="2" class="tabDetailViewDF"><slot>{$MOD.LBL_PDF_FONT_SIZE_DATA_TEXT}&nbsp;</slot></td>
                </tr>
            </table>
        </div>
        {/if}
        <!--//END SUGARCRM flav=pro ONLY -->

        <!--//BEGIN SUGARCRM flav!=dce ONLY -->
        <!--//BEGIN SUGARCRM flav!=sales ONLY -->
        <div id='calendar_options'>
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="detail view">
            <tr>
            <th colspan='4' align="left" width="100%" valign="top"><h4><slot>{$MOD.LBL_CALENDAR_OPTIONS}</slot></h4></th>
            </tr>
            <tr>
            <td width="15%" scope="row"><slot>{$MOD.LBL_PUBLISH_KEY}:</slot></td>
            <td width="20%"><slot>{$CALENDAR_PUBLISH_KEY}&nbsp;</slot></td>
            <td width="65%"><slot>{$MOD.LBL_CHOOSE_A_KEY}&nbsp;</slot></td>
            </tr>
            <tr>
            <td width="15%" scope="row"><slot><nobr>{$MOD.LBL_YOUR_PUBLISH_URL}:</nobr></slot></td>
            <td colspan=2><slot>{$CALENDAR_PUBLISH_URL}</slot></td>
            </tr>
            <tr>
            <td width="15%" scope="row"><slot>{$MOD.LBL_SEARCH_URL}:</slot></td>
            <td colspan=2><slot>{$CALENDAR_SEARCH_URL}</slot></td>
            </tr>
            </table>
        </div>
        <!--//END SUGARCRM flav!=sales ONLY -->
        <!--//END SUGARCRM flav!=dce ONLY -->
        <!--//BEGIN SUGARCRM flav!=sales ONLY -->
        <div id='edit_tabs'>
            <table width="100%" border="0" cellspacing="0" cellpadding="0"  class="detail view">
            <tr>
            <th colspan='4' align="left" width="100%" valign="top"><h4><slot>{$MOD.LBL_LAYOUT_OPTIONS}</slot></h4></th>
            </tr>
            <tr>
            <td width="15%" scope="row"><slot>{$MOD.LBL_USE_GROUP_TABS}:</slot></td>
            <td><slot><input class="checkbox" type="checkbox" disabled {$USE_GROUP_TABS}></slot></td>
            <td><slot>{$MOD.LBL_NAVIGATION_PARADIGM_DESCRIPTION}&nbsp;</slot></td>
            </tr>
            <tr>
            <td width="15%" scope="row"><slot>{$MOD.LBL_MAX_TAB}:</slot></td>
            <td width="15%"><slot>{$MAX_TAB}&nbsp;</slot></td>
            <td><slot>{$MOD.LBL_MAX_TAB_DESCRIPTION}&nbsp;</slot></td>
            </tr>
            <tr>
            <td width="15%" scope="row"><slot>{$MOD.LBL_SUBPANEL_TABS}:</slot></td>
            <td><slot><input class="checkbox" type="checkbox" disabled {$SUBPANEL_TABS}></slot></td>
            <td><slot>{$MOD.LBL_SUBPANEL_TABS_DESCRIPTION}&nbsp;</slot></td>
            </tr>
            </table>
        </div>
        <div id="user_holidays">
        {$USER_HOLIDAYS_SUBPANEL}
        </div>
        <!--//END SUGARCRM flav!=sales ONLY -->
        <div id="oauth_tokens">
        {$OAUTH_TOKENS_SUBPANEL}
        </div>
    </div>
{if !$SHOW_ROLES}
</div>
{/if}
