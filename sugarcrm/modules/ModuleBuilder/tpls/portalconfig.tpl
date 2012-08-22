{*
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
//FILE SUGARCRM flav=pro ONLY
*}
<link rel="stylesheet" href="sidecar/lib/chosen/chosen.css"/>
<script src="sidecar/lib/chosen/chosen.jquery.js"></script>
<form id='0' name='0'>
    <table class='tabform' width='100%' cellpadding=4>

        <tr>
            <td colspan='2'>{$mod.LBL_PORTAL_CONFIGURE}</td>
        </tr>
        <tr>
            <td colspan='2' nowrap>
            {$mod.LBL_PORTAL_ENABLE}:
                <input type="checkbox" name="appStatus" {if $appStatus eq 'online'}checked{/if} class='portalField' id="appStatus" value="online"/>
            </td>
        </tr>
        <tr>
            <td colspan='1' nowrap>
                {$mod.LBL_PORTAL_LOGO_URL}:
            </td>
            <td colspan='1' nowrap>
                <input class='portalProperty portalField' id='logoURL' name='logoURL' value='{$logoURL}' size=60>
            </td>
        </tr>
        <tr>
            <td colspan='1' nowrap>
                {$mod.LBL_PORTAL_LIST_NUMBER}:<span class="required">*</span>
            </td>
            <td colspan='1' nowrap>
                <input class='portalProperty portalField' id='maxQueryResult' name='maxQueryResult' value='{$maxQueryResult}' size=4>
            </td>
        </tr>
        <tr>
            <td colspan='1' nowrap>
                {$mod.LBL_PORTAL_DETAIL_NUMBER}:<span class="required">*</span>
            </td>
            <td colspan='1' nowrap>
                <input class='portalProperty portalField' id='fieldsToDisplay' name='fieldsToDisplay' value='{$fieldsToDisplay}' size=4>
            </td>
        </tr>
        <tr>                            
            <td colspan='1' nowrap>     
                {$mod.LBL_PORTAL_SEARCH_RESULT_NUMBER}:<span class="required">*</span>
            </td>                       
            <td colspan='1' nowrap>                                 
                <input class='portalProperty portalField' id='maxSearchQueryResult' name='maxSearchQueryResult' value='{$maxSearchQueryResult}' size=4>
            </td>                       
        </tr>
        <tr>
            <td colspan='1' nowrap>
                {$mod.LBL_PORTAL_DEFAULT_ASSIGN_USER}:<span class="required">*</span>
            </td>
            <td colspan='1' nowrap>
                <select data-placeholder="{$mod.LBL_USER_SELECT}" class="chzn-select portalProperty portalField" id='defaultUser' name='defaultUser' >
                {foreach from=$userList item=user key=userId}
                    <option value="{$userId}" {if $userId == $defaultUser}selected{/if}>{$user}</option>
                {/foreach}
                </select>
            </td>
        </tr>
        <tr>
            <td colspan='2' nowrap>
                <input type='button' class='button' id='gobutton' value='Save'>
            </td>
        </tr>

    </table>
</form>
<div>

    {if $disabledDisplayModules}
    <br>
    <p>
        {$mod.LBL_PORTAL_DISABLED_MODULES}
    <ul>
        {foreach from=$disabledDisplayModulesList item=modName}
            <li>{$modName}</li>
        {/foreach}
    </ul>
    </p>
    <p>
        {$mod.LBL_PORTAL_ENABLE_MODULES}
    </p>
    {/if}

</div>
{literal}

<script language='javascript'>
    $('.chzn-select').chosen({allow_single_deselect: true});
    addToValidate(0, "logoURL", "portal_logo_url", false, {/literal}"{$mod.LBL_PORTAL_LOGO_URL}"{literal});
    addToValidate(0, "maxQueryResult", "int", true,{/literal}"{$mod.LBL_PORTAL_LIST_NUMBER}"{literal});
    addToValidate(0, "fieldsToDisplay", "int", true,{/literal}"{$mod.LBL_PORTAL_DETAIL_NUMBER}"{literal});
    addToValidate(0, "maxSearchQueryResult", "int", true,{/literal}"{$mod.LBL_PORTAL_LIST_NUMBER}"{literal}); 
    $('#gobutton').click(function(event){
        var field;
        var fields = $('.portalField');
        var props = {};
        var fName; var i;
        for(i=0; i<fields.length; i++) {
            field = $(fields[i]);
            props[field.attr('name')] = field.val();
            if (field.is(':checked')) {
                props[field.attr('name')] = 'true';
            }
        }
        retrieve_portal_page($.param(props))
    });
    function retrieve_portal_page(props) {
        if (validate_form(0,'')){
        ModuleBuilder.getContent("module=ModuleBuilder&action=portalconfigsave&" + props);
        }
    }
</script>
{/literal}
