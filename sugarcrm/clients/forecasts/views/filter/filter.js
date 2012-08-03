/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.FilterView
 * @alias SUGAR.App.layout.FilterView
 * @extends View.View
 */
({

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.model = this.context.forecasts.filters;
    },

    /**
     * Determines whether the config settings are set to show buckets or binary for forecasts and adjusts the
     * category filter accordingly
     * @param field
     * @private
     */
    _renderField: function(field) {
        if (field.name == 'category') {
            if (app.config.showBuckets) {
                field.def.options = 'commit_stage_dom';
                field.def.value = "70";
            } else {
                field.def.options = 'forecasts_filters_category';
                field.def.value = "Committed";
            }
            field.def.multi = app.config.showBuckets;
            field = this._setUpCategoryField(field);
        }
        app.view.View.prototype._renderField.call(this, field);
    },

    /**
     * Sets up the save event and handler for the commit_stage dropdown fields in the worksheet.
     * @param field the commit_stage field
     * @return {*}
     * @private
     */
    _setUpCategoryField: function (field) {
        /**
         * updates the selection when a change event is triggered from a dropdown/multiselect
         * @param event the event that was triggered
         * @param input the (de)selection
         * @private
         */
        field._updateSelections = function(event, input) {
            // TODO:  this
        };
        field.events = _.extend({"change select": "_updateSelections"}, field.events);
        return field;
    }
})
