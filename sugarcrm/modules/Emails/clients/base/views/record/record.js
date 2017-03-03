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

    STATE_DRAFT: 'Draft',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        var loadingRequests = 0;

        this._super('initialize', [options]);

        if (this.model.get('state') === this.STATE_DRAFT) {
            this._alertUserDraftState();
        }

        this.listenTo(this.model, 'change:state', this._alertUserDraftState);

        this.listenTo(this.model, 'change:from', function() {
            this._renderRecipientsField('from');
        });
        this.listenTo(this.model, 'change:to', function() {
            this._renderRecipientsField('to');
        });
        this.listenTo(this.model, 'change:cc', function() {
            this._renderRecipientsField('cc');
        });
        this.listenTo(this.model, 'change:bcc', function() {
            this._renderRecipientsField('bcc');
        });

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

    /*
    * Alerts the user that the draft email was opened in the record view route.
    * Allows the user to click and open the draft in the compose drawer.
     */
    _alertUserDraftState: function() {
        var model = this.model;

        app.alert.dismiss('email-draft-alert');

        if (model.get('state') === this.STATE_DRAFT) {
            app.alert.show('email-draft-alert', {
                level: 'warning',
                autoClose: false,
                title: ' ',
                messages: app.lang.get('LBL_OPEN_DRAFT_ALERT', this.module, {subject: model.get('name')}),
                onLinkClick: function(event) {
                    var route =  '#' + app.router.buildRoute(model.module + '/drafts', model.get('id'));
                    app.alert.dismiss('email-draft-alert');
                    app.router.navigate(route, {trigger: true});
                }
            });
        }
    },

    /**
     * Render the specified recipients field
     */
    _renderRecipientsField: function(fieldName) {
        var field = this.getField(fieldName);
        if (field) {
            field.render();
        }
    },

    /**
     * @inheritdoc
     * When record name is empty, return (no subject)
     */
    _getNameForMessage: function(model) {
        var name = this._super('_getNameForMessage', [model]);

        if (_.isEmpty(name)) {
            return app.lang.get('LBL_NO_SUBJECT', this.module);
        }

        return name;
    }
})
