/**
 * View that displays the chart options for forecasts module
 * @class View.Views.ChartOptionsView
 * @alias SUGAR.App.layout.ChartOptionsView
 * @extends View.View
 */
({

    /**
     * Current Selected User
     */
    selectedUser: null,

    /**
     * Fields created in this class
     */
    fields : [],

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
                self.selectedUser = {id: user.id, "isManager":user.isManager, showOpps: user.showOpps};
                this.toggleGroupByFieldVisibility();
            }, this);
        }
    },

    /**
     * Method to toggle the field visibility of the group by field
     */
    toggleGroupByFieldVisibility: function() {
        if(!_.isUndefined(this.fields['group_by']) && this.selectedUser.isManager && this.selectedUser.showOpps === false) {
            this.fields['group_by'].$el.hide();
        } else {
            this.fields['group_by'].$el.show();
        }
    },

    /**
     * Overriding _renderField because we need to set up the events to set the proper value depending on which field is
     * being changed.
     * binary for forecasts and adjusts the category filter accordingly
     * @param field
     * @private
     */
    _renderField: function(field) {
        field = this._setUpCategoryField(field);
        app.view.View.prototype._renderField.call(this, field);

        // save the fields for later use
        this.fields[field.name] = field;

        // toggle the visibility of the group by field for the initial render
        this.toggleGroupByFieldVisibility();
    },

    /**
     * Sets up the save event and handler for the  dropdown fields in the chart options view.
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
        field._updateSelections = function(fieldName) {
            var contextMap = {
                group_by: 'selectedGroupBy',
                dataset: 'selectedDataSet'
            };
            return function(event, input) {
                this.view.context.forecasts.set(contextMap[fieldName], input.selected);
            };
        }(field.name);

        return field;
    }

})
