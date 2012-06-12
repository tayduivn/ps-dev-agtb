/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.FilterView
 * @alias SUGAR.App.layout.FilterView
 * @extends View.View
 */
({

    viewSelector: '.filterOptions',

    bindDataChange: function() {
        var self = this,
            model = this.context.model.filters;

        model.on('change', function() {
            self.buildDropdowns(this);
        });
    },

    buildDropdowns: function(model) {
        var self = this;
        self.$el.find(self.viewSelector).empty();
        _.each(model.attributes, function(data, key) {
            var chosen = app.view.createField({
                    def: {
                        name: key,
                        type: 'enum'
                    },
                    view: self
                }),
                $chosenPlaceholder = $(chosen.getPlaceholder().toString());

            self.$el.find(self.viewSelector).append($chosenPlaceholder);

            chosen.options.viewName = 'edit';
            chosen.label = model[key].get('label');
            if (model[key].get('default')) {
                chosen.model.set(key, model[key].get('default'));
            }
            chosen.def.options = model[key].get('options');
            chosen.setElement($chosenPlaceholder);
            chosen.render();

            if (key === 'timeperiods') {
                self.setupTimePeriodActions($chosenPlaceholder, model[key]);
            }
        });
    },

    setupTimePeriodActions: function($dropdown, model) {
        var self = this;
        $dropdown.on('change', 'select', function(event, data) {
            var label = $(this).find('option:[value='+data.selected+']').text();
            self.context.set('selectedTimePeriod', label);
        });

        if (model.get('default')) {
            $dropdown.find('select').trigger('change', {
                selected: model.get('default')
            });
        }
    }

})
