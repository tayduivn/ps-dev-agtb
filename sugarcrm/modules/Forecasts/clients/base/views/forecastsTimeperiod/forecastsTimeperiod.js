/**
 * View that displays committed forecasts for current user.  If the manager view is selected, the Forecasts
 * of Rollup type are shown; otherwise the Forecasts of Direct type are shown.
 *
 * @class View.Views.ForecastsTimeperiod
 * @alias SUGAR.App.layout.ForecastsTimeperiod
 * @extends View.View
 */
({
    /**
     * the timeperiod field metadata that gets used at render time
     */
    timeperiod: {},

    initialize : function(options) {
        app.view.View.prototype.initialize.call(this, options);

        this.timePeriodId = app.defaultSelections.timeperiod_id.id;

        _.each(this.meta.panels, function(panel) {
            this.timeperiod = _.find(panel.fields, function (item){
                return _.isEqual(item.name, 'timeperiod');
            });
        }, this);
    },


    /**
     * Overriding _renderField because we need to set up the events to set the proper value depending on which field is
     * being changed.
     * @param field
     * @protected
     */
    _renderField: function(field) {
        if (field.name == "timeperiod") {
            field = this._setUpTimeperiodField(field);
        }
        app.view.View.prototype._renderField.call(this, field);
    },

    /**
     * Sets up the save event and handler for the dropdown fields in the timeperiod view.
     * @param field the commit_stage field
     * @return {*}
     * @private
     */
    _setUpTimeperiodField: function (field) {

        field.events = _.extend({"change select": "_updateSelections"}, field.events);
        field.bindDomChange = function() {};

        /**
         * updates the selection when a change event is triggered from a dropdown
         * @param event the event that was triggered
         * @param input the (de)selection
         * @private
         */
        field._updateSelections = function(event, input) {
            var label = this.$el.find('option:[value='+input.selected+']').text();
            //Set the default selection so that when render is called on the view it will use the newly selected value
            app.defaultSelections.timeperiod_id.id = input.selected;
            this.view.context.forecasts.set('selectedTimePeriod', {"id": input.selected, "label": label});
            // make it close the container to act like a normal dropdown
            this.$el.find('div.chzn-container-active').removeClass('chzn-container-active');
        };

        // INVESTIGATE: Should this be retrieved from the model, instead of directly?
        app.api.call("read", app.api.buildURL("Forecasts", "timeperiod"), '', {success: function(results) {
            this.field.def.options = results;
            if(!this.field.disposed) {
                this.field.render();
            }
        }}, {field: field, view: this});

        field.def.value = app.defaultSelections.timeperiod_id.id;
        return field;
    }

})
