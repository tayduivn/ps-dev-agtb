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

        if(this.editableWorksheet === true)
        {
            if (field.def.clickToEdit === true) {
                new app.view.ClickToEditField(field, this);
            }

            if (field.name == "expected_commit_stage") {
                new app.view.BucketGridEnum(field, this);
            }
        }
    },

    bindDataChange: function(params) {
        var self = this;
        this._collection = this.context.forecasts.forecastschedule;

        if (this._collection) {
            //this._collection.on("reset", function() { self.render() }, this);

            this._collection.on("change", function() {
                _.each(this._collection.models, function(model, index) {
                    if(model.hasChanged("include_expected")) {
                        this._collection.url = this.url;
                        model.save();
                       }
                    if(model.hasChanged("expected_commit_stage")) {
                        if(model.get("expected_commit_stage") == '100') {
                            this._collection.models[index].set("include_expected", '1');
                        } else {
                            this._collection.models[index].set("include_expected", '0');
                        }
                        this._collection.url = this.url;
                       model.save();
                    }
                }, this);
            }, this);
        }
    },

    _setForecastColumn: function(fields) {
        var self = this;
        var forecastField, commitStageField;

        _.each(fields, function(field) {
            if (field.name == "include_expected") {
                field.enabled = !app.config.show_buckets;
                forecastField = field;
            } else if (field.name == "expected_commit_stage") {
                field.enabled = app.config.show_buckets;
                field.options = app.config.buckets_dom || 'commit_stage_dom';
                field.view = (self.editableWorksheet === true) ? 'edit' : 'default';
                commitStageField = field;
            }
        });
        return app.config.show_buckets ? forecastField : commitStageField;
    },

    /**
     * Renders view
     *
     * @protected
     */
    _renderHtml : function(ctx, options) {
        this._setForecastColumn(this.meta.panels[0].fields);
        app.view.View.prototype._renderHtml.call(this, ctx, options);
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

