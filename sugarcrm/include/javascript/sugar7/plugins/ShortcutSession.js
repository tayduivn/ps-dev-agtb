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
(function (app) {
    app.events.on('app:init', function () {
        /**
         * Get the list of shortcuts that is allowed in this session.
         *
         * @private
         * @param {View.Layout} layout
         * @returns {Array}
         */
        var _createShortcutSession = function(layout) {
            var shortcutList = layout.options.meta.shortcuts || layout.shortcuts;
            if (!_.isEmpty(shortcutList)) {
                app.shortcuts.createSession(shortcutList, this);
            }
        };

        app.plugins.register('ShortcutSession', ['layout'], {
            /**
             * Create new shortcut session.
             */
            onAttach: function(layout) {
                this._bindShortcutEvents(layout);
            },

            /**
             * Binds events that this plugin uses.
             *
             * @param {View.Layout} layout
             */
            _bindShortcutEvents: function(layout) {
                layout.on('init', _.bind(_createShortcutSession, this, layout));
            }
        });
    });
})(SUGAR.App);
