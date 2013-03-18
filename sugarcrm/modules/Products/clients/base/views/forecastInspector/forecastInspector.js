({
    /**
     * Forecast Inspector
     */
    initialize : function(options) {
        app.view.View.prototype.initialize.call(this, options);

        // Set the show flag on every field.  This will aid is in hiding and showing the fields
        _.each(this.meta.panels[0].fields, function(field) {
           field.show = true;
        });
        // Fetch the Model's Data
        this.model.fetch();
    },

    /**
     * Bind to model to make it so that it will re-render once it has loaded.
     */
    bindDataChange : function() {
        this.model.on('change reset', function() {
            this.model.isNotEmpty = true;

            // handle the display of the best/worst/likely fields
            var cfg = app.metadata.getModule('Forecasts', 'config');

            // check the likely amount
            if(cfg.show_worksheet_likely == 0) {
                this.hideFieldMeta('likely_case');
            }
            // check the best_case
            if(cfg.show_worksheet_best == 0) {
                this.hideFieldMeta('best_case');
            }
            // check the worst_case
            if(cfg.show_worksheet_worst == 0) {
                this.hideFieldMeta('worst_case');
            }

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
    },

    /**
     * Utility method to hide a specific field from displaying when it renders
     *
     * @param field_name
     */
    hideFieldMeta: function(field_name) {
        _.each(this.meta.panels[0].fields, function(field) {
            if(field.name == field_name) {
                field.show = false;
            }
        });
    }
})