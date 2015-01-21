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
 * @class View.Views.Base.SpotlightResultsView
 * @alias SUGAR.App.view.views.BaseSpotlightResultsView
 * @extends View.View
 */
({
    className: 'spotlight-results',
    tagName: 'ul',

    /**
     * @inheritDoc
     *
     * - Listens to `spotlight:results` on the layout to update the results.
     * - Listens to `keydown` on `window` to highlight an item.
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.layout.on('spotlight:results', function(results) {
            this.results = results;
            this.render();
        }, this);

        $(window).on('keydown.'+this.cid, _.bind(this.focus, this));
    },

    /**
     * @inheritDoc
     */
    _render: function() {
        this._super('_render');
        this.$('li:first').addClass('hover');
    },

    /**
     * Highlights an item in the list.
     */
    focus: function(e) {
        var $li = this.$('li.hover');
        // enter?
        if (e.keyCode == 13) {
            app.router.navigate(this.$('li.hover').data('route'), {trigger: true});
            this.layout.toggle();
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
        $(window).off('keydown.'+this.cid);
        this._super('_dispose');
    }
})
