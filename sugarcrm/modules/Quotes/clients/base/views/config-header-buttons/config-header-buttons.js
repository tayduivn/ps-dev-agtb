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
        var footerRows = [];
        var quotesMeta = app.metadata.getModule('Quotes', 'fields');

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

        // tweak any worksheet columns fields
        _.each(saveObj.worksheet_columns, function(col) {
            if (col.name === 'product_template_name') {
                // force product_template_name to be required if it exists
                col.required = true;
            }
        }, this);

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

        _.each(saveObj.footer_rows, function(row) {
            let obj = {
                name: row.name,
                type: row.syncedType || row.type
            };
            if (row.syncedCssClass || row.css_class) {
                obj.css_class = row.syncedCssClass || row.css_class;
            }
            if (row.hasOwnProperty('default')) {
                obj.default = row.default;
            }
            if (quotesMeta[row.name] && !quotesMeta[row.name].formula) {
                obj.type = 'quote-footer-currency';
                obj.default = '0.00';
                if (!obj.css_class || (row.css_class && row.css_class.indexOf('quote-footer-currency') === -1)) {
                    obj.css_class = 'quote-footer-currency';
                }
            }

            footerRows.push(obj);
        }, this);

        saveObj.footer_rows = footerRows;

        return saveObj;
    }
})
