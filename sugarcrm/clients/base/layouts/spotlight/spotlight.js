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

    initialize: function(options) {
        this._super('initialize', [options]);
        app.shortcuts.register(app.shortcuts.GLOBAL + 'Spotlight', 'shift+space', this.toggle, this, true);
        this.on('spotlight:config', this.openConfigPanel, this);
    },

    _render: function() {
        if (!app.api.isAuthenticated()) {
            return;
        }
        this._super('_render');
    },

    escape: function(evt) {
        if (evt.keyCode === 27) {
            this.toggle();
        }
    },

    toggle: function() {
        this.$('input').val('');
        this.$el.fadeToggle(50, 'linear', _.bind(this.onToggleComplete, this));
    },

    onToggleComplete: function() {
        this.$('input').focus();
    },

    openConfigPanel: function() {
        app.drawer.open({
            layout: 'spotlight-config',
            context: this.context
        }, this.saveConfig);
    },

    saveConfig: function(model) {
        console.log('saving the config', model);
    }
})
