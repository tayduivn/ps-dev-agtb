/**
 * View that displays the timeframe options for forecasts module
 * @class View.Views.ForecastsTimeframesView
 * @alias SUGAR.App.layout.ForecastsTimeframesView
 * @extends View.View
 */
({

    /**
     * Overriding _renderField because we need to set up the events to set the proper value depending on which field is
     * being changed.
     * binary for forecasts and adjusts the category filter accordingly
     * @param field
     * @protected
     */
    _renderField: function(field) {
        if (field.name == "timeframes") {
            field = this._setUpTimeframeField(field);
        }
        app.view.View.prototype._renderField.call(this, field);
    },

    /**
     * Sets up the save event and handler for the dropdown fields in the timeframe view.
     * @param field the commit_stage field
     * @return {*}
     * @private
     */
    _setUpTimeframeField: function (field) {
        var timeframes;

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
            var id = this.$el.find('option:[value='+input.selected+']').val();
            this.context.forecasts.set('selectedTimePeriod', {"id": id, "label": label});

//            this.view.context.forecasts.set("selectedTimePeriod", input.selected);
        };

        // INVESTIGATE: Should this be retrieved from the model, instead of directly?
        app.api.call("read", this.context.forecasts.timeframes.url, '', {success: function(results) {
            this.field.def.options = results;
            this.field.render();
        }}, {field: field, view: this});

        field.def.value = app.defaultSelections.timeperiod_id.id;

        return field;
    }

})
