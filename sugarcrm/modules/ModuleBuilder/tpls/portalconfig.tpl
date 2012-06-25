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
<form id='0' name='0'>
    <table class='tabform' width='100%' cellpadding=4>

        <tr>
            <td colspan='2'>{$mod.LBL_PORTAL_CONFIGURE}</td>
        </tr>
        <tr>
            <td colspan='2' nowrap>
            {$mod.LBL_PORTAL_ENABLE}:
                <input type="checkbox" name="on" {if $on eq 1}checked{/if} class='portalField' id="on" value="1"/>
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
            <td colspan='2' nowrap>
                <input type='button' class='button' id='gobutton' value='Save'>
            </td>
        </tr>

    </table>
</form>

{literal}

<script language='javascript'>
    addToValidate(0, "appName", "alpha", true,{/literal}"{$mod.LBL_PORTAL_APP_NAME}"{literal});
    addToValidate(0, "restURL", "alpha", true,{/literal}"{$mod.LBL_PORTAL_REST_URL}"{literal});
    addToValidate(0, "listSize", "int", true,{/literal}"{$mod.LBL_PORTAL_LIST_NUMBER}"{literal});
    addToValidate(0, "fieldsToDisplay", "int", true,{/literal}"{$mod.LBL_PORTAL_DETAIL_NUMBER}"{literal});
    $('#gobutton').click(function(event){
        var field;
        var fields = $('.portalField');
        console.log(fields);
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
