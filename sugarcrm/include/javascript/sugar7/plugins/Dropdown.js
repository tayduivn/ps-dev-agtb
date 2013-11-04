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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
(function(app) {
    app.events.on('app:init', function() {
        /**
         * Adds the ability to hide the dropdown menu
         * when the mouse is clicked on bwc elements.
         */
        app.plugins.register('Dropdown', ['view'], {
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
