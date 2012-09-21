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
                buttons: this.meta.buttons
            }
        });
        this.context.set('subnavModel', this.subnavModel);
    },

    /**
     * Render and push down the view below
     * @private
     */
    _render: function() {
        var next, newMarginTop;

        this.app.view.View.prototype._render.call(this);

        //push down the view below by the subnav height
        next = this.$el.next();
        newMarginTop = parseInt(next.css('margin-top'), 10) + this.$el.find('.subnav').height();
        next.css('margin-top', newMarginTop + 'px');
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
        window.history.back();
    },

    /**
     * Handle click on the edit button
     */
    edit: function() {
        this.app.navigate(this.context, this.model, "edit", {trigger:true});
    },

    /**
     * Only re-render the view. Do not push down the view below.
     */
    bindDataChange: function() {
        this.subnavModel.on("change", this.app.view.View.prototype._render, this);
    }
})