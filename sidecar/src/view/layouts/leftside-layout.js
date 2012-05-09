(function(app) {

    /**
     * Layout that places views in the left column
     * @class View.Layouts.LeftsideLayout
     * @alias SUGAR.App.layout.LeftsideLayout
     * @extends View.Layout
     */
    app.view.layouts.LeftsideLayout = app.view.Layout.extend({
        /**
         * Add a view (or layout) to this layout.
         * @param {View.Layout/View.View} comp Componant to add
         */
        _placeComponent: function(comp) {

            if (!this.$el.children()[0]) {
                this.$el.addClass("span7");
            }
            //add the layout to the column
            $(comp.el).appendTo(this.$el);
        }
    });

})(SUGAR.App);