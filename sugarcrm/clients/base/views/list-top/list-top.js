({
/**
 * Top view that displays a list of models pulled from the context's collection.
 * @class View.Views.ListViewTop
 * @alias SUGAR.App.layout.ListViewTop
 * @extends View.View
 */
    events: {
        'click [rel=tooltip]': 'fixTooltip',
        'click .search': 'showSearch'
    },
    _renderHtml: function() {
        if (app.acl.hasAccess('create', this.module)) {
            this.context.set('isCreateEnabled', true);
        }

        app.view.View.prototype._renderHtml.call(this);

    },
    fixTooltip: function() {
        console.log("click on a tooltip");
        this.$(".tooltip").hide();
    },
    showSearch: function() {
        // Toggle on search filter and off the pagination buttons
        this.$('.search').toggleClass('active');
        this.layout.trigger("list:search:toggle");
    }

})
