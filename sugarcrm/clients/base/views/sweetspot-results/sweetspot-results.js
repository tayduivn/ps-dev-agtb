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
 * @class View.Views.Base.SweetspotResultsView
 * @alias SUGAR.App.view.views.BaseSweetspotResultsView
 * @extends View.View
 */
({
    className: 'sweetspot-results',
    tagName: 'ul',

    events: {
        'click a[href]': 'triggerHide'
    },

    /**
     * @inheritDoc
     *
     * - Listens to `sweetspot:results` on the layout to update the results.
     * - Listens to `keydown` on `window` to highlight an item.
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        /**
         * The list of results returned by Sweet Spot, split by category.
         *
         * @type {Array}
         */
        this.results = [];
        this.records = [];
        this.actions = [];
        this.keywords = [];

        /**
         * Stores the index of the currently highlighted list element.
         * This is used for keyboard navigation.
         *
         * @property {number}
         */
        this.activeIndex = null;

        this.layout.on('sweetspot:results', function(results) {
            this.actions = this._formatResults(results.actions);
            this.records = this._formatResults(results.records);
            this.keywords = this._formatResults(results.keywords);
            this.results = this.keywords.concat(this.actions).concat(this.records);
            this.showMore = results.showMore;
            this.term = results.term;
            this.render();
        }, this);


        this.layout.on('show', function() {
            this.results = this.actions = this.records = this.keywords = [];
            $(window).on('keydown.' + this.cid, _.bind(this.keydownHandler, this));
            this.render();
        }, this);

        this.layout.on('hide', function() {
            $(window).off('keydown.' + this.cid);
        }, this);
    },

    /**
     * @inheritDoc
     */
    _render: function() {
        this._super('_render');
        this.activeIndex = 0;
        if (this.results.length) {
            this._highlightActive();
        }
    },

    /**
     * Formats the {@link #results} to:
     * -include labels if none are present by default.
     *
     * @param {Array} results The list of actions/commands.
     * @return {Array} The formatted list of actions/commands.
     */
    _formatResults: function(results) {
        if (_.isEmpty(results)) {
            return [];
        }
        _.each(results, function(item) {
            if (!item.label) {
                item.label = item.name.substr(0, 2);
            }
        });
        return results;
    },

    /**
     * Handle the keydown events.
     * @param {event} e The `keydown` event.
     */
    keydownHandler: function(e) {
        switch (e.keyCode) {
            case 13: // enter
                this.triggerAction();
                break;
            case 40: // down arrow
                this.moveForward();
                e.preventDefault();
                break;
            case 38: // up arrow
                this.moveBackward();
                e.preventDefault();
                break;
        }
    },

    triggerHide: function() {
        this.layout.hide();
    },

    /**
     * Triggers the action linked to the active element.
     *
     * Navigates to the view or calls the callback method.
     */
    triggerAction: function() {
        this.triggerHide();
        var route = this.$('.active > a').attr('href');
        if (route) {
            app.router.navigate(route, {trigger: true});
        }
        var action = this.$('a.hover').data('callback');
        if (action) {
            this.layout.triggerSystemAction(action);
        }
    },

    /**
     * Highlight the active element and unhighlight the rest of the elements.
     */
    _highlightActive: function() {
        this.$('.active').removeClass('active');
        var nth = this.activeIndex;
        var $active = this.$('[data-sweetaction="true"]:nth(' + nth + ')');
        $active.addClass('active');
        $active.find('a').focus();
        this.$el.prev().find('input').focus();
    },

    /**
     * Moves to the next the active element.
     */
    moveForward: function() {
        // check to make sure we will be in bounds.
        this.activeIndex++;
        var upperBound = this.showMore ? this.results.length + 1 : this.results.length;
        if (this.activeIndex < upperBound) {
            // We're in bounds, just go to the next element in this view.
            this._highlightActive();
        } else {
            this.activeIndex = 0;
            this._highlightActive();

        }
    },

    /**
     * Moves to the previous the active element.
     */
    moveBackward: function() {
        // check to make sure we will be in bounds.
        if (this.activeIndex > 0) {
            // We're in bounds, just go to the previous element in this view
            this.activeIndex--;
            this._highlightActive();
        } else {
            var lastIndex = this.showMore ? this.results.length : this.results.length - 1;
            this.activeIndex = lastIndex;
            this._highlightActive();
        }
    },

    /**
     * @inheritDoc
     */
    _dispose: function() {
        $(window).off('keydown.' + this.cid);
        this._super('_dispose');
    }
})
