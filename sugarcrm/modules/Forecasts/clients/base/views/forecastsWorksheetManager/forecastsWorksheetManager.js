/**
 * View that displays header for current app
 * @class View.Views.WorksheetView
 * @alias SUGAR.App.layout.WorksheetView
 * @extends View.View
 *
 *
 * Events Triggered
 *
 * forecasts:commitButtons:enabled
 *      on: context.forecasts
 *      by: _render()
 *      when: done rendering if enableCommit is true
 *
 * forecasts:worksheetmanager:rendered
 *      on: context.forecasts
 *      by: _render()
 *      when: done rendering
 *
 * forecasts:forecastcommitbuttons:triggerSaveDraft
 *      on: context.forecasts
 *      by: safeFetch()
 *      when: user performs an action that causes a check to be made against dirty data
 *
 */
({
    url: 'rest/v10/ForecastManagerWorksheets',
    show: false,
    viewModule: {},
    selectedUser: {},
    gTable:'',
    // boolean for enabled expandable row behavior
    isExpandableRows:'',
    _collection:{},
    // boolean to denote that a fetch is currently in progress
    fetchInProgress :  false,


    /**
     * Template to use wen updating the likelyCase on the committed bar
     */
    commitLogTemplate : _.template('<article><%= text %><br><date><%= text2 %></date></article>'),

    /**
     * Template to use when we are fetching the commit history
     */
    commitLogLoadingTemplate : _.template('<div class="extend results"><article><%= loadingMessage %></article></div>'),

    /**
     * Handle Any Events
     */
    events:{
        'click a[rel=historyLog] i.icon-exclamation-sign':'displayHistoryLog'
    },

    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize:function (options) {
        this.viewModule = app.viewModule;
        var self = this;

        app.view.View.prototype.initialize.call(this, options);

        //set up base selected user
    	this.selectedUser = {id: app.user.get('id'), "isManager":app.user.get('isManager'), "showOpps": false};
        this.timePeriod = app.defaultSelections.timeperiod_id.id
        this.ranges = app.defaultSelections.ranges.id

        this._collection = this.context.forecasts.worksheetmanager;
        this._collection.url = this.createURL();
        this._collection.isDirty = false;

        this.totalModel = new (Backbone.Model.extend(
            {
                amount : 0,
                quota : 0,
                best_case : 0,
                best_adjusted : 0,
                likely_case : 0,
                likely_adjusted : 0,
                worst_case : 0,
                worst_adjusted : 0
            }
        ));
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
        this._collection.url = this.createURL();
        this.safeFetch(true);
    },

    /**
     * Clean up any left over bound data to our context
     */
    unbindData : function() {
        if(this._collection) this._collection.off(null, null, this);
        if(this.context.forecasts) this.context.forecasts.off(null, null, this);
        if(this.context.forecasts.worksheetmanager) this.context.forecasts.worksheetmanager.off(null, null, this);
        app.view.View.prototype.unbindData.call(this);
    },

    bindDataChange: function() {
        if(this._collection)
        {
            this._collection.on("reset", function(){
            	this.render();
            }, this);
        }
        // listening for updates to context for selectedUser:change
        if (this.context.forecasts) {
            var self = this;

            this.context.forecasts.on("change:selectedUser",
                function(context, selectedUser) {
                    this.updateWorksheetBySelectedUser(selectedUser);
                }, this);
            this.context.forecasts.on("change:selectedTimePeriod",
                function(context, timePeriod) {
                    this.updateWorksheetBySelectedTimePeriod(timePeriod);
                }, this);
            this.context.forecasts.on("change:selectedRanges",
                function(context, ranges) {
                    this.updateWorksheetBySelectedRanges(ranges);
                },this);
            this.context.forecasts.worksheetmanager.on("change", function() {
            	this.calculateTotals();
            }, this);
            this.context.forecasts.on("forecasts:committed:saved forecasts:commitButtons:saved", function(){
            	if(this.showMe()){
            		var model = this.context.forecasts.worksheetmanager;
            		model.url = this.createURL();
            		this.safeFetch();
            	}
            }, this);
            
            /*
             * // TODO: tagged for 6.8 see SFA-253 for details
            this.context.forecasts.config.on('change:show_worksheet_likely', function(context, value) {
                // only trigger if this component is rendered
                if(!_.isEmpty(self.el.innerHTML)) {
                    self.setColumnVisibility(['likely_case', 'likely_adjusted'], value, self);
                }
            });

            this.context.forecasts.config.on('change:show_worksheet_best', function(context, value) {
                // only trigger if this component is rendered
                if(!_.isEmpty(self.el.innerHTML)) {
                    self.setColumnVisibility(['best_case', 'best_adjusted'], value, self);
                }
            });

            this.context.forecasts.config.on('change:show_worksheet_worst', function(context, value) {
                // only trigger if this component is rendered
                if(!_.isEmpty(self.el.innerHTML)) {
                    self.setColumnVisibility(['worst_case', 'worst_adjusted'], value, self);
                }
            });
            */
            
            var worksheet = this;
            $(window).bind("beforeunload",function(){
                if(worksheet._collection.isDirty){
                	return app.lang.get("LBL_WORKSHEET_SAVE_CONFIRM_UNLOAD", "Forecasts");
                }            	
            });
        }
    },


    /**
     * Sets the visibility of a column or columns if array is passed in
     *
     * @param cols {Array} the sName of the columns to change
     * @param value {*} int or Boolean, 1/true or 0/false to show the column
     * @param ctx {Object} the context of this view to have access to the checkForColumnsSetVisibility function
     */
    setColumnVisibility: function(cols, value, ctx) {
        var aoColumns = this.gTable.fnSettings().aoColumns;

        for(var i in cols) {
            var columnName = cols[i];
            for(var k in aoColumns) {
                if(aoColumns[k].sName == columnName)  {
                    this.gTable.fnSetColumnVis(k, value == 1);
                    break;
                }
            }
        }
    },

    /**
     * Checks if colKey is in the table config keymaps on the context
     *
     * @param colKey {String} the column sName to check in the keymap
     * @return {*} returns null if not found in the keymap, returns true/false if it did find it
     */
    checkConfigForColumnVisibility: function(colKey) {
        return app.forecasts.utils.getColumnVisFromKeyMap(colKey, this.name, this.context.forecasts.config);
    },

    /**
     * This function checks to see if the worksheet is dirty, and gives the user the option
     * of saving their work before the sheet is fetched.
     * @param fetch {boolean} Tells the function to go ahead and fetch if true, or runs dirty checks (saving) w/o fetching if false 
     */
    safeFetch: function(fetch){
        //fetch currently already in progress, no need to duplicate
        if(this.fetchInProgress) {
            return;
        }
        //mark that a fetch is in process so no duplicate fetches begin
        this.fetchInProgress = true;
        if(typeof fetch == 'undefined')
        {
            fetch = true;
        }
    	var collection = this._collection;
    	var self = this;
    	if(collection.isDirty){
    		//unsaved changes, ask if you want to save.
    		if(confirm(app.lang.get("LBL_WORKSHEET_SAVE_CONFIRM", "Forecasts"))){
                self.context.forecasts.trigger("forecasts:forecastcommitbuttons:triggerSaveDraft");
		    }
    		else {
    			//ignore, fetch still
    			collection.isDirty = false;
    			self.context.forecasts.set({reloadCommitButton: true});
    			if(fetch){
    				collection.fetch();
    			}
    			
    		}
    	}
    	else{
    		//no changes, fetch like normal.
    		if(fetch){
    			collection.fetch();
    		}
    		
    	}
        this.fetchInProgress = false;
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
        if (field.viewName !="edit" && field.def.clickToEdit === true && _.isEqual(this.selectedUser.id, app.user.get('id'))) {
            field = new app.view.ClickToEditField(field, this);
        }
    },

    /**
     * Renders view
     */
    _render:function () {
        var self = this;
        var enableCommit = false;
      
        if(!this.showMe()){
        	return false;
        }
        $("#view-sales-rep").addClass('hide').removeClass('show');
        $("#view-manager").addClass('show').removeClass('hide');
        this.context.forecasts.set({currentWorksheet: "worksheetmanager"});
        
        app.view.View.prototype._render.call(this);

        // parse metadata into columnDefs
        // so you can sort on the column's "name" prop from metadata
        var columnDefs = [];
        var fields = this.meta.panels[0].fields;

        for( var i = 0; i < fields.length; i++ )  {
            if(fields[i].enabled) {
                // in case we add column rearranging
                var fieldDef = {
                    "sName": fields[i].name,
                    "bVisible" : this.checkConfigForColumnVisibility(fields[i].name)
                };

                //Apply sorting for the worksheet
                if(typeof(fields[i].type) != "undefined")
                {
                    switch(fields[i].type)
                    {
                        case "int":
                        case "currency":
                        case "editableCurrency":
                            fieldDef["sSortDataType"] = "dom-number";
                            fieldDef["sType"] = "numeric";
                            fieldDef["sClass"] = "number";
                            break;
                    }
                    switch(fields[i].name)
                    {
                        case "name":
                            fieldDef["sWidth"] = "30%";
                            break;
                    }
                }

                columnDefs.push(fieldDef);
            }
        }

        this.gTable = this.$el.find(".worksheetManagerTable").dataTable(
            {
                "bAutoWidth": false,
                "aaSorting": [],
                "aoColumns": columnDefs,
                "bInfo":false,
                "bPaginate":false
            }
        );

        //see if anything in the model is a draft version
        _.each(this._collection.models, function(model, index){
        	if(model.get("version") == 0){
        		enableCommit = true;
        	}
        });
        if (enableCommit) {
        	self.context.forecasts.trigger("forecasts:commitButtons:enabled");
        }
        
        this.calculateTotals();
        self.context.forecasts.trigger('forecasts:worksheetmanager:rendered');

    },

    /**
     * Handle the click event from a history log icon click.
     *
     * @param event
     */
    displayHistoryLog:function (event) {
        var self = this;
        var nTr = _.first($(event.target).parents('tr'));
        // test to see if it's open
        if (self.gTable.fnIsOpen(nTr)) {
            // if it's open, close it
            self.gTable.fnClose(nTr);
        } else {
            //Open this row

            var colspan = $(nTr).children('td').length;

            self.gTable.fnOpen(nTr, this.commitLogLoadingTemplate({'loadingMessage' : App.lang.get("LBL_LOADING_COMMIT_HISTORY", 'Forecasts')}) , 'details');
            $(nTr).next().children("td").attr("colspan", colspan);

            self.fetchUserCommitHistory(event, nTr);
        }
    },

    /**
     * Event handler when popoverIcon is clicked,
     * @param event
     * @return {*}
     */
    fetchUserCommitHistory: function(event, nTr) {
        var options = {
            timeperiod_id : this.timePeriod,
            user_id : $(event.target).attr('data-uid'),
            forecast_type : 'direct'
        };

        var dataCommitDate = $(event.target).attr('data-commitdate');

        return app.api.call('read',
             app.api.buildURL('Forecasts', 'committed', null, options),
            null,
            {
                success : function(data) {
                    var commitDate = new Date(dataCommitDate),
                        newestModel = new Backbone.Model(_.first(data)),
                        // get everything that is left but the first item.
                        otherModels = _.last(data, data.length-1),
                        oldestModel = {};

                    // using for because you can't break out of _.each
                    for(var i = 0; i < otherModels.length; i++) {
                        // check for the first model equal to or past the forecast commit date
                        // we want the last commit just before the whole forecast was committed
                        if(new Date(otherModels[i].date_modified) <= commitDate) {
                            oldestModel = new Backbone.Model(otherModels[i]);
                            break;
                        }
                    }

                    // create the history log
                    outputLog = app.forecasts.utils.createHistoryLog(oldestModel,newestModel,this.context.forecasts.config);
                    // update the div that was created earlier and set the html to what was the commit log
                    $(nTr).next().children("td").children("div").html(this.commitLogTemplate(outputLog));
                }
            },
            { context : this }
        );
    },


    calculateTotals:function () {
        var self = this,
            amount = 0,
            quota = 0,
            best_case = 0,
            best_adjusted = 0,
            likely_case = 0,
            likely_adjusted = 0,
            worst_adjusted = 0,
            worst_case = 0;

        if(!this.showMe()){
            // if we don't show this worksheet set it all to zero
            this.context.forecasts.set({
                updatedManagerTotals : {
                    'amount' : amount,
                    'quota' : quota,
                    'best_case' : best_case,
                    'best_adjusted' : best_adjusted,
                    'likely_case' : likely_case,
                    'likely_adjusted' : likely_adjusted,
                    'worst_adjusted' : worst_adjusted,
                    'worst_case' : worst_case
                }
            }, {silent:true});
            return false;
        }


        _.each(self._collection.models, function (model) {

           var base_rate = parseFloat(model.get('base_rate'));
           amount 			+= app.currency.convertWithRate(model.get('amount'), base_rate);
           quota 			+= app.currency.convertWithRate(model.get('quota'), base_rate);
           best_case 		+= app.currency.convertWithRate(model.get('best_case'), base_rate);
           best_adjusted 	+= app.currency.convertWithRate(model.get('best_adjusted'), base_rate);
           likely_case 		+= app.currency.convertWithRate(model.get('likely_case'), base_rate);
           likely_adjusted 	+= app.currency.convertWithRate(model.get('likely_adjusted'), base_rate);
           worst_case       += app.currency.convertWithRate(model.get('worst_case'), base_rate);
           worst_adjusted 	+= app.currency.convertWithRate(model.get('worst_adjusted'), base_rate);
        });

        self.totalModel.set({
            amount : amount,
            quota : quota,
            best_case : best_case,
            best_adjusted : best_adjusted,
            likely_case : likely_case,
            likely_adjusted : likely_adjusted,
            worst_case : worst_case,
            worst_adjusted : worst_adjusted
        });

        //in case this is needed later..
        var totals = {
            'amount' : amount,
            'quota' : quota,
            'best_case' : best_case,
            'best_adjusted' : best_adjusted,
            'likely_case' : likely_case,
            'likely_adjusted' : likely_adjusted,
            'worst_case' : worst_case,
            'worst_adjusted' : worst_adjusted
        };

        // we need to remove it, just in case it's the same to force it to re-render
        this.context.forecasts.unset("updatedManagerTotals", {silent: true});
        this.context.forecasts.set("updatedManagerTotals", totals);
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
     * Event Handler for updating the worksheet by a selected ranges
     *
     * @param params is always a context
     */
    updateWorksheetBySelectedRanges:function (params) {
        if (this.context.forecasts.config.get('forecast_ranges') != 'show_binary') {
            // TODO: this.
        } else {
            this.ranges = _.first(params);
        }

        var model = this.context.forecasts.worksheetmanager;
        if(!this.showMe()){
            return false;
        }
        model.url = this.createURL();
        this.safeFetch(true);
    },

    /**
     * Event Handler for updating the worksheet by a timeperiod id
     *
     * @param params is always a context
     */
    updateWorksheetBySelectedTimePeriod:function (params) {
    	this.timePeriod = params.id;
        var model = this.context.forecasts.worksheetmanager;
        if(!this.showMe()){
        	return false;
        }
        model.url = this.createURL();
        this.safeFetch(true);
    },

    createURL:function() {
        var url = this.url;
        var args = {};
        if(this.timePeriod) {
           args['timeperiod_id'] = this.timePeriod;
        }

        if(this.ranges) {
            args['ranges'] = this.ranges;
        }

        if(this.selectedUser)
        {
           args['user_id'] = this.selectedUser.id;
        }

        url = app.api.buildURL('ForecastManagerWorksheets', '', '', args);
        return url;
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
