/**
 *
 * @class View.Views.ChartOptionsView
 * @alias SUGAR.App.layout.ChartOptionsView
 * @extends View.View
 */
({

    viewSelector: '.chartOptions',

    bindDataChange: function() {
        var self = this,
            model = this.context.forecasts.chartoptions;

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

            if (key === 'groupby') {
                self.handleGroupByEvents($chosenPlaceholder);
            } else if (key === 'dataset') {
                self.handleDataSetEvents($chosenPlaceholder);
            }

            if (modelData.default) {
                $chosenPlaceholder.find('select').trigger('change', {
                    selected: modelData.default
                });
            }
        });
    },

    handleGroupByEvents: function($dropdown) {
        var self = this;
        $dropdown.on('change', 'select', function(event, data) {
            var label = $(this).find('option:[value='+data.selected+']').text();
            var id = $(this).find('option:[value='+data.selected+']').val();
            self.context.set('selectedGroupBy', {"id": id, "label": label});
        });
    },

    handleDataSetEvents: function($dropdown) {
        var self = this;
        $dropdown.on('change', 'select', function(event, data) {
            var label = $(this).find('option:[value='+data.selected+']').text();
            var id = $(this).find('option:[value='+data.selected+']').val();
            self.context.set('selectedDataSet', {"id": id, "label": label});
        });
    }

})
