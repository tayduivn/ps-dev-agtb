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
    fetchCollection: function()
    {
        this._collection.url = this.createURL();
        var self = this;
        this._collection.fetch({success : function() { self.render() } });
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

        if(this.isMyWorksheet())
        {
            if (field.def.clickToEdit === true) {
                new app.view.ClickToEditField(field, this);
            }

            if (field.name == "commit_stage") {
                new app.view.BucketGridEnum(field, this);
            }
        }
    },

    bindDataChange: function(params) {
        var self = this;
        this._collection = this.context.forecasts.forecastschedule;

        if (this._collection) {
            //this._collection.on("reset", function() { self.render() }, this);

            this._collection.on("change:include_expected", function() {
                _.each(this._collection.models, function(model) {
                    if(model.hasChanged("include_expected")) {
                       model.url = this.url;
                       if(model.get('id') && model.url.indexOf(model.get('id')) === -1)
                       {
                           model.url += "/" + model.get('id');
                       }
                       model.save();
                    }
                }, this);
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

    /**
     * Determines if this Worksheet belongs to the current user, applicable for determining if this view should show,
     * or whether to render the clickToEdit field
     * @return {Boolean} true if it is the worksheet of the logged in user, false if not.
     */
    isMyWorksheet: function() {
        return _.isEqual(app.user.get('id'), this.selectedUserId);
    }

})

