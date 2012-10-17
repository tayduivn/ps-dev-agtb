({
    /**
     * used to hold the label string from metadata to get rendered in the template.
     */
    label: '',

    /**
     * used to hold the metadata for the forecasts_categories field, used to manipulate and render out as the radio buttons
     * that correspond to the fieldset for each bucket type.
     */
    forecast_categories_field: {},

    /**
     * Used to hold the buckets_dom field metadata, used to retrieve and set the proper bucket dropdowns based on the
     * selection for the forecast_categories
     */
    buckets_dom_field: {},

    /**
     * Used to hold the category_ranges field metadata, used for rendering the sliders that correspond to the range
     * settings for each of the values contained in the selected buckets_dom dropdown definition.
     */
    category_ranges_field: {},

    /**
     * Used to keep track of the selection as it changes so that it can be used to determine how to hide and show the
     * sub-elements that contain the fields for setting the category ranges
     */
    selection: '',

    /**
     * Initializes the view, and then initializes up the parameters for the field metadata holder parameters that get
     * used to render the fields in the view, since they are not rendered in a standard way.
     * @param options
     */
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        this.label = _.first(this.meta.panels).label;

        // sets this.<array_item>_field to the corresponding field metadata, which gets used by the template to render these fields later.
        _.each(['forecast_categories', 'buckets_dom', 'category_ranges'], function(item){
            var fields = _.first(this.meta.panels).fields;

            this[item + '_field'] = function(fieldName, fieldMeta) {
                return _.find(fieldMeta, function(field) { return field.name == this; }, fieldName);
            }(item, fields);

        }, this);

        // set the values for forecast_categories_field and buckets_dom_field from the model, so it can be set to selected properly when rendered
        this.forecast_categories_field.value = this.model.get('forecast_categories');
        this.buckets_dom_field.value = this.model.get('buckets_dom');

        if(!_.isUndefined(options.meta.registerLabelAsBreadCrumb) && options.meta.registerLabelAsBreadCrumb == true) {
            this.layout.registerBreadCrumbLabel(options.meta.panels[0].label);
        }
    },

    _renderHtml: function(ctx, options) {
        app.view.View.prototype._renderHtml.call(this, ctx, options);

        this._addForecastCategorySelectionHandler();
    },

    /**
     * Adds the selection event handler on the forecast category radio which sets on the model the value of the bucket selection, the
     * correct dropdown list based on that selection, as well as opens up the element to show the range setting sliders
     * @private
     */
    _addForecastCategorySelectionHandler: function (){
        var element = this.$el.find(':radio[name="' + this.forecast_categories_field.name + '"]');

        element.change({
            view:this
        }, this.selectionHandler);

        // manually trigger the handler so that it will render for the default/previously set value
        element.triggerHandler("change");
    },

    selectionHandler: function(event) {
        var view = event.data.view,
            oldValue,
            bucket_dom,
            hideElement, showElement;

        // get the value of the previous selection so that we can hide that element
        oldValue = view.selection;
        // now set the new selection, so that if they change it, we can later hide the things we are about to show.
        view.selection = this.value;

        bucket_dom = view.buckets_dom_field.options[this.value];

        hideElement = view.$el.find('#' + oldValue + '_ranges');
        showElement = view.$el.find('#' + this.value + '_ranges');

        if (showElement.children().length == 0) {
            // add the things here...
            _.each(app.lang.getAppListStrings(bucket_dom), function(label, key) {
                // TODO: use a text input, for now, this will be replaced by a range field slider
                var ranges,
                    minVal, maxVal,
                    callbackCtx;
                var handler = function(event) {
                    var view = event.data.view,
                        key = event.data.key,
                        category = event.data.category,
                        setting;

                    ranges = view.model.get(category + '_ranges');
                    setting = ranges[key] || {};

                    setting[event.target.name] = event.target.value;
                    ranges[key] = setting;

                    view.model.set(category + '_ranges', ranges);
                };

                ranges = view.model.get(this.category + '_ranges');
                this.showElement.append($('<p>' + label + '</p>'));


                callbackCtx = {view: this.view, key: key, ranges: ranges, category: this.category};
                minVal = ranges[key]?ranges[key]['min']:'';
                var min = $('<input name="min" type="text" value="' + minVal + '" />').change(callbackCtx, handler);
                this.showElement.append(min);

                maxVal = ranges[key]?ranges[key]['max']:'';
                var max = $('<input name="max" type="text" value="' + maxVal + '" />').change(callbackCtx, handler);
                this.showElement.append(max);

            }, {view: view, showElement:showElement, category: this.value});
        }

        if (hideElement) {
            hideElement.toggleClass('hide', true);
        }
        if (showElement){
            showElement.toggleClass('hide', false);
        }

        // set the forecast category and associated dropdown dom on the model
        view.model.set(this.name, this.value);
        view.model.set(view.buckets_dom_field.name, bucket_dom);
    }
})