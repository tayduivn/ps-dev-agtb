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
    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        // FIXME Sidecar should be modified to allow multiple top level contexts. When this happens, quick search
        // should use that context instead of layout.collection.
        this.collection = app.data.createMixedBeanCollection();

        app.shortcuts.register(app.shortcuts.GLOBAL + 'Sweetspot', 'shift+space', this.toggle, this, true);
        app.events.on('app:logout router:reauth:load', this.hide, this);
        app.events.on('app:sync:complete sweetspot:reset', this._setTheme, this);

        this.on('sweetspot:config', this.openConfigPanel, this);
        this.on('sweetspot:calc:resultsHeight', this.calculateResultsHeight, this);
        this.on('sweetspot:has:results', function(results) {
            var hasResults = true;
            if (_.isEmpty(results) ||
                (!results.actions.length && !results.keywords.length && !results.records.length)
            ) {
                hasResults = false;
            }
            this.$el.toggleClass('has-results', hasResults);
        });

        $(window).on('resize.sweetspot-' + this.cid, _.bind(this.calculateResultsHeight, this));

        /**
         * Flag to indicate the visible state of the sweet spot.
         *
         * @type {boolean}
         * @private
         */
        this._isVisible = false;
    },

    /**
     * @inheritDoc
     */
    _render: function() {
        if (!this.isReady()) {
            return;
        }
        this._super('_render');
        this._setTheme();
    },

    /**
     * Sets the theme for sweetspot.
     *
     * @protected
     */
    _setTheme: function() {
        var prefs = app.user.getPreference('sweetspot');
        var theme = prefs && prefs.theme;

        this.$el.removeAttr('data-theme');
        if (theme) {
            this.$el.attr('data-theme', theme);
        }
    },

    /**
     * Binds events that this layout uses.
     *
     * @protected
     */
    _bindEvents: function() {
        this.bindEsc();
        this.bindOutsideClick();
    },

    /**
     * Unbinds events that this layout uses.
     *
     * @protected
     */
    _unbindEvents: function() {
        this.unbindEsc();
        this.unbindOutsideClick();
    },

    /**
     * Binds the outside `click` event.
     */
    bindOutsideClick: function() {
        $('body').bind('click.' + this.cid, _.bind(function(e) {
            if ($(e.target).closest('#sweetspot').length === 0) {
                this.hide();
            }
        }, this));
    },

    /**
     * Unbinds the outside `click` event.
     */
    unbindOutsideClick: function() {
        $('body').unbind('click.' + this.cid);
    },

    /**
     * Binds the `esc` keydown event.
     */
    bindEsc: function() {
        $(document).on('keydown.' + this.cid, _.bind(function(evt) {
            if (evt.keyCode == 27) {
                this.hide();
            }
        }, this));
    },

    /**
     * Unbinds the `esc` keydown event.
     */
    unbindEsc: function() {
        $(document).off('keydown.' + this.cid);
    },

    /**
     * Checks if this layout is ready to be {@link #show displayed}, or
     * {@link #_render rendered}.
     *
     * FIXME SC-2761: Checking `isVisible` on the header component is necessary
     * for disabling this layout on full-page modal views like the first login
     * wizard. However, hiding additionalComponents should be event driven,
     * see https://github.com/sugarcrm/Mango/pull/18722#discussion_r11782561.
     *
     * @return {boolean} `true` if this layout is OK to render/show, `false`
     *   otherwise.
     */
    isReady: function() {
        return app.api.isAuthenticated() && app.isSynced && app.additionalComponents.header.isVisible();
    },

    /**
     * @override
     */
    isVisible: function() {
        return this._isVisible;
    },

    /**
     * @override
     */
    show: function() {
        if (this.isVisible()) {
            return;
        }
        if (!this.triggerBefore('show')) {
            return false;
        }
        if (!this.isReady()) {
            return;
        }

        this._isVisible = true;
        this.$('input').val('');
        this.$el.fadeToggle(50, 'linear', _.bind(this.focusInput, this));
        this.trigger('show');
        this.calculateResultsHeight();
        this._bindEvents();
    },

    /**
     * @override
     */
    hide: function() {
        if (!this.isVisible()) {
            return;
        }
        if (!this.triggerBefore('hide')) {
            return false;
        }

        this._isVisible = false;
        this._unbindEvents();
        this.$el.fadeToggle(50, 'linear');
        this.$el.removeClass('has-results');
        this.trigger('hide');
    },

    /**
     * Toggles the Sweet Spot.
     */
    toggle: function() {
        if (this.isVisible()) {
            this.hide();
        } else {
            this.show();
        }
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
        // TODO: This is bad and there should be an option in drawer.js to
        // prevent opening an already-open drawer of the same type.
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
        });
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
        },
        openConfig: function() {
            this.openConfigPanel();
        }
    },

    /**
     * Calculates the results dropdown height based on the window height and
     * triggers 'sweetspot:results:adjustMaxHeight' passing the value.
     */
    calculateResultsHeight: function() {
        var distanceToFooter = 80;
        var resultsMaxHeight = $(window).height() - this.$el.offset().top - $('footer').height() - distanceToFooter;
        if (resultsMaxHeight > 460) {
            resultsMaxHeight = 460;
        }
        this.trigger('sweetspot:results:adjustMaxHeight', resultsMaxHeight);
    },

    /**
     * @inheritDoc
     */
    unbind: function() {
        $(window).off('resize.sweetspot-' + this.cid);
        this._super('unbind');
    },

    /**
     * @inheritDoc
     */
    _dispose: function() {
        this._unbindEvents();
        this._super('_dispose');
    }
})
