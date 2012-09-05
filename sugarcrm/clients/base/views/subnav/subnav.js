({
    events: {
        'click [name=save_button]': 'save',
        'click [name=cancel_button]': 'cancel',
        'click [name=edit_button]': 'edit'
    },

    /**
     * Initialize the view and prepare the model with default button metadata
     * for the current layout.
     */
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        this.subnavModel = new Backbone.Model();
        this.subnavModel.set({
            meta: {
                buttons: this.meta.buttons[this.context.get('layout')]
            }
        });
        this.context.set('subnavModel', this.subnavModel);
    },

    /**
     * Render and fix the subnav to the top
     * @private
     */
    _render: function() {
        var headerHeight, next, newMarginTop;

        this.app.view.View.prototype._render.call(this);

        if (this.$el.css('position') !== 'fixed') {
            headerHeight = $('#header .navbar').height();

            //place subnav below the header
            this.$el.css({
                position: 'fixed',
                width: '100%',
                top: headerHeight
            });

            //push down the view below by the subnav height
            next = this.$el.next();
            newMarginTop = parseInt(next.css('margin-top'), 10) + this.$el.height();
            next.css('margin-top', newMarginTop + 'px');
        }
    },

    /**
     * Handle click on the save button
     */
    save: function() {
        this.context.trigger("subnav:save");
    },

    /**
     * Handle click on the cancel button
     */
    cancel: function() {
        this.app.navigate(this.context, this.model, 'detail');
    },

    /**
     * Handle click on the edit button
     */
    edit: function() {
        this.app.navigate(this.context, this.model, "edit", {trigger:true});
    },

    bindDataChange: function() {
        this.subnavModel.on("change", this.render, this);
    }
})