({
    forecast_categories_field: {},
    buckets_dom_field: {},
    category_ranges_field: {},

    initialize: function(options) {
        var fields;
        app.view.View.prototype.initialize.call(this, options);
        fields = _.first(this.meta.panels).fields;

        // TODO - refactor this to use more functional approach as it is repetitive and can be reduced.
        this.forecast_categories_field = _.find(fields, function(field) { return field.name == 'forecast_categories'; });
        this.buckets_dom_field = _.find(fields, function(field) { return field.name == 'buckets_dom'; });
        this.category_ranges_field = _.find(fields, function(field) { return field.name == 'category_ranges'; });

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

