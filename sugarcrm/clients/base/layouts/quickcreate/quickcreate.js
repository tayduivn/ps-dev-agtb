({
    initialize: function(options) {
        app.view.Layout.prototype.initialize.call(this, options);

        this.context.on('quickcreate:save', this.save, this);
        this.context.on('quickcreate:dupecheck', this.dupeCheck, this);
    },

    save: function(success, error) {
        this.model.save(null, {
            success: success,
            error: error
        });
    },

    dupeCheck: function(success, error) {
        //TODO: perform duplicate check here
        console.log('dupe check complete');
        success();
    }
})