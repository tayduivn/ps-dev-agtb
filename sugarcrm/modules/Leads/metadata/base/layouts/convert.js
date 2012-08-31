({
    /**
     * Parent model that holds all sub-models and logic for performing the convert action
     */
    convertModel: {},

    /**
     * Initialize convert layout
     * @param options
     */
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
        this.context.off("lead:convert", this.processConvert);
        this.context.on("lead:convert", this.processConvert, this);
    },

    /**
     * Save the convert model and process the responses
     */
    processConvert: function() {
        var self = this;

        app.alert.show('save_edit_view', {level: 'info', title: 'Please Wait. Processing the conversion of the lead.'});
        this.convertModel.save(null, {
            success: function(data) {
                app.alert.dismiss('save_edit_view');
                self.app.navigate(self.context, self.model, 'detail');
                self.displayResults(data);
            }
        });

    },

    /**
     * Displays the results in the detail page.
     */

    displayResults: function(data) {
        var modules = data.attributes.modules;
        var message = [];
        _.each(modules, function(module) {
          var link = (app.router.buildRoute(module.module_name, module.id, 'detail'));

          message.push(module.module_name + ": <a href='#" + link + "'>" + module.name + "</a>");
        });

       app.alert.show('convert-results', {level:"info", title:'Lead Converted', messages:message, autoclose:false});
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

    /**
     * Add sub-views defined by the convert metadata to the layout
     */
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

    /**
     * Add the convert-top view to the layout
     */
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

    /**
     * Add the convert-bottom view to the layout
     */
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

    /**
     * Fetch Leads data to the parent model
     */
    loadData: function() {
        var self = this;
        self.model.fetch({
            success: function() {
                self.populateSubModelsFromLeadsData();
            },
            error: function() {
                //todo: handle error case
            }
        });
    },

    /**
     * Iterate over the sub-models and copy Leads data to the sub-models
     */
    populateSubModelsFromLeadsData: function() {
        var self = this,
            leadModel = self.model;

        //iterate over sub-models
        _.each(self.meta, function(moduleMetadata, moduleName) {
            var moduleFieldNames = self.getFieldNames(moduleName);
            var subModel = self.convertModel.get(moduleName);

            //default field mapping: copy over data if the field name is the same
            _.each(moduleFieldNames, function(moduleFieldName) {
                if (leadModel.has(moduleFieldName)) {
                    subModel.set(moduleFieldName, leadModel.get(moduleFieldName));
                }
            });

            //additional field mapping: copy over data according to the metadata field mapping
            _.each(moduleMetadata.additionalFieldMapping, function(sourceField, targetField) {
                if (leadModel.has(sourceField)) {
                    subModel.set(targetField, leadModel.get(sourceField));
                }
            });

            //todo: if moduleName == 'Opportunities' then opportunity.amount = unformat_number(lead.opportunity_amount)
        });
    }
})