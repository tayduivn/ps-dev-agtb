<!-- // FILE SUGARCRM flav=ent ONLY -->
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
<link rel="stylesheet" href="{sugar_getjspath file='include/javascript/select2/select2.css'}"/>
<script language='javascript' src="{sugar_getjspath file='include/javascript/select2/select2.js'}"></script>
<form id='0' name='0'>
{sugar_csrf_form_token}
    <table class='tabform' width='100%' cellpadding=4>

        <tr>
            <td colspan='1' nowrap>
                {$mod.LBL_PORTAL_ENABLE_PORTAL}:
            </td>
            <td colspan='1' nowrap>
                <input type="checkbox" name="appStatus" {if $appStatus eq 'online'}checked{/if} class='portalField' id="appStatus" value="online"/>
            </td>
        </tr>
        {if $appStatus eq 'online'}
        <tr>
            <td>&nbsp;</td>
            <td colspan='1' nowrap>
                {$mod.LBL_PORTAL_SITE_URL} <a href="{$siteURL}/portal/index.html" target="_blank">{$siteURL}/portal/index.html</a>
            </td>
        </tr>
        {/if}
        <tr>
            <td colspan='1' nowrap>
                {$mod.LBL_PORTAL_ENABLE_SEARCH}:
            </td>
            <td colspan='1' nowrap>
                <input type="checkbox" name="caseDeflection" {if $caseDeflection eq 'enabled'}checked{/if} class='portalField' id="caseDeflection" value="enabled"/>
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
                {$mod.LBL_PORTAL_DEFAULT_ASSIGN_USER}:
            </td>
            <td colspan='1' nowrap class="defaultUser">
                <select data-placeholder="{$mod.LBL_USER_SELECT}" class="portalProperty portalField" id='defaultUser' data-name='defaultUser' >
                {foreach from=$userList item=user key=userId}
                    <option value="{$userId}" {if $userId == $defaultUser}selected{/if}>{$user}</option>
                {/foreach}
                </select>
            </td>
        </tr>
        <tr>
            <td colspan='1' nowrap>
                {$mod.LBL_PORTAL_MODULES}: {sugar_help text=$mod.LBL_CONFIG_PORTAL_MODULES_HELP}
            </td>
            <td colspan='1' nowrap>
                <div class='portal-module-list-container'>
                    <div class='portal-module-list-header'>
                        {$mod.LBL_CONFIG_PORTAL_MODULES_DISPLAYED}
                    </div>
                    <div class='portal-module-list-scrolldiv'>
                        <ul class='portal-module-list' id='enabled-module-list'>
                            {foreach from=$displayedPortalTabs item=module}
                                <li class='ui-state-default mod-list-item' id="{$module.module}">{$module.label}</li>
                            {/foreach}
                        </ul>
                    </div>
                </div>
                <div class='portal-module-list-container'>
                    <div class='portal-module-list-header'>
                        {$mod.LBL_CONFIG_PORTAL_MODULES_HIDDEN}
                    </div>
                    <div class='portal-module-list-scrolldiv'>
                        <ul class='portal-module-list' id='disabled-module-list'>
                            {foreach from=$hiddenPortalTabs item=module}
                                <li class='ui-state-default mod-list-item' id='{$module.module}'>{$module.label}</li>
                            {/foreach}
                        </ul>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                {$mod.LBL_PORTAL_LOGOMARK_URL}: {sugar_help text=$mod.LBL_CONFIG_PORTAL_LOGOMARK_URL}
            </td>
        <td colspan='1' nowrap>
            <input class='portalProperty portalField logoURL' id='logomarkURL' name='logomarkURL'
                   value='{$logomarkURL}'
                   size=60>
        </td>
        </tr>
        <tr>
            <td colspan='1' nowrap>
                {$mod.LBL_PORTAL_LOGOMARK_PREVIEW}:
            </td>
            <td>
                <div class="company_logo_image_container">
                    <img src='{$logomarkURL}' hide width="22px" height="22px" id="company_logomark_image" alt="{$mod_strings.LBL_LOGO}" />
                </div>
            </td>
        </tr>
        <tr>
            <td colspan='1' nowrap>
                {$mod.LBL_PORTAL_LOGO_URL}: {sugar_help text=$mod.LBL_CONFIG_PORTAL_LOGO_URL}
            </td>
            <td colspan='1' nowrap>
                <input class='portalProperty portalField logoURL' id='logoURL' name='logoURL'
                       value='{$logoURL}' size=60 disabled>
            </td>
        </tr>
        <tr>
            <td colspan='1' nowrap>
                {$mod.LBL_PORTAL_LOGO_PREVIEW}:
            </td>
            <td>
                <div class="company_logo_image_container">
                    <img width="450 px" height="24 px" id="company_logo_image" name = "company_logo_image"
                         src='{$logoURL}' alt="{$mod_strings.LBL_LOGO}" />
                </div>
            </td>
        </tr>
        <tr>
            <td colspan='2' nowrap>
                <input type='button' class='button' id='gobutton' value='{$mod.LBL_BTN_SAVE}'>
            </td>
        </tr>

    </table>
