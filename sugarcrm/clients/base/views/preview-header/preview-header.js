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
 * @class View.Views.Base.PreviewHeaderView
 * @alias SUGAR.App.view.views.BasePreviewHeaderView
 * @extends View.View
 */
({
    className: 'preview-headerbar',

    events: {
        'click [data-direction]': 'triggerPagination',
        'click .preview-headerbar .closeSubdetail': 'triggerClose',
        'click [data-action=edit]': 'triggerEdit',
        'click [data-action=save]': 'triggerSave',
        'click [data-action=cancel]': 'triggerCancel'
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this._delegateEvents();
        //only allow preview edit when on a recordlist and user has acl access
        if (this.context.get('layout') === 'records' && app.acl.hasAccessToModel('edit', this.model)) {
            this.previewEdit = true;
        }
    },

    /**
     * Set up event listeners
     *
     * @private
     */
    _delegateEvents: function() {
        if (this.layout) {
            this.layout.off('preview:pagination:update', null, this);
            this.layout.on('preview:pagination:update', this.render, this);
            this.layout.on('preview:save:complete', this.hideSaveAndCancel, this);
        }
    },

    triggerPagination: function(e) {
        var direction = this.$(e.currentTarget).data();
        this.layout.trigger("preview:pagination:fire", direction);
    },

    triggerClose: function() {
        app.events.trigger("list:preview:decorate", null, this);
        app.events.trigger("preview:close");
    },

    /**
     * Call preview view to turn on editing
     */
    triggerEdit: function() {
        this.showSaveAndCancel();
        this.layout.trigger('preview:edit');
    },

    /**
     * Trigger preview view to do save actions
     */
    triggerSave: function() {
        this.layout.trigger('button:save_button:click');
    },

    /**
     * Trigger preview view to do cancel actions
     */
    triggerCancel: function() {
        this.hideSaveAndCancel();
        this.layout.trigger('button:cancel_button:click');
    },

    /**
     * Show the save and cancel buttons in the preview-header and
     * hide the left, right and x buttons if user has acl access
     *
     */
    showSaveAndCancel: function() {
        this.$('.save-btn, .cancel-btn').show();
        this.$('.btn-left, .btn-right, .closeSubdetail').hide();
    },

    /**
     * Hide the save and cancel buttons and show the left, right and
     * x buttons
     *
     */
    hideSaveAndCancel: function() {
        this.$('.save-btn, .cancel-btn').hide();
        this.$('.btn-left, .btn-right, .closeSubdetail').show();
    }
})
