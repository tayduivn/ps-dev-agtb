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
     * To register a shortcut for a particular field, view, or layout, add the
     * following on render:
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

    app.events.once('app:init', function() {
        app.before('app:view:change', function() {
            // clear shortcuts before any view change
            app.shortcuts.activate(app.shortcuts.SCOPE.GLOBAL);
            return true;
        });
    });

    app.shortcuts = {
        _keys: {}, //the scope to which each key is attached
        _shortcuts: {}, //registered shortcut keys
        _savedShortCuts: [], //saved shortcut keys, which then can be restored.

        /**
         * All available shortcut keys scopes
         */
        SCOPE: {
            GLOBAL: 'global',
            RECORD: 'record',
            LIST: 'list',
            CREATE: 'create'
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
         * Note: In the event of key conflicts, only the last registration will
         * be kept.
         * Note: Shortcut keys are only available for components inside main pane, unless they are globally scoped.
         *
         * @param {String} scope - name of scope to be registered to
         * @param {String|Array} key - a string or an array of strings of shortcut key combinations and sequences
         * @param {Function} func - callback function to be called
         * @param {View.Component} component - component that the shortcut keys are registered from
         */
        register: function(scope, key, func, component) {
            var self = this;

            if (scope !== this.SCOPE.GLOBAL &&
                (scope !== this.currentScope.get() ||
                    !this._isComponentInMainPane(component))
            ) {
                return;
            }

            if (!_.isArray(key)) {
                key = [key];
            }

            _.each(key, function(k) {
                this._removeKeyConflicts(k);
                this._bindShortcutKeys(scope, k, func, component);
            }, this);

            component._dispose = _.wrap(component._dispose, function(func) {
                self.unregister(key, component);
                func.call(component);
            });
        },

        /**
         * Unregister shortcut keys for a particular scope.
         * @param {String} scope - name of scope to be unregistered from
         * @param {String|Array} key - a string or an array of strings of shortcut key combinations and sequences
         * @param {View.Component} component - component that the shortcut keys were registered from
         */
        unregister: function(scope, key, component) {
            if (!_.isArray(key)) {
                key = [key];
            }

            _.each(key, function(k) {
                if (this._shortcuts[scope] &&
                    this._shortcuts[scope][k] &&
                    this._shortcuts[scope][k].component === component
                ) {
                    this._shortcuts[scope][k] = {};
                    this._keys[k] = undefined;
                    Mousetrap.unbind(k);
                }
            }, this);
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
            var globalShortcuts = this._shortcuts[this.SCOPE.GLOBAL];

            this._keys = {};
            this._shortcuts = {};
            this._shortcuts[this.SCOPE.GLOBAL] = globalShortcuts;

            this.currentScope.set();
            Mousetrap.reset();

            this._registerGlobalKeyBindings();
        },

        /**
         * Save the currently active shortcut.
         */
        save: function() {
            this._savedShortCuts.push({
                scope: this.currentScope.get(),
                shortcuts: this._shortcuts[this.currentScope.get()]
            });
        },

        /**
         * Restore the last set of shortcuts.
         */
        restore: function() {
            var saved = this._savedShortCuts.pop();
            this.activate(saved.scope);
            _.each(saved.shortcuts, function(value, key) {
                this.register(saved.scope, key, value.func, value.component);
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
            var wrapper = _.wrap(func, function(callback) {
                var args = Array.prototype.slice.call(arguments, 1);
                callback.apply(component, args);
                return false;
            });

            if (!this._shortcuts[scope]) {
                this._shortcuts[scope] = {};
            }

            if (!this._shortcuts[scope][key]) {
                this._shortcuts[scope][key] = {};
            }

            this._keys[key] = scope;
            this._shortcuts[scope][key].func = func;
            this._shortcuts[scope][key].component = component;

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
            _.each(this._shortcuts[this.SCOPE.GLOBAL], function(value, key) {
                this._bindShortcutKeys(this.SCOPE.GLOBAL, key, value.func, value.component);
            }, this);
        },

        /**
         * Unregisters any shortcuts that are bound to the specified key.
         * @param {String} key
         * @private
         */
        _removeKeyConflicts: function(key) {
            var scope = this._keys[key] || null;

            if (scope &&
                this._shortcuts[scope] &&
                !_.isEmpty(this._shortcuts[scope][key])
            ) {
                this.unregister(
                    scope,
                    key,
                    this._shortcuts[scope][key].component
                );
            }
        }
    };
})(SUGAR.App);
