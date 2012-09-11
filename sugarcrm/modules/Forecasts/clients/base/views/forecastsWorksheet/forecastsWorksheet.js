/**
 * View that displays header for current app
 * @class View.Views.WorksheetView
 * @alias SUGAR.App.layout.WorksheetView
 * @extends View.View
 */
({

    url: 'rest/v10/ForecastWorksheets',
    show: false,
    viewModule: {},
    selectedUser: {},
    gTable:'',
    aaSorting:[],
    // boolean for enabled expandable row behavior
    isExpandableRows:'',
    isEditableWorksheet:false,
    _collection:{},


    /**
     * This function handles updating the totals calculation and calling the render function.  It takes the model entry
     * that was updated by the toggle event and calls the Backbone save function on the model to invoke the REST APIs
     * to handle persisting the changes
     *
     * @param model Backbone model entry that was affected by the toggle event
     */
    toggleIncludeInForecast:function(model)
    {
        var self = this;
        self._collection.url = self.url;
        model.save(null, { success:_.bind(function() {
        	this.aaSorting = this.gTable.fnSettings()["aaSorting"];
        	this.render(); 
        }, this)});
    },

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


        //set up base selected user
    	this.selectedUser = {id: app.user.get('id'), "isManager":app.user.get('isManager'), "showOpps": false};

        //Create a Backbone.Model instance to represent the included amounts
        this.includedModel = new Backbone.Model(
            {
                includedAmount : 0,
                includedBest : 0,
                includedCount : 0
            }
        );

        //Create a Backbone.Model instance to represent the overall amounts
        self.overallModel = new Backbone.Model(
            {
                overallAmount : 0,
                overallBest : 0
            }
        )

        // INIT tree with logged-in user       
        this.timePeriod = app.defaultSelections.timeperiod_id.id;
        this.updateWorksheetBySelectedCategory(app.defaultSelections.category);
        this._collection.url = this.createURL();

    },

    createURL:function() {
        var url = this.url;
        var args = {};
        if(this.timePeriod) {
           args['timeperiod_id'] = this.timePeriod;
        }

        if(this.selectedUser)
        {
           args['user_id'] = this.selectedUser.id;
        }

        url = app.api.buildURL('ForecastWorksheets', '', '', args);
        /*
        var params = '';
        _.each(args, function (value, key) {
            params += '&' + key + '=' + encodeURIComponent(value);
        });

        if(params)
        {
            url += '?' + params.substr(1);
        }*/
        return url;
    },

    /**
     * Sets up the save event and handler for the commit_stage dropdown fields in the worksheet.
     * @param field the commit_stage field
     * @return {*}
     * @private
     */
    _setUpCommitStage: function (field) {
        field._save = function(event, input) {
            this.model.set('commit_stage', input.selected);
            this.view.context.set('selectedToggle', field);
        };
        field.events = _.extend({"change select": "_save"}, field.events);
        return field;
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

        if(field.name == "commit_stage")
        {
            //Set the field.def.optoins value based on app.config.buckets_dom (if set)
            field.def.options = app.config.buckets_dom || 'commit_stage_dom';
            if(this.isEditableWorksheet)
            {
               field = this._setUpCommitStage(field);
            }
        }

        /*
        if (this.isEditableWorksheet === true && field.name == "commit_stage") {
            field = this._setUpCommitStage(field);
        }
        */
        app.view.View.prototype._renderField.call(this, field);

        if (this.isEditableWorksheet === true && field.viewName !="edit" && field.def.clickToEdit === true) {
            new app.view.ClickToEditField(field, this);
        }

        if (this.isEditableWorksheet === true && field.name == "commit_stage") {
            new app.view.BucketGridEnum(field, this);
        }
    },

    bindDataChange: function(params) {
        var self = this;
        if (this._collection) {
            this._collection.on("reset", function() { self.calculateTotals(), self.render(); }, this);

            this._collection.on("change", function() {
                _.each(this._collection.models, function(element, index){
                    if(element.hasChanged("forecast")) {
                        this.toggleIncludeInForecast(element);
                    }
                    if(element.hasChanged("commit_stage")) {
                        if(element.get("commit_stage") == '100') {
                            this._collection.models[index].set("forecast", '1');
                        } else {
                            this._collection.models[index].set("forecast", '0');
                        }
                        this.toggleIncludeInForecast(element);
                    }
                }, this);
            }, this);
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
            this.context.forecasts.worksheet.on("change", function() {
            	this.calculateTotals();
            }, this);
            this.context.forecasts.on("change:reloadWorksheetFlag", function(){
            	if(this.context.forecasts.get('reloadWorksheetFlag') && this.showMe()){
            		var model = this.context.forecasts.worksheet;
            		model.url = this.createURL();
            		this.safeFetch();
            		this.context.forecasts.set({reloadWorksheetFlag: false});
            	}
            }, this);
            var worksheet = this;
            $(window).bind("beforeunload",function(){
            	worksheet.safeFetch();
            });
        }
    },
    
    /**
     * This function checks to see if the worksheet is dirty, and gives the user the option
     * of saving their work before the sheet is fetched.
     */
    safeFetch: function(){
    	var collection = this._collection; 
    	var self = this;
    	if(collection.isDirty){
    		//unsaved changes, ask if you want to save.
    		if(confirm(app.lang.get("LBL_WORKSHEET_SAVE_CONFIRM", "Forecasts"))){
    			_.each(collection.models, function(model, index){
					var isDirty = model.get("isDirty");
					if(typeof(isDirty) == "boolean" && isDirty ){
        				model.set({draft: 1}, {silent:true});
        				model.save();
        				model.set({isDirty: false}, {silent:true});
        			}  
				});
    			collection.isDirty = false;
				$.when(!collection.isDirty).then(function(){
	    			self.context.forecasts.set({reloadCommitButton: true});
	    			collection.fetch();
    		});
			
		}
    		else{
    			//ignore, fetch still
    			collection.isDirty = false;
    			self.context.forecasts.set({reloadCommitButton: true});
    			collection.fetch();
    		}
    	}
    	else{
    		//no changes, fetch like normal.
    		collection.fetch();	
    	}    	
    },

    _setForecastColumn: function(fields) {
        var self = this;
        var forecastField, commitStageField;

        _.each(fields, function(field) {
            if (field.name == "forecast") {
                field.enabled = (app.config.show_buckets == 0);
                forecastField = field;
            } else if (field.name == "commit_stage") {
                field.enabled = (app.config.show_buckets == 1);
                field.view = self.isEditableWorksheet ? 'edit' : 'default';
                commitStageField = field;
            }
        });

        return (app.config.show_buckets == 1) ? forecastField : commitStageField;
    },

    /**
     * Renders view
     */
    _render: function() {
        var self = this;
        var enableCommit = false;
        
        if(!this.showMe()){
        	return false;
        }
        $("#view-sales-rep").show();
        $("#view-manager").hide();
		this.context.forecasts.set({currentWorksheet: "worksheet"});
        this.isEditableWorksheet = this.isMyWorksheet();
        var unusedField = this._setForecastColumn(this.meta.panels[0].fields);

        app.view.View.prototype._render.call(this);

        // parse metadata into columnDefs
        // so you can sort on the column's "name" prop from metadata
        var columnDefs = [];
        var fields = _.without(this.meta.panels[0].fields, unusedField);
        var columnKeys = {};

        _.each(fields, function(field, key){
            var name = field.name;
            var fieldDef = { "sName": name, "aTargets": [ key ] };
            if(typeof(field.type) != "undefined" && field.type == "bool"){
            	fieldDef["sSortDataType"] = "dom-checkbox";
            }
            columnDefs.push(fieldDef);
            columnKeys[name] = key;
        });
        this.gTable = this.$('.worksheetTable').dataTable(
            {
                "aoColumnDefs": columnDefs,
                "aaSorting": this.aaSorting,
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

        //Remove all events that may be associated with forecastschedule view
        this.context.forecasts.forecastschedule.off();
        this.context.forecasts.forecastschedule.on("change", function() { this.calculateTotals(); }, this);
        //Create the view for expected opportunities
        var viewmeta = app.metadata.getView("Forecasts", "forecastSchedule");
        var view = app.view.createView({name:"forecastSchedule", meta:viewmeta, timeperiod_id:this.timePeriod, user_id:this.selectedUser.id });

        $("#expected_opportunities").remove();
        view.fetchCollection();
        $("#summary").prepend(view.$el);

        // fix the style on the rows that contain a checkbox
        this.$el.find('td:has(span>input[type=checkbox])').addClass('center');
        
        //see if anything in the model is a draft version
        _.each(this._collection.models, function(model, index){
        	if(model.get("version") == 0){
        		enableCommit = true;
        	}
        });
        if(enableCommit){
        	self.context.forecasts.set({commitButtonEnabled: true});
        }
        else{
        	self.context.forecasts.set({commitButtonEnabled: false});
        }
        
        this.calculateTotals();
        this.createSubViews();
        this.includedView.render();
        this.overallView.render();

        return this;
    },


    /**
     * Creates the "included totals" and "overall totals" subviews for the worksheet
     */
    createSubViews: function() {
        var self = this;

        var IncludedView = Backbone.View.extend({
            id : 'included_totals',
            tagName : 'tr',

            initialize: function() {
                _.bindAll(this, 'render');
                //self._collection.bind('change', self.render);
                this.model.bind('change', this.render);
            },

            render: function() {
                var self = this;
                var source = $("#included_template").html();
                var hb = Handlebars.compile(source);
                $('#included_totals').html(hb(self.model.toJSON()));
                return this;
            }
        });

        this.includedView = new IncludedView({
            model : self.includedModel
        });


        var OverallView = Backbone.View.extend({
            id : 'overall_totals',
            tagName : 'tr',

            initialize: function() {
                _.bindAll(this, 'render');
                //self._collection.bind('change', self.render);
                this.model.bind('change', this.render);
            },

            render: function() {
                var self = this;
                var source = $("#overall_template").html();
                var hb = Handlebars.compile(source);
                $('#overall_totals').html(hb(self.model.toJSON()));
                return this;
            }
        });

        this.overallView = new OverallView({
            model : self.overallModel
        });
    },



    /**
     * Determines if this Worksheet belongs to the current user, applicable for determining if this view should show,
     * or whether to render the clickToEdit field
     * @return {Boolean} true if it is the worksheet of the logged in user, false if not.
     */
    isMyWorksheet: function() {
        return _.isEqual(app.user.get('id'), this.selectedUser.id);
    },

    /**
     * Determines if this Worksheet should be rendered
     */
    showMe: function(){
    	var selectedUser = this.selectedUser;
    	this.show = false;

    	if(selectedUser.showOpps || !selectedUser.isManager){
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
        var includedWorst = 0;
        var overallAmount = 0;
        var overallBest = 0;
        var includedCount = 0;
        var lostCount = 0;
        var lostAmount = 0;
        var wonCount = 0;
        var wonAmount = 0;
        var totalCount = 0;

        if(!this.showMe()){
            // if we don't show this worksheet set it all to zero
        	this.context.forecasts.set("updatedTotals", {
                'best_case' : includedBest,
                'worst_case' : includedWorst,
                'timeperiod_id' : self.timePeriod,
                'lost_count' : lostCount,
                'lost_amount' : lostAmount,
                'won_count' : wonCount,
                'won_amount' : wonAmount,
                'included_opp_count' : includedCount,
                'total_opp_count' : totalCount,
                'amount' : includedAmount,
                'overall_amount' : overallAmount
            });
            return false;
        }

        //Get the excluded_sales_stage property.  Default to empty array if not set
        app.config.sales_stage_won = app.config.sales_stage_won || [];
        app.config.sales_stage_lost = app.config.sales_stage_lost || [];

        _.each(self._collection.models, function (model) {

            var won = app.config.sales_stage_won.indexOf(model.get('sales_stage')) !== -1;
            var lost = app.config.sales_stage_lost.indexOf(model.get('sales_stage')) !== -1;
            var amount = parseFloat(model.get('amount'));
            var included = model.get('forecast');
            var best = parseFloat(model.get('best_case'));
            var worst = parseFloat(model.get('worst_case'));

            if(won)
            {
                if(included == true || included == 1) {
                    wonAmount += amount;
                }
                wonCount++;
            } else if(lost) {
                if(included == true || included == 1) {
                    lostAmount += amount;
                }
                lostCount++;
            }

            if(included == true || included == 1) {
                includedAmount += amount;
                includedBest += best;
                includedWorst += worst;
                includedCount++;
            }

            overallAmount += amount;
            overallBest += best;
        });

        //Now see if we need to add the expected opportunity amounts
        if(this.context.forecasts.forecastschedule.models)
        {
            _.each(this.context.forecasts.forecastschedule.models, function(model) {
               if(model.get('status') == 'Active')
               {
                    var amount = model.get('expected_amount');
                    var best = model.get('expected_best_case');
                    var worst = model.get('expected_worst_case');

                    //Check for null condition and, if so, set to 0
                    amount = amount != null ? parseFloat(amount) : 0;
                    best = best != null ? parseFloat(best) : 0;

                    if(model.get('include_expected') == 1)
                    {
                        includedAmount += amount;
                        includedBest += best;
                        includedWorst += worst;
                    }

                    overallAmount += amount;
                    overallBest += best;
               }
           });
        }

        self.includedModel.set({
            includedAmount : includedAmount,
            includedBest : includedBest,
            includedCount : includedCount,
            includedWorst : includedWorst
        });
        self.includedModel.change();

        self.overallModel.set({
            overallAmount : overallAmount,
            overallBest : overallBest
        });
        self.overallModel.change();

        var totals = {
            'best_case' : includedBest,
            'worst_case' : includedWorst,
            'timeperiod_id' : self.timePeriod,
            'lost_count' : lostCount,
            'lost_amount' : lostAmount,
            'won_count' : wonCount,
            'won_amount' : wonAmount,
            'included_opp_count' : includedCount,
            'total_opp_count' : self._collection.models.length,
            'amount' : includedAmount,
            'overall_amount' : overallAmount
        };

        this.context.forecasts.set("updatedTotals", totals);
    },

    /**
     * Event Handler for updating the worksheet by a selected user
     *
     * @param params is always a context
     */
    updateWorksheetBySelectedUser:function (selectedUser) {
        this.selectedUser = selectedUser;
        if(this.selectedUser && !this.selectedUser){
        	return false;
        }
        this._collection.url = this.createURL();
        this.safeFetch();
    },

    /**
     * Event Handler for updating the worksheet by a selected category
     *
     * @param params is always a context
     */
    updateWorksheetBySelectedCategory:function (params) {
        // Set the filters for the datatable then re-render
        var self = this;

        if (app.config.show_buckets == 1) { // buckets

             $.fn.dataTableExt.afnFiltering.splice(0, $.fn.dataTableExt.afnFiltering.length);
             $.fn.dataTableExt.afnFiltering.push (
                    function(oSettings, aData, iDataIndex)
                    {
                        var editable = self.isMyWorksheet();
                        var returnVal = null;
                        //If we are in an editable worksheet get the selected dropdown value; otherwise, get the enum detail/default text
                        var selectVal = editable ? $(aData[0]).find("select").attr("value") : $(aData[0]).text().trim();

                        if(editable && (typeof(selectVal) != "undefined" && _.contains(params, selectVal)))
                        {
                            returnVal = selectVal;
                        } else if(!editable) {
                            //Get the array for the bucket stages
                            var buckets_dom = app.lang.getAppListStrings(app.config.buckets_dom || 'commit_stage_dom');
                            _.each(params, function(filter)
                            {
                                if(buckets_dom[filter] == selectVal)
                                {
                                   returnVal = selectVal;
                                   return;
                                }
                            });
                        }

                        return returnVal;
                    }
                );
        } else {  // not buckets
            // INVESTIGATE:  this needs to be more dynamic and deal with potential customizations based on how filters are built in admin and/or studio
            if(_.first(params) == "1") {//committed
                $.fn.dataTableExt.afnFiltering.push (
                    function(oSettings, aData, iDataIndex)
                    {
                        var val = aData[0];
                        var jVal = $(val);

                        var returnVal = null;

                        // our custom checkbox sort has taken over, this is now a 1 or 0
                        if(val.length == 1){
                            if(val == 1){
                                returnVal = val;
                            }
                        }
                        //initial load still has html here, or it is a dropdown.
                        else{
                            var checkboxVal = jVal.find("input").attr("checked");

                            if(typeof(checkboxVal) != "undefined"){
                                returnVal = 1;
                            }
                        }

                        return returnVal;
                    }
                );
            } else {
                //pipeline
                //Remove the filters
                $.fn.dataTableExt.afnFiltering.splice(0, $.fn.dataTableExt.afnFiltering.length);
            }
        }
        this.render();
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
        this._collection.url = this.createURL();
        this.safeFetch();
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
