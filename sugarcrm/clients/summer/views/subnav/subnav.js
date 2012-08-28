({
    events: {
        'click [name=save_button]': 'saveModel'
    },
    /**
     * Listens to the app:view:change event and show or hide the subnav
     */
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.context.set('subnavModel', new Backbone.Model());
        this.subnavModel = this.context.get('subnavModel');
    },
    saveModel: function() {
        this.context.trigger("subnav:save");
    },

    bindDataChange: function() {
        if (this.subnavModel) {
            this.subnavModel.on("change", this.render, this);
        }
    }
})
