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
/**
 * @class View.Fields.Base.SaveAndSendInvitesButtonField
 * @alias SUGAR.App.view.fields.BaseSaveAndSendInvitesButtonField
 * @extends View.Fields.Base.Rowaction
 */
({
    extendsFrom: 'RowactionField',

    /**
     * @inheritdoc
     *
     * Sets the type to "rowaction" so that the templates are loaded from
     * super.
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.type = 'rowaction';
    },

    /**
     * @inheritdoc
     *
     * Shows a confirmation before save to ask the user whether or not invite
     * emails should be sent to the participants after saving the record.
     *
     * The confirmation buttons are bound to {@link #handleYes} and
     * {@link #handleNo} for "yes" and "no," respectively.
     */
    rowActionSelect: function(event) {
        if (this.preventClick(event) === false) {
            return;
        }

        app.alert.show('save_confirmation', {
            title: app.lang.get('LBL_SEND_INVITES_CONFIRMATION', this.module),
            level: 'confirmation',
            messages: app.lang.get('NTC_SEND_INVITES_CONFIRMATION', this.module),
            confirm: {
                label: app.lang.get('LBL_YES', this.module),
                callback: _.bind(function() {
                    this.handleYes(event);
                }, this)
            },
            cancel: {
                label: app.lang.get('LBL_NO', this.module),
                callback: _.bind(function() {
                    this.handleNo(event);
                }, this)
            }
        });
    },

    /**
     * Handler for when the user clicks the "yes" button from the confirmation.
     *
     * Tells the model to send invite emails when saving the record by setting
     * the "send_invites" flag to true -- only if the participants collection
     * is not dirty -- and triggering the continuation of the save operation.
     *
     * Delays the sending of invite emails until after the participants
     * collection has been synchronized when the participants collection is
     * dirty. Instead of re-saving the record with the "send_invites" flag set
     * to true in this case, the send_invites API is called. This helps to
     * avoid triggering 409 conflicts.
     *
     * Calls {@link View.Fields.Base.RowactionField#propagateEvent} to inform
     * listeners that the button was clicked and to proceed with saving the
     * record.
     *
     * @param {Object} [event] The click event.
     */
    handleYes: function(event) {
        var invitees = this.model.get('invitees');

        if (invitees && invitees.hasChanged()) {
            invitees.once('sync', function() {
                var url = app.api.buildURL(this.module, 'send_invites', {id: this.id});
                app.api.call('update', url);
            }, this.model);
        } else {
            this.model.set('send_invites', true, {silent: true});
        }

        this.propagateEvent(event);
    },

    /**
     * Handler for when the user clicks the "no" button from the confirmation.
     *
     * Tells the model not to send invite emails when saving the record by
     * setting the "send_invites" flag to false and triggering the continuation
     * save when the user chooses "no."
     *
     * Calls {@link View.Fields.Base.RowactionField#propagateEvent} to inform
     * listeners that the button was clicked and to proceed with saving the
     * record.
     *
     * @param {Object} [event] The click event.
     */
    handleNo: function(event) {
        this.model.set('send_invites', false, {silent: true});
        this.propagateEvent(event);
    }
})
