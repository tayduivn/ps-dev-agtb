({
    events: {
        'click .dropdown-toggle' : 'toggleClicked',
        'click a[data-id]' : 'navigateClicked'
    },
    dashboards: null,
    toggleClicked: function(evt) {
        var self = this;
        if(!_.isEmpty(this.dashboards)) {
            return;
        }
        this.collection.fetch({
            silent: true,
            success: function(collection) {
                collection.remove(self.model, {silent:true});
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
    }
})
