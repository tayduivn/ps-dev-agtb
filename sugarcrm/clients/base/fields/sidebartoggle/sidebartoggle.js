/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

/**
 * Some events have been deprecated in 7.2 and removed.
 * List of changes:
 *
 * - `sidebarRendered` has been removed. Now, when the field renders it triggers
 *    an event `sidebar:state:ask` to ask for the state, and listens to
 *    `sidebar:state:respond` for the response.
 *    {@link Field.Sidebartoggle#toggleState}
 *
 * - `toggleSidebar` has been removed. Triggers `sidebar:toggle` instead.
 *
 * - `toggleSidebarArrows` has been removed. Listens to `sidebar:state:respond`
 *    instead.
 *
 * - `openSidebarArrows` has been removed. Listens to `sidebar:state:send`
 *    instead.
 *
 * - The app event `app:toggle:sidebar` has been removed. Listen to
 *   `sidebar:state:changed` instead.
 */
({
    extendsFrom: 'button',

    events: {
        // The event is on the anchor to and not on the icon to ensure
        // `hit area` is big enough.
        'click .drawerTrig': 'toggle'
    },

    /**
     * The selector for the element that carries the `open` or `close` classes.
     *
     * @type {String}
     */
    _chevron: '.drawerTrig i',

    /**
     * The `icon` classes that carries the button when `open` or `close`.
     *
     * @type {Object}
     */
    _classes: {
        close: 'icon-double-angle-left',
        open: 'icon-double-angle-right'
    },

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        // FIXME these events should be listened on the `default` layout instead of the global context (SC-2398).
        app.controller.context.on('sidebar:state:respond', this.toggleState, this);
        app.controller.context.on('sidebar:state:changed', this.toggleState, this);

        app.controller.context.trigger('sidebar:state:ask');
    },

    /**
     * Toggle the `open` or `close` class of the icon.
     *
     * @param {String} [state] The state. Possible values: `open` or `close`.
     */
    toggleState: function(state) {
        if (state !== 'open' && state !== 'close') {
            var current = this.$(this._chevron).hasClass(this._classes.open);
            state = current ? 'close' : 'open';
        }
        this.updateArrowsWithDirection(state);
    },

    /**
     * Toggle the `open` or `close` class of the icon.
     *
     * @deprecated 7.2 and will be removed on 7.5. Use
     *  {@link Field.Sidebartoggle#toggleState} by triggering `sidebar:toggle`
     *  instead.
     */
    updateArrows: function() {
        app.logger.warn('Field.Sidebartoggle#updateArrows was called and is deprecated. ' +
            'The event "toggleSidebarArrows" is deprecated. ' +
            'Please update your code to trigger "sidebar:state:respond" instead');
        this.toggleState();
    },

    /**
     * Set the `open` state.
     *
     * @deprecated 7.2 and will be removed on 7.5. Use
     *  {@link Field.Sidebartoggle#toggleState} by triggering `sidebar:toggle`
     *  instead.
     */
    sidebarArrowsOpen: function() {
        app.logger.warn('Field.Sidebartoggle#sidebarArrowsOpen was called and is deprecated. ' +
            'The event "openSidebarArrows" is deprecated. ' +
            'Please update your code to trigger "sidebar:state:respond" instead.');
        this.toggleState('open');
    },


    /**
     * Update the icon class to `open` or `close` state.
     *
     * @param {String} state The state. Possible values : `open` or `close`.
     */
    updateArrowsWithDirection: function(state) {
        var $chevron = this.$(this._chevron);
        if (state === 'open') {
            $chevron.removeClass(this._classes.close).addClass(this._classes.open);
        } else if (state === 'close') {
            $chevron.removeClass(this._classes.open).addClass(this._classes.close);
        } else {
            app.logger.warn('updateArrowsWithDirection called with invalid state; ' +
                'should be "open" or "close", but was: ' + state);
        }
    },

    /**
     * Toggle the sidebar.
     *
     * @param {Event} The `click` event.
     */
    toggle: function(event) {
        // FIXME this should be triggered on the `default` layout instead of the global context (SC-2398).
        app.controller.context.trigger('sidebar:toggle');
    },

    /**
     * @inheritDoc
     */
    unbind: function() {
        this._super('unbind');
        // FIXME the events should be happening on the `default` layout instead of the global context (SC-2398).
        app.controller.context.off(null, null, this);//remove all events for context `this`
    }
})
