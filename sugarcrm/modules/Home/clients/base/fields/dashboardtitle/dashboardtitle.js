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
    }
})
