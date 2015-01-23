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
        this.isOpen = false;
        app.shortcuts.register(app.shortcuts.GLOBAL + 'Spotlight', 'shift+space', this.toggle, this, true);
        this.on('spotlight:config', this.openConfigPanel, this);
        app.events.on('app:logout', function() {
            if (this.isOpen) {
                this.toggle();
            }
        }, this);
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
        this.isOpen = !this.isOpen;
        this.$('input').val('');
        this.$el.fadeToggle(50, 'linear', _.bind(this.focusInput, this));
        this.trigger('spotlight:status', this.isOpen);
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
        var activeDrawerLayout = app.drawer.getActiveDrawerLayout();
        if (activeDrawerLayout.type === 'spotlight-config') {
            return;
        }

        app.drawer.open({
            layout: 'spotlight-config',
            context: {
                skipFetch: true,
                forceNew: true
            }
        }, this.saveConfig);
    },

    /**
     * Saves the spotlight settings when the drawer closes.
     *
     * @param {Data.BeanCollection} collection The spotlight configuration
     *   collection.
     */
    saveConfig: function(collection) {
        if (collection.cancel) {
            return;
        }
        var json = collection.toJSON();
        var key = app.user.lastState.buildKey('spotlight', 'config');
        app.user.lastState.set(key, json);
        app.events.trigger('spotlight:reset');
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
