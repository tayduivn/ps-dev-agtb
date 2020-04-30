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
 * @class View.Layouts.Home.ConsoleSideDrawerLayout
 * @alias SUGAR.App.view.layouts.HomeConsoleSideDrawerLayout
 * @extends View.Layouts.Base.SideDrawerLayout
 */
({
    extendsFrom: 'SideDrawerLayout',

    /**
     * @inheritdoc
     * Added edit action.
     */
    events: {
        'click [data-action=edit]': 'edit',
        'click [data-action=close]': 'close'
    },

    /**
     * Flag indicating if edit action related functionality should be displayed or not.
     * @property {boolean}
     */
    hasEditAccess: false,

    /**
     * Flag indicating if close and edit actions may be performed or not at the moment.
     * @property {boolean}
     */
    areActionsEnabled: true,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this.setEditAccess();
        this._super('initialize', [options]);
        this.initComponentVariables();
        this.bindEvents();
    },

    /**
     * Stores the close and edit buttons on the component so they would be accessible easier.
     */
    initComponentVariables: function() {
        this.$closeButton = $(this.$el.children()[1]);
        this.$editButton = $(this.$el.children()[2]);
    },

    /**
     * Initiates listening to application events.
     */
    bindEvents: function() {
        app.events.on('drawer:enable:actions', this.enableButtonActions, this);
    },

    /**
     * Will check and set the edit access on the file so the edit button would be displayed or hidden.
     * Only admins should be able to edit the dashlets in a side drawer.
     */
    setEditAccess: function() {
        var configModuleName = 'ConsoleConfiguration';
        var configACLs = app.user.getAcls()[configModuleName];
        var isSystemAdmin = app.user.get('type') == 'admin';
        var hasAdminAccess = !_.has(configACLs, 'admin');
        this.hasEditAccess = isSystemAdmin || hasAdminAccess;
    },

    /**
     * Will switch to edit mode.
     */
    edit: function() {
        if (this.areActionsEnabled) {
            this.disableButtonActions();
            app.events.trigger('drawer:edit');
        }
    },

    /**
     * Close only if the action is enabled.
     */
    close: function() {
        if (this.areActionsEnabled) {
            this._super('close');
        }
    },

    /**
     * It will enable close and edit actions on the side-drawer.
     */
    enableButtonActions: function() {
        this.areActionsEnabled = true;
        this.$closeButton.removeClass('disabled');
        this.$editButton.removeClass('disabled');
    },

    /**
     * It will disable close and edit actions on the side-drawer.
     */
    disableButtonActions: function() {
        this.areActionsEnabled = false;
        this.$closeButton.addClass('disabled');
        this.$editButton.addClass('disabled');
    },
})
