(function(app) {

    /**
     * Layout that places views in a table with each view in its own column
     * @class View.Layouts.ColumnsLayout
     * @alias SUGAR.App.layout.ColumnsLayout
     * @extends View.Layout
     */
    app.view.layouts.ColumnsLayout = app.view.Layout.extend({
        //column layout uses a table for columns and prevent wrapping
        /**
         * Add a view (or layout) to this layout.
         * @param {View.Layout/View.View} comp Componant to add
         */
        _placeComponent: function(comp) {
            if (!this.$el.children()[0]) {
                this.$el.append("<table><tbody><tr></tr></tbody></table>");
            }
            //Create a new td and add the layout to it
            $().add("<td></td>").append(comp.el).appendTo(this.$el.find("tr")[0]);
        }
    });

})(SUGAR.App);