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

        this.selection = this.context.forecasts.config.get('forecast_categories');

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
        // finds all radiobuttons with this name
        var elements = this.$el.find(':radio[name="' + this.forecast_categories_field.name + '"]');

        // apply change handler to all elements
        elements.change({
            view:this
        }, this.selectionHandler);

        // of the elements find the one that is checked
        _.each(elements, function(el) {
            if($(el).prop('checked')) {
                // manually trigger the handler on the checked element so that it will render
                // for the default/previously set value
                $(el).triggerHandler("change");
            }
        });
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

        view.fieldRanges = {}; // a placeholder for the individual ranges that will be used to build the range setting

        if (showElement.children().length == 0) {
            // add the things here...
            _.each(app.lang.getAppListStrings(bucket_dom), function(label, key) {
                var rangeField,
                    model = new Backbone.Model(),
                    fieldSettings;

                model.set(key, this.view.model.get(this.category + '_ranges')[key]);
                fieldSettings = {
                    view: this.view,
                    def: _.find(
                        _.find(
                            _.first(this.view.meta.panels).fields,
                            function(field) {
                                return field.name ==  'category_ranges';
                            }
                        ).ranges,
                        function(range) {
                            return range.name == this.key
                        },
                        {key: key}
                    ),
                    viewName:'edit',
                    context: this.view.context,
                    module: this.view.module,
                    model: model,
                    meta: app.metadata.getField('range')
                };

                rangeField = app.view.createField(fieldSettings);
                this.showElement.append(rangeField.el);
                rangeField.render();
                // now give the view a way to get at this field's model, so it can be used to set the value on the
                // real model.
                 view.fieldRanges[key] = rangeField;

            }, {view: view, showElement:showElement, category: this.value});
        }



        if (hideElement) {
            hideElement.toggleClass('hide', true);
        }
        if (showElement){
            showElement.toggleClass('hide', false);
        }

        view.connectSliders(view.fieldRanges, this.value);


        // set the forecast category and associated dropdown dom on the model
        view.model.set(this.name, this.value);
        view.model.set(view.buckets_dom_field.name, bucket_dom);
    },

    connectSliders: function(sliders, category) {
        if(category == 'show_binary') {
            sliders.include.sliderDelegate = sliders.exclude.moveSlider;
            sliders.include.model.on('change', function(includeModel) {
                var excludeVal = this.model.get('exclude');
                excludeVal.max = includeModel.get('include').min - 1;
                // theshark - remove
                console.log('exclude max: ' + excludeVal.max);
                this.model.set({exclude: excludeVal}, {silent: true});
            }, sliders.exclude);
            sliders.exclude.model.on('change', function(excludeModel) {
                var includeVal = this.model.get('include');
                includeVal.min = excludeModel.get('exclude').max + 1;
                // theshark - remove
                console.log('include min: ' + includeVal.min);
                this.model.set({include: includeVal}, {silent: true});
            }, sliders.include);
        } else if(category == 'show_buckets') {
//            sliders.include.model.on('change', function() {
//
//            });
//            sliders.upside.model.on('change', function() {
//
//            });
//            sliders.exclude.model.on('change', function() {
//
//            });
        }
    }
})