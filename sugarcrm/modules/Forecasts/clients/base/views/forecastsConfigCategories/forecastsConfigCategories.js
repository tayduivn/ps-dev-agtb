({
    forecast_categories_field: {},
    buckets_dom_field: {},
    category_ranges_field: {},

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        // sets this.<array_item>_field to the corresponding field metadata, which gets used by the template to render these fields later.
        _.each(['forecast_categories', 'buckets_dom', 'category_ranges'], function(item){
            var fields = _.first(this.meta.panels).fields;

            this[item + '_field'] = function(fieldName, fieldMeta) {
                return _.find(fieldMeta, function(field) { return field.name == this; }, fieldName);
            }(item, fields);

        }, this);

    },

    _renderHtml: function(ctx, options) {
        app.view.View.prototype._renderHtml.call(this, ctx, options);

        // set up the event that happens when a bucket type radio is selected
        // This should set the forecastCategory and buckets_dom field, as well as populate and display the range sliders appropriately.
        this.$el.find(':radio[name="' + this.forecast_categories_field.name + '"]').change({
            view:this
        }, function(evt) {
            var view = evt.data.view;
            var placeholder = view.$el.find('.'+this.value+'RangeElements');

            view.model.set(this.name, this.value);
            view.model.set(view.buckets_dom_field.name, view.buckets_dom_field.options[this.value]);

        });

    }
})