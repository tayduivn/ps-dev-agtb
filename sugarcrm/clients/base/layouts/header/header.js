({
    /**
     * Listen to events to resize the header to fit the browser width
     * @param options
     */
    initialize: function(options) {
        this.app.view.Layout.prototype.initialize.call(this, options);

        this.app.events.on("app:sync:complete", this.resize, this);
        this.app.events.on("app:view:change", this.resize, this);

        var resize = _.bind(this.resize, this);
        $(window)
            .off("resize", resize)
            .on("resize", resize);
    },

    /**
     * Places all components within this layout inside nav-collapse div
     * @param component
     * @private
     */
    _placeComponent: function(component) {
        this.$el.find('.nav-collapse').append(component.$el);
    },

    /**
     * Calculates the width that the module list should resize to and triggers an event
     * that tells the module list to resize
     */
    resize: function() {
        var maxMenuWidth = this.$(".navbar-inner > .container-fluid").width() - 100; //100px: spacing for submegamenu, padding and border lines

        var totalWidth = 0;
        _.each(this._components, function(component) {
            if (component.name !== 'modulelist') {
                totalWidth += component.$el.children().first().width();
            }
        });

        this.trigger('view:resize', maxMenuWidth - totalWidth);
    }
})
