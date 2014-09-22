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
     * Setting model event to allow unsetting of send_invites after validation error or data sync completed.
     * @inheritDoc
     */
    bindDataChange: function() {
        if (!this.model) {
            return;
        }

        this.model.on('error:validation data:sync:complete', function() {
            this.model.unset('send_invites');
        }, this);
    },

    /**
     * @inheritDoc
     *
     * Triggers the save event on the record once button is clicked
     *
     * @see View.Fields.Base.RowactionField#getTarget
     * @param {Object} [event] The click event.
     */
    propagateEvent: function(event) {
        var triggerName = this.view.save_button ? this.view.save_button : 'save_button',
            trigger = 'button:' + triggerName + ':click';

        this.model.set('send_invites', true, {silent: true});
        this.context.trigger(trigger);
    }
})
