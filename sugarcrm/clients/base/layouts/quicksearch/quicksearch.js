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
/**
 * @class View.Views.Base.QuicksearchLayout
 * @alias SUGAR.App.view.views.BaseQuicksearchLayout
 * @extends View.View
 */
({
    className: 'navbar search',

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        /**
         * Key to indicate version 2 search (new global search). This is used by the component views to determine
         * what version of the API to use. Default is false.
         * @type {boolean}
         */
        this.v2 = this.meta.v2 || false;

        /**
         * Index of the focused component. Only the focused component should have keyboard listeners.
         * @type {number}
         */
        this.compOnFocusIndex = 0;

        // When a component is trying to navigate from its last element to the next component,
        // Check to make sure there is a next navigable component. If it exists, set it to the component to focus
        this.before('navigate:next:component', function() {
            var i = this.compOnFocusIndex, comp;
            while (comp = this._components[++i]) {
                if (_.result(comp, 'isFocusable')) {
                    this.compOnFocusIndex = i;
                    return true;
                }
            }
            return false;
        }, this);

        // When a component is trying to navigate from its first element to the previous component,
        // Check to make sure there is a previous navigable component. If it exists, set it to the component to focus
        this.before('navigate:previous:component', function() {
            var i = this.compOnFocusIndex, comp;
            while (comp = this._components[--i]) {
                if (_.result(comp, 'isFocusable')) {
                    this.compOnFocusIndex = i;
                    return true;
                }
            }
            return false;
        }, this);

        // Navigate to the next component. We have already set this.compOnFocusIndex in the before function.
        this.on('navigate:next:component', function() {
            this._components[this.compOnFocusIndex].trigger('navigate:focus:receive', true);
        }, this);

        // Navigate to the previous component. We have already set this.compOnFocusIndex in the previous function.
        this.on('navigate:previous:component', function() {
            this._components[this.compOnFocusIndex].trigger('navigate:focus:receive', false);
        }, this);

        // Navigate to a specific component. This bypasses the previous/next logic.
        this.on('navigate:to:component', function(componentName) {
            var newIndex = this.compOnFocusIndex;
            // Find the index of the component that is requesting focus.
            // We cannot use `layout.getComponent()` because that only returns the component, not the index.
            _.each(this._components, function(component, index) {
                if (componentName === component.name) {
                    newIndex = index;
                    return;
                }
            });
            // Unfocus the old component and focus on the new component.
            this._components[this.compOnFocusIndex].trigger('navigate:focus:lost');
            this.compOnFocusIndex = newIndex;
            this._components[this.compOnFocusIndex].trigger('navigate:focus:receive');
        }, this);

        // Reset navigation
        this.on('quicksearch:clear', function() {
            this.trigger('quicksearch:dropdown:close');
            this.removeFocus();
            app.router.off('route', null, this);
            $(document).off('click.globalsearch.data-api', '.navbar .search');
            $(document).off('click.globalsearch.data-api');
        }, this);

        // Open the quicksearch results
        var self = this;
        this.on('quicksearch:results:open', function() {
            // When we click away from the results, close the results
            $(document)
                .on('click.globalsearch.data-api', function() {
                    self.trigger('quicksearch:clear');
                })
                .on('click.globalsearch.data-api', '.navbar .search', function(e) { e.stopPropagation() });

            // When we navigate away from the current page, close the results.
            app.router.on('route', function() {
                self.trigger('quicksearch:clear');
            });
        });
    },

    /**
     * Hides the views in this template.
     * First calls `navigate:focus:lost` on the focused view. Then calls `quicksearch:results:close` on itself.
     * Finally, it resets the component on focus.
     */
    removeFocus: function() {
        this._components[this.compOnFocusIndex].trigger('navigate:focus:lost');
        this.compOnFocusIndex = 0;
    },

    /**
     * @inheritDoc
     */
    unbind: function() {
        $(document).off('click.globalsearch.data-api');
        app.router.off('route', null, this);
        this._super('unbind');
    }
})
