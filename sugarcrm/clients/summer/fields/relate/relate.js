({
    events: {
        'keyup .chzn-search input': 'throttleSearch'
    },
    /**
     * Initializes field and binds all function calls to this
     * @param {Object} options
     */
    initialize: function(options) {
        _.bindAll(this);
        this.app.view.Field.prototype.initialize.call(this, options);
        this.optionsTemplateC = this.app.template.getField(this.type, "options");
    },
    /**
     * Renders relate field
     */
    _render: function() {
        var self = this;
        var result = this.app.view.Field.prototype._render.call(this);
        this.$(".relateEdit").chosen({
            no_results_text: "Searching for " // TODO Add labels support
        }).change(function(event) {
            var selected = $(event.target).find(':selected');
            self.model.set(self.def.id_name, self.unformat(selected.attr('id')));
            self.model.set(self.def.name, self.unformat(selected.attr('value')));
        });
        return result;
    },
    /**
     * Throttles search ajax
     * @param {Object} e event object
     * @param {Integer} interval interval to throttle
     */
    throttleSearch: function(e, interval) {
        if (interval === 0 && e.target.value != "") {
            this.search(e);
            return;
        } else {
            interval = 500;
            clearTimeout(this.throttling);
            delete this.throttling;
        }

        this.throttling = setTimeout(this.throttleSearch, interval, e, 0);
    },
    /**
     * Searches for related field
     * @param event
     */
    search: function(event) {
        var self = this;
        var collection = app.data.createBeanCollection(this.def.module);
        collection.fetch({
            params: {basicSearch:event.target.value},  // TODO update this to filtering API
            success: function(data) {
                if (data.models.length > 0) {
                    self.options = data.models;
                    var options = self.optionsTemplateC(self);
                    self.$('select').html(options);
                    self.$('select').trigger("liszt:updated");
                } else {
                    //TODO trigger error we found nothing
                }
            }

        });
    }

})