(function(app) {

    app.view.views.ListView = app.view.View.extend({
        initialize: function(options) {
            // Mobile shows only the first two fields
            options.meta.panels[0].fields.length = 2;
            app.view.View.prototype.initialize.call(this, options);
        },

        addOne: function(model) {
            app.logger.debug('ADD ONE!');
            var fieldId = app.view.getFieldId();
            var item = Handlebars.helpers.listItem(model, this, this.meta.panels[0].fields);
            this.$('.items').append(item.toString());

            for(var i = fieldId + 1; i <= app.view.getFieldId(); ++i) {
                this._renderField(this.fields[i]);
            }
        },

        removeOne: function(model) {
            app.logger.debug('REMOVE ONE!');
            this.$("#" + model.module + model.id).remove();
        },

        bindDataChange: function() {
            if (this.collection) {
                this.collection.on("reset", this.render, this);
                this.collection.on('add', this.addOne, this);
                this.collection.on('remove', this.removeOne, this);
            }
        }

    });

})(SUGAR.App);