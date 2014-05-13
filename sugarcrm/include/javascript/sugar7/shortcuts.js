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
(function(app) {

    /**
     * Provides framework to add shortcut keys to the application.
     *
     * Shortcuts are grouped into various scopes, which are predefined top-level layouts.
     * To enable shortcuts, 'shortcuts' => true must be added to the layout definition for the
     * top-level layout, which would then enable shortcuts just for that layout and any global
     * shortcuts.
     *
     * To register a shortcut for a particular field, view, or layout, add the following:
     * app.shortcuts.register('<top-level layout>', '<shortcut key>', <callback function>, <current component>);
     *
     * This framework is implemented on top of Mousetrap JS library (http://craig.is/killing/mice)
     *
     * Supported keys:
     * - all alphanumeric characters and symbols
     * - shift, ctrl, alt, option, meta, command
     * - backspace, tab, enter, return, capslock, esc, escape, space, pageup, pagedown, end, home, ins, del
     * - left, up, right, down
     *
     * Key combination: 'ctrl+s'
     * Multiple keys: ['ctrl+a','command+a']
     * Key sequence: 'f ctrl+a'
     */

    var _shortcuts = {}, //registered shortcut keys
        _savedShortCuts = []; //saved shortcut keys, which then can be restored.

    app.events.once('app:init', function() {
        app.before('app:view:change', function() {
            // clear shortcuts before any view change
            app.shortcuts.activate(app.shortcuts.SCOPE.GLOBAL);
            return true;
        });
    });

    app.shortcuts = {
        /**
         * All available shortcut keys scopes
         */
        SCOPE: {
            GLOBAL: 'global',
            RECORD: 'record',
            RECORDS: 'records',
            CREATE_ACTIONS: 'create-actions'
        },

        /**
         * Currently active scope
         */
        currentScope: {
            _scope: undefined,

            /**
             * Get current scope.
             * @returns {String}
             */
            get: function() {
                return this._scope;
            },

            /**
             * Set given scope as the current scope.
             * @param {String} scope
             */
            set: function(scope) {
                this._scope = scope;
            }
        },

        /**
         * Register shortcut keys for a particular scope.
         *
         * Note: If it registers the same key in the same scope twice, it only registers the first.
         * Note: Shortcut keys are only available for components inside main pane, unless they are globally scoped.
         *
         * @param {String} scope - name of scope to be registered to
         * @param {String|Array} key - a string or an array of strings of shortcut key combinations and sequences
         * @param {Function} func - callback function to be called
         * @param {View.Component} component - component that the shortcut keys are registered from
         */
        register: function(scope, key, func, component) {
            var self = this;

            if (!_shortcuts[scope]) {
                _shortcuts[scope] = {};
            }
            if (!_shortcuts[scope][key]) {
                _shortcuts[scope][key] = {};
            }

            if (((scope === this.currentScope.get()) || (scope === this.SCOPE.GLOBAL)) && _.isEmpty(_shortcuts[scope][key])) {
                _shortcuts[scope][key].func = func;
                _shortcuts[scope][key].component = component;

                this._bindShortcutKeys(scope, key, func, component);

                component.before('dispose', function() {
                    self.unregister(scope, key, component);
                    return true;
                });
            }
        },

        /**
         * Unregister shortcut keys for a particular scope.
         * @param {String} scope - name of scope to be unregistered from
         * @param {String|Array} key - a string or an array of strings of shortcut key combinations and sequences
         * @param {View.Component} component - component that the shortcut keys were registered from
         */
        unregister: function(scope, key, component) {
            if (_shortcuts[scope] && _shortcuts[scope][key] && (_shortcuts[scope][key].component === component)) {
                _shortcuts[scope][key] = {};
                Mousetrap.unbind(key);
            }
        },

        /**
         * Set the given scope as the currently active scope.
         * @param {String} scope
         */
        activate: function(scope) {
            this.clear();
            this.currentScope.set(scope);
        },

        /**
         * Remove all shortcuts except global shortcuts.
         */
        clear: function() {
            var globalShortcuts = _shortcuts[this.SCOPE.GLOBAL];
            _shortcuts = {};
            _shortcuts[this.SCOPE.GLOBAL] = globalShortcuts;

            this.currentScope.set();
            Mousetrap.reset();

            this._registerGlobalKeyBindings();
        },

        /**
         * Save the currently active shortcut.
         */
        save: function() {
            _savedShortCuts.push(_shortcuts[this.currentScope.get()]);
        },

        /**
         * Restore the last set of shortcuts.
         */
        restore: function() {
            var saved = _savedShortCuts.pop();
            _.each(saved, function(value, key) {
                this.register(this.currentScope.get(), key, value.func, value.component);
            }, this);
        },

        /**
         * Bind given function to keys, given a particular scope and component.
         * @param {String} scope
         * @param {String|Array} key
         * @param {Function} func
         * @param {View.Component} component
         * @private
         */
        _bindShortcutKeys: function(scope, key, func, component) {
            var self = this,
                wrapper = _.wrap(func, function(callback) {
                    var args = Array.prototype.slice.call(arguments, 1);
                    if (self._isComponentInMainPane(component) || (scope === self.SCOPE.GLOBAL)) {
                        callback.apply(component, args);
                    }
                });

            _shortcuts[scope][key].func = func;
            _shortcuts[scope][key].component = component;

            Mousetrap.bind(key, wrapper);
        },

        /**
         * Is the component in the main pane?
         * @param {View.Component} component
         * @returns {boolean}
         * @private
         */
        _isComponentInMainPane: function(component) {
            return (component.$el.closest('.main-pane').length > 0);
        },

        /**
         * Register all global shortcuts.
         * @private
         */
        _registerGlobalKeyBindings: function() {
            _.each(_shortcuts[this.SCOPE.GLOBAL], function(value, key) {
                this._bindShortcutKeys(this.SCOPE.GLOBAL, key, value.func, value.component);
            }, this);
        },

        /**
         * Do not use! Testing purposes only!
         * @private
         */
        _clearAll: function() {
            _shortcuts = {};
            _savedShortCuts = [];
            this.currentScope._scope = undefined;
            Mousetrap.reset();
        }
    };
})(SUGAR.App);
