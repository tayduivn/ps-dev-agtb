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
 * The layout for the Omnichannel console.
 *
 * @class View.Layouts.Base.OmnichannelConsoleLayout
 * @alias SUGAR.App.view.layouts.BaseOmnichannelConsoleLayout
 * @extends View.Layout
 */
({
    /**
     * Css class for this component.
     * @property {string}
     */
    className: 'omni-console',

    /**
     * Current state: 'opening', 'idle', 'closing', ''.
     * @property {string}
     */
    currentState: '',

    /**
     * Size of console with ccp only.
     * The ccp itself can be 200px to a maximum of 320px wide and 400px to 465px tall according to:
     * https://github.com/amazon-connect/amazon-connect-streams
     *
     * @property {Object}
     */
    ccpSize: {
        width: 300,
        height: 435
    },

    /**
     * Showing ccp only or all.
     * @property {boolean}
     */
    ccpOnly: true,

    /**
     * Event handlers.
     * @property {Object}
     */
    events: {
        'click [data-action=close]': 'close',
        'click [data-action=end-session]': 'toggleSession',
    },

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        $(window).on('resize.omniConsole', _.bind(this._resize, this));
        this._setSize(false);
    },

    /**
     * Open the console.
     */
    open: function() {
        // open the console if not yet
        if (!this.isOpen()) {
            this._setSize();
            this.currentState = 'opening';
            this.$el.show('slide', {direction: 'down'}, 300);
            this.currentState = 'idle';
            app.router.on('route', this.closeImmediately, this);
            $main = app.$contentEl.children().first();
            $main.on('drawer:add.omniConsole', _.bind(this.closeImmediately, this));
        }
    },

    /**
     * Tell if the console is opened.
     * @return {boolean} True if open, false if not.
     */
    isOpen: function() {
        return this.currentState !== '';
    },

    /**
     * Close the console immediately.
     */
    closeImmediately: function() {
        this.$el.hide();
        this.currentState = '';
        this._offEvents();
    },

    /**
     * Show/end session.
     */
    toggleSession: function() {
        if (this.isOpen()) {
            this.ccpOnly = !this.ccpOnly;
            this.$el.animate({
                'left': 0,
                'top': this._determineTop(),
                'right': this._determineRight(),
                'bottom': this._determineBottom()
            });
            this.$('[data-action=end-session]').toggle();
        }
    },

    /**
     * Close the console.
     */
    close: function() {
        if (this.isOpen()) {
            this.currentState = 'closing';
            this.$el.hide('slide', {direction: 'down'}, 300);
            this.currentState = '';
            this._offEvents();
        }
    },

    /**
     * Unsubscribe to events.
     * @private
     */
    _offEvents: function() {
        app.router.off('route', this.closeImmediately);
        $main = app.$contentEl.children().first();
        $main.off('drawer:add.omniConsole', this.closeImmediately);
    },

    /**
     * Calculate the right of the console.
     * @return {number}
     * @private
     */
    _determineRight: function() {
        if (this.ccpOnly) {
            return $(window).width() - this.ccpSize.width - 6;
        }
        return 0;
    },

    /**
     * Calculate the height of the console.
     * @return {number}
     * @private
     */
    _determineBottom: function() {
        return $('footer').outerHeight();
    },

    /**
     * Calculate the top of the console.
     * @return {number}
     * @private
     */
    _determineTop: function() {
        var headerHeight = $('#header .navbar').outerHeight();
        var footerHeight = $('footer').outerHeight();
        var windowHeight = $(window).height();
        if (this.ccpOnly) {
            return windowHeight - footerHeight - this.ccpSize.height;
        }
        return headerHeight;
    },

    /**
     * Set the size of the console.
     * @param {boolean} True if showing ccp only, false otherwise
     * @private
     */
    _setSize: function(ccpOnly) {
        if (!_.isUndefined(ccpOnly)) {
            this.ccpOnly = ccpOnly;
        }
        this.$el.css({
            'left': 0,
            'top': this._determineTop(),
            'right': this._determineRight(),
            'bottom': this._determineBottom()
        });
    },

    /**
     * Resize the console.
     * @private
     */
    _resize: _.throttle(function() {
        if (this.disposed) {
            return;
        }
        // resize the console if it is opened
        if (this.currentState === 'idle') {
            this._setSize();
        }
    }, 30),

    /**
     * @inheritdoc
     */
    _dispose: function() {
        $(window).off('resize.omniConsole');
        app.router.off('route', null, this);
        this._super('_dispose');
    }
})
