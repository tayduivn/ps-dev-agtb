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
     * a placeholder for the individual range sliders that will be used to build the range setting
     */
    fieldRanges: {},

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

    _render: function() {
        app.view.View.prototype._render.call(this);

        this._addForecastCategorySelectionHandler();

        return this;
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

        if (showElement.children().length == 0) {
            // add the things here...
            _.each(app.lang.getAppListStrings(bucket_dom), function(label, key) {
                var rangeField,
                    model = new Backbone.Model(),
                    fieldSettings;

                // get the value in the current model and use it to display the slider
                model.set(key, this.view.model.get(this.category + '_ranges')[key]);

                // build a range field
                fieldSettings = {
                    view: this.view,
                    def: _.find(
                        _.find(
                            _.first(this.view.meta.panels).fields,
                            function(field) {
                                return field.name == 'category_ranges';
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

                // this gives the field a way to save to the view's real model.  It's wrapped in a closure to allow us to
                // ensure we have everything when switching contexts from this handler back to the view.
                rangeField.sliderDoneDelegate = function(category, key, view) {
                    return function (value) {
                        view.updateRangeSettings(category, key, value);
                    };
                }(this.category, key, this.view);

            }, {view: view, showElement:showElement, category: this.value});
        }

        if (hideElement) {
            hideElement.toggleClass('hide', true);
        }
        if (showElement){
            showElement.toggleClass('hide', false);
        }

        // use call to set context back to the view for connecting the sliders
        view.connectSliders.call(view, this.value, view.fieldRanges);

        // set the forecast category and associated dropdown dom on the model
        view.model.set(this.name, this.value);
        view.model.set(view.buckets_dom_field.name, bucket_dom);
    },

    /**
     * updates the setting in the model for the specific range types.
     * This gets triggered when the range after the user changes a range slider
     * @param category - the selected category: `show_buckets` or `show_binary`
     * @param range - the range being set, i. e. `include`, `exclude` or `upside` for `show_buckets` category
     * @param value - the value being set
     */
    updateRangeSettings: function(category, range, value) {
        var catRange = category + '_ranges',
            setting = this.model.get(catRange);
        setting[range] = value;
        this.model.unset(catRange, {silent: true});
        this.model.set(catRange, setting);
    },

    /**
     * Graphically connects the sliders to the one below, so that they move in unison when changed, based on category.
     * @param category - the forecasts category that was selected, i. e. 'show_binary' or 'show_buckets'
     * @param sliders - an object containing the sliders that have been set up in the page.  This is created in the
     * selection handler when the user selects a category type.
     */
    connectSliders: function(category, sliders) {
        if(category == 'show_binary') {
            sliders.include.sliderChangeDelegate = function (value) {
                sliders.exclude.$el.find(sliders.exclude.fieldTag).noUiSlider('move', {to: value.min-1});
            };
            sliders.exclude.sliderChangeDelegate = function (value) {
                sliders.include.$el.find(sliders.include.fieldTag).noUiSlider('move', {to: value.max+1});
            }
        } else if (category == 'show_buckets') {
            sliders.include.sliderChangeDelegate = function (value) {
                sliders.upside.$el.find(sliders.upside.fieldTag).noUiSlider('move', {handle: 'upper', to: value.min-1});
                if(value.min <= sliders.upside.$el.find(sliders.upside.fieldTag).noUiSlider('value')[0] + 1) {
                    sliders.upside.$el.find(sliders.upside.fieldTag).noUiSlider('move', {handle: 'lower', to: value.min-2});
                }
                if(value.min <= sliders.exclude.$el.find(sliders.exclude.fieldTag).noUiSlider('value')[1] + 2) {
                    sliders.exclude.$el.find(sliders.exclude.fieldTag).noUiSlider('move', {to: value.min-3});
                }
            };
            sliders.upside.sliderChangeDelegate = function (value) {
                sliders.include.$el.find(sliders.include.fieldTag).noUiSlider('move', {to: value.max+1});
                sliders.exclude.$el.find(sliders.exclude.fieldTag).noUiSlider('move', {to: value.min-1});
            };
            sliders.exclude.sliderChangeDelegate = function (value) {
                sliders.upside.$el.find(sliders.upside.fieldTag).noUiSlider('move', {handle: 'lower', to: value.max+1});
                if(value.max >= sliders.upside.$el.find(sliders.upside.fieldTag).noUiSlider('value')[1] - 1) {
                    sliders.upside.$el.find(sliders.upside.fieldTag).noUiSlider('move', {handle: 'upper', to: value.max+2});
                }
                if(value.max >= sliders.include.$el.find(sliders.include.fieldTag).noUiSlider('value')[0] - 2) {
                    sliders.include.$el.find(sliders.include.fieldTag).noUiSlider('move', {to: value.max+3});
                }
            }
        }

    }
})