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
 * @class View.Layouts.Base.SpotlightLayout
 * @alias SUGAR.App.view.layouts.BaseSpotlightLayout
 * @extends View.Layout
 */
({
    events: {
        'keyup': 'escape'
    },

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        app.shortcuts.register(app.shortcuts.GLOBAL + 'Spotlight', 'shift+space', this.toggle, this, true);
        this.on('spotlight:config', this.openConfigPanel, this);
    },

    /**
     * @inheritDoc
     */
    _render: function() {
        if (!app.api.isAuthenticated()) {
            return;
        }
        this._super('_render');
    },

    /**
     * Closes the spotlight when you press `Esc`.
     *
     * @param {Event} evt The `keyup` event.
     */
    escape: function(evt) {
        if (evt.keyCode === 27) {
            this.toggle();
        }
    },

    /**
     * Toggles the spotlight.
     */
    toggle: function() {
        this.$('input').val('');
        this.$el.fadeToggle(50, 'linear', _.bind(this.focusInput, this));
    },

    /**
     * Focus on the input.
     */
    focusInput: function() {
        this.$('input').focus();
    },

    /**
     * Opens a drawer with the {@link View.Layouts.Base.SpotlightConfigLayout}
     * to configure the spotlight.
     */
    openConfigPanel: function() {
        app.drawer.open({
            layout: 'spotlight-config',
            context: this.context
        }, this.saveConfig);
    },

    /**
     * Saves the spotlight settings when the drawer closes.
     *
     * @param {Data.Bean} model The spotlight model.
     */
    saveConfig: function(model) {
        console.log('saving the config', model);
    },

    /**
     * Trigger a system action.
     *
     * @param {string} method Name of the method in {@link #_systemActions}.
     */
    triggerSystemAction: function(method) {
        if (!_.isFunction(this._systemActions[method])) {
            return;
        }
        this._systemActions[method].call(this);
    },

    /**
     * List of system action callbacks.
     *
     * Use {@link #triggerSystemAction} to trigger them.
     */
    _systemActions: {
        toggleHelp: function() {
            app.events.trigger('app:help');
        }
    }
})
