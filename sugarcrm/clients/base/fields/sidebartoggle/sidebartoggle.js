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
 * @class View.Fields.Base.SidebartoggleField
 * @alias SUGAR.App.view.fields.BaseSidebartoggleField
 * @extends View.Field
 */
/**
 * Some events have been deprecated in 7.2 and removed.
 * List of changes:
 *
 * - `sidebarRendered` has been removed. Now, when the field renders it calls
 *    {@link Layout.Default#isSidePaneVisible} directly to get the current
 *    state.
 *
 * - `toggleSidebar` has been removed. Triggers `sidebar:toggle` instead.
 *
 * - `toggleSidebarArrows` has been removed. Listens to `sidebar:state:changed`
 *    instead.
 *
 * - `openSidebarArrows` has been removed. Listens to `sidebar:state:changed`
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

        var defaultLayout = this.closestComponent('sidebar');
        if (defaultLayout && _.isFunction(defaultLayout.isSidePaneVisible)) {
            this.toggleState(defaultLayout.isSidePaneVisible() ? 'open' : 'close');
            this.listenTo(defaultLayout, 'sidebar:state:changed', this.toggleState);
        }
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
        var defaultLayout = this.closestComponent('sidebar');
        if (defaultLayout) {
            defaultLayout.trigger('sidebar:toggle');
        }
    }
})
