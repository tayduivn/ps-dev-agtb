/**
 * View that displays expected opportunities
 * @extends View.View
 */
({

    url: 'rest/v10/ForecastSchedule',
    tagName: 'tr',
    class: 'view-forecastSchedule',
    id: 'expected_opportunities',
    viewModule: {},
    selectedUser: {},
    selectedUserId: null,
    timePeriodId: null,
    editableWorksheet: false,
    _collection:{},
    fieldsMeta: {},
    show_worksheet_likely: false,
    show_worksheet_best: false,
    show_worksheet_worst: false,
    expected_amount_field: {},
    expected_best_case_field: {},
    expected_worst_case_field: {},
    expected_commit_stage_field: {},
    isBinary: true,

    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);
        this.selectedUserId = options.user_id ? options.user_id : app.user.get('id');
        this.timePeriodId = options.timeperiod_id ? options.timeperiod_id : app.defaultSelections.timeperiod_id.id;
        this._collection = this.context.forecasts.forecastschedule;
        this._collection.url = this.createURL();
        this.fieldsMeta = _.first(this.meta.panels).fields;

        // get the config values to determine whether a field should be shown or not.
        _.each(['show_worksheet_likely', 'show_worksheet_best', 'show_worksheet_worst'], function(item) {
            this[item] = options.context.forecasts.config.get(item);
        }, this);

        // sets this.<array_item>_field to the corresponding field metadata, which gets used by the template to render these fields later.
        _.each(['expected_amount', 'expected_best_case', 'expected_worst_case', 'expected_commit_stage'], function(item) {
            this[item + '_field'] = function(fieldName, fieldMeta) {
                return _.find(fieldMeta, function(field) { return field.name == this; }, fieldName);
            }(item, this.fieldsMeta);
        }, this);
    },

    createURL : function() {
        var args = {};
        args.timeperiod_id = this.timePeriodId;
        args.user_id = this.selectedUserId;
        return app.api.buildURL('ForecastSchedule', null, null, args);
    },

    /**
     * This is a helper function to fetch the collection given the existing filters for timeperiod and selected user
     */
    fetchCollection: function(callback)
    {
        this._collection.url = this.createURL();
        var self = this;
        this._collection.fetch({success : function() { 
            self.render();
            if(_.isFunction(callback)){
                callback();
            }
        }});
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
    	if(field.name == "expected_commit_stage")
        {
            //Set the field.def.options value based on buckets_dom setting (if set)
            field.def.options = this.context.forecasts.config.get("buckets_dom") || 'commit_stage_dom';
            if(this.editableWorksheet)
            {               
               field = this._setUpCommitStage(field);
            }
        }
    	
    	app.view.View.prototype._renderField.call(this, field);
        
    	if (this.editableWorksheet === true && field.def.clickToEdit === true) {
            new app.view.ClickToEditField(field, this);
        }

        if (this.editableWorksheet === true && field.name == "expected_commit_stage") {
            new app.view.BucketGridEnum(field, this, "ForecastSchedule");
        }
     
    },

    bindDataChange: function(params) {
        var self = this;
        this._collection = this.context.forecasts.forecastschedule;

        if (this._collection) {
            this._collection.on("change", function() {
            	self.context.forecast.trigger("forecasts:commitButtons:enabled");
                _.each(this._collection.models, function(model, index) {
                    if(model.hasChanged("expected_commit_stage") || model.hasChanged("expected_amount") || model.hasChanged("expected_best_case") || model.hasChanged("expected_worst_case")) {
                       this._collection.url = this.url;
                       model.save();
                       self.context.forecasts.set('expectedOpportunities', model);
                    }
                }, this);
            }, this);

            // listen for any of the show_worksheet_ config settings, and listen for the worksheet to re-render
            this.context.forecasts.config.on('change:show_worksheet_likely change:show_worksheet_best change:show_worksheet_worst', function(context, value) {
                self.show_worksheet_likely = context.get('show_worksheet_likely') == 1;
                self.show_worksheet_best = context.get('show_worksheet_best') == 1;
                self.show_worksheet_worst = context.get('show_worksheet_worst') == 1;
                self._render();
            });
        }
    },

    _setForecastColumn: function(fields) {
        var self = this;

        _.each(fields, function(field) {
            if (field.name == "expected_commit_stage") {
                field.view = self.editableWorksheet ? self.name : 'detail';
                var forecastCategories = self.context.forecasts.config.get("forecast_categories");

                //show_binary, show_buckets, show_n_buckets
                if(forecastCategories == "show_binary"){
                    field.type = 'bool';
                }
            } else {
                field.enabled = app.forecasts.utils.getColumnVisFromKeyMap(field.name, self.name, self.context.forecasts.config);
            }
        });
    },

    _setUpCommitStage: function(field) {
    	var forecastCategories = this.context.forecasts.config.get("forecast_categories");
    	var self = this;

    	//show_binary, show_buckets, show_n_buckets
    	if(forecastCategories == "show_binary"){
            this.isBinary = true;
    		field.type = "bool";
    					
    		field.format = function(value){
    			return value == "include";
    		};
    		field.unformat = function(value){
    			return this.$el.find(".checkbox").prop("checked") ? "include" : "exclude";
    		};
    	}
    	else{
            this.isBinary = false;
    		field.type = "enum";
    		field.def.options = this.context.forecasts.config.get("buckets_dom") || 'commit_stage_dom';
    	}  	
    	
        return field;
    },
    
    /**
     * Renders view
     *
     * @protected
     */
    _render: function() {
        if(this.context.forecasts.get('currentWorksheet') == 'worksheet') {
            this.editableWorksheet = this.isMyWorksheet();
            this._setForecastColumn(this.fieldsMeta);
            app.view.View.prototype._render.call(this);
        }
        return this;
    },

    /**
     * Determines if this Worksheet belongs to the current user, applicable for determining if this view should show,
     * or whether to render the clickToEdit field
     * @return {Boolean} true if it is the worksheet of the logged in user, false if not.
     */
    isMyWorksheet: function() {
        return _.isEqual(app.user.get('id'), this.selectedUserId);
    }

})

