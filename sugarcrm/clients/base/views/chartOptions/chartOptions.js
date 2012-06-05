/**
 *
 * @class View.Views.ChartOptionsView
 * @alias SUGAR.App.layout.ChartOptionsView
 * @extends View.View
 */
({

    render: false,

    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize: function(options){
        var self = this,
            model;

        app.view.View.prototype.initialize.call(this, options);

        model = self.layout.getModel('chartoptions');
        model.on('change', function() {
            self.buildDropdowns(this);
        });
    },

    render : function (){
        // only let this render once.  since if there is more than one view on a layout it renders twice
        if(!this.rendered) {
            app.view.View.prototype.render.call(this);
            this.rendered = true;
        }
    },

    buildDropdowns: function(model) {
        var self = this;
        _.each(model.attributes, function(data, key) {
            var chosen = app.view.createField({
                    def: {
                        name: key,
                        type: 'enum'
                    },
                    view: self
                }),
                $chosenPlaceholder = $(chosen.getPlaceholder().toString());

            self.$el.find('#chartType').before($chosenPlaceholder);

            chosen.options.viewName = 'edit';
            chosen.label = model[key].get('label');
            chosen.def.options = model[key].get('options');
            chosen.setElement($chosenPlaceholder);
            chosen.render();
        });
    }

})
