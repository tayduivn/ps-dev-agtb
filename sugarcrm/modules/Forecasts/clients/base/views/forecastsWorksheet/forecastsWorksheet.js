/**
 * View that displays header for current app
 *
 * @class View.Views.WorksheetView
 * @alias SUGAR.App.layout.WorksheetView
 * @extends View.View
 *
 *
 * Events Triggered
 *
 * forecasts:forecastcommitbuttons:triggerCommit
 *      on: context.forecasts
 *      by: safeFetch()
 *      when: user clicks ok on confirm dialog that they want to commit data
 *
 * forecasts:forecastcommitbuttons:triggerSaveDraft
 *      on: context.forecasts
 *      by: safeFetch()
 *      when: user performs an action that causes a check to be made against dirty data
 *
 * forecasts:worksheet:rendered
 *      on: context.forecasts
 *      by: _render
 *      when: the worksheet is done rendering
 *
 * forecasts:worksheet:filtered
 *      on: context.forecasts
 *      by: updateWorksheetBySelectedCategory()
 *      when: dataTable is finished filtering itself
 *
 * forecasts:worksheet:filtered
 *      on: context.forecasts
 *      by: updateWorksheetBySelectedCategory()
 *      when: dataTable is finished filtering itself and has destroyed and redrawn itself
 */
