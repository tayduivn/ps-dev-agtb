({
    /**
     * Initialize convert layout
     * @param options
     */
    initialize: function(options) {
        var leadId;

        this.app.view.Layout.prototype.initialize.call(this, options);

        //create parent convert model to hold all sub-models
        leadId = this.context.get('modelId');
        this.context.convertModel = this.createConvertModel(leadId);

        //build the layout
        this.addWizardTopView();
        this.addWizardLayout();

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
        this.context.convertModel.save(null, {
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


    addWizardTopView: function() {
        var def = {'view' : 'convert-wizard-top'};
        this.addComponent(app.view.createView({
            context: this.context,
            name: def.view,
            module: this.context.get("module"),
            layout: this,
            id: this.model.id
        }), def);
    },

    /**
     * Add the convert-wizard sub-layout
     */
    addWizardLayout: function() {
        this.addComponent(app.view.createLayout({
            context: this.context,
            name: 'convert-wizard',
            module: this.context.get("module"),
            meta: this.meta
        }));
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
            var subModel = self.context.convertModel.get(moduleName);

            //field mappings: copy over data according to the metadata field mapping
            _.each(moduleMetadata.fieldMapping, function(sourceField, targetField) {
                if (leadModel.has(sourceField)) {
                    subModel.set(targetField, leadModel.get(sourceField));
                }
            });
        });
    }
})