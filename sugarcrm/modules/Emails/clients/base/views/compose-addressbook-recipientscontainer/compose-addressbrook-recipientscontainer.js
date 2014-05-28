/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
/**
 * @class View.Views.Base.Emails.ComposeAddressbookRecipientscontainerView
 * @alias SUGAR.App.view.views.BaseEmailsComposeAddressbookRecipientscontainerView
 * @extends View.Views.Base.RecordView
 */
({
    extendsFrom:         "RecordView",
    enableHeaderButtons: false,
    enableHeaderPane:    false,
    events:              {},

    initialize: function(options) {
        this._super("initialize", [options]);
        this.model.isNotEmpty = true;
    },

    /**
     * Override to remove unwanted functionality.
     *
     * @param prefill
     */
    setupDuplicateFields: function(prefill) {},

    /**
     * Override to remove unwanted functionality.
     */
    delegateButtonEvents: function() {},

    /**
     * Override to remove unwanted functionality.
     */
    initButtons: function() {
        this.buttons = {};
    },

    /**
     * Override to remove unwanted functionality.
     */
    showPreviousNextBtnGroup: function() {},

    /**
     * Override to remove unwanted functionality.
     */
    bindDataChange: function() {},

    /**
     * Override to remove unwanted functionality.
     *
     * @param isEdit
     */
    toggleHeaderLabels: function(isEdit) {},

    /**
     * Override to remove unwanted functionality.
     *
     * @param field
     */
    toggleLabelByField: function (field) {},

    /**
     * Override to remove unwanted functionality.
     *
     * @param e
     * @param field
     */
    handleKeyDown: function(e, field) {},

    /**
     * Override to remove unwanted functionality.
     *
     * @param state
     */
    setButtonStates: function(state) {},

    /**
     * Override to remove unwanted functionality.
     *
     * @param title
     */
    setTitle: function(title) {}
})
