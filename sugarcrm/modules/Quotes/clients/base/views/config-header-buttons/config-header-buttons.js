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
 * @class View.Views.Base.Quotes.ConfigHeaderButtonsView
 * @alias SUGAR.App.view.views.BaseQuotesConfigHeaderButtonsView
 * @extends  View.View.Base.ConfigHeaderButtonsView
 */
({
    /**
     * @inheritdoc
     */
    extendsFrom: 'BaseConfigHeaderButtonsView',

    /**
     * @inheritdoc
     */
    _getSaveConfigAttributes: function() {
        var saveObj = this.model.toJSON();
        var lineNum;

        // make sure line_num field exists in worksheet_columns
        lineNum = _.find(saveObj.worksheet_columns, function(col) {
            return col.name === 'line_num';
        }, this);

        if (!lineNum) {
            saveObj.worksheet_columns.unshift({
                name: 'line_num',
                label: null,
                widthClass: 'cell-xsmall',
                css_class: 'line_num tcenter',
                type: 'line-num',
                readonly: true
            });
        }

        // make sure related_fields contains description and product name fields
        if (!_.contains(saveObj.worksheet_columns_related_fields, 'description')) {
            saveObj.worksheet_columns_related_fields.push('description');
        }
        if (!_.contains(saveObj.worksheet_columns_related_fields, 'name')) {
            saveObj.worksheet_columns_related_fields.push('name');
        }
        if (!_.contains(saveObj.worksheet_columns_related_fields, 'product_template_id')) {
            saveObj.worksheet_columns_related_fields.push('product_template_id');
        }
        if (!_.contains(saveObj.worksheet_columns_related_fields, 'product_template_name')) {
            saveObj.worksheet_columns_related_fields.push('product_template_name');
        }

        return saveObj;
    }
})
