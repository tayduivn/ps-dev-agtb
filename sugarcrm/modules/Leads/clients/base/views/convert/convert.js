({
    events: {
        'click [name=convert_continue_button]': 'showNextStep'
    },

    /**
     * Initialize convert layout
     * @param options
     */
    initialize: function(options) {
        var leadId;

        app.view.View.prototype.initialize.call(this, options);

        var linkedListNode = function(key){
            return {
                key: key,
                next: null
            };
        };

        var linkedList = function(){
            var head = null;
            var next = null;
            var insert = function(node){
                if(head == null) {
                    head = node;
                }
                if(next != null) {
                    next.next = node;
                }

                next = node;
            };

            var search = function(key){
                var node = head;
                while (node !== null && node.key !== key){
                    node = node.next;
                };
                return node;
            };

            var getHead = function(){
                return head;
            };

            return {
                insert: insert,
                search: search,
                getHead: getHead
            };
        };

        var convertSteps = new linkedList();
        _.each(this.meta.modules, function(element, index, list){
            convertSteps.insert(new linkedListNode(element.module));
        });

        this.context.steps = convertSteps;

        //create parent convert model to hold all sub-models
        leadId = this.context.get('modelId');
        this.context.convertModel = this.createConvertModel(leadId);

        //listen for convert button click
        this.context.off("lead:convert", this.processConvert);
        this.context.on("lead:convert", this.processConvert, this);

    },

    render: function() {
        app.view.View.prototype.render.call(this);

        var self = this,
            moduleName = this.context.steps.getHead().key;

        $('.accordion').on('show', function (e) {
            self.initiateContinue(e);
        });

        this.initiateAccordion();
        this.initiateSubComponents();
        this.populateSubModelsFromLeadsData();
        this.showAccordion(moduleName);
    },

    insertViewInAccordionBody: function(moduleName, contentType, def) {
        //initialize child context for sub-model
        var self = this,
            context = self.context.getChildContext(def.context);

        context.prepare();

        var view = app.view.createView({
            context: context,
            name: def.view,
            module: moduleName,
           // submodule: moduleName,
            layout: self
        });

        var dupView = $('#collapse' + moduleName).find('.' + contentType + 'View');
        dupView.append(view.$el);
        view.render();

        if(contentType === 'duplicate') {
            view.loadData();
        }

        return view;
    },

    /**
     * Check for possible duplicates before creating a new record
     * @param callback
     */
    initiateContinue: function(callback) {
        var self = this;
    /*
        async.waterfall([
            _.bind(this.showNextStep, this)
        ], function(error) {
            if (error) {
                console.log("Saving failed.");
                //TODO: handle error
            } else {
                callback();
            }
        });
        */

        this.context.currentStep = this.context.steps.search(callback.target.dataset.module);

        var result = true;

        return result;
    },

    initiateAccordion: function() {
        _.each(this.meta.modules, function(element, index, list) {
            var accordionBody = '#collapse' + element.module;
            $(accordionBody).collapse({
                parent: '#convert-accordion'
            });
        });
    },

    /**
     * Add sub-views defined by the convert metadata to the layout
     */
    initiateSubComponents: function() {
        var self = this;

        _.each(this.meta.modules, function(element, index, list) {
            var duplicateView,
                recordView,
                def = {
                    'view' : 'list',
                    'context' : {'module' : element.module}
                };

            duplicateView = self.insertViewInAccordionBody(element.module, 'duplicate', def);

            def = {
                'view' : 'edit',
                'context' : {'module' : element.module}
            };

            recordView = self.insertViewInAccordionBody(element.module, 'record', def);

            //add sub-model to the parent object for later saving
            self.context.convertModel.addSubModel(element.module, {duplicateView: duplicateView.context.get('model')});
        });
    },

    showNextStep: function() {
        var currentStep = this.context.currentStep;

        if(!_.isEmpty(currentStep.next)) {
            this.showAccordion(currentStep.next.key);
        }
    },

    showAccordion: function(moduleName) {
        var accordionBody = '#collapse' + moduleName;

        $(accordionBody).collapse('show');
        $(accordionBody).css('height', 'auto');
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
                app.navigate(self.context, self.model, 'detail');
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
     * Iterate over the sub-models and copy Leads data to the sub-models
     */
    populateSubModelsFromLeadsData: function() {
        var self = this,
            leadModel = self.model;

        debugger;
        //iterate over sub-models
        _.each(self.meta.modules, function(element, index, list) {
            var subModel = self.context.convertModel.get(element.module);

            //field mappings: copy over data according to the metadata field mapping
            _.each(element.fieldMapping, function(sourceField, targetField) {
                if (leadModel.has(sourceField)) {
                    subModel.set(targetField, leadModel.get(sourceField));
                }
            });
        });
    }

    /*
    *         app.view.View.prototype.initialize.call(this, options);
     this.fallbackFieldTemplate = "edit";
     this.$el.addClass('tab-pane');
     this.$el.attr('id', 'tab-pane-' + options.submodule);
     this.meta = app.metadata.getView(options.submodule, 'edit') || {};
     */
})
