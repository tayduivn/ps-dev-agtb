/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.ForecastsFilterView
 * @alias SUGAR.App.layout.FilterView
 * @extends View.View
 */
({
    /**
     * Store the created fields by name in an array
     */
    fields:[],

    events:{
        'focus .chzn-container input': 'dropFocus',
        'click .chzn-container .chzn-drop' : 'chznClick',
        'click .chzn-select-legend': 'chznContainerClick'
    },

    dropFocus:function (evt) {

        var el = $(evt.target).parents('.chzn-container').find('.chzn-drop');
        var left = el.css('left');
        if (left == "-9000px") {
            el.width(0);
        } else {
            el.width(100).css("left", "auto").css("right", "0px");
        }
    },

    chznClick: function(evt) {
        $(evt.target).css("right","auto");
    },

    /**
     * handler for click event on filter
     * @param evt
     */
    chznContainerClick: function (evt)
    {
        var chosen = this.fields.category.$el.find('select').data('chosen');
        chosen.results_toggle();
    },

    /**
     * Initialize because we need to set the selectedUser variable
     * @param options
     */
    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);
        this.selectedUser = {id:app.user.get('id'), isManager:app.user.get('isManager'), showOpps:false};
    },

    /**
     * Clean up any left over bound data to our context
     */
    unbindData : function() {
        if(this.context.forecasts) this.context.forecasts.off(null, null, this);
        app.view.View.prototype.unbindData.call(this);
    },

    /**
     * Watch for the selectedUser Change
     */
    bindDataChange:function () {

        var self = this;

        if (this.context && this.context.forecasts) {
            this.context.forecasts.on("change:selectedUser", function (context, user) {
                self.selectedUser = user;
                this.toggleCategoryFieldVisibility();
            }, this);
        }
    },

    /**
     * Method to toggle the field visibility of the group by field
     */
    toggleCategoryFieldVisibility:function () {
        if (!_.isUndefined(this.fields['category']) && this.selectedUser.isManager && this.selectedUser.showOpps === false) {
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
    _renderField:function (field) {
        if (field.name == 'category') {
            field.def.options = this.context.forecasts.config.get('buckets_dom') || 'show_binary_dom';
            field.def.value = this.context.forecasts.has("selectedCategory") ? this.context.forecasts.get("selectedCategory") : app.defaultSelections.category;
            field = this._setUpCategoryField(field);
        }
        app.view.View.prototype._renderField.call(this, field);

        field.$el.find('.chzn-container').css("width", "100%");
        field.$el.find('.chzn-choices').prepend('<legend class="chzn-select-legend">Filter <i class="icon-caret-down"></i></legend>');
        field.$el.find('.chzn-results li').after("<span class='icon-ok' />");

        // override default behavior of chosen - @see #58125
        var chosen = field.$el.find('select').data('chosen');
        chosen.container_mousedown = function(){};

        this.fields[field.name] = field;
    },

    /**
     * Override the render to have call the group by toggle
     *
     * @private
     */
    _render:function () {
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
    _setUpCategoryField:function (field) {

        field.events = _.extend({"change select":"_updateSelections"}, field.events);
        field.bindDomChange = function () {
        };

        /**
         * updates the selection when a change event is triggered from a dropdown/multiselect
         * @param event the event that was triggered
         * @param input the (de)selection
         * @private
         */
        field._updateSelections = function (event, input) {
            var selectedCategory = this.context.forecasts.get("selectedCategory");
            var selectElement = this.$el.find("select");
            var id;

            if (!_.isArray(selectedCategory)) {
                selectedCategory = new Array();
            }

            if (this.def.multi) { // if it's a multiselect we need to add or drop the correct values from the filter model
                if (_.has(input, "selected")) {
                    id = input.selected;
                    if (!_.contains(selectedCategory, id)) {
                        selectedCategory = _.union(selectedCategory, id);
                    }
                } else if (_.has(input, "deselected")) {
                    id = input.deselected;
                    if (_.contains(selectedCategory, id)) {
                        selectedCategory = _.without(selectedCategory, id);
                    }
                }
            } else {  // not multi, just set the selected filter
                selectedCategory = new Array(input.selected);
            }
            this.context.forecasts.set('selectedCategory', selectedCategory);
        };

        return field;
    }
})
