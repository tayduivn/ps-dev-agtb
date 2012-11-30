({
    /**
     * Forecast Inspector
     */
    initialize : function(options) {
        app.view.View.prototype.initialize.call(this, options);
        // Fetch the Model's Data
        this.model.fetch();
    },

    /**
     * Bind to model to make it so that it will re-render once it has loaded.
     */
    bindDataChange : function() {
        this.model.on('change reset', function() {
            this.model.isNotEmpty = true;
            this.render();
        }, this)
    },

    /**
     * Update and Fetch the new id for which ever model is passed in
     *
     * @param newId
     */
    updateModelId : function(newId) {
        this.model.set({id: newId}, {silent: true});

        this.model.fetch();
    }
})