({
    fieldTag: "select",

    initialize: function(opts) {
        _.bindAll(this);

        this.endpoint = opts.def.endpoint;

        app.view.Field.prototype.initialize.call(this, opts);
        this.optionsTemplateC = app.template.getField("select-api", "options", "Emails");
    },

    _render: function() {
        app.view.Field.prototype._render.call(this);

        var action = (this.endpoint.action) ? this.endpoint.action : null,
            attributes = (this.endpoint.attributes) ? this.endpoint.attributes : null,
            params = (this.endpoint.params) ? this.endpoint.params : null;


        var myURL = app.api.buildURL(this.endpoint.module, action, attributes, params);
        app.api.call('GET', myURL, null,{
                success: this.populateValues,
                error: function(e) {
                    console.log('Failed to retrieve the outbound configs: ' + e);
                }
            }
        );
    },

    populateValues: function(results) {

        var options,
            defaultResult,
            defaultValue,
            self = this;

        if (_.isUndefined(results) || _.isEmpty(results)) {
            return;
        }

        options = this.optionsTemplateC({configs:results});
        this.$(this.fieldTag).html(options);

        //set default value
        defaultResult = _.find(results, function(result) {
            return result.default;
        });
        defaultValue = (defaultResult) ? defaultResult.id : results[0].id;
        if (!this.model.has(this.name)) {
            this.model.set(this.name, defaultValue);
        }

        this.$(this.fieldTag).chosen().change(function(event) {
            var selected = $(event.target).find(':selected');
            //self.model.set(self.def.id_name, self.unformat(selected.attr('id')));
            self.model.set(self.def.name, self.unformat(selected.attr('value')));
            self.context.trigger(self.def.name + ':change', selected);
        });

        this.$('.chzn-search').hide();

    },

    bindDataChange: function() {
        this.model.on('change', function(model) {
            this.$(this.fieldTag).val(model.get(this.name));
        }, this);
    }
})
