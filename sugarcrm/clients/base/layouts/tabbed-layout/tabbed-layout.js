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
     * @inheritdoc
     */
    bindDataChange: function() {
        this.context.on('tabbed-layout:tab:change', this.toggleTabVisibility, this);
    },

    /**
     * @inheritdoc
     */
    render: function() {
        var isPreview = this.name === 'preview-pane';

        if (isPreview) {
            this.$('a[data-toggle="tab"]').off('shown.bs.tab');
        }

        this._super('render');

        if (isPreview) {
            this.$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                var tabName = $(e.target.parentElement).data('tab-name');
                var $navTabs = $(e.target).parents('.nav-tabs');
                var $tabbable = $(e.target).parents('.tabbable');

                $navTabs.toggleClass('preview-pane-tabs', tabName === 'preview');
                $tabbable.toggleClass('preview-active', tabName === 'preview');
            });
        }

    },

    /**
     * Toggles the visibility of multiple tabbed-layout layouts
     *
     * @param {string} tabName The name of the tab being toggled
     * @param {boolean} isVisible True if the tab is visible now or not
     */
    toggleTabVisibility: function(tabName, isVisible) {
        var method = isVisible ? 'hide' : 'show';

        if (this.name.indexOf(tabName) === -1) {
            this[method]();
        }
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
    _placeComponent: function(comp, def) {
        var id = _.uniqueId('record-bottom');
        var compDef = def.layout || def.view || {};
        var lblKey = compDef.label || compDef.name || compDef.type;
        var lblName = compDef.name || compDef.title || compDef;

        if (!lblKey) {
            // handles the 'preview' case returning the label
            // 'LBL_PREVIEW' for translations
            lblKey = 'LBL_' + compDef.toUpperCase();
        }

        var label = app.lang.get(lblKey, this.module) || lblKey;
        var $nav = $('<li/>').html('<a href="#' + id + '" onclick="return false;" data-toggle="tab">' + label + '</a>');
        var $content = $('<div/>').addClass('tab-pane').attr('id', id).html(comp.el);
        var $ulNav = this.$('.nav');

        $ulNav.addClass(this.name + '-tabs');
        $nav.addClass('nav-item');
        $nav.data('tab-name', lblName);

        if (!this.firstIsActive) {
            $nav.addClass('active');
            $content.addClass('active');

            if (lblName === 'preview') {
                this.$('.tabbable').addClass('preview-active');
            }
        }

        this.firstIsActive = true;
        this.$('.tab-content').append($content);
        $ulNav.append($nav);
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
    },

    /**
     * @inheritdoc
     */
    dispose: function() {
        if (this.name === 'preview-pane') {
            this.$('a[data-toggle="tab"]').off('shown.bs.tab');
        }

        this._super('dispose');
    }
})
