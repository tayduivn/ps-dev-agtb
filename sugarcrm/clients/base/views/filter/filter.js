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
                    self.buildDropdown();
                }
            });


            this.rendered = true;
        },

        buildDropdown: function() {
            var chosen = app.view.createField({
                def: {
                    name: 'timeperiods',
                    type: 'enum'
                },
                view: this
            }),
                filter = this.$el.html(chosen.getPlaceholder().toString());

            chosen.options.viewName = 'edit';
            chosen.label = 'Forecast Period';
            chosen.def.options = this.model.timeperiods.toJSON();
            chosen.setElement(filter.find("span[sfuuid='" + chosen.sfId + "']"));
            chosen.render();
        }

    });

})(SUGAR.App);