</form>
{literal}

<script language='javascript'>

    // Set up jQuery actions for the portal module lists to make the items drag/drop sortable
    $(function() {
        $('.portal-module-list').sortable({
            stop: function() {
                // Prevent the user from emptying the entire list of displayed Portal modules
                if ($('#enabled-module-list li').length < 1) {
                    $(this).sortable('cancel');
                }
            },
            connectWith: '.portal-module-list'
        }).disableSelection();
    });

    // Retrieves the configured list of Portal modules
    function getModuleListConfig() {
        var moduleList = document.getElementById('enabled-module-list').getElementsByTagName('li');
        var result = ['Home'];
        for (var i = 0; i < moduleList.length; i++) {
            result.push(moduleList[i].id);
        }
        return result;
    }

    // Hack: In iframe and jquery's getting loaded twice so $ doesn't seem to have select2 plugin
    jQuery('#defaultUser').select2({
        placeholder: "{$mod.LBL_USER_SELECT}",
        allowClear: true,
        width: '50%'
    });
    addToValidateRange(0, "maxQueryResult", "int", true,{/literal}"{$mod.LBL_PORTAL_LIST_NUMBER}"{literal},1,100);
    addToValidateUrl(0, 'logoURL', 'alphanumeric', false, {/literal}"{$mod.LBL_PORTAL_LOGO_URL}"{literal});
    addToValidateUrl(0, 'logomarkURL', 'alphanumeric', false, {/literal}"{$mod.LBL_PORTAL_LOGOMARK_URL}"{literal});
    $('#gobutton').click(function(event){
        var $field, fields, props, i, key, val;
        fields = $('.portalField');
        props = {};

        for(i=0; i<fields.length; i++) {
            $field = $(fields[i]);
            key = $field.attr('name') || $field.data('name');
            val = $field.val();
            // select2 copies over attributes (including .portalField class) to a temp element and
            // so we end up with an extra fields element; so here we ignore if not both key/val
            if(key) props[key] = val;

            if ($field.is(':checked')) {
                // We look for both: isset, and, 'true' on other side ('online' still considered falsy!)
                props[key] = 'true';
            }
        }
        props['portalModules'] = getModuleListConfig();
        retrieve_portal_page($.param(props));
    });

    // Updates the preview for logomark
    $('#logomarkURL').change(function() {
        var previewLogoMark, url;
        url = $('#logomarkURL').val();
        previewLogoMark = $('#company_logomark_image');
        if (url) {
            // set the image
            previewLogoMark.attr('src', url);
            showPreview(previewLogoMark);
        } else {
            hidePreview(previewLogoMark);
            hidePreview($('#company_logo_image'));
            $('#logoURL').val('');
            disableLogoUrl();
        }
    });

    $('#company_logomark_image').on('load', function() {
        // enable the logo textbox
        enableLogoUrl();

        // show the logoMark preview
        showPreview($('#company_logomark_image'));
    });

    function hidePreview(preview) {
        preview.hide();
    }

    function showPreview(preview) {
        preview.show();
    }

    function disableLogoUrl() {
        $('#logoURL').prop('disabled', true);
    }

    function enableLogoUrl() {
        $('#logoURL').prop('disabled', false);
    }

    $('#company_logomark_image').on('error', function() {
        // disable the logo textbox
        $('#logoURL').val('');
        disableLogoUrl();

        // hide the logo preview
        hidePreview($('#company_logo_image'));
    });

    // Updates the preview for logo
    $('#logoURL').change(function() {
        var previewLogo, url;
        url = $('#logoURL').val();
        previewLogo = $('#company_logo_image');

        if (url) {
            // set the image
            previewLogo.attr('src', url);
            showPreview(previewLogo);
        } else {
            hidePreview(previewLogo);
        }
    });

    function retrieve_portal_page(props) {
        if (validate_form(0,'')) {
            ModuleBuilder.getContent("module=ModuleBuilder&action=portalconfigsave&" + props);
            removeFromValidate(0, 'maxQueryResult');
            removeFromValidate(0, 'logomarkURL');
            removeFromValidate(0, 'logoURL');
        }
    }
</script>
{/literal}
