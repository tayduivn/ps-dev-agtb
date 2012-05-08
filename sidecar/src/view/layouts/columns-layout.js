(function(app) {

    /**
     * Layout that places views in columns with each view in a column
     * @class View.Layouts.ColumnsLayout
     * @alias SUGAR.App.layout.ColumnsLayout
     * @extends View.Layout
     */
    app.view.layouts.ColumnsLayout = app.view.Layout.extend({
        /**
         * Add a view (or layout) to this layout.
         * @param {View.Layout/View.View} comp Componant to add
         */
        _placeComponent: function(comp) {
            if (!this.$el.children()[0]) {
                this.$el.addClass("container-fluid").append('<div class="row-fluid"></div>');
            }
            //add the column to the layout
            $(comp.el).appendTo(this.$el.find(".row-fluid")[0]);
        }
    });

})(SUGAR.App);