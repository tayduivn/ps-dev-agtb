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
(function(app) {
    app.events.on('app:init', function() {
        /**
         * Adds the ability to hide the dropdown menu
         * when the mouse is clicked on bwc elements.
         */
        app.plugins.register('Dropdown', ['layout', 'view'], {
            onAttach: function(component, plugin) {
                this.on('init', function() {
                    app.events.on('app:view:change', this.bindIframeListener, this);
                    app.routing.before('route', this.closeDropdown, this, true);
                });
            },

            /**
             * Bind the mouse click listener on bwc iframe elements.
             */
            bindIframeListener: function() {
                var view = app.controller.layout.getComponent('bwc'),
                    self = this;
                if (!view) {
                    return;
                }

                view.$el.load(function() {
                    $(this.contentWindow.document).children('html')
                        .on('click.dropdown', _.bind(self.closeDropdown, self));
                });
            },

            /**
             * Close the dropdown menu.
             */
            closeDropdown: function() {
                this.$('.dropdown-menu').trigger('click.bs.dropdown');
            },

            /**
             * Detach the event handlers for closing dropdown menu.
             */
            unbindBeforeHandler: function() {
                app.routing.offBefore('route', this.closeDropdown, this);
            },

            /**
             * {@inheritDoc}
             * Unbind beforeHandlers.
             */
            onDetach: function() {
                var view = app.controller.layout && app.controller.layout.getComponent('bwc');
                if (view) {
                    $(view.$el.contentWindow.document).children('html').off('click.dropdown');
                }
                app.events.off('app:view:change', null, this);
                this.unbindBeforeHandler();
            }

        });
    });
})(SUGAR.App);
