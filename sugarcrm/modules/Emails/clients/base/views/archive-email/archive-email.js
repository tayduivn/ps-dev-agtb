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
 * @class View.Views.Base.Emails.ArchiveEmailView
 * @alias SUGAR.App.view.views.BaseEmailsArchiveEmailView
 * @extends View.Views.Base.Emails.CreateView
 */
({
    extendsFrom: 'EmailsCreateView',

    /**
     * @inheritdoc
     *
     * Add click event handler to archive an email.
     */
    initialize: function(options) {
        this.events = _.extend({}, this.events, {
            'click [name=archive_button]': 'archive'
        });
        this._super('initialize', [options]);
    },

    /**
     * Set headerpane title.
     * @private
     */
    _render: function() {
        var $controls;

        this._super('_render');

        $controls = this.$('.control-group:not(.hide) .control-label');
        if ($controls.length) {
            $controls.last().addClass('end-fieldgroup');
        }

        this.setTitle(app.lang.get('LBL_ARCHIVE_EMAIL', this.module));
    },

    /**
     * Archive email if validation passes.
     */
    archive: function() {
        this.model.doValidate(this.getFieldsToValidate(), _.bind(function(isValid) {
            if (isValid) {
                this.archiveEmail();
            }
        }, this));
    },

    /**
     * Get fields that needs to be validated.
     * @return {Object}
     */
    getFieldsToValidate: function() {
        var fields = {};
        _.each(this.fields, function(field) {
            fields[field.name] = field.def;
        });
        return fields;
    },

    /**
     * Call archive api.
     */
    archiveEmail: function() {
        this.model.set('state', 'Archived');
        this.save();
    },

    /**
     * @inheritdoc
     *
     * Build the appropriate success message for an archvived email.
     */
    buildSuccessMessage: function() {
        return app.lang.get('LBL_EMAIL_ARCHIVED', this.module);
    },

    /**
     * No need to warn of configuration status for archive email because no
     * email is being sent.
     */
    notifyConfigurationStatus: $.noop,

    /**
     * No need to insert the default signature for archive email.
     */
    _initializeDefaultSignature: $.noop
})
