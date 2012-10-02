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
        if (this.meta && this.meta.label) {
            this.title = app.lang.get(this.meta.label, this.context.module);
        }
        this.context.on("subnav:set:title",function(title){
            this.title = title;
            this.render();
        }, this);
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
        var self = this;
        if (this.meta.field) {
            this.model.on(
                "change:"+this.meta.field,
                function() {
                    self.title = self.model.get(this.meta.field);
                    self.render();
                },
                this
          );
        }
    }
})