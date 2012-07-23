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

    buildDropdowns:function (model) {
        var self = this,
            default_values = {};
        self.$el.find('.chartOptions').empty();

        _.each(model.attributes, function (data, key) {
            var modelData = model.get(key);
            var chosen = app.view.createField({
                    def:{
                        name:key,
                        type:'enum',
                        options: modelData.options
                    },
                    view:self
                }),
                $chosenPlaceholder = $(chosen.getPlaceholder().toString());

            self.$el.find(self.viewSelector).append($chosenPlaceholder);

            chosen.options.viewName = 'drawer';
            chosen.label = modelData.label;

            if (modelData.default) {
                chosen.model.set(key, modelData.default);
                default_values.id = modelData.default;
                default_values.label = modelData.options[modelData.default];
            }
            chosen.setElement($chosenPlaceholder);
            chosen.render();

            if (key === 'group_by') {
                self.handleGroupByEvents($chosenPlaceholder);
            } else if (key === 'dataset') {
                self.handleDataSetEvents($chosenPlaceholder);
            }

        });
    },

    handleGroupByEvents: function($dropdown) {
        var self = this;
        $dropdown.on('change', 'select', function(event, data) {
            var label = $(this).find('option:[value='+data.selected+']').text();
            var id = $(this).find('option:[value='+data.selected+']').val();
            self.context.forecasts.set('selectedGroupBy', {"id": id, "label": label});
        });
    },

    handleDataSetEvents: function($dropdown) {
        var self = this;
        $dropdown.on('change', 'select', function(event, data) {
            var label = $(this).find('option:[value='+data.selected+']').text();
            var id = $(this).find('option:[value='+data.selected+']').val();
            self.context.forecasts.set('selectedDataSet', {"id": id, "label": label});
        });
    }

})
