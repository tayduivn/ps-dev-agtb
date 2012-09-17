({
    /**
     * Initialize convert layout
     * @param options
     */
    initialize: function(options) {
        this.app.view.Layout.prototype.initialize.call(this, options);
        this.$el.addClass("tabbable");
        this.addWizardNav();
        this.addWizardPanesLayout();
    },

    /**
     * Add the convert-nav view to the layout
     */
    addWizardNav: function() {
        var def = {'view' : 'convert-wizard-nav'};
        this.addComponent(app.view.createView({
            context: this.context,
            name: def.view,
            module: this.context.get("module"),
            layout: this,
            id: this.model.id
        }), def);
    },

    addWizardPanesLayout: function() {
        this.addComponent(app.view.createLayout({
            context: this.context,
            name: 'convert-wizard-panes',
            module: this.context.get("module"),
            meta: this.meta
        }));
    },
})