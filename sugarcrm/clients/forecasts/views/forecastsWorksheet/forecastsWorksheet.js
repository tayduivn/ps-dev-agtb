/**
 * View that displays header for current app
 * @class View.Views.WorksheetView
 * @alias SUGAR.App.layout.WorksheetView
 * @extends View.View
 */
({

    url: 'rest/v10/Forecasts/worksheet',
    show: false,

    _name_type_map: {
//        best_case_worksheet: 'int',
//        likely_case_worksheet: 'int',
//        probability: 'percent',
        sales_stage: 'enum'
    },

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
        var self = this;

        this.viewModule = app.viewModule;

        //set expandable behavior to false by default
        this.isExpandableRows = false;

        app.view.View.prototype.initialize.call(this, options);

        this._collection = this.context.forecasts.worksheet;

        var TotalModel = Backbone.Model.extend({

        });

        this.totalModel = new TotalModel(
            {
                includedAmount : 0,
                includedBest : 0,
                includedLikely : 0,
                includedCount : 0,
                overallAmount : 0,
                overallBest : 0,
                overallLikely : 0
            }
        );


        var TotalView = Backbone.View.extend({
            id : 'summary',

            tagName : 'tfoot',

            initialize: function() {
                self.context.on("change:selectedToggle", function(context, data) {
                    self.refresh();
                });
            },

            render: function() {
                var self = this;
                var hb = Handlebars.compile("<tr><th colspan='5' style='text-align: right;'>Included Total</th>" +
                    "<th>{{includedAmount}}</th><th>{{includedBest}}</th><th>{{includedLikely}}</th></tr>" +
                    "<tr class='overall'><th colspan='5' style='text-align: right;'>Overall Total</th>" +
                    "<th>{{overallAmount}}</th><th>{{overAllBest}}</th><th>{{overallLikely}}</th></tr>");
                $('#summary').html(hb(self.model.toJSON()));
                return this;
            }
        });

        this.totalView = new TotalView({
            model : this.totalModel
        });


        // INIT tree with logged-in user
        var selectedUser = {
            'id' : app.user.get('id')
        }
        this.updateWorksheetBySelectedUser(selectedUser);
    },

    createURL:function() {
        var url = this.url;
        var args = {};
        if(this.timePeriod) {
           args['timeperiod_id'] = this.timePeriod;
        }

        if(this.selectedUser)
        {
           args['user_id'] = this.selectedUser;
        }

        var params = '';
        _.each(args, function (value, key) {
            params += '&' + key + '=' + encodeURIComponent(value);
        });

        if(params)
        {
            url += '?' + params.substr(1);
        }
        return url;
    },

    /**
     * Initializes clickToEdit field types (chosen, datepicker, etc...)
     * @private
     */
    _initCTETypes: function() {
        $.editable.addInputType('enum', {
            element: function(settings, original) {
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
                    $(this).parent().submit();
                });
            },

            /**
             * process value from chosen for submittal
             * @param settings
             * @param original
             */
            submit: function(settings, original) {
                $("input", this).val($("select", this).filter(".cteSelect").val());
            }
        });
    },

    /**
     * Adds the icon and associated events/handlers to the clickToEdit field
     * @param field
     * @private
     */
    _addCTEIcon: function(field) {
        // add icon markup
        var outerElement = field.$el;
        field.cteIcon = $('<span class="span2" style=" border-right: medium none; position: absolute; left: -5px; width: 15px"><i class="icon-pencil icon-sm"></i></span>');

        // add events
        field.showCteIcon = function(){
            this.$el.parent().css('overflow-x', 'visible');
            this.$el.before(this.cteIcon);
        };

        field.hideCteIcon = function(){
            this.$el.parent().find(this.cteIcon).detach();
            this.$el.parent().css('overflow-x', 'hidden');
        };

        var events = field.events || {};
        field.events = _.extend(events, {
            'mouseenter': 'showCteIcon',
            'mouseleave': 'hideCteIcon'
        });
        field.delegateEvents();

    },

    /**
     * Renders the field as clickToEditable
     * @private
     */
    _renderClickToEditField: function(field) {
        var self = this;
        this._initCTETypes();
        this._addCTEIcon(field);

        field.$el.editable(function(value, settings){
                return value;
            },
            {
                type: this._name_type_map[field.name] || 'text',
                select: true,
                field: field,
                view: self,
                onedit:function(settings, original){
                    // hold value for use later in case user enters a +/- percentage
                    if (settings.field.type == "int"){
                        settings.field.holder = $(original).html();
                    }
                    console.log("onedit");
                },
                onreset:function(settings, original){
                    console.log("onreset");
                },
                onsubmit:function(settings, original){
                    console.log("onsubmit");
                },
                callback: function(value, settings) {
                    try{
                        // if it's an int, and the user entered a +/- percentage, calculate it
                        if(settings.field.type == "int"){
                            orig = settings.field.holder;
                            if(value.match(/^[+-][0-1]?[0-9]?[0-9]%$/)) {
                                value = eval(orig + value[0] + "(" + value.substring(1,value.length-1) / 100 + "*" + orig +")");
                            } else if (!value.match(/^[0-9]*$/)) {
                                value = orig;
                            }
                        }

                        settings.field.model.set(settings.field.name, value);
                        settings.field.model.url = self.url;
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
     * Renders a field.
     *
     * This method sets field's view element and invokes render on the given field.  If clickToEdit is set to true
     * in metadata, it will also render it as clickToEditable.
     * @param {View.Field} field The field to render
     * @protected
     */
    _renderField: function(field) {
        app.view.View.prototype._renderField.call(this, field);
        if (field.viewName !="edit" && field.def.clickToEdit) {
            this._renderClickToEditField(field);
        }
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
            // STORY 31921015 - Make the forecastsWorksheet work with the new event from the Forecast Filter
            this.context.forecasts.on("change:renderedForecastFilter", function(context, defaultValues) {
                this.updateWorksheetBySelectedTimePeriod({id: defaultValues.timeperiod_id});
                this.updateWorksheetBySelectedCategory({id: defaultValues.category});
            }, this);
            // END STORY 31921015
            this.context.forecasts.on("change:showManagerOpportunities",
                function(context, showOpps) {
                    this.updateWorksheetByMgrOpportunities(showOpps);
                }, this);
        }
    },

    /**
     * Refresh the view
     *
     * This method ensures that we first calculate the totals from the collection before calling render to redraw results
     * @param context
     */
    refresh:function(context) {
        $.when(this.calculateTotals(), this.render());
    },

    /**
     * Renders view
     */
    render:function () {
        var self = this;
        
        if(!this.showMe()){
        	return false;
        }
        $("#view-sales-rep").show();
        $("#view-manager").hide();
        
        app.view.View.prototype.render.call(this);
        
        // parse metadata into columnDefs
        // so you can sort on the column's "name" prop from metadata
        var columnDefs = [];
        var fields = this.meta.panels[0].fields;
        var columnKeys = {};

        for( var i = 0; i < fields.length; i++ )  {
            var name = fields[i].name;
            columnDefs.push( { "sName": name, "aTargets": [ i ] } );
            columnKeys[name] = i;
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

        this.totalView.render();

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

    	if(!isManager || (isManager && userId.localeCompare(selectedUser) != 0)){
    		this.show = true;
    	}	
    	
    	return this.show;
    },

    /**
     *
     * @param selectedUser
     */
    calculateTotals: function() {
        var self = this;
        var includedAmount = 0;
        var includedBest = 0;
        var includedLikely = 0;
        var overallAmount = 0;
        var overallBest = 0;
        var overallLikely = 0;
        var includedCount = 0;

        _.each(self._collection.models, function (model) {
            var included = model.get('forecast');
            var amount = parseFloat(model.get('amount'));
            var likely = parseFloat(model.get('likely_case_worksheet'));
            var best = parseFloat(model.get('best_case_worksheet'));

            if(included)
            {
                includedAmount += amount;
                includedLikely += likely;
                includedBest += best;
                includedCount++;
            }
            overallAmount += amount;
            overallLikely += likely;
            overallBest += best;
        });

        self.totalModel.set('includedAmount', includedAmount);
        self.totalModel.set('includedBest', includedBest);
        self.totalModel.set('includedLikely', includedLikely);
        self.totalModel.set('overallAmount', overallAmount);
        self.totalModel.set('overallBest', overallBest);
        self.totalModel.set('overallLikely', overallLikely);

        var totals = {
            'likely_case' : includedLikely,
            'best_case' : includedBest,
            'timeperiod_id' : self.timePeriod,
            'forecast_type' : 'Direct',
            'opp_count' : includedCount,
            'amount' : includedAmount
        };


        this.context.set("updatedTotals", totals);
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
        this._collection = this.context.forecasts.worksheet;
        this._collection.url = this.createURL();
        this._collection.fetch();
    },

    /**
     * Event Handler for updating the worksheet by a selected category
     *
     * @param params is always a context
     */
    updateWorksheetBySelectedCategory:function (params) {
        // Set the filters for the datatable then re-render
        if(params.id == "Committed")
        {
            $.fn.dataTableExt.afnFiltering.push (
                function(oSettings, aData, iDataIndex)
                {
                    var val = $(aData[0]).html();
                    return /checked/.test(val);
                }
            );
        } else {
            //Remove the filters
            $.fn.dataTableExt.afnFiltering.splice(0, $.fn.dataTableExt.afnFiltering.length);
        }
        this.refresh();
    },

    /**
     * Event Handler for updating the worksheet by a timeperiod id
     *
     * @param params is always a context
     */
    updateWorksheetBySelectedTimePeriod:function (params) {
        this.timePeriod = params.id;
        if(!this.showMe()){
        	return false;
        }
        this._collection = this.context.forecasts.worksheet;
        this._collection.url = this.createURL();
        this._collection.fetch();
    },

    /***
     * Event Handler for showing a manager's opportunities
     *
     * @param showOpps boolean value to display manager's opportunities or not
     */
    updateWorksheetByMgrOpportunities: function(showOpps){
        // TODO: Add functionality for whatever happens when "My Opportunities" is clicked
        if(showOpps) {
            // Show manager's Opportunities (forecastWorksheet for manager's id)
        } else {
            // Show manager's worksheet view (forecastWorksheetManager for manager's id)
        }
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
