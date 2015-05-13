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
 * Headerpane view for the {@link View.Layouts.Base.SearchLayout
 * Search layout}.
 *
 * @class View.Views.Base.SearchHeaderpaneView
 * @alias SUGAR.App.view.views.BaseSearchHeaderpaneView
 * @extends View.Views.Base.HeaderpaneView
 */
({
    extendsFrom: 'HeaderpaneView',

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.context.on('change:searchTerm change:tagParams', function(model, value) {
            if (_.isString(value) && value) {
                this.searchTerm = value;
            } else {
                var tagParams = this.context.get('tagParams') || [];
                this.searchTerm = tagParams.join(', ');
            }
            this.render();
        }, this);

        // use the searchTerm for the title of search, unless we are doing a tag related search
        // then we use the tag name
        var tagParams = this.context.get('tagParams') || [];
        this.searchTerm = this.context.get('searchTerm') || tagParams.join(', ');
    },

    /**
     * Formats the title passing the search term.
     *
     * @override
     */
    _formatTitle: function(title) {
        if (!title) {
            return '';
        }
        return app.lang.get(title, this.module, {
            searchTerm: new Handlebars.SafeString(this.searchTerm)
        });
    },

    /**
     * @inheritDoc
     */
    unbind: function() {
        this._super('unbind');
        $(window).off('resize.searchheader');
        this.layout.off('headerpane:adjust_fields', this.adjustTitle);
    }
})
