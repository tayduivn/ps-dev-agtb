(function(app, $) {

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
            this.model.on('change', this.onChange, this);
        },

        render : function (){
            var self = this;
            // only let this render once.  since if there is more than one view on a layout it renders twice
            if(this.rendered) return;

            app.view.View.prototype.render.call(this);
            this.model.fetch();

            this.rendered = true;
        },

        onChange: function() {
            var field = app.view.createField({
                def: {
                    type: 'enum'
                },
                view: this,
                model: this.model.timeperiods
            });

            var placeholder = field.getPlaceholder();
            var dropdown = this.buildForecastPeriodDropdown();
            var filter = this.$el.html(placeholder.toString());
            field.setElement(filter.find("span[sfuuid='" + field.sfId + "']"));
            field.options.viewName = "edit";
            field.render();
        },

        buildForecastPeriodDropdown: function() {
            var periods = this.model.get('timeperiods'),
                result = [];
            $.each(periods, function(index, value) {
                result.push('<option value="');
                result.push(index);
                result.push('">');
                result.push(value);
                result.push('</option> ');
            });
            return result.join('');
        }

    });

})(SUGAR.App, jQuery);