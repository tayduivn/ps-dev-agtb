(function(app) {

    /**
     * View that displays a list of models pulled from the context's collection.
     * @class View.Views.FilterView
     * @alias SUGAR.App.layout.FilterView
     * @extends View.View
     */
    app.view.views.FilterView = app.view.View.extend({

        model: null,

        render: false,

        /**
         * Initialize the View
         *
         * @constructor
         * @param {Object} options
         */
        initialize: function(options){
            app.view.View.prototype.initialize.call(this, options);
            this.model = new app.Model.Filters();
        },

        render : function (){
            var self = this;
            // only let this render once.  since if there is more than one view on a layout it renders twice
            if(this.rendered) return;

            app.view.View.prototype.render.call(this);
            this.model.fetch({
                success: function() {
                    self.buildDropdowns();
                }
            });

            this.rendered = true;
        },

        buildDropdowns: function() {
            var self = this;
            _.each(this.model.attributes, function(data, key) {
                var chosen = app.view.createField({
                        def: {
                            name: key,
                            type: 'enum'
                        },
                        view: self
                    }),
                    filter = self.$el.append(chosen.getPlaceholder().toString());

                chosen.options.viewName = 'edit';
                chosen.label = self.model[key].get('label');
                chosen.def.options = self.model[key].get('options');
                chosen.setElement(filter.find('span[sfuuid="' + chosen.sfId + '"]'));
                chosen.render();
            });
        }

    });

})(SUGAR.App);