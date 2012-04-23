({
    events: {
        'keyup .chzn-search input': 'throttleSearch'
    },

    fieldType: "select",

    initialize: function(options) {
        _.bindAll(this);
        this.app.view.Field.prototype.initialize.call(this, options);
    },

    render: function() {
        var self = this;
        var result = this.app.view.Field.prototype.render.call(this);
        $(this.fieldType + "[name=" + this.name + "]").chosen({no_results_text: "Searching for "}).change(function(event) { // TODO Add labels support
            var selected = $(event.target).find(':selected');
            self.model.set(self.fieldDef.id_name, self.unformat(selected.attr('id')));
            self.model.set(self.fieldDef.name, self.unformat(selected.attr('value')));
        });
        return result;
    },

    throttleSearch: function(e, interval) {
        if (interval === 0) {
            this.search(e);
            return;
        } else {
            interval = 500;
            clearTimeout(this.throttling);
            delete this.throttling;
        }

        this.throttling = setTimeout(this.throttleSearch, interval, e, 0);
    },

    search: function(event) {
        var self = this;
        var collection = app.data.createBeanCollection(this.fieldDef.module);
        collection.fetch({
            params: [
                {key: "q", value: event.target.value}
            ],
            success: function(data) {
                if (data.models.length > 0) {
                    self.options = data.models;
                    self.getOptionsTemplate();
                    var options = self.optionsTemplateC(self);
                    self.$('select').append(options);
                    self.$('select').trigger("liszt:updated");
                } else {
                    //TODO trigger we found nothing
                }
            }

        });
    },

    getOptionsTemplate: function() {
        var templateKey = "sugarField." + this.type + ".options";

        var templateSource = null;

        if (this.meta) {
            templateSource = this.meta.views["options"];
        }
        this.optionsTemplateC = app.template.get(templateKey) || app.template.compile(templateSource, templateKey);
    }
})