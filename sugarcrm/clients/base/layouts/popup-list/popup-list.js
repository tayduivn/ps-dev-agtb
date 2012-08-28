/**
 * Layout that places components using bootstrap fluid layout divs
 * @class View.Layouts.ListLayout
 * @extends View.FluidLayout
 */
({
    /**
     * Places a view's element on the page. This shoudl be overriden by any custom layout types.
     * @param {View.View} comp
     * @protected
     * @method
     */
    _placeComponent: function(comp, def) {
        var size = def.size || 12;

        // Helper to create boiler plate layout containers
        function createLayoutContainers(self) {
            // Only creates the containers once
            if (!self.$el.children()[0]) {
                self.$el
                    .append($('<div/>').addClass('row-fluid')
                    .append($('<div/>').addClass("span" + size)
                    .append($('<div/>').addClass("list")
                )));
            }
        }

        createLayoutContainers(this);

        // All components of this layout will be placed within the
        // innermost container div.
        this.$el.find('.list').append(comp.el);
    }

})