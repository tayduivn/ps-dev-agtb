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
 * @class View.Layouts.Base.TabbedLayoutLayout
 * @alias SUGAR.App.view.layouts.BaseTabbedLayoutLayout
 * @extends View.Layout
 */
({
    maxTabs: undefined,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this.firstIsActive = false;

        if (options.meta) {
            // default to no tabs
            options.meta.notabs = true;
            if (options.meta.components) {
                // update metadata notabs setting before parent view initialize
                options.meta.notabs = options.meta.components.length <= 1;
            }
        }

        this.updateLayoutConfig();

        this._super('initialize', [options]);
    },

    /**
     * Extensible function that updates any local config vars that need to be set.
     */
    updateLayoutConfig: function() {
        this.maxTabs = 3;
    },

    /**
     * @inheritdoc
     */
    addComponent: function(component, def) {
        this._super('addComponent', [component, def]);

        // If we're using tabs,
        // and all the components have been added,
        // and there are more components than maxTabs is set for
        if (
            !this.meta.notabs &&
            this._components.length === this.meta.components.length &&
            this._components.length > this.maxTabs
        ) {
            this._addTabNavControls();
        }
    },

    /**
     * Adds tab navigation controls if needed
     *
     * @protected
     */
    _addTabNavControls: function() {
        var $nav = $('<li/>').html('< > X');
        $nav.addClass('tab-controls');

        this.$('.nav').append($nav);
    },

    /**
     * @inheritdoc
     */
    _placeComponent: function(comp, def) {
        var id = _.uniqueId('record-bottom');
        var compDef = def.layout || def.view || {};
        var lblKey = compDef.label || compDef.name || compDef.type;
        var label = app.lang.get(lblKey, this.module) || lblKey;
        var $nav = $('<li/>').html('<a href="#' + id + '" onclick="return false;" data-toggle="tab">' + label + '</a>');
        var $content = $('<div/>').addClass('tab-pane').attr('id', id).html(comp.el);

        $nav.addClass('nav-item');
        if (!this.firstIsActive) {
            $nav.addClass('active');
            $content.addClass('active');
        }

        this.firstIsActive = true;
        this.$('.tab-content').append($content);
        this.$('.nav').append($nav);
    },

    /**
     * When a component is removed, the tab layout needs to remove the tabs as well,
     * including possibly setting a new active tab
     *
     * @param {Object} component The component to remove from the layout
     */
    removeComponent: function(component) {
        var i = _.isNumber(component) ? component : this._components.indexOf(component);

        this._super('removeComponent', [i]);

        var $tabNavEl = $(this.$('.nav-tabs li')[i]);
        var $tabContentEl = $(this.$('.tab-pane')[i]);
        var resetActive = $tabNavEl.hasClass('active');

        $tabNavEl.remove();
        $tabContentEl.remove();

        if (resetActive) {
            this.$(this.$('li')[0]).addClass('active');
            this.$(this.$('.tab-pane')[0]).addClass('active');
        }
    }
})
