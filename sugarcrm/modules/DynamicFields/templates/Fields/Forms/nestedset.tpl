{*
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
*}

{include file="modules/DynamicFields/templates/Fields/Forms/coreTop.tpl"}
<tr>
    <td class='mbLBL'>{sugar_translate module="DynamicFields" label="LBL_NESTED_MODULE"}:</td>
    <td>
        {html_options name="ext2" id="ext2" selected=$vardef.category_provider options=$nestedBeans}
        <input type='hidden' name='ext3' value='{$vardef.id_name}' />
    </td>
</tr>
<tr>
    <td class='mbLBL'>{sugar_translate module="DynamicFields" label="LBL_NESTED_ROOT"}:</td>
    <td>
        <script>
            var beanRoots = JSON.parse('{$beanRoots}'),
                root = '{$vardef.category_root}';
            {literal}
            $('#ext2').on('change', function() {
                var $el = $('#ext2');
                $('#ext4').children().remove();
                _.each(beanRoots[$el.val()], function(value) {
                    $('<option></option>', {value: value.id}).text(value.name).appendTo($('#ext4'));
                });
                return true;
            });
            {/literal}
            $('#ext2').change();
            $('#ext4').val(root);
        </script>
        {html_options name="ext4" id="ext4" options=array()}
    </td>
</tr>
{include file="modules/DynamicFields/templates/Fields/Forms/coreBottom.tpl"}
