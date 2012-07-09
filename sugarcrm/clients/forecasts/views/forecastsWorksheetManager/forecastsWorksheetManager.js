/**
 * View that displays header for current app
 * @class View.Views.WorksheetView
 * @alias SUGAR.App.layout.WorksheetView
 * @extends View.View
 */
({
    show: false,
    showOpps: false,

    viewModule: {},
    selectedUser: {},

    gTable:'',

    // boolean for enabled expandable row behavior
    isExpandableRows:'',

    _collection:{},

    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize:function (options) {
        this.viewModule = app.viewModule;
        var self = this;
        //set expandable behavior to false by default
        this.isExpandableRows = false;
        this.category = 'Committed',
        
        app.view.View.prototype.initialize.call(this, options);
        this._collection = this.context.forecasts.worksheetmanager;
        
        //set up base selected user
    	this.selectedUser = {id: app.user.get('id'), "isManager":app.user.get('isManager'), "showOpps": false};
    },

    /**
     * Event Handler for updating the worksheet by a selected user
     *
     * @param params is always a context
     */
    updateWorksheetBySelectedUser:function (selectedUser) {
        this.selectedUser = selectedUser;
        if(!this.showMe()){
        	return false;
        }
        this._collection = this.context.forecasts.worksheetmanager;
        this._collection.url = this.createURL() + "?user_id=" + this.selectedUser.id;
        this._collection.fetch();
    },

    bindDataChange: function() {
        if(this._collection)
        {
            this._collection.on("reset", this.refresh, this);
        }
        // listening for updates to context for selectedUser:change
        if (this.context.forecasts) {
            this.context.forecasts.on("change:selectedUser",
                function(context, selectedUser) {
                    this.updateWorksheetBySelectedUser(selectedUser);
                }, this);
            this.context.forecasts.on("change:selectedTimePeriod",
                function(context, timePeriod) {
                    this.updateWorksheetBySelectedTimePeriod(timePeriod);
                }, this);
            this.context.forecasts.on("change:selectedCategory",
                function(context, category) {
                    this.updateWorksheetBySelectedCategory(category);
                },this);
            this.context.forecasts.on("change:renderedForecastFilter", function(context, defaultValues) {
                this.updateWorksheetBySelectedTimePeriod({id: defaultValues.timeperiod_id});
                this.updateWorksheetBySelectedCategory({id: defaultValues.category});
            }, this);
        }
    },
    
    /**
     * Refresh the view
     *
     * This method ensures that we refresh the view after data pulls
     * @param context
     */
    refresh:function(context) {
        
    	this.render();
    },
    
    /**
     * Renders view
     */
    render:function () {
        var self = this;
        
        if(!this.showMe()){
        	return false;
        }
        $("#view-sales-rep").hide();
        $("#view-manager").show();
        app.view.View.prototype.render.call(this);
        
        // parse metadata into columnDefs
        // so you can sort on the column's "name" prop from metadata
        var columnDefs = [];
        var fields = this.meta.panels[0].fields;

        for( var i = 0; i < fields.length; i++ )  {
            var name = fields[i].name;
            columnDefs.push( { "sName": name, "aTargets": [ i ] } );
        }

        this.gTable = this.$(".view-forecastsWorksheetManager").dataTable(
            {
                "aoColumnDefs": columnDefs,
                "bInfo":false,
                "bPaginate":false
            }
        );

        // if isExpandable, add expandable row behavior
        if (this.isExpandableRows) {
            $('.worksheetManagerTable tr').on('click', function () {
                if (self.gTable.fnIsOpen(this)) {
                    self.gTable.fnClose(this);
                } else {
                    self.gTable.fnOpen(this, self.formatAdditionalDetails(this), 'details');
                }
            });
        }
    },
    
    /**
     * Determines if this Worksheet should be rendered
     */
    showMe: function(){
    	var selectedUser = this.selectedUser;
    	this.show = false;
    	
    	if(!selectedUser.showOpps && selectedUser.isManager){
    		this.show = true;
    	}
    	
    	return this.show;
    },

    /**
     * Event Handler for updating the worksheet by a selected category
     *
     * @param params is always a context
     */
    updateWorksheetBySelectedCategory:function (params) {
        this.category = params.id;
        this.render();
    },

    /**
     * Event Handler for updating the worksheet by a timeperiod id
     *
     * @param params is always a context
     */
    updateWorksheetBySelectedTimePeriod:function (params) {
        var model = this.context.forecasts.worksheet;
        if(!this.showMe()){
        	return false;
        }
        model.url = app.config.serverUrl + "/Forecasts/worksheetmanager?timeperiod_id=" + params.id;
        model.fetch();
    },

    createURL:function()
    {
        var url = app.config.serverUrl + "/Forecasts/worksheetmanager";
        return url;
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

            var title = this.app.lang.get(cols[i].sTitle);

            if (onlyVisible) {
                if (cols[i].bVisible) {
                    retColumns.push(title);
                }
            } else {
                retColumns.push(title);
            }
        }

        return retColumns;
    },

    /***
     * Checks current gTable to see if a particular column name exists
     * @param columnName the column sName you're checking for.  NOT the Column sTitle/heading
     * @return {Boolean} true if it exists, false if not
     */
    hasColumn:function(columnName) {
        var containsColumnName = false;
        var cols = this.gTable.fnSettings().aoColumns;

        for (var i in cols) {
            if(cols[i].sName == columnName)  {
                containsColumnName = true;
                break;
            }
        }

        return containsColumnName;
    }
})
