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
    events: {
        'click': 'toggle'
    },

    /**
     * Store the current `open` or `close` state
     *
     * @type {String}
     */
    _state: 'open',

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        // FIXME these events should be listened on the `default` layout instead of the global context (SC-2398).
        app.controller.context.on('sidebar:state:respond sidebar:state:changed', this.toggleState, this);

        app.controller.context.trigger('sidebar:state:ask');
    },

    /**
     * Toggle the `open` or `close` class of the icon.
     *
     * @param {String} [state] The state. Possible values: `open` or `close`.
     */
    toggleState: function(state) {
        if (state !== 'open' && state !== 'close') {
            state = (this._state === 'open') ? 'close' : 'open';
        }
        this._state = state;
        if (!this.disposed) {
            this.render();
        }
    },

    /**
     * Toggle the sidebar.
     *
     * @param {Event} event The `click` event.
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
        app.controller.context.off(null, null, this);
    }
})
