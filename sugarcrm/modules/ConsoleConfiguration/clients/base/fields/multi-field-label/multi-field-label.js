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
/**
 * @class View.Fields.Base.ConsoleConfiguration.MultiFieldColumnLinkField
 * @alias SUGAR.App.view.fields.BaseConsoleConfigurationMultiFieldColumnLinkField
 * @extends View.Fields.Base.BaseField
 */
({
    events: {
        'click .multi-field-label': 'multiFieldColumnLinkClicked'
    },

    /**
     * Create a new empty block and append it to the field list
     * @param e
     */
    multiFieldColumnLinkClicked: function(e) {
        var multiRow = app.lang.get('LBL_CONSOLE_MULTI_ROW', this.module);
        var multiRowHint = app.lang.get('LBL_CONSOLE_MULTI_ROW_HINT', this.module);
        var newMultiField = '<li class="multi-field-block"><ul class="multi-field"><li class="list-header"><i>' +
            multiRow +
            '</i><i class="fa fa-times-circle console-field-remove"></i></li><div class="multi-field-hint">' +
            multiRowHint + '</div></ul></li>';
        $(e.currentTarget).closest('div.column').find('ul.field-list:first').append(newMultiField);
    }
})
