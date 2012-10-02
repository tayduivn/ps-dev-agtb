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
            	self.context.forecasts.set({commitButtonEnabled: true});
                _.each(this._collection.models, function(model, index) {

                    if(model.hasChanged("expected_commit_stage") || model.hasChanged("expected_amount") || model.hasChanged("expected_best_case") || model.hasChanged("expected_worst_case")) {
                       this._collection.url = this.url;
                       model.save();
                    }

                }, this);
            }, this);
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

            		_.each(self.meta.panels[0].fields, function(meta){
            			if(meta.name == "expected_commit_stage"){
            				meta.type="bool";
            			}
            		});	          		            		
            	}
            }
        });

    },

    _setUpCommitStage: function(field) {
    	var forecastCategories = this.context.forecasts.config.get("forecast_categories");
    	var self = this;

    	//show_binary, show_buckets, show_n_buckets
    	if(forecastCategories == "show_binary"){
    		field.type = "bool";
    					
    		field.format = function(value){
    			return (value=="include") ? true : false;
    		};
    		field.unformat = function(value){
    			return this.$el.find(".checkbox").prop("checked") ? "include" : "exclude";
    		};
    	}
    	else{
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
        this.editableWorksheet = this.isMyWorksheet();
        this._setForecastColumn(this.meta.panels[0].fields);
        app.view.View.prototype._render.call(this);
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