({

    url: 'rest/v10/ForecastWorksheets',
    show: false,
    viewModule: {},
    selectedUser: {},
    gTable:'',
    gTableDefs:{},
    aaSorting:[],
    // boolean for enabled expandable row behavior
    isExpandableRows:'',
    isEditableWorksheet:false,
    _collection:{},
    columnDefs : [],    
    mgrNeedsCommitted : false,
    commitButtonEnabled : false,
    // boolean to denote that a fetch is currently in progress
    fetchInProgress : false,
    
    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize:function (options) {
        var self = this;
        
        self.gTableDefs = {
                                "bAutoWidth": false,
                                "aoColumnDefs": self.columnDefs,
                                "aaSorting": self.aaSorting,
                                "bInfo":false,
                                "bPaginate":false
                          };
        
        this.viewModule = app.viewModule;

        //set expandable behavior to false by default
        this.isExpandableRows = false;

        app.view.View.prototype.initialize.call(this, options);
        this._collection = this.context.forecasts.worksheet;

        //set up base selected user
        this.selectedUser = {id: app.user.get('id'), "isManager":app.user.get('isManager'), "showOpps": false};

        // INIT tree with logged-in user       
        this.timePeriod = app.defaultSelections.timeperiod_id.id;
        this.updateWorksheetBySelectedCategory(app.defaultSelections.category);
        this._collection.url = this.createURL();
    },

    /**
     *
     * @return {String}
     */
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
        return url;
    },
        
    /**
     * Sets up the save event and handler for the commit_stage dropdown fields in the worksheet.
     *
     * @param field the commit_stage field
     * @return {*}
     * @private
     */
    _setUpCommitStage: function (field) {
        var forecastCategories = this.context.forecasts.config.get("forecast_categories");
        var self = this;
                
        //show_binary, show_buckets, show_n_buckets
        if(forecastCategories == "show_binary"){
            field.type = "bool";
            field.format = function(value){
                return value == "include";
            };
            field.unformat = function(value){
                return this.$el.find(".checkbox").prop('checked') ? "include" : "exclude";
            };
        }
        else{
            field.type = "enum";
            field.def.options = this.context.forecasts.config.get("buckets_dom") || 'commit_stage_dom';
        }      
        
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
            //Set the field.def.options value based on app.config.buckets_dom (if set)
            field.def.options = this.context.forecasts.config.get("buckets_dom") || 'commit_stage_dom';
            //field = this._setUpCommitStage(field);
            if(!this.isEditableWorksheet)
            {
                field.view = 'detail';
            }
        }
        app.view.View.prototype._renderField.call(this, field);

        if (this.isEditableWorksheet === true && field.viewName !="edit" && field.def.clickToEdit === true && !_.contains(this.context.forecasts.config.get("sales_stage_won"), field.model.get('sales_stage')) && !_.contains(this.context.forecasts.config.get("sales_stage_lost"), field.model.get('sales_stage'))) {
            new app.view.ClickToEditField(field, this);
        }

        /*if (this.isEditableWorksheet === true && field.name == "commit_stage") {
            new app.view.BucketGridEnum(field, this, "ForecastWorksheets");
        }*/
    },

    /**
     * Clean up any left over bound data to our context
     */
    unbindData : function() {
        if(this._collection) this._collection.off(null, null, this);
        if(this.context.forecasts) this.context.forecasts.off(null, null, this);
        if(this.context.forecasts.worksheet) this.context.forecasts.worksheet.off(null, null, this);
        app.view.View.prototype.unbindData.call(this);
    },

    /**
     *
     * @param {Object} params
     */
    bindDataChange: function(params) {
        var self = this;
        if (this._collection) {
            this._collection.on("reset", function() {self.render(); }, this);
            this._collection.on("change", function() {
                _.each(this._collection.models, function(element){
                    if(element.hasChanged("commit_stage")) {
                        this.gTable.fnDestroy();
                        this.gTable = this.$('.worksheetTable').dataTable(self.gTableDefs);
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
            this.context.forecasts.on("forecasts:committed:saved", function(){
                if(this.showMe()){
                    var model = this.context.forecasts.worksheet;
                    model.url = this.createURL();
                    this.safeFetch();                   
                    this.mgrNeedsCommitted = true;
                }                
            }, this);
            
            this.context.forecasts.on("forecasts:commitButtons:enabled", function(){
                if(_.isEqual(app.user.get('id'), self.selectedUser.id)){
                    self.commitButtonEnabled = true;
                }
            },this);
            
            this.context.forecasts.on("forecasts:commitButtons:disabled", function(){
                self.commitButtonEnabled = false;
            },this);

            /*
             * // TODO: tagged for 6.8 see SFA-253 for details
            this.context.forecasts.config.on('change:show_worksheet_likely', function(context, value) {
                // only trigger if this component is rendered
                if(!_.isEmpty(self.el.innerHTML)) {
                    self.setColumnVisibility(['likely_case'], value, self);
                }
            });

            this.context.forecasts.config.on('change:show_worksheet_best', function(context, value) {
                // only trigger if this component is rendered
                if(!_.isEmpty(self.el.innerHTML)) {
                    self.setColumnVisibility(['best_case'], value, self);
                }
            });

            this.context.forecasts.config.on('change:show_worksheet_worst', function(context, value) {
                // only trigger if this component is rendered
                if(!_.isEmpty(self.el.innerHTML)) {
                    self.setColumnVisibility(['worst_case'], value, self);
                }
            });
            */
            this.context.forecasts.config.on('change:buckets_dom change:forecast_categories', this.render, this);

            var worksheet = this;
            $(window).bind("beforeunload",function(){
                //if the record is dirty, warn the user.
                if(worksheet._collection.isDirty){
                    return app.lang.get("LBL_WORKSHEET_SAVE_CONFIRM_UNLOAD", "Forecasts");
                }
                //special manager cases for messages
                else if((self.context.forecasts.get("currentWorksheet") == "worksheet") && self.selectedUser.isManager && self.context.forecasts.config.get("show_forecasts_commit_warnings")){
                    /*
                     * If the manager has a draft version saved, but hasn't committed that yet, they need to be shown a dialog that 
                     * lets them know, and gives them the option of committing before the page reloads. This happens if the commit button
                     * is enabled and they are on the rep worksheet.
                     */
                    if(self.commitButtonEnabled ){
                        var msg = app.lang.get("LBL_WORKSHEET_COMMIT_CONFIRM", "Forecasts").split("<br>");
                        //show dialog
                        return msg[0];                                       
                    }
                    else if(self.mgrNeedsCommitted){
                        return app.lang.get("LBL_WORKSHEET_COMMIT_ALERT", "Forecasts");
                    }
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
        var aoColumns = ctx.gTable.fnSettings().aoColumns;

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
     *
     * @param fetch {boolean} Tells the function to go ahead and fetch if true, or runs dirty checks (saving) w/o fetching if false 
     */
    safeFetch: function(fetch){
        //fetch currently already in progress, no need to duplicate
        if(this.fetchInProgress) {
            return;
        }
        //mark that a fetch is in process so no duplicate fetches begin
        this.fetchInProgress = true;
        if(_.isUndefined(fetch))
        {
            fetch = true;
        }
        var collection = this._collection; 
        var self = this;
        
        /*
         * First we need to see if the collection is dirty. This is marked if any of the models 
         * is marked as dirty. This will show the "unsaved changes" dialog
         */
        if(collection.isDirty){
            //unsaved changes, ask if you want to save.
            if(confirm(app.lang.get("LBL_WORKSHEET_SAVE_CONFIRM", "Forecasts"))){
                self.context.forecasts.trigger("forecasts:forecastcommitbuttons:triggerSaveDraft");
            }
            //user clicked cancel, ignore and fetch if fetch is enabled
            else{
                
                collection.isDirty = false;
                self.context.forecasts.set({reloadCommitButton: true});
                if(fetch){
                    collection.fetch();
                }
            }
        }
        /*
         * Next, we need to check to see if the user is a manager.  They have their own requirements and dialogs (those described below)
         */
        else if(self.selectedUser.isManager && (self.context.forecasts.get("currentWorksheet") == "worksheet") && self.context.forecasts.config.get("show_forecasts_commit_warnings")){
            /*
             * If the manager has a draft version saved, but hasn't committed that yet, they need to be shown a dialog that 
             * lets them know, and gives them the option of committing before the page reloads. This happens if the commit button
             * is enabled and they are on the rep worksheet.
             */
            if(self.commitButtonEnabled){
                var msg = app.lang.get("LBL_WORKSHEET_COMMIT_CONFIRM", "Forecasts").split("<br>");
                //show dialog
                if(confirm(msg[0] + "\n\n" + msg[1])){
                    self.context.forecasts.trigger("forecasts:forecastcommitbuttons:triggerCommit");
                }
                //canceled, continue fetching
                else{
                    if(fetch){
                        collection.fetch();
                    }
                }
                    
            }
            else if(self.mgrNeedsCommitted){
                alert(app.lang.get("LBL_WORKSHEET_COMMIT_ALERT", "Forecasts"));
                self.mgrNeedsCommitted = false;
                if(fetch){
                    collection.fetch();
                }
                
            }
            //No popups needed, fetch like normal
            else{
                if(fetch){
                    collection.fetch();
                }
            }
        }
        //default case, fetch like normal
        else{    
            if(fetch){
                collection.fetch();
            }    
        }
        //mark that the fetch is over
        this.fetchInProgress = false;
    },

    /**
     *
     * @param {Object} fields
     * @private
     */
    _setForecastColumn: function(fields) {
        var self = this;

        _.each(fields, function(field) {
            if (field.name == "commit_stage") {
                field.view = self.isEditableWorksheet ? self.name : 'detail';
            }
        });

    },

    /**
     * renders the view
     *
     * @return {Object} this
     * @private
     */
    _render: function() {
        var self = this;
        var enableCommit = false;
        var fields = this.meta.panels[0].fields;
        var columnKeys = {};

        if(!this.showMe()){
            return false;
        }
        $("#view-sales-rep").addClass('show').removeClass('hide');
        $("#view-manager").addClass('hide').removeClass('show');           
        
        this.context.forecasts.set({currentWorksheet: "worksheet"});
        this.isEditableWorksheet = this.isMyWorksheet();
        this._setForecastColumn(this.meta.panels[0].fields);

        app.view.View.prototype._render.call(this);

        // parse metadata into columnDefs
        // so you can sort on the column's "name" prop from metadata

        _.each(fields, function(field, key){
            if(field.enabled)
            {
                var name = field.name;

                var fieldDef = {
                    "sName": name,
                    "aTargets": [ key ],
                    "bVisible" : self.checkConfigForColumnVisibility(field.name)
                };

                if(typeof(field.type) != "undefined")
                {
                    //Apply sorting for the worksheet
                    switch(field.type)
                    {
                        case "enum":
                        case "bool":
                            // disable sorting for non-numerical fields
                            fieldDef["bSortable"] = false;
                            break;
                        case "int":
                        case "currency":
                            fieldDef["sSortDataType"] = "dom-number";
                            fieldDef["sType"] = "numeric";
                            break;
                    }
                    // apply class and width
                    switch(field.name) {
                        case "likely_case":
                            fieldDef["sClass"] = "number likely";
                            fieldDef["sWidth"] = "22%";
                            break;
                        case "best_case":
                            fieldDef["sClass"] = "number best";
                            fieldDef["sWidth"] = "22%";
                            break;
                        case "worst_case":
                            fieldDef["sClass"] = "number worst";
                            fieldDef["sWidth"] = "22%";
                            break;
                        case "probability":
                            fieldDef["sClass"] = "number";
                            break;
                    }
                }

                self.columnDefs.push(fieldDef);
                columnKeys[name] = key;
            }
        });
        this.gTable = this.$('.worksheetTable').dataTable(this.gTableDefs);

        // set dynamic widths on currency columns showing original currency

        var likelyWidths= $('.likely .converted').map(function() {
            return $(this).width();
        }).get();

        var likelyLabelWidths= $('.likely label.original').map(function() {
            return $(this).width();
        }).get();

        var bestWidths= $('.best .converted').map(function() {
            return $(this).width();
        }).get();

        var bestLabelWidths= $('.best label.original').map(function() {
            return $(this).width();
        }).get();

        var worstWidths= $('.worst .converted').map(function() {
            return $(this).width();
        }).get();

        var worstLabelWidths= $('.worst label.original').map(function() {
            return $(this).width();
        }).get();

        $('.likely .converted').width(_.max(likelyWidths));
        $('.likely label.original').width(_.max(likelyLabelWidths));
        $('.best .converted').width(_.max(bestWidths));
        $('.best label.original').width(_.max(bestLabelWidths));
        $('.worst .converted').width(_.max(worstWidths));
        $('.worst label.original').width(_.max(worstLabelWidths));

        // now set table column width from this value
        $('.number .likely').width($('.likely .converted').width()+$('.likely label.original').width());
        $('.number .best').width($('.best .converted').width()+$('.best label.original').width());
        $('.number .worst').width($('.worst .converted').width()+$('.worst label.original').width());

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

        self.calculateTotals();

        // fix the style on the rows that contain a checkbox
        this.$el.find('td:has(span>input[type=checkbox])').addClass('center');
                
        // Trigger event letting other components know worksheet finished rendering
        self.context.forecasts.trigger("forecasts:worksheet:rendered");

        //Check to see if any worksheet entries are older than the source data.  If so, that means that the
        //last commit is older, and that we need to enable the commit buttons
        _.each(this._collection.models, function(model, index){
            if(!_.isEmpty(model.get("w_date_modified")) && (new Date(model.get("w_date_modified")) < new Date(model.get("date_modified")))) {
                enableCommit = true;
            }
        });
        if (enableCommit) {
            self.context.forecasts.trigger("forecasts:commitButtons:enabled");
        }
        return this;
    },

    /**
     * Determines if this Worksheet belongs to the current user, applicable for determining if this view should show,
     * or whether to render the clickToEdit field
     *
     * @return {Boolean} true if it is the worksheet of the logged in user, false if not.
     */
    isMyWorksheet: function() {
        return _.isEqual(app.user.get('id'), this.selectedUser.id);
    },

    /**
     * Determines if this Worksheet should be rendered
     *
     * @return {Boolean} this.show
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
        var self = this,
            includedAmount = 0,
            includedBest = 0,
            includedWorst = 0,
            overallAmount = 0,
            overallBest = 0,
            overallWorst = 0,
            includedCount = 0,
            lostCount = 0,
            lostAmount = 0,
            wonCount = 0,
            wonAmount = 0,
            totalCount = 0;

        if(!this.showMe()){
            // if we don't show this worksheet set it all to zero
            this.context.forecasts.set({
                updatedTotals : {
                    'amount' : includedAmount,
                    'best_case' : includedBest,
                    'worst_case' : includedWorst,
                    'overall_amount' : overallAmount,
                    'overall_best' : overallBest,
                    'overall_worst' : overallWorst,
                    'timeperiod_id' : self.timePeriod,
                    'lost_count' : lostCount,
                    'lost_amount' : lostAmount,
                    'won_count' : wonCount,
                    'won_amount' : wonAmount,
                    'included_opp_count' : includedCount,
                    'total_opp_count' : totalCount
                }
            }, {silent:true});
            return false;
        }

        //Get the excluded_sales_stage property.  Default to empty array if not set
        var sales_stage_won_setting = this.context.forecasts.config.get('sales_stage_won') || [];
        var sales_stage_lost_setting = this.context.forecasts.config.get('sales_stage_lost') || [];

        _.each(self._collection.models, function (model) {
            var won = _.include(sales_stage_won_setting, model.get('sales_stage'))
                lost = _.include(sales_stage_lost_setting, model.get('sales_stage')),
                amount = parseFloat(model.get('likely_case')),
                commit_stage = model.get('commit_stage'),
                best = parseFloat(model.get('best_case')),
                base_rate = parseFloat(model.get('base_rate')),
                worst = parseFloat(model.get('worst_case')),
                worst_base =  app.currency.convertWithRate(worst, base_rate),
                amount_base = app.currency.convertWithRate(amount, base_rate),
                best_base = app.currency.convertWithRate(best, base_rate);

            if(won) {
                wonAmount += amount_base;
                wonCount++;
            } else if(lost) {
                lostAmount += amount_base;
                lostCount++;
            }

            if(commit_stage === 'include') {
                includedAmount += amount_base;
                includedBest += best_base;
                includedWorst += worst_base;
                includedCount++;
            }

            overallAmount += amount_base;
            overallBest += best_base;
            overallWorst += worst_base;
        });


        var totals = {
            'amount' : includedAmount,
            'best_case' : includedBest,
            'worst_case' : includedWorst,
            'overall_amount' : overallAmount,
            'overall_best' : overallBest,
            'overall_worst' : overallWorst,
            'timeperiod_id' : self.timePeriod,
            'lost_count' : lostCount,
            'lost_amount' : lostAmount,
            'won_count' : wonCount,
            'won_amount' : wonAmount,
            'included_opp_count' : includedCount,
            'total_opp_count' : self._collection.models.length
        };
       
        this.context.forecasts.unset("updatedTotals", {silent: true});
        this.context.forecasts.set("updatedTotals", totals);
    },

    /**
     * Event Handler for updating the worksheet by a selected user
     *
     * @param params is always a context
     */
    updateWorksheetBySelectedUser:function (selectedUser) {
        //do a dirty check before fetching. Safe fetch uses selected user for some of its checks, so we need to check
        //things before this.selectedUser is replaced.
        this.safeFetch(false);        
        this.selectedUser = selectedUser;
        if(this.selectedUser && !this.selectedUser){
            return false;
        }
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
        var self = this,
            forecast_categories_setting = this.context.forecasts.config.get('forecast_categories') || 'show_binary';

        // start with no filters, i. e. show everything.
        $.fn.dataTableExt.afnFiltering.splice(0, $.fn.dataTableExt.afnFiltering.length);
        if (!_.isEmpty(params)) {
            $.fn.dataTableExt.afnFiltering.push (
                function(oSettings, aData, iDataIndex) {

                    // This is required to prevent manager view from filtering incorrectly, since datatables does filtering globally
                    if(oSettings.nTable == _.first($('.worksheetManagerTable'))) {
                        return true;
                    }

                    var editable = self.isMyWorksheet(),
                        selectVal,
                        rowCategory = $(_.first(aData)),
                        checkState;

                    //If we are in an editable worksheet get the selected dropdown/checkbox value; otherwise, get the detail/default text
                    if (forecast_categories_setting == 'show_binary') {
                        checkState = rowCategory.find('input').attr('checked');
                        selectVal = ((checkState == "checked") || (checkState == "on") || (checkState == "1")) ? 'include' : 'exclude';
                    } else {
                        selectVal = editable ? rowCategory.find("select").attr("value") : rowCategory.text().trim().toLowerCase();
                    }

                    self.context.forecasts.trigger('forecasts:worksheet:filtered');

                    return (_.contains(params, selectVal));

                }
            );
        }
        if(!_.isUndefined(this.gTable.fnDestroy)){
            this.gTable.fnDestroy();
            this.gTable = this.$('.worksheetTable').dataTable(self.gTableDefs);
            // fix the style on the rows that contain a checkbox
            this.$el.find('td:has(span>input[type=checkbox])').addClass('center');
            this.context.forecasts.trigger('forecasts:worksheet:filtered');
        }
    },

    /**
     * Event Handler for updating the worksheet by a timeperiod id
     *
     * @param {Object} params is always a context
     */
    updateWorksheetBySelectedTimePeriod:function (params) {
        this.timePeriod = params.id;
        if(!this.showMe()){
            return false;
        }
        this._collection.url = this.createURL();
        this.safeFetch(true);
    },

    /**
     * Formats the additional details div when a user clicks a row in the grid
     *
     * @param {Object} dRow the row from the datagrid that user has clicked on
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
     * @param {Object} dTable datatable param so we can grab all the column headings from it
     * @param {Boolean} onlyVisible -OPTIONAL, defaults true- if we want to return only visible column headings or not
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
     *
     * @param {String} columnName the column sName you're checking for.  NOT the Column sTitle/heading
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
