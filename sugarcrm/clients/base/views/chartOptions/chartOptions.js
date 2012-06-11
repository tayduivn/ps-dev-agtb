/**
 *
 * @class View.Views.ChartOptionsView
 * @alias SUGAR.App.layout.ChartOptionsView
 * @extends View.View
 */
({

    bindDataChange: function() {
        var self = this,
            model = this.context.model.chartoptions;

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
                $chosenPlaceholder = $(chosen.getPlaceholder().toString());

            self.$el.find('.chartOptions').append($chosenPlaceholder);

            chosen.options.viewName = 'edit';
            chosen.label = model[key].get('label');
            chosen.def.options = model[key].get('options');
            chosen.setElement($chosenPlaceholder);
            chosen.render();

            $chosenPlaceholder.on('change', 'select', function(event, data) {
                model[key].set('value', data.selected);
            });
        });
    }

})
