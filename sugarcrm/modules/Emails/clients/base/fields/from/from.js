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
 * @extends View.Fields.Base.Emails.EmailRecipientsField
 */
({
    extendsFrom: 'EmailsEmailRecipientsField',

    /**
     * @inheritdoc
     *
     * Use the select2 templates from the email-recipients field so that we
     * don't need to replicate them in the from field.
     *
     * @override
     */
    _initSelect2Templates: function() {
        this._super('_initSelect2Templates', ['email-recipients']);
    },

    /**
     * @inheritdoc
     *
     * @param {Backbone.Collection} value The value to format.
     * @return {string} formatted value.
     */
    format: function(value) {
        value = this._super('format', [value]);

        if (this.action !== 'edit') {
            this._setTooltipText(value);
        }

        return value;
    },

    /**
     * Set the tooltip text which will include the name (if exists) and the
     * email address.
     *
     * When a name exists, it is displayed, so the email address is needed in
     * the tooltip. If the name is too long, it will be ellipsified, which is
     * why we have the name in the tooltip too.
     *
     * @param {Array} value Array of recipients already formatted by the
     *   email-recipients field.
     * @private
     */
    _setTooltipText: function(value) {
        var fromModel;
        var fromName = '';
        var fromEmail = '';

        this.tooltipText = '';

        if (_.isArray(value) && value.length > 0) {
            fromModel = _.first(value);

            if (fromModel.has('name')) {
                fromName = fromModel.get('name');
            }
            fromEmail = fromModel.get('email_address');

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
    },

    /**
     * @inheritdoc
     *
     * Limit the number of choices that can be made to 1 since there can be only
     * one person in the From field.
     *
     * @override
     */
    _getSelect2Options: function() {
        var options = this._super('_getSelect2Options');
        return _.extend(options, {
            maximumSelectionSize: 1,
            formatSelectionTooBig: function() {
                return '';
            }
        });
    },

    /**
     * @inheritdoc
     *
     * No address book for the from field.
     *
     * @override
     */
    _addAddressBookIconPadding: $.noop,

    /**
     * @inheritdoc
     *
     * No need to call fetchAllRecipients for from field.
     *
     * @override
     */
    _fetchAllRecipients: $.noop
})
