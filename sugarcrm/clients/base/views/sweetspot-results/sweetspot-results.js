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

    /**
     * @inheritDoc
     *
     * - Listens to `sweetspot:results` on the layout to update the results.
     * - Listens to `keydown` on `window` to highlight an item.
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.layout.on('sweetspot:results', function(results) {
            this.results = this._formatResults(results);
            this.render();
        }, this);

        this.layout.on('sweetspot:status', this.toggleCallback, this);
    },

    /**
     * @inheritDoc
     */
    _render: function() {
        this._super('_render');
        this.$('li:first').addClass('hover');
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
            return results;
        }
        _.each(results, function(item) {
            if (!item.label) {
                item.label = item.name.substr(0, 2);
            }
        });
        return results;
    },

    toggleCallback: function(isOpen) {
        if (isOpen) {
            $(window).on('keydown.' + this.cid, _.bind(this.focus, this));
        } else {
            $(window).off('keydown.' + this.cid);
        }
    },

    /**
     * Highlights an item in the list.
     */
    focus: function(e) {
        var $li = this.$('li.hover');
        // enter?
        if (e.keyCode == 13) {
            this.layout[this.$('li.hover').data('action')];
            this.layout.toggle();
            if (this.$('li.hover').data('route')) {
                app.router.navigate(this.$('li.hover').data('route'), {trigger: true});
            }
            var action = this.$('li.hover').data('callback');
            this.layout.triggerSystemAction(action);
            return;
        }
        $li.removeClass('hover')
        var $next;

        // up arrow?
        if (e.keyCode == 40) {
            $next = $li.next();
            if ($next.length === 0) {
                $next = this.$('li:first');
            }
            $next.addClass('hover');
            e.preventDefault();
        }

        // down arrow?
        if (e.keyCode == 38) {
            $next = $li.prev();
            if ($next.length === 0) {
                $next = this.$('li:last');
            }
            $next.addClass('hover');
            e.preventDefault();
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
