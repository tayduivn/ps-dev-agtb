({
    initialize: function(options) {
        app.view.Layout.prototype.initialize.call(this, options);

        this.context.on('quickcreate:save', this.save, this);
        this.context.on('quickcreate:dupecheck', this.dupeCheck, this);
    },

    save: function(success, error) {
        this.model.save(null, {
            fieldsToValidate: this.getFields(this.module),
            success: success,
            error: error
        });
    },

    dupeCheck: function(noDupFound, dupFound) {
        var self = this;
        var options = {
            limit: this.limit || null,
            params: {
                q: 'first_name=charley'
            },
            fields: this.collection.fields || {},
            success: function(collection) {
                var keys = self.retrieveUserKeys();
                if (collection.models.length > 0) {
                    self.context.trigger('quickcreate:list:toggled', true);
                    // self.showDuplicateAlertMessage();
                    //  self.context.trigger('quickcreate:actions:duplicate', true);
                    dupFound();
                }
                noDupFound();
            },
            error: noDupFound
        };

        this.collection.fetch(options);
    },

    retrieveUserKeys:function () {
        var keys,fields;
debugger;
        fields = this.getFields(this.module);
        keys =[];

        _.each(fields, function (field, index, list) {
            keys.push('ddd');
        });
    }
})