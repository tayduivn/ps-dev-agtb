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
 * @class View.Fields.Base.Emails.FromField
 * @alias SUGAR.App.view.fields.BaseEmailsFromField
 * @extends View.Fields.Base.BaseField
 */
({
    /**
     * @inheritdoc
     *
     * @param {Backbone.Collection} value The value to format.
     * @return {string} formatted value.
     */
    format: function(value) {
        var fromModel;
        var fromName = '';
        var fromEmail = '';

        this.tooltipText = '';

        if (value instanceof Backbone.Collection && value.length > 0) {
            fromModel = value.first();
            if (fromModel.has('name')) {
                fromName = fromModel.get('name');
            }
            if (fromModel.has('email_address_used')) {
                fromEmail = fromModel.get('email_address_used');
            }
            if (_.isEmpty(fromEmail)) {
                fromEmail = app.utils.getPrimaryEmailAddress(fromModel);
            }

            if (!_.isEmpty(fromName)) {
                this.tooltipText = fromName;
            }
            if (!_.isEmpty(fromEmail)) {
                if (!_.isEmpty(this.tooltipText)) {
                    this.tooltipText += ' <' + fromEmail + '>';
                } else {
                    this.tooltipText = fromEmail;
                }
            }
        }

        return fromName || fromEmail;
    }
})
