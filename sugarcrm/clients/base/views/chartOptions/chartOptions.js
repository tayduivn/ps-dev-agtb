/**
 *
 * @class View.Views.ChartOptionsView
 * @alias SUGAR.App.layout.ChartOptionsView
 * @extends View.View
 */
({

    bindDataChange: function() {
        var self = this,
            model = this.context.forecasts.chartoptions;

        model.on('change', function() {
            self.buildDropdowns(this);
        });
    },

    buildDropdowns: function(model) {
        var self = this;
        self.$el.find('.chartOptions').empty();
        _.each(model.attributes, function(data, key) {
            var chosen = app.view.createField({
                    def: {
                        name: key,
                        type: 'enum'
                    },
                    view: self
                }),
                $chosenPlaceholder = $(chosen.getPlaceholder().toString()),
                modelData = model.get(key);

            self.$el.find('.chartOptions').append($chosenPlaceholder);

            chosen.options.viewName = 'edit';
            chosen.label = modelData.label;
            chosen.def.options = modelData.options;
            chosen.setElement($chosenPlaceholder);
            chosen.render();

            $chosenPlaceholder.on('change', 'select', function(event, data) {
                //set data in context to trigger events
            });
        });
    }

})
