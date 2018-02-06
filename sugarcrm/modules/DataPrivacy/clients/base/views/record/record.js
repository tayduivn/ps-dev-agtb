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
 * @class View.Views.Base.DataPrivacy.RecordView
 * @alias SUGAR.App.view.views.BaseDataPrivacyRecordView
 * @extends View.Views.Base.RecordView
 */
({
    extendsFrom: 'RecordView',

    /**
     * @inheritdoc
     *
     */
    bindDataChange: function() {
        this._super('bindDataChange');
        this.model.on('change', function() {
            if (!this.inlineEditMode &&
                this.action !== 'edit') {
                this.setButtonStates(this.STATE.VIEW);
            }
        }, this);
    },

    /**
     * @inheritdoc
     *
     */
    setButtonStates: function(state) {
        this._super('setButtonStates', [state]);
        this.setCompleteButtons(state);
    },

    /**
     * @inheritdoc
     *
     *  Depending on the type index, we either show or hide
     *  complete_button and erase_complete_button
     */
    setCompleteButtons: function(state) {
        var erase = (this.model.get('type') === 'Request to Erase Information');
        if (state === this.STATE.VIEW && app.acl.hasAccess('admin', this.module)) {
            this.currentState = state;
            _.each(this.buttons, function(field) {
                if (this.shouldHide(erase, field)) {
                    field.hide();
                }
            }, this);
            this.toggleButtons(true);
        }
    },

    /**
     * @inheritdoc
     *
     * Check whether the button should be hidden
     */
    shouldHide: function(erase, field) {
        if ((erase && field.name === 'complete_button') || (!erase && field.name === 'erase_complete_button')) {
            return true;
        }
        return false;
    },
})
