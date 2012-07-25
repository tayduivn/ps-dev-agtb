/**
 * View that displays header for current app
 * @class View.Views.WorksheetView
 * @alias SUGAR.App.layout.WorksheetView
 * @extends View.View
 */
({

    url: 'rest/v10/ForecastSchedule',
    viewSelector: '.forecastSchedule',
    show: false,
    viewModule: {},
    selectedUser: {},
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
        model.save(null, { success:_.bind(function() { self.render(); }, this)});
    },

    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);
        this._collection = this.context.forecasts.forecastschedule;
        this.selectedUser = app.user;
        this.timePeriodId = app.defaultSelections.timeperiod_id.id;
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
     * Renders a field.
     *
     * This method sets field's view element and invokes render on the given field.  If clickToEdit is set to true
     * in metadata, it will also render it as clickToEditable.
     * @param {View.Field} field The field to render
     * @protected
     */
    _renderField: function(field) {

        app.view.View.prototype._renderField.call(this, field);


        if (this.isMyWorksheet() && field.def.clickToEdit === true) {
            new app.view.ClickToEditField(field, this);
        }

        if( this.isMyWorksheet() && field.name == "commit_stage") {
            new app.view.BucketGridEnum(field, this);
        }

    },

    bindDataChange: function(params) {
        var self = this;
        this._collection = this.context.forecasts.forecastschedule;
        this._collection.on("reset", function() { self.render() }, this);

        if (this.context.forecasts) {
            this.context.forecasts.on("change:selectedUser",
                function(context, selectedUser) {
                    this.updateWorksheetBySelectedUser(selectedUser);
                }, this);
            this.context.forecasts.on("change:selectedTimePeriod",
                function(context, timePeriod) {
                    this.updateWorksheetBySelectedTimePeriod(timePeriod);
                }, this);
        }
    },

    _setForecastColumn: function(fields) {
        var self = this;
        var forecastField, commitStageField;
        var isOwner = self.isMyWorksheet();

        _.each(fields, function(field) {
            if (field.name == "forecast") {
                field.enabled = !app.config.showBuckets;
                forecastField = field;
            } else if (field.name == "commit_stage") {
                field.enabled = app.config.showBuckets;
                if(!isOwner)
                {
                   field.view = 'default';
                }
                commitStageField = field;
            }
        });
        return app.config.showBuckets?forecastField:commitStageField;
    },


    _renderHtml: function(ctx, options) {
        if (this.template) {
            try {
                this.$el = $("expected_opportunities");
                debugger;
                this.$el.html(this.template(ctx || this, options || this.options.templateOptions));
                this.delegateEvents();
            } catch (e) {
                app.logger.error("Failed to render " + this + "\n" + e);
                // TODO: trigger app event to render an error message
            }
        }
    },

    /*
    _render: function () {
        if(!this.showMe())
        {
        	return false;
        }
        var self = this;
        var unusedField = this._setForecastColumn(this.meta.panels[0].fields);
        app.view.View.prototype._render.call(this);
        var source = $("#expected_template").html();
        var hb = Handlebars.compile(source);
        $("#expected_opportunities").html(hb(self._collection.toJSON()));
        return this;
    },
    */

    /**
     * Add a click event listener to the commit button
     */
    events: {
        "div a[id=include_expected]" : "includeExpected"
    },

    /**
     * Function to handle the toggle state of including/excluding expected amounts
     */
    includeExpected: function() {
        debugger;
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
     * Event Handler for updating the worksheet by a selected user
     *
     * @param params is always a context
     */
    updateWorksheetBySelectedUser:function (selectedUser) {
        this.selectedUser = selectedUser;
        if(!this.showMe())
        {
        	return false;
        }
        this._collection.url = this.createURL();
        this._collection.fetch();
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
        this._collection.fetch();
    }

})

