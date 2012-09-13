({
    initialize: function(options) {
        this.app.view.Layout.prototype.initialize.call(this, options);
        this.$el.addClass("tab-content");
        this.addSubComponents();
    },

    /**
     * Add sub-views defined by the convert metadata to the layout
     */
    addSubComponents: function() {
        var self = this;

        _.each(this.meta, function(moduleMetadata, moduleName) {
            var context;

            var def = {
                'view' : 'convert-wizard-pane',
                'context' : {'module' : moduleName}
            };

            //initialize child context for sub-model
            context = self.context.getChildContext(def.context);
            context.prepare();

            self.addComponent(app.view.createView({
                context: context,
                name: def.view,
                module: self.context.get("module"),
                submodule: moduleName,
                layout: self,
                id: def.id
            }), def);

            //add sub-model to the parent object for later saving
            self.context.convertModel.addSubModel(moduleName, context.get('model'));
        });
    },

    /**
     * Add a view (or layout) to this layout.
     * @param {View.Layout/View.View} comp Component to add
     */
    _placeComponent: function(comp) {
        if (!this.$el.children()[0]) {
            comp.$el.addClass("active");
        }
        this.$el.append(comp.$el);
    }

})