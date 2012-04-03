(function(app) {

    /**
     * Layout that places components using bootstrap fluid layout divs
     * @class View.Layouts.FluidLayout
     * @extends View.Layout
     */
    app.layout.FluidLayout = app.layout.Layout.extend({
        /**
         * Places a view's element on the page. This shoudl be overriden by any custom layout types.
         * @param {View.View} comp
         * @protected
         * @method
         */
        _placeComponent: function(comp, def) {
            var size = def.size || 4;
            if (!this.$el.children()[0]) {
                this.$el.addClass("container-fluid").append('<div class="row-fluid"></div>');
            }

            //Create a new td and add the layout to it
            $().add("<div></div>").addClass("span" + size).append(comp.el).appendTo(this.$el.find("div.row-fluid")[0]);
        }
    });

})(SUGAR.App);