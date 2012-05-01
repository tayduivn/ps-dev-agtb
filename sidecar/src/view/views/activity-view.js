(function(app) {

    /**
     * View that displays a list of models pulled from the context's collection.
     * @class View.Views.ListView
     * @alias SUGAR.App.layout.ListView
     * @extends View.View
     */
    app.view.views.ActivityView = app.view.View.extend({
        events: {
            'click .search': 'showSearch',
            'click .addNoteClick': 'openNoteModal'
        },
        showSearch: function() {
            var searchEl = '.search';
            $(searchEl).toggleClass('active');
            $(searchEl).parent().parent().parent().find('.dataTables_filter').toggle();
            $(searchEl).parent().parent().parent().find('.form-search').toggleClass('hide');
            return false;
        },
        openNoteModal: function() {
            this.$el.find('#noteModal').modal('show');
            this.$el.find('li.open').removeClass('open');
            return false;
        }
    });

})(SUGAR.App);