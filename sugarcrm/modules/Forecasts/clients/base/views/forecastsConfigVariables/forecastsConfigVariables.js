({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        if(!_.isUndefined(options.meta.registerLabelAsBreadCrumb) && options.meta.registerLabelAsBreadCrumb == true) {
            this.layout.registerBreadCrumbLabel(options.meta.panels[0].label);
        }
    },
    /**
     * Overriding _renderField because we need to set up the multiselect fields to work properly
     * @param field
     * @private
     */
    _renderField: function(field) {
        if (field.def.multi) {
            field = this._setUpMultiselectField(field);
        }
        app.view.View.prototype._renderField.call(this, field);
    },

    /**
     * Sets up the save event and handler for the variables dropdown fields in the config settings.
     * @param field the dropdown multi-select field
     * @return {*}
     * @private
     */
    _setUpMultiselectField: function (field) {
        // INVESTIGATE:  This is to get around what may be a bug in sidecar. The field.value gets overriden somewhere and it shouldn't.
        field.def.value = this.model.get(field.name);

        field.events = _.extend({"change select": "_updateSelections"}, field.events);

        field.bindDomChange = function() {};

        /**
         * updates the selection when a change event is triggered from a dropdown/multiselect
         * @param event the event that was triggered
         * @param input the (de)selection
         * @private
         */
        field._updateSelections = function(event, input) {
            var fieldValue = this.model.get(this.name);
            var id;

            if (_.has(input, "selected")) {
                id = input.selected;
                if (!_.contains(fieldValue, id)) {
                    fieldValue = _.union(fieldValue, id);
                }
            } else if(_.has(input, "deselected")) {
                id = input.deselected;
                if (_.contains(fieldValue, id)) {
                    fieldValue = _.without(fieldValue, id);
                }
            }
            this.def.value = fieldValue;
            this.model.set(this.name, fieldValue);
        };

        return field;
    }
})