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
-->
<!-- END METADATA SECTION -->
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
                </tr><tr> 
                    <td width="15%" scope="row"><slot>{$MOD.LBL_FDOW}:</slot></td>
                    <td><slot>{$FDOW}&nbsp;</slot></td>
                    <td><slot></slot>{$MOD.LBL_FDOW_TEXT}&nbsp;</td>
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
