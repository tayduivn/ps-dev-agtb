(function(app) {

    /**
     * Layout that places views in the right column
     * @class View.Layouts.RightsideLayout
     * @alias SUGAR.App.layout.RightsideLayout
     * @extends View.Layout
     */
    app.view.layouts.RightsideLayout = app.view.Layout.extend({
        /**
         * Add a view (or layout) to this layout.
         * @param {View.Layout/View.View} comp Componant to add
         */
        _placeComponent: function(comp) {

            if (!this.$el.children()[0]) {
                this.$el.addClass("span5").addClass("rightside");
            }
            //add the layout to the column
            $(comp.el).appendTo(this.$el);
        }
    });

})(SUGAR.App);