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
        },
        /**
         * Renders Header view
         */
        render: function() {
            app.view.View.prototype.render.call(this);
            app.events.on('treeview:node_select', this.handleTreeNodeSelect, this);
            this.gTable = this.$el.find('#gridTable').dataTable();
        },

        handleTreeNodeSelect: function(params)
        {
            console.log(params);

            sInput = 'seed_will_id seed_chris_id';
            var asSearch = sInput.split( ' ' );
            var sRegExpString = '^(?=.*?'+asSearch.join( ')(?=.*?' )+').*$';

            //console.log(sRegExpString);

            this.gTable.fnFilter( params.selected.id );
            //console.log(json_data);
        }


    });

})(SUGAR.App);