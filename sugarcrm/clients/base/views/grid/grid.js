/**
 * View that displays header for current app
 * @class View.Views.GridView
 * @alias SUGAR.App.layout.GridView
 * @extends View.View
 */
({

    gTable:'',

    // boolean for enabled expandable row behavior
    isExpandableRows:'',

    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize:function (options) {
        //set expandable behavior to false by default
        this.isExpandableRows = false;

        app.view.View.prototype.initialize.call(this, options);

        var self = this;
        // listening for updates to context for selectedUser:change
        this.layout.context.on("selectedUser:change", function(selectedUserId){
            // filter grid
            self.filterGridById(selectedUserId);
        });
    },

    /**
     * Renders Grid view
     */
    render:function () {
        var self = this;
        app.view.View.prototype.render.call(this);

        this.gTable = this.$el.find('#gridTable').dataTable(
            {
                "bInfo":false,
                "bPaginate":false
            }
        );

        // if isExpandable, add expandable row behavior
        if (this.isExpandableRows) {
            $('#gridTable tr').on('click', function () {
                if (self.gTable.fnIsOpen(this)) {
                    self.gTable.fnClose(this);
                } else {
                    self.gTable.fnOpen(this, self.formatAdditionalDetails(this), 'details');
                }
            });
        }
    },

    /**
     * Event Handler for filtering the grid by an ID value
     *
     * @param params event data
     */
    filterGridById:function (params) {
        var id;
        // This part of the function needs to be able to handle
        // any configuration of data that comes in from params that might possibly need
        // to be able to filter the grid table.
        if (params.hasOwnProperty('selected') && params.selected.hasOwnProperty('id')) {
            // This configuration works for the jsTree treeview:node_select event
            id = params.selected.id;
        } else if (params.hasOwnProperty('id')) {
            id = params.id;
        }else {
            // no structure, just sending the id straight in
            id = params;
        }

        this.gTable.fnFilter(id);
    },

    /**
     * Formats the additional details div when a user clicks a row in the grid
     *
     * @param dRow the row from the datagrid that user has clicked on
     * @return {String} html output to be shown to the user
     */
    formatAdditionalDetails:function (dRow) {
        // grab reference to the datatable
        var dTable = this.gTable;
        // get row data from datatable
        var data = dTable.fnGetData(dRow);
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
    getColumnHeadings:function (dTable, onlyVisible) {
        // onlyVisible needs to default to true if it is not false
        if (onlyVisible !== false) {
            onlyVisible = typeof onlyVisible !== 'undefined' ? onlyVisible : true;
        }

        var cols = dTable.fnSettings().aoColumns;

        var retColumns = [];

        for (var i in cols) {
            if (onlyVisible) {
                if (cols[i].bVisible) {
                    retColumns.push(cols[i].sTitle);
                }
            } else {
                retColumns.push(cols[i].sTitle);
            }
        }

        return retColumns;
    }
})