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
 * @class View.Layouts.Base.SweetspotLayout
 * @alias SUGAR.App.view.layouts.BaseSweetspotLayout
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
        app.shortcuts.register(app.shortcuts.GLOBAL + 'Sweetspot', 'shift+space', this.toggle, this, true);
        this.on('sweetspot:config', this.openConfigPanel, this);
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
     * Closes the Sweet Spot when you press `Esc`.
     *
     * @param {Event} evt The `keyup` event.
     */
    escape: function(evt) {
        if (evt.keyCode === 27) {
            this.toggle();
        }
    },

    /**
     * Toggles the Sweet Spot.
     */
    toggle: function() {
        this.isOpen = !this.isOpen;
        this.$('input').val('');
        this.$el.fadeToggle(50, 'linear', _.bind(this.focusInput, this));
        this.trigger('sweetspot:status', this.isOpen);
    },

    /**
     * Focuses on the Sweet Spot input.
     */
    focusInput: function() {
        this.$('input').focus();
    },

    /**
     * Opens a drawer with the {@link View.Layouts.Base.SweetspotConfigLayout}
     * to configure the Sweet Spot.
     */
    openConfigPanel: function() {
        var activeDrawerLayout = app.drawer.getActiveDrawerLayout();
        if (activeDrawerLayout.type === 'sweetspot-config') {
            return;
        }

        app.drawer.open({
            layout: 'sweetspot-config',
            context: {
                skipFetch: true,
                forceNew: true
            }
        }, this.saveConfig);
    },

    /**
     * Saves the Sweet Spot settings when the drawer closes.
     *
     * @param {Data.BeanCollection} collection The Sweet Spot configuration
     *   collection.
     */
    saveConfig: function(collection) {
        if (collection.cancel) {
            return;
        }
        var json = collection.toJSON();
        var key = app.user.lastState.buildKey('sweetspot', 'config');
        app.user.lastState.set(key, json);
        app.events.trigger('sweetspot:reset');
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
