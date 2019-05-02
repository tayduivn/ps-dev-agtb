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
 * @class View.Layouts.Base.SideDrawerLayout
 * @alias SUGAR.App.view.layouts.BaseSideDrawerLayout
 * @extends View.Layout
 */
({
    /**
     * @inheritdoc
     */
    className: 'side-drawer',

    /**
     * Function to be called once drawer is closed.
     * @property {Function}
     */
    onCloseCallback: null,

    /**
     * Current drawer state: 'opening', 'idle', 'closing', ''.
     * @property {string}
     */
    currentState: '',

    /**
     * Drawer configs.
     * @property {Object}
     */
    drawerConfigs: {
        // pixels between drawer's top and nav bar's bottom
        topPixels: 0,
        // pixels between drawer's bottom and footer's top
        bottomPixels: 0,
        // drawer's right in pixel or percentage
        right: 0,
        // drawer's left in pixel or percentage
        left: '25%',
    },

    /**
     * Main content of the App.
     * @property {Object}
     */
    $main: null,

    /**
     * @inheritdoc
     */
    events: {
        'click [data-action=close]': 'close'
    },

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.$main = app.$contentEl.children().first();
        this.$main.on('drawer:add', _.bind(this.toggle, this));
        this.$main.on('drawer:remove', _.bind(this.toggle, this));
        $(window).on('resize.sidedrawer', _.bind(this._resizeDrawer, this));
    },

    /**
     * Config the drawer.
     * @param {Object} [configs={}] Drawer configs.
     */
    config: function(configs) {
        this.drawerConfigs = _.extend({}, this.drawerConfigs, configs);
        this.$el.css('top', $('#header .navbar').outerHeight() + this.drawerConfigs.topPixels);
        this.$el.css('height', this._determineDrawerHeight());
        this.$el.css('left', this.drawerConfigs.left);
        this.$el.css('right', this.drawerConfigs.right);
    },

    /**
     * Open the specified layout or view in this drawer.
     *
     * You can pass the current context if you want the context created to be a
     * child of that current context. If you don't pass a `scope`, it will
     * create a child of the main context (`app.controller.context`).
     *
     * @param {Object} def The component definition.
     * @param {Function} onClose Callback method when the drawer closes.
     */
    open: function(def, onClose) {
        // open the drawer if not yet
        if (!this.isOpen()) {
            this.currentState = 'opening';
            this.$el.show('slide', {direction: 'left'}, 800);
            this.currentState = 'idle';
        }

        // store the callback function to be called later
        this.onCloseCallback = onClose;

        // remove old content
        if (this._components.length) {
            _.each(this._components, function(component) {
                component.dispose();
            }, this);
            this._components = [];
        }

        // initialize content definition components
        this._initializeComponentsFromDefinition(def);

        var component = _.last(this._components);
        if (component) {
            // load and render new content in drawer
            component.loadData();
            component.render();
        }
    },

    /**
     * Tell if the drawer is opened.
     * @return {boolean} True if open, false if not.
     */
    isOpen: function() {
        return this.currentState !== '';
    },

    /**
     * Show/hide the drawer.
     */
    toggle: function() {
        if (this.isOpen()) {
            this.$el.toggle();
        }
    },

    /**
     * Close the drawer.
     */
    close: function() {
        this.currentState = 'closing';
        this.$el.hide();
        this.currentState = '';

        // remove drawer content
        _.each(this._components, function(component) {
            component.dispose();
        }, this);
        this._components = [];

        // execute callback
        var callbackArgs = Array.prototype.slice.call(arguments, 0);
        if (this.onCloseCallback) {
            this.onCloseCallback.apply(window, callbackArgs);
        }
    },

    /**
     * Force to create a new context and create components from the layout/view
     * definition. If the parent context is defined, make the new context as a
     * child of the parent context.
     *
     * NOTE: this function is copied from drawer.js to have consistent behavior
     *
     * @param {Object} def The layout or view definition.
     * @private
     */
    _initializeComponentsFromDefinition: function(def) {
        var parentContext;

        if (_.isUndefined(def.context)) {
            def.context = {};
        }

        if (_.isUndefined(def.context.forceNew)) {
            def.context.forceNew = true;
        }

        if (!(def.context instanceof app.Context) && def.context.parent instanceof app.Context) {
            parentContext = def.context.parent;
            // remove the `parent` property to not mess up with the context attributes.
            delete def.context.parent;
        }

        this.initComponents([def], parentContext);
    },

    /**
     * Calculate the height of the drawer
     * @return {number}
     * @private
     */
    _determineDrawerHeight: function() {
        var windowHeight = $(window).height();
        var headerHeight = $('#header .navbar').outerHeight() + this.drawerConfigs.topPixels;
        var footerHeight = $('footer').outerHeight() + this.drawerConfigs.bottomPixels;

        return windowHeight - headerHeight - footerHeight;
    },

    /**
     * Resize the height of the drawer.
     * @private
     */
    _resizeDrawer: _.throttle(function() {
        // resize the drawer if it is opened.
        if (this.currentState === 'idle') {
            var drawerHeight = this._determineDrawerHeight();
            this.$el.css('height', drawerHeight);
        }
    }, 300),

    /**
     * @override
     */
    _placeComponent: function(component) {
        if (this.disposed) {
            return;
        }
        this.$el.find('.drawer-content').append(component.el);
    },

    /**
     * @inheritdoc
     */
    _dispose: function() {
        this.$main.off('drawer:add', this.toggle);
        this.$main.off('drawer:remove', this.toggle);
        $(window).off('resize.sidedrawer');
        this._super('_dispose');
    },
})
