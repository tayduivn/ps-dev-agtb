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
            var self = this;
            app.view.View.prototype.render.call(this);

            this.gTable = this.$el.find('#gridTable').dataTable();

            $('#gridTable tr').on( 'click', function() {
                if ( self.gTable.fnIsOpen(this) )  {
                    self.gTable.fnClose( this );
                } else {
                    self.gTable.fnOpen( this, formatAdditionalDetails(this), 'details' );
                }
            });
        },

        /**
         * Event Handler for if a node is selected in a tree-view
         *
         * @param params
         */
        handleTreeNodeSelect: function(params)
        {
            this.gTable.fnFilter( params.selected.id );
        },

        /**
         * Formats the additional details div when a user clicks a row in the grid
         *
         * @param dRow the row from the datagrid that user has clicked on
         * @return {String} html output to be shown to the user
         */
        formatAdditionalDetails : function( dRow )  {
            // grab reference to the datatable
            var dTable = this.gTable;
            // get row data from datatable
            var data = dTable.fnGetData( dRow );
            // grab column headings array
            var colHeadings = this.getColumnHeadings(dTable);

            // TEMPORARY PLACEHOLDER OUTPUT - inline CSS, no class
            // this will all be changed once we have a more firm requirement for what should display here
            var output = '<table cellpadding="5" cellspacing="0" border="0" style="margin: 10px 0px 10px 50px">';
            output += '<tr><td>' + colHeadings[0] + '</td><td>' + data[0] + '</td></tr>';
            output += '<tr><td>' + colHeadings[1] + '</td><td>' + data[1] + '</td></tr>';
            output += '<tr><td>' + colHeadings[2] + '</td><td>' + data[2] + '</td></tr>';
            output += '<tr><td>' + colHeadings[3] + '</td><td>' + data[3] + '</td></tr>';
            output += '<tr><td>' + colHeadings[4] + '</td><td>' + data[4] + '</td></tr>';
            output += '</table>';

            return output;
        },

        /**
         * Returns an array of column headings
         *
         * @param dTable datatable param so we can grab all the column headings from it
         * @param onlyVisible -OPTIONAL, defaults true- if we want to return only visible column headings or not
         * @return {Array} column heading title strings in an array ["heading","heading2"...]
         */
        getColumnHeadings : function( dTable , onlyVisible )  {
            // onlyVisible needs to default to true if it is not false
            if( onlyVisible !== false )  {
                onlyVisible = typeof onlyVisible !== 'undefined' ? onlyVisible : true;
            }

            var cols = dTable.fnSettings().aoColumns;

            var retColumns = [];

            for( var i in cols )  {
                if( onlyVisible )  {
                    if( cols[i].bVisible )  {
                        retColumns.push( cols[i].sTitle );
                    }
                } else {
                    retColumns.push( cols[i].sTitle );
                }
            }

            return retColumns;
        }

    });

})(SUGAR.App);