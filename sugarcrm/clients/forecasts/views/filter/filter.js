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
                field.value = "70";
                field.def.value = "70";
            } else {
                field.def.options = 'forecasts_filters_category';
                field.value = "Committed"; //this should work to set the value of the select field, but it is getting reset somewhere in sidecar processing
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
        // TODO: this may need to go in bindDomChange instead of a new function...
        field._updateSelections = function(event, input) {
            var selectElement = this.$el.find("select");

            if(this.def.multi) { // if it's a multiselect we need to add or drop the correct values from the filter model
                // TODO:  This needs to be implemented across all views affected by filters, based on the decisions made for
                //  how buckets get defined.
                if (_.has(input, "selected")) {
                    var id = input.selected;
                } else if(_.has(input, "deselected")) {
                }
            } else {  // not multi, just set the selected filter
                var id = input.selected;
//                this.view.context.forecasts.filters.set('category', id);
                // TODO:  This needs to be fixed throughout to be {id: label} or something similar to be in line with buckets
                this.view.context.forecasts.set('selectedCategory', {id: id, label:app.lang.getAppListStrings(this.def.options)[id]});
                selectElement.val(id);
            }
        };

        field.events = _.extend({"change select": "_updateSelections"}, field.events);
        return field;
    }
})
