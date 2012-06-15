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
            model = this.context.model.forecasts.filters;

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
                $chosenPlaceholder = $(chosen.getPlaceholder().toString()),
                modelData = model.get(key);

            self.$el.find(self.viewSelector).append($chosenPlaceholder);

            chosen.options.viewName = 'edit';
            chosen.label = modelData.label;
            if (modelData.default) {
                chosen.model.set(key, modelData.default);
            }
            if (modelData.multiselect) {
                chosen.def.multi = modelData.multiselect;
            }
            chosen.def.options = modelData.options;
            chosen.setElement($chosenPlaceholder);
            chosen.render();

            if (key === 'timeperiods') {
                self.setupTimePeriodActions($chosenPlaceholder, modelData);
            } else if (key == 'stages') {
                self.setupStageActions($chosenPlaceholder, modelData);
            }
        });
    },

    setupStageActions: function($multiselect, modelData) {
        var self = this;
        $multiselect.on('change', 'select', function(event, data) {
            //Get the selected stages
            var selected = new Array();
            $('select[name="stages"]').children(':selected').each(function() {
                var value = $(this).val();
                if(trim(value) != '')
                {
                    selected.push(value);
                }
            });
            self.context.set('selectedStages', selected);
        });
    },

    setupTimePeriodActions: function($dropdown, modelData) {
        var self = this;

        $dropdown.on('change', 'select', function(event, data) {
            var label = $(this).find('option:[value='+data.selected+']').text();
            var id = $(this).find('option:[value='+data.selected+']').val();
            self.context.set('selectedTimePeriod', {"id": id, "label": label});
        });

        if (modelData.default) {
            $dropdown.find('select').trigger('change', {
                selected: modelData.default
            });
        }
    }

})
