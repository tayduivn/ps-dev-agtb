(function(app) {

    /**
     * View that displays header for current app
     * @class View.Views.GridView
     * @alias SUGAR.App.layout.GridView
     * @extends View.View
     */
    app.view.views.GridView = app.view.View.extend({

        gTable: '',

        /**
         * Initialize the View
         *
         * @constructor
         * @param {Object} options
         */
        initialize: function(options){
            app.view.View.prototype.initialize.call(this, options);

            // add event listener for treeview:node_select so grid can be updated via a tree-view event
            app.events.on('treeview:node_select', this.handleTreeNodeSelect, this);
        },

        /**
         * Renders Grid view
         */
        render: function() {
            app.view.View.prototype.render.call(this);
            this.gTable = this.$el.find('#gridTable').dataTable();
        },

        /**
         * Event Handler for if a node is selected in a tree-view
         *
         * @param params
         */
        handleTreeNodeSelect: function(params)
        {
            this.gTable.fnFilter( params.selected.id );
        }


    });

})(SUGAR.App);