//(function(app) {
//
//    /**
//     * Layout that places components using bootstrap fluid layout divs
//     * @class View.Layouts.ListLayout
//     * @extends View.FluidLayout
//     */
//    app.view.layouts.ListLayout = app.view.Layout.extend({
//        /**
//         * Places a view's element on the page. This shoudl be overriden by any custom layout types.
//         * @param {View.View} comp
//         * @protected
//         * @method
//         */
//        _placeComponent: function(comp, def) {
//            var size = def.size || 12;
//
//            // Helper to create boiler plate layout containers
//            function createLayoutContainers(self) {
//                // Only creates the containers once
//                if (!self.$el.children()[0]) {
//                    self.$el.addClass("container-fluid")
//                        .append($('<div/>').addClass('row-fluid')
//                            .append($('<div/>').addClass("span" + size)
//                                .append($('<div/>').addClass("thumbnail list")
//                        )));
//                }
//            }
//            createLayoutContainers(this);
//
//            // All components of this layout will be placed within the
//            // innermost container div.
//            this.$el.find('.thumbnail').append(comp.el);
//        }
//
//    });
//
//})(SUGAR.App);
