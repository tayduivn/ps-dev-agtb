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
        //TODO: perform duplicate check here
        console.log('dupe check complete');
        var options = {
            limit: this.limit || null,
            params: {
                q: 'kim'
            },
            fields: this.collection.fields || {},
            success: function(collection) {
              //  self.retrieveUserKeys();
                if (collection.models.length > 0) {
                    self.context.trigger('quickcreate:list:toggled', true);
                   // self.showDuplicateAlertMessage();
                  //  self.context.trigger('quickcreate:actions:duplicate', true);
                }
                dupFound();
            },
            error: noDupFound
        };

        this.collection.fetch(options);
    },

    retrieveUserKeys:function () {
        var fields = this.getFields(this.module);
        debugger;
        _.each(fields, function (fieldErrors, fieldName) {
            //retrieve the field by name
            var field = self.getField(fieldName);
            if (field) {
                var controlGroup = field.$el.parents('.control-group:first');

                if (controlGroup) {
                    //Clear out old messages
                    controlGroup.find('.add-on').remove();
                    controlGroup.find('.help-block').html("");

                    controlGroup.addClass("error");
                    controlGroup.find('.controls').addClass('input-append');
                    _.each(fieldErrors, function (errorContext, errorName) {
                        controlGroup.find('.help-block').append(self.app.error.getErrorString(errorName, errorContext));
                    });
                    controlGroup.find('.controls input:last').after('<span class="add-on"><i class="icon-exclamation-sign"></i></span>');
                }
            }
        });
    }
})