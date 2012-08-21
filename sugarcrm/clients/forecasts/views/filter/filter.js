/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.FilterView
 * @alias SUGAR.App.layout.FilterView
 * @extends View.View
 */
({
    /**
     * Store the created fields by name in an array
     */
    fields: [],

    /**
     * Initialize because we need to set the selectedUser variable
     * @param options
     */
    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);
        this.selectedUser = {id: app.user.get('id'), isManager:app.user.get('isManager'), showOpps : false};
    },

    /**
     * Watch for the selectedUser Change
     */
    bindDataChange: function() {

        var self = this;

        if(this.context && this.context.forecasts) {
            this.context.forecasts.on("change:selectedUser", function(context, user) {
                self.selectedUser = user;
                this.toggleCategoryFieldVisibility();
            }, this);
        }
    },

    /**
     * Method to toggle the field visibility of the group by field
     */
    toggleCategoryFieldVisibility: function() {
        if(!_.isUndefined(this.fields['category']) && this.selectedUser.isManager && this.selectedUser.showOpps === false) {
            this.fields['category'].$el.hide();
        } else {
            this.fields['category'].$el.show();
        }
    },

    /**
     * Overriding _renderField because we need to determine whether the config settings are set to show buckets or
     * binary for forecasts and adjusts the category filter accordingly
     * @param field
     * @private
     */
    _renderField: function(field) {
        if (field.name == 'category') {
            field.def.options = app.config.show_buckets?'commit_stage_dom':'forecasts_filters_category';
//            field.value = "70"; // INVESTIGATE: this should work to set the value of the select field, but it is getting reset somewhere in sidecar processing
            field.def.value = "70"; // INVESTIGATE:  this needs to be more dynamic and deal with potential customizations based on how filters are built in admin and/or studio
            field.def.multi = app.config.show_buckets;
            field = this._setUpCategoryField(field);
        }
        app.view.View.prototype._renderField.call(this, field);

        this.fields[field.name] = field;
    },

    /**
     * Override the render to have call the group by toggle
     *
     * @private
     */
    _render : function() {
        app.view.View.prototype._render.call(this);

        // toggle the visibility of the group by field for the initial render
        this.toggleCategoryFieldVisibility();

        return this;
    },

    /**
     * Sets up the save event and handler for the commit_stage dropdown fields in the worksheet.
     * @param field the commit_stage field
     * @return {*}
     * @private
     */
    _setUpCategoryField: function (field) {

        field.events = _.extend({"change select": "_updateSelections"}, field.events);
        field.bindDomChange = function() {};

        /**
         * updates the selection when a change event is triggered from a dropdown/multiselect
         * @param event the event that was triggered
         * @param input the (de)selection
         * @private
         */
        field._updateSelections = function(event, input) {
            var selectedCategory = this.context.forecasts.get("selectedCategory");
            var selectElement = this.$el.find("select");
            var id;

            if (!_.isArray(selectedCategory)) {
                selectedCategory = new Array();
            }

            if(this.def.multi) { // if it's a multiselect we need to add or drop the correct values from the filter model
                if (_.has(input, "selected")) {
                    id = input.selected;
                    if (!_.has(selectedCategory, id)) {
                        selectedCategory.push(id);
                    }
                } else if(_.has(input, "deselected")) {
                    id = input.deselected;
                    if (_.has(selectedCategory, id)) {
                        selectedCategory = _.without(selectedCategory, id);
                    }
                }
            } else {  // not multi, just set the selected filter
                selectedCategory = new Array(input.selected);
            }
            this.view.context.forecasts.set('selectedCategory', selectedCategory);
        };

        return field;
    }
})
