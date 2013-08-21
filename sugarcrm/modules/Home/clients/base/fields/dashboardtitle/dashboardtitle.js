({
    events: {
        'click .dropdown-toggle' : 'toggleClicked',
        'click a[data-id]' : 'navigateClicked'
    },
    dashboards: null,
    toggleClicked: function(evt) {
        var self = this;
        if (!_.isEmpty(this.dashboards)) {
            return;
        }
        this.collection.fetch({
            silent: true,
            success: function(collection) {
                var pattern = /^(LBL|TPL|NTC|MSG)_(_|[a-zA-Z0-9])*$/;
                collection.remove(self.model, {silent:true});
                _.each(collection.models, function(model) {
                    if (pattern.test(model.get('name'))) {
                        model.set('name',
                            app.lang.get(model.get('name'), collection.module || null)
                        );
                    }
                });
                self.dashboards = collection;
                var optionTemplate = app.template.getField(self.type, "options", self.module);
                self.$(".dropdown-menu").html(optionTemplate(collection));
            }
        });
    },
    navigateClicked: function(evt) {
        var id = $(evt.currentTarget).data("id");
        this.navigate(id);
    },
    navigate: function(id) {
        this.view.layout.navigateLayout(id);
    },
    /**
     * Inspect the dashlet's label and convert i18n string only if it's concerned
     *
     * @param {String} i18n string or user typed string
     * @return {String} Translated string
     */
    format: function(value) {
        var module = this.context.parent ? this.context.parent.get("module") : this.context.get("module"),
            pattern = /^(LBL|TPL|NTC|MSG)_(_|[a-zA-Z0-9])*$/;
        return pattern.test(value) ? app.lang.get(value, module) : value;
    },

    /**
     * {@inheritdoc}
     *
     * Override template for dashboard title on home page.
     * Need display it as label so use `f.base.detail` template.
     */
    _loadTemplate: function() {
        app.view.Field.prototype._loadTemplate.call(this);

        if (this.context && this.context.get('model') &&
            this.context.get('model').dashboardModule === 'Home'
        ) {
            this.template = app.template.getField('base', this.tplName) || this.template;
        }
    }
})
