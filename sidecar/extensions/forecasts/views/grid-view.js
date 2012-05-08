(function(app) {

    /**
     * View that displays header for current app
     * @class View.Views.GridView
     * @alias SUGAR.App.layout.GridView
     * @extends View.View
     */
    app.view.views.GridView = app.view.View.extend({

        gTable: '',

        initialize: function(options){
            app.view.View.prototype.initialize.call(this, options);
            app.events.on('treeview:node_select', this.handleTreeNodeSelect, this);
        },
        /**
         * Renders Grid view
         */
        render: function() {
            app.view.View.prototype.render.call(this);
            this.gTable = this.$el.find('#gridTable').dataTable();
        },

        handleTreeNodeSelect: function(params)
        {
            this.gTable.fnFilter( params.selected.id );
        }


    });

})(SUGAR.App);