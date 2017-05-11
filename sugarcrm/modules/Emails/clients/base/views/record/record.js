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
 * @class View.Views.Base.Emails.RecordView
 * @alias SUGAR.App.view.views.BaseEmailsRecordView
 * @extends View.Views.Base.RecordView
 */
({
    extendsFrom: 'RecordView',

    /**
     * Constant representing the state of an email when it is a draft.
     *
     * @property {string}
     */
    STATE_DRAFT: 'Draft',

    /**
     * @inheritdoc
     *
     * Alerts the user if the email is a draft, so that user can switch to
     * composing the email instead of simply viewing it.
     */
    initialize: function(options) {
        var loadingRequests = 0;

        this._super('initialize', [options]);

        if (this.model.get('state') === this.STATE_DRAFT) {
            this._alertUserDraftState();
        }

        this.on('loading_collection_field', function() {
            loadingRequests++;
            this.toggleButtons(false);
        }, this);

        this.on('loaded_collection_field', function() {
            loadingRequests--;

            if (loadingRequests === 0) {
                this.toggleButtons(true);
            }
        }, this);
    },

    /**
     * @inheritdoc
     *
     * Alerts the user if the email becomes a draft -- most likely due to
     * asynchronous data patching -- so that user can switch to composing the
     * email instead of simply viewing it.
     *
     * Renders the recipients fieldset anytime there are changes to the 'from',
     * `to`, `cc`, or `bcc` fields.
     */
    bindDataChange: function() {
        var self = this;

        /**
         * Render the specified recipients field.
         *
         * @param {string} fieldName
         */
        function renderRecipientsField(fieldName) {
            var field = self.getField(fieldName);

            if (field) {
                field.render();
            }
        }

        if (this.model) {
            this.listenTo(this.model, 'change:state', this._alertUserDraftState);

            this.listenTo(this.model, 'change:from', function() {
                renderRecipientsField('from');
            });
            this.listenTo(this.model, 'change:to', function() {
                renderRecipientsField('to');
            });
            this.listenTo(this.model, 'change:cc', function() {
                renderRecipientsField('cc');
            });
            this.listenTo(this.model, 'change:bcc', function() {
                renderRecipientsField('bcc');
            });

        }

        this._super('bindDataChange');
    },

    /**
     * Alerts the user if a draft was opened in the record view, so the user
     * can switch to composing the email instead of simply viewing it.
     *
     * @private
     */
    _alertUserDraftState: function() {
        app.alert.dismiss('email-draft-alert');

        if (this.model.get('state') === this.STATE_DRAFT) {
            app.alert.show('email-draft-alert', {
                level: 'warning',
                autoClose: false,
                title: ' ',
                messages: app.lang.get('LBL_OPEN_DRAFT_ALERT', this.module, {subject: this.model.get('name')}),
                onLinkClick: _.bind(function(event) {
                    var route = '#' + app.router.buildRoute(this.model.module, this.model.get('id'), 'compose');

                    app.alert.dismiss('email-draft-alert');
                    app.router.navigate(route, {trigger: true});
                }, this)
            });
        }
    },

    /**
     * @inheritdoc
     *
     * @return {string} Returns (no subject) when the record name is empty.
     */
    _getNameForMessage: function(model) {
        var name = this._super('_getNameForMessage', [model]);

        if (_.isEmpty(name)) {
            return app.lang.get('LBL_NO_SUBJECT', this.module);
        }

        return name;
    }
})
