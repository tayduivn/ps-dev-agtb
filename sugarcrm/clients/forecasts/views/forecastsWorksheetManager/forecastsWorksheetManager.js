/**
 * View that displays header for current app
 * @class View.Views.WorksheetView
 * @alias SUGAR.App.layout.WorksheetView
 * @extends View.View
 */
({

    _name_type_map: {
//        best_case_worksheet: 'int',
//        likely_case_worksheet: 'int',
//        probability: 'percent',
        sales_stage: 'enum'
    },
    show: false,

    viewModule: {},

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
        this._collection = this.context.forecasts.worksheet;

        // listening for updates to context for selectedUser:change
        this.layout.context.on("change:selectedUser", function(context, selectedUser) { this.updateWorksheetBySelectedUser(selectedUser); }, this);
        this.layout.context.on("change:selectedTimePeriod", function(context, timePeriod) { self.updateWorksheetBySelectedTimePeriod(timePeriod); });
        this.layout.context.on("change:selectedCategory", function(context, category) { self.updateWorksheetBySelectedCategory(category); });

        //TEMP FUNCTIONALITY, WILL BE HANDLED DIFFERENTLY SOON
        this.layout.context.on("change:showManagerOpportunities", function(context, showManagerOpportunities) { self.showManagerOpportunities = showManagerOpportunities;} );
    },


    /**
     * Initializes clickToEdit field types (chosen, datepicker, etc...)
     * @private
     */
    _initCTETypes: function() {
        $.editable.addInputType('enum', {
            element: function(settings, original) {
//                debugger;
                var selEl = $('<select class="cteSelect">');
                _.each(app.lang.getAppListStrings(settings.field.def.options), function (value, key) {
                    var option = $("<option>").val(key).append(value);
                    selEl.append(option);
                });
                $(this).append(selEl);
                var hidden = $('<input type="hidden">');
                $(this).append(hidden);
                return(hidden);
            },

            /**
             * sets up and attaches the chosen plugin for this type.
             * @param settings
             * @param original
             */
            plugin: function(settings, original) {
                var self = this;
                self.passedSettings = settings;
                self.passedOriginal = original;
                $("select", this).filter(".cteSelect").chosen().change(self, function(e){
//                    debugger;
//                    e.data.submit(e.data.passedSettings, e.data.passedOriginal);
                    e.data.passedSettings.field.$el.html($(this).val());
                });
            },

            /**
             * process value from chosen for submittal
             * @param settings
             * @param original
             */
            submit: function(settings, original) {
//                debugger;
                $("input", this).val($("select", this).filter(".cteSelect").val());
            }
        });
    },

    /**
     * Renders the field as clickToEditable
     * @private
     */
    _renderClickToEditField: function(field) {
        this._initCTETypes();
        var outerElement = this.$("span[sfuuid='" + field.sfId + "']");

        outerElement.editable(function(value, settings){return value;},
            {
                type: field.def.type || this._name_type_map[field.name] || 'text',
                select: true,
                field: field,
                callback: function(value, settings) {
                    try{
                        settings.field.model.save(settings.field.name, value);
                    } catch (e) {
                        app.logger.error('Unable to save model in forecastsWorksheet.js: _renderClickToEditField - ' + e);
                    }
                    return value;
                }
            }
        );
    },
    
    /**
     * Event Handler for updating the worksheet by a selected user
     *
     * @param params is always a context
     */
    updateWorksheetBySelectedUser:function (selectedUser) {
        this.selectedUser = selectedUser.id;
        if(!this.showMe()){
        	return false;
        }
        this._collection = this.context.forecasts.worksheetmanager;
        this._collection.url = this.createURL();
        this._collection.fetch();
        this.render();
    },

    /**
     * Renders a field.
     *
     * This method sets field's view element and invokes render on the given field.  If clickToEdit is set to true
     * in metadata, it will also render it as clickToEditable.
     * @param {View.Field} field The field to render
     * @protected
     */
    _renderField: function(field) {
        app.view.View.prototype._renderField.call(this, field);
        if (field.viewName !="edit" && field.def.clickToEdit){
            this._renderClickToEditField(field);
        }
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
        /*
        // parse metadata into columnDefs
        // so you can sort on the column's "name" prop from metadata
        var columnDefs = [];
        var fields = this.meta.panels[0].fields;
        for( var i = 0; i < fields.length; i++ )  {
            columnDefs.push( { "sName": fields[i].name, "aTargets": [ i ] } );
        }

        this.gTable = this.$('.worksheetTable').dataTable(
            {
                "aoColumnDefs": columnDefs,
                "bInfo":false,
                "bPaginate":false
            }
        );

        // if isExpandable, add expandable row behavior
        if (this.isExpandableRows) {
            $('.worksheetTable tr').on('click', function () {
                if (self.gTable.fnIsOpen(this)) {
                    self.gTable.fnClose(this);
                } else {
                    self.gTable.fnOpen(this, self.formatAdditionalDetails(this), 'details');
                }
            });
        }

        //Only filter for forecast == 1 or forecast == -1 and probability >= 70 if searching for Committed
        if(this.category == 'Committed')
        {
            $.fn.dataTableExt.afnFiltering.push(
                function(oSettings, aData, iDataIndex)
                {
                    var forecast = parseInt($(aData[0]).html());
                    var probability = parseInt($(aData[4]).html());
                    return (forecast === 1 || (forecast === -1 && probability >= 70));
                }
            );
        } else {
            $.fn.dataTableExt.afnFiltering = [];
        }*/

    },
    
    /**
     * Determines if this Worksheet should be rendered
     */
    showMe: function(){
    	var isManager = app.user.get('isManager');
    	var userId = app.user.get('id');
    	var selectedUser = userId;
    	this.show = false;
    	if(this.selectedUser){
    		selectedUser = this.selectedUser;
    	}
    	
    	if(isManager && userId.localeCompare(selectedUser) == 0){
    		this.show = true;
    	}
    
    	return this.show;
    },


    /***
     * TEMPORARY FUNCTION just to show flag toggle in console
     */
    updateWorksheetByMgrOpps: function(params){
        var model = this.context.forecasts.worksheet;
        model.url = app.config.serverUrl + "/Forecasts/worksheetmanager?timeperiod_id=" + params.id;
        this.render();
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
        this.render();
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
