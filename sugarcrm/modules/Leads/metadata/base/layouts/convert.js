({
    /**
     * Parent model that holds all sub-models and logic for performing the convert action
     */
    convertModel: {},

    initialize: function(options) {
        var leadId;

        this.app.view.Layout.prototype.initialize.call(this, options);

        //create parent convert model to hold all sub-models
        leadId = this.context.get('modelId');
        this.convertModel = this.createConvertModel(leadId);

        //build the layout
        this.addTopView();
        this.addSubComponents();
        this.addBottomView();

        //listen for convert button click
        this.context.off("lead:convert", this.convertModel.save);
        this.context.on("lead:convert", this.convertModel.save, this.convertModel);
    },

    /**
     * Creates the parent model that holds all sub-models and logic for performing the convert action
     * @return {*} instance of a backbone model.
     */
    createConvertModel: function (id) {
        var convertModel = Backbone.Model.extend({
            sync: function (method, model, options) {
                myURL = app.api.buildURL('Leads', 'convert', {id:id});
                return app.api.call(method, myURL, model, options);
            },

            addSubModel: function (name, model) {
                this.set(name, model);
            }
        });

        return new convertModel();
    },

    addSubComponents: function() {
        var self = this;

        _.each(this.meta, function(moduleMetadata, moduleName) {
            var context, view;

            var def = {
                'view' : 'accordion-panel',
                'context' : {'module' : moduleName}
            };

            //initialize child context for sub-model
            context = self.context.getChildContext(def.context);
            context.prepare();

            //todo: map field values from lead to sub-model

            //create and add view for sub-model
            view = app.view.createView({
                context: context,
                name: def.view,
                module: context.get("module"),
                layout: self,
                id: def.id
            });
            self.addComponent(view, def);

            //add sub-model to the parent object for later saving
            self.convertModel.addSubModel(moduleName, context.get('model'));
        });
    },

    addTopView: function() {
        var def = {'view' : 'convert-top'};
        this.addComponent(app.view.createView({
            context: this.context,
            name: def.view,
            module: this.context.get("module"),
            layout: this,
            id: this.model.id
        }), def);

    },

    addBottomView: function() {
        var def = {'view' : 'convert-bottom'};
        this.addComponent(app.view.createView({
            context: this.context,
            name: def.view,
            module: this.context.get("module"),
            layout: this,
            id: this.model.id
        }), def);
    },

    loadData: function() {
        var self = this,
            leadModel;

        self.model.fetch({
            success: function() {
                leadModel = self.model;
                _.each(self.meta, function(moduleMetadata, moduleName) {
                    var moduleFieldNames = self.getFieldNames(moduleName);
                    var subModel = self.convertModel.get(moduleName);
                    _.each(moduleFieldNames, function(moduleFieldName) {
                        if (leadModel.has(moduleFieldName)) {
                            subModel.set(moduleFieldName, leadModel.get(moduleFieldName));
                        }
                    });
                    _.each(moduleMetadata.additionalFieldMapping, function(sourceField, targetField) {
                        debugger;
                        if (leadModel.has(sourceField)) {
                            subModel.set(targetField, leadModel.get(sourceField));
                        }
                    });
                    //todo: if moduleName == 'Opportunities' then opportunity.amount = unformat_number(lead.opportunity_amount)
                });
            }
        });
    }
